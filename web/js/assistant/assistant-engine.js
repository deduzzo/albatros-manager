/**
 * Assistente AI - Engine principale
 *
 * Orchestratore che collega la chat UI, il Web Worker WebLLM
 * e le tool definitions per l'assistente AI del gestionale.
 */

import { AssistantChat } from './assistant-chat.js';
import { toolDefinitions, executeToolCall, keywordRoute, extractParams } from './assistant-tools.js';

const DEFAULT_MODEL = 'Qwen2.5-3B-Instruct-q4f16_1-MLC';
const STORAGE_MODEL_KEY = 'assistant_selected_model';
const GENERATION_TIMEOUT = 30000; // 30 secondi

// Modelli che supportano function calling in WebLLM
const FUNCTION_CALLING_MODELS = [
    'Hermes-2-Pro-Llama-3-8B-q4f16_1-MLC',
    'Hermes-2-Pro-Llama-3-8B-q4f32_1-MLC',
    'Hermes-2-Pro-Mistral-7B-q4f16_1-MLC',
    'Hermes-3-Llama-3.1-8B-q4f32_1-MLC',
    'Hermes-3-Llama-3.1-8B-q4f16_1-MLC',
];

class AssistantEngine {
    constructor() {
        this.worker = null;
        this.chat = null;
        this.isFullscreen = false;
        this.isReady = false;
        this.isGenerating = false;
        this.systemPrompt = '';
        this.conversationHistory = [];
        this._generationTimer = null;
    }

    // ── Initialization ─────────────────────────────────────────────────

    async init() {
        // Verifica supporto WebGPU
        const hasWebGPU = await this._checkWebGPU();

        // Determina modalita (fullscreen o widget)
        this.isFullscreen = !!document.getElementById('fullscreenMessages');

        if (!hasWebGPU) {
            this._showUnsupported();
            return;
        }

        if (this.isFullscreen) {
            this._initFullscreen();
        } else {
            this._initWidget();
        }

        // Inizializza il worker
        this._initWorker();

        // Carica riepilogo per il system prompt
        await this._loadRiepilogo();

        // In modalita fullscreen, carica subito il modello
        if (this.isFullscreen) {
            this._loadModel();
        }
    }

    // ── WebGPU Check ───────────────────────────────────────────────────

    async _checkWebGPU() {
        if (!navigator.gpu) return false;
        try {
            const adapter = await navigator.gpu.requestAdapter();
            return !!adapter;
        } catch (e) {
            return false;
        }
    }

    _showUnsupported() {
        const fab = document.getElementById('assistantFab');
        if (fab) fab.style.display = 'none';

        const banner = document.getElementById('assistantUnsupported');
        if (banner) banner.style.display = 'block';
    }

    // ── Fullscreen Mode ────────────────────────────────────────────────

    _initFullscreen() {
        this.chat = new AssistantChat({
            messagesId: 'fullscreenMessages',
            inputId: 'fullscreenInput',
            sendBtnId: 'fullscreenSend',
            typingId: 'fullscreenTyping',
            suggestionsId: 'fullscreenSuggestions',
            onSendMessage: (text) => this._onUserMessage(text)
        });
    }

    // ── Widget Mode ────────────────────────────────────────────────────

    _initWidget() {
        // Mostra FAB
        const fab = document.getElementById('assistantFab');
        if (fab) {
            fab.style.display = 'flex';
            fab.addEventListener('click', () => this._togglePanel());
        }

        // Init chat
        this.chat = new AssistantChat({
            messagesId: 'assistantMessages',
            inputId: 'assistantInput',
            sendBtnId: 'assistantSend',
            typingId: 'assistantTyping',
            suggestionsId: 'assistantSuggestions',
            onSendMessage: (text) => this._onUserMessage(text)
        });

        // Bind pulsanti header
        this._bindWidgetButtons();
    }

    _bindWidgetButtons() {
        // Chiudi
        const closeBtn = document.getElementById('assistantClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this._togglePanel(false));
        }

        // Espandi (vai a pagina fullscreen)
        const expandBtn = document.getElementById('assistantExpand');
        if (expandBtn) {
            expandBtn.addEventListener('click', () => {
                window.location.href = '/site/assistente';
            });
        }

        // Refresh (ricarica modello)
        const refreshBtn = document.getElementById('assistantRefresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.isReady = false;
                this._loadModel();
            });
        }

        // Cancella chat
        const clearBtn = document.getElementById('assistantClear');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.chat.clearMessages();
                this.conversationHistory = [];
            });
        }

        // Impostazioni
        const settingsBtn = document.getElementById('assistantSettings');
        if (settingsBtn) {
            settingsBtn.addEventListener('click', () => {
                const modal = document.getElementById('assistantSettingsModal');
                if (modal && typeof bootstrap !== 'undefined') {
                    const bsModal = new bootstrap.Modal(modal);
                    // Imposta valore corrente nel select
                    const select = document.getElementById('assistantModelSelect');
                    if (select) {
                        select.value = this._getSelectedModel();
                    }
                    bsModal.show();
                }
            });
        }

        // Applica modello dalle impostazioni
        const applyBtn = document.getElementById('assistantModelApply');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                const select = document.getElementById('assistantModelSelect');
                if (select) {
                    localStorage.setItem(STORAGE_MODEL_KEY, select.value);
                    // Chiudi modal
                    const modal = document.getElementById('assistantSettingsModal');
                    if (modal && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getInstance(modal)?.hide();
                    }
                    // Ricarica modello
                    this.isReady = false;
                    this._loadModel();
                }
            });
        }
    }

    _togglePanel(forceState) {
        const panel = document.getElementById('assistantPanel');
        const fab = document.getElementById('assistantFab');
        if (!panel) return;

        const isOpen = panel.classList.contains('open');
        const shouldOpen = forceState !== undefined ? forceState : !isOpen;

        if (shouldOpen) {
            panel.classList.add('open');
            if (fab) fab.classList.add('active');
            // Carica modello al primo apertura
            if (!this.isReady && !this.isGenerating) {
                this._loadModel();
            }
        } else {
            panel.classList.remove('open');
            if (fab) fab.classList.remove('active');
        }
    }

    // ── Worker Communication ───────────────────────────────────────────

    _initWorker() {
        this.worker = new Worker('/js/assistant/assistant-worker.js', { type: 'module' });

        this.worker.onmessage = (e) => {
            const { type, data } = e.data;

            switch (type) {
                case 'progress':
                    this._onProgress(data);
                    break;

                case 'status':
                    this._onStatus(data);
                    break;

                case 'token':
                    this._onToken(data);
                    break;

                case 'tool_calls':
                    this._onToolCalls(data);
                    break;

                case 'done':
                    this._onDone(data);
                    break;

                case 'error':
                    this._onError(data);
                    break;
            }
        };

        this.worker.onerror = (e) => {
            this._onError(`Errore worker: ${e.message}`);
        };
    }

    _getSelectedModel() {
        return localStorage.getItem(STORAGE_MODEL_KEY) || DEFAULT_MODEL;
    }

    _loadModel() {
        const model = this._getSelectedModel();
        this._updateStatus('Caricamento modello...', 'loading');
        this._showDownloadProgress(true);

        this.worker.postMessage({ type: 'init', data: { model } });
    }

    // ── Progress & Status ──────────────────────────────────────────────

    _onProgress(data) {
        const { text, progress } = data;
        const percent = Math.round((progress || 0) * 100);

        // Aggiorna barra download
        if (this.isFullscreen) {
            this._updateElement('fullscreenDownloadText', text);
            this._updateElement('fullscreenDownloadPercent', `${percent}%`);
            const bar = document.getElementById('fullscreenDownloadBar');
            if (bar) bar.style.width = `${percent}%`;
        } else {
            this._updateElement('assistantDownloadText', text);
            this._updateElement('assistantDownloadPercent', `${percent}%`);
            const bar = document.getElementById('assistantDownloadFill');
            if (bar) bar.style.width = `${percent}%`;
        }
    }

    _onStatus(status) {
        if (status === 'ready') {
            this.isReady = true;
            this._showDownloadProgress(false);
            this._updateStatus('Pronto', 'ready');

            // Abilita input
            if (this.chat) {
                this.chat.setInputEnabled(true);
            }
        }
    }

    _updateStatus(text, statusClass) {
        if (this.isFullscreen) {
            const badge = document.getElementById('fullscreenStatus');
            if (badge) {
                badge.textContent = text;
                badge.className = `badge ${statusClass === 'ready' ? 'bg-green-lt' : statusClass === 'loading' ? 'bg-orange-lt' : 'bg-red-lt'}`;
                if (statusClass === 'ready') {
                    badge.innerHTML = `<span class="badge-dot badge-dot-animated bg-green"></span> ${text}`;
                } else if (statusClass === 'loading') {
                    badge.innerHTML = `<span class="badge-dot"></span> ${text}`;
                }
            }
        } else {
            const statusText = document.getElementById('assistantStatusText');
            if (statusText) statusText.textContent = text;

            const badge = document.getElementById('assistantStatusBadge');
            if (badge) {
                badge.className = `assistant-fab-badge status-${statusClass}`;
                if (statusClass === 'loading') badge.classList.add('loading');
            }
        }
    }

    _showDownloadProgress(show) {
        const elId = this.isFullscreen ? 'fullscreenDownload' : 'assistantDownload';
        const el = document.getElementById(elId);
        if (el) el.style.display = show ? 'block' : 'none';
    }

    _updateElement(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    // ── System Prompt ──────────────────────────────────────────────────

    async _loadRiepilogo() {
        try {
            const response = await fetch('/assistant-api/riepilogo-generale', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                this.systemPrompt = this._buildSystemPrompt(null);
                return;
            }

            const data = await response.json();
            this.systemPrompt = this._buildSystemPrompt(data);
        } catch (e) {
            console.warn('Errore caricamento riepilogo:', e);
            this.systemPrompt = this._buildSystemPrompt(null);
        }
    }

    _buildSystemPrompt(riepilogoData) {
        let prompt = `Sei l'assistente AI di Albatros Manager (gestione docenti/corsi FSE-PNRR-MIM). Rispondi in italiano, conciso e preciso. Usa tabelle markdown ed elenchi. Non inventare dati.`;

        if (riepilogoData) {
            // Compatta il riepilogo per ridurre i token
            const r = riepilogoData;
            prompt += `\n\nDATI ATTUALI: ${r.progetti?.totale || 0} progetti (${r.progetti?.attivi || 0} attivi), ${r.docenti?.totale || 0} docenti (${r.docenti?.con_assegnazione || 0} assegnati), ${r.studenti?.totale || 0} studenti (${r.studenti?.frequentanti || 0} frequentanti), ${r.moduli?.totale || 0} moduli (${r.moduli?.senza_docente || 0} scoperti). Budget: ${r.finanziario?.budget_totale || 0}€, Pagato: ${r.finanziario?.pagato || 0}€, Da pagare: ${r.finanziario?.da_pagare || 0}€.`;
        }

        return prompt;
    }

    // ── Model Capabilities ─────────────────────────────────────────────

    _supportsToolCalling() {
        const model = this._getSelectedModel();
        return FUNCTION_CALLING_MODELS.some(m => model.includes(m) || m.includes(model));
    }

    // ── User Message Handling ──────────────────────────────────────────

    async _onUserMessage(text) {
        if (!this.isReady) {
            this.chat.addMessage('assistant', 'Il modello non e ancora pronto. Attendi il completamento del caricamento.');
            return;
        }

        if (this.isGenerating) {
            return;
        }

        this.isGenerating = true;
        this.chat.setInputEnabled(false);
        this.chat.showTyping(true);

        // Aggiungi alla cronologia conversazione
        this.conversationHistory.push({ role: 'user', content: text });

        if (this._supportsToolCalling()) {
            // Modello con function calling (Hermes) → invia tools
            const messages = [
                { role: 'system', content: this.systemPrompt },
                ...this.conversationHistory
            ];

            this.worker.postMessage({
                type: 'chat',
                data: { messages, tools: toolDefinitions }
            });
        } else {
            // Modello senza function calling (Qwen) → keyword routing + formattazione LLM
            await this._handleWithKeywordFallback(text);
            return;
        }

        // Avvia timer timeout
        this._startGenerationTimeout();
    }

    /**
     * Per modelli senza function calling: usa keyword routing per ottenere i dati,
     * poi chiedi al modello di formattare la risposta in italiano.
     */
    async _handleWithKeywordFallback(userText) {
        const fn = keywordRoute(userText);

        if (fn) {
            try {
                const params = extractParams(userText, fn);
                const result = await executeToolCall(fn, params);

                if (result.error) {
                    throw new Error(result.error);
                }

                // Chiedi al modello di formattare la risposta con i dati (compatto)
                const dataStr = JSON.stringify(result);
                // Tronca a max 2000 caratteri per non eccedere il context
                const truncatedData = dataStr.length > 2000 ? dataStr.substring(0, 2000) + '...(troncato)' : dataStr;

                const messages = [
                    { role: 'system', content: this.systemPrompt },
                    {
                        role: 'user',
                        content: `Domanda: "${userText}"\n\nDati:\n${truncatedData}\n\nRispondi in italiano usando questi dati.`
                    }
                ];

                this.worker.postMessage({
                    type: 'chat',
                    data: { messages, tools: [] }
                });

                this._startGenerationTimeout();
            } catch (e) {
                // API fallita, rispondi con il riepilogo generale
                this._respondWithRiepilogo(userText);
            }
        } else {
            // Nessun match keyword → rispondi con il riepilogo nel system prompt
            this._respondWithRiepilogo(userText);
        }
    }

    /**
     * Fallback finale: rispondi basandosi solo sul riepilogo gia nel system prompt
     */
    _respondWithRiepilogo(userText) {
        const messages = [
            { role: 'system', content: this.systemPrompt },
            ...this._getCleanHistory()
        ];

        this.worker.postMessage({
            type: 'chat',
            data: { messages, tools: [] }
        });

        this._startGenerationTimeout();
    }

    /**
     * Restituisce la cronologia filtrata: solo messaggi con content stringa.
     * Rimuove messaggi tool_calls/tool che Qwen non sa gestire.
     */
    _getCleanHistory() {
        return this.conversationHistory.filter(m =>
            typeof m.content === 'string' && m.content.length > 0 &&
            (m.role === 'user' || m.role === 'assistant')
        );
    }

    // ── Token Streaming ────────────────────────────────────────────────

    _onToken(token) {
        this._resetGenerationTimeout();

        // Avvia stream se non ancora iniziato
        if (!this._streamActive) {
            this.chat.showTyping(false);
            this.chat.startBotStream();
            this._streamActive = true;
        }

        this.chat.appendToStream(token);
    }

    _onDone(fullResponse) {
        this._clearGenerationTimeout();

        if (this._streamActive) {
            this.chat.endStream();
            this._streamActive = false;
        } else if (fullResponse) {
            this.chat.showTyping(false);
            this.chat.addMessage('assistant', fullResponse);
        }

        // Salva risposta nella cronologia
        if (fullResponse) {
            this.conversationHistory.push({ role: 'assistant', content: fullResponse });
        }

        this.isGenerating = false;
        this.chat.showTyping(false);
        this.chat.setInputEnabled(true);
    }

    // ── Tool Calls ─────────────────────────────────────────────────────

    async _onToolCalls(toolCalls) {
        this._clearGenerationTimeout();
        this.chat.showTyping(true);

        // Aggiungi la risposta del modello con tool_calls alla cronologia
        this.conversationHistory.push({
            role: 'assistant',
            content: null,
            tool_calls: toolCalls.map(tc => ({
                id: tc.id,
                type: 'function',
                function: {
                    name: tc.function.name,
                    arguments: JSON.stringify(tc.function.arguments)
                }
            }))
        });

        // Esegui ogni tool call
        for (const tc of toolCalls) {
            const functionName = tc.function.name;
            const args = tc.function.arguments || {};

            try {
                const result = await executeToolCall(functionName, args);

                // Aggiungi il risultato alla cronologia
                this.conversationHistory.push({
                    role: 'tool',
                    tool_call_id: tc.id,
                    content: JSON.stringify(result)
                });
            } catch (error) {
                this.conversationHistory.push({
                    role: 'tool',
                    tool_call_id: tc.id,
                    content: JSON.stringify({ error: error.message })
                });
            }
        }

        // Re-invia al worker SENZA tools per ottenere la risposta finale
        const messages = [
            { role: 'system', content: this.systemPrompt },
            ...this.conversationHistory
        ];

        this.worker.postMessage({
            type: 'chat',
            data: { messages, tools: [] }
        });

        this._startGenerationTimeout();
    }

    // ── Error Handling & Fallback ──────────────────────────────────────

    async _onError(errorMessage) {
        this._clearGenerationTimeout();

        console.warn('Errore assistente:', errorMessage);

        // Prova fallback con keyword routing (senza inquinare la history)
        const lastUserMsg = this.conversationHistory
            .filter(m => m.role === 'user' && typeof m.content === 'string')
            .pop();

        if (lastUserMsg) {
            const fallbackFn = keywordRoute(lastUserMsg.content);

            if (fallbackFn) {
                try {
                    this.chat.showTyping(true);
                    const params = extractParams(lastUserMsg.content, fallbackFn);
                    const result = await executeToolCall(fallbackFn, params);

                    // Crea messaggi freschi SENZA tool_calls (compatibile con tutti i modelli)
                    const dataStr = JSON.stringify(result);
                    const truncatedData = dataStr.length > 2000 ? dataStr.substring(0, 2000) + '...(troncato)' : dataStr;

                    const messages = [
                        { role: 'system', content: this.systemPrompt },
                        {
                            role: 'user',
                            content: `Domanda: "${lastUserMsg.content}"\n\nDati:\n${truncatedData}\n\nRispondi in italiano.`
                        }
                    ];

                    this.worker.postMessage({
                        type: 'chat',
                        data: { messages, tools: [] }
                    });

                    this._startGenerationTimeout();
                    return;
                } catch (fallbackError) {
                    console.warn('Errore fallback:', fallbackError);
                }
            }
        }

        // Nessun fallback disponibile, mostra errore
        if (this._streamActive) {
            this.chat.endStream();
            this._streamActive = false;
        }

        this.chat.showTyping(false);
        this.chat.addMessage('assistant',
            'Mi dispiace, si e verificato un errore durante l\'elaborazione della richiesta. Riprova o riformula la domanda.'
        );

        this.isGenerating = false;
        this.chat.setInputEnabled(true);
    }

    // ── Generation Timeout ─────────────────────────────────────────────

    _startGenerationTimeout() {
        this._clearGenerationTimeout();
        this._generationTimer = setTimeout(() => {
            if (this.isGenerating) {
                // Abort generazione
                if (this.worker) {
                    this.worker.postMessage({ type: 'abort' });
                }

                if (this._streamActive) {
                    this.chat.endStream();
                    this._streamActive = false;
                }

                this.chat.showTyping(false);
                this.chat.addMessage('assistant',
                    'La generazione ha impiegato troppo tempo ed e stata interrotta. Riprova con una domanda piu semplice.'
                );

                this.isGenerating = false;
                this.chat.setInputEnabled(true);
            }
        }, GENERATION_TIMEOUT);
    }

    _resetGenerationTimeout() {
        this._startGenerationTimeout();
    }

    _clearGenerationTimeout() {
        if (this._generationTimer) {
            clearTimeout(this._generationTimer);
            this._generationTimer = null;
        }
    }
}

// ── Auto-init on DOMContentLoaded ──────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const engine = new AssistantEngine();
    engine.init();
});
