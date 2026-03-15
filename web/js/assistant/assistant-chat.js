/**
 * Assistente AI - Chat UI Logic
 *
 * Gestisce l'interfaccia chat: messaggi, streaming, cronologia,
 * suggerimenti rapidi e rendering markdown semplificato.
 */

const STORAGE_KEY = 'assistant_chat_history';
const MAX_MESSAGES = 50;

export class AssistantChat {
    /**
     * @param {object} options
     * @param {string} options.messagesId - ID del container messaggi
     * @param {string} options.inputId - ID dell'input text
     * @param {string} options.sendBtnId - ID del pulsante invio
     * @param {string} options.typingId - ID dell'indicatore di digitazione
     * @param {string} options.suggestionsId - ID del container suggerimenti
     * @param {function} options.onSendMessage - Callback quando l'utente invia un messaggio
     */
    constructor(options) {
        this.messagesEl = document.getElementById(options.messagesId);
        this.inputEl = document.getElementById(options.inputId);
        this.sendBtn = document.getElementById(options.sendBtnId);
        this.typingEl = document.getElementById(options.typingId);
        this.suggestionsEl = document.getElementById(options.suggestionsId);
        this.onSendMessage = options.onSendMessage || (() => {});

        this.messages = [];
        this._currentStreamEl = null;
        this._currentStreamContent = '';

        this._loadHistory();
        this._bindEvents();
    }

    // ── Public Methods ─────────────────────────────────────────────────

    /**
     * Aggiunge un messaggio alla chat.
     * @param {'user'|'assistant'} role
     * @param {string} content
     */
    addMessage(role, content) {
        this.messages.push({ role, content });

        const msgDiv = document.createElement('div');
        msgDiv.className = `assistant-message assistant-message-${role}`;

        const bubble = document.createElement('div');
        bubble.className = 'assistant-bubble';

        if (role === 'user') {
            bubble.textContent = content;
        } else {
            bubble.innerHTML = this._renderMarkdown(content);
        }

        msgDiv.appendChild(bubble);
        this.messagesEl.appendChild(msgDiv);
        this._scrollToBottom();
        this._saveHistory();
    }

    /**
     * Crea un messaggio bot vuoto per lo streaming.
     * @returns {HTMLElement} Elemento bubble per lo streaming
     */
    startBotStream() {
        this._currentStreamContent = '';

        const msgDiv = document.createElement('div');
        msgDiv.className = 'assistant-message assistant-message-assistant';

        const bubble = document.createElement('div');
        bubble.className = 'assistant-bubble';
        bubble.innerHTML = '<span class="assistant-cursor">|</span>';

        msgDiv.appendChild(bubble);
        this.messagesEl.appendChild(msgDiv);

        this._currentStreamEl = bubble;
        this._scrollToBottom();
        return bubble;
    }

    /**
     * Aggiunge un token allo stream corrente.
     * @param {string} token
     */
    appendToStream(token) {
        if (!this._currentStreamEl) return;

        this._currentStreamContent += token;
        this._currentStreamEl.innerHTML = this._renderMarkdown(this._currentStreamContent)
            + '<span class="assistant-cursor">|</span>';
        this._scrollToBottom();
    }

    /**
     * Finalizza lo stream corrente e salva nella cronologia.
     */
    endStream() {
        if (!this._currentStreamEl) return;

        // Rimuovi cursore e renderizza contenuto finale
        this._currentStreamEl.innerHTML = this._renderMarkdown(this._currentStreamContent);

        this.messages.push({ role: 'assistant', content: this._currentStreamContent });
        this._saveHistory();

        this._currentStreamEl = null;
        this._currentStreamContent = '';
    }

    /**
     * Mostra/nasconde l'indicatore di digitazione.
     * @param {boolean} show
     */
    showTyping(show) {
        if (this.typingEl) {
            this.typingEl.style.display = show ? 'flex' : 'none';
        }
        if (show) this._scrollToBottom();
    }

    /**
     * Abilita/disabilita input e pulsante invio.
     * @param {boolean} enabled
     */
    setInputEnabled(enabled) {
        if (this.inputEl) {
            this.inputEl.disabled = !enabled;
            if (enabled) this.inputEl.focus();
        }
        if (this.sendBtn) {
            this.sendBtn.disabled = !enabled;
        }
    }

    /**
     * Cancella tutti i messaggi dalla chat e dal localStorage.
     */
    clearMessages() {
        this.messages = [];
        if (this.messagesEl) {
            this.messagesEl.innerHTML = '';
        }
        this._currentStreamEl = null;
        this._currentStreamContent = '';
        localStorage.removeItem(STORAGE_KEY);
    }

    // ── Private Methods ────────────────────────────────────────────────

    /**
     * Carica la cronologia dal localStorage e la renderizza.
     */
    _loadHistory() {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) {
                const parsed = JSON.parse(stored);
                if (Array.isArray(parsed)) {
                    // Prendi solo gli ultimi MAX_MESSAGES
                    this.messages = parsed.slice(-MAX_MESSAGES);
                    this._renderHistory();
                }
            }
        } catch (e) {
            console.warn('Errore caricamento cronologia chat:', e);
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    /**
     * Salva la cronologia nel localStorage (max 50 messaggi).
     */
    _saveHistory() {
        try {
            const toSave = this.messages.slice(-MAX_MESSAGES);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(toSave));
        } catch (e) {
            console.warn('Errore salvataggio cronologia chat:', e);
        }
    }

    /**
     * Renderizza la cronologia caricata nel DOM.
     */
    _renderHistory() {
        if (!this.messagesEl) return;

        for (const msg of this.messages) {
            const msgDiv = document.createElement('div');
            msgDiv.className = `assistant-message assistant-message-${msg.role}`;

            const bubble = document.createElement('div');
            bubble.className = 'assistant-bubble';

            if (msg.role === 'user') {
                bubble.textContent = msg.content;
            } else {
                bubble.innerHTML = this._renderMarkdown(msg.content);
            }

            msgDiv.appendChild(bubble);
            this.messagesEl.appendChild(msgDiv);
        }

        this._scrollToBottom();
    }

    /**
     * Collega gli event listener per input, invio e suggerimenti.
     */
    _bindEvents() {
        // Invio con Enter
        if (this.inputEl) {
            this.inputEl.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey && !this.inputEl.disabled) {
                    e.preventDefault();
                    this._sendCurrentInput();
                }
            });
        }

        // Click pulsante invio
        if (this.sendBtn) {
            this.sendBtn.addEventListener('click', () => {
                this._sendCurrentInput();
            });
        }

        // Click suggerimenti rapidi
        if (this.suggestionsEl) {
            this.suggestionsEl.addEventListener('click', (e) => {
                const btn = e.target.closest('.assistant-suggestion-btn');
                if (btn && btn.dataset.query) {
                    const query = btn.dataset.query;
                    this.addMessage('user', query);
                    this.onSendMessage(query);
                    // Nascondi suggerimenti dopo il primo uso
                    this.suggestionsEl.style.display = 'none';
                }
            });
        }
    }

    /**
     * Invia il contenuto corrente dell'input.
     */
    _sendCurrentInput() {
        if (!this.inputEl) return;
        const text = this.inputEl.value.trim();
        if (!text) return;

        this.inputEl.value = '';
        this.addMessage('user', text);
        this.onSendMessage(text);

        // Nascondi suggerimenti dopo il primo messaggio
        if (this.suggestionsEl) {
            this.suggestionsEl.style.display = 'none';
        }
    }

    /**
     * Scrolla il container messaggi in fondo.
     */
    _scrollToBottom() {
        if (this.messagesEl) {
            this.messagesEl.scrollTop = this.messagesEl.scrollHeight;
        }
    }

    /**
     * Rendering markdown semplificato.
     * Supporta: bold, italic, code inline, tabelle, liste, line break.
     * @param {string} text
     * @returns {string} HTML renderizzato
     */
    _renderMarkdown(text) {
        if (!text) return '';

        // Escape HTML
        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

        // Code blocks (``` ... ```)
        html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, (_, lang, code) => {
            return `<pre><code class="language-${lang}">${code.trim()}</code></pre>`;
        });

        // Inline code
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Bold
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

        // Italic
        html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');

        // Tabelle
        html = this._renderTables(html);

        // Liste (processate per riga)
        html = this._renderLists(html);

        // Line breaks (doppie newline → paragrafi, singola → <br>)
        html = html
            .replace(/\n\n+/g, '</p><p>')
            .replace(/\n/g, '<br>');

        // Wrappa in paragrafo se non inizia con tag blocco
        if (!html.match(/^<(?:pre|table|ul|ol|p)/)) {
            html = '<p>' + html + '</p>';
        }

        return html;
    }

    /**
     * Renderizza tabelle markdown (| col1 | col2 |).
     * @param {string} html
     * @returns {string}
     */
    _renderTables(html) {
        const lines = html.split('\n');
        let inTable = false;
        const result = [];
        let headerProcessed = false;

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();

            if (line.match(/^\|(.+\|)+$/)) {
                // Riga separatore (|---|---|)
                if (line.match(/^\|[\s\-:]+(\|[\s\-:]+)+\|?$/)) {
                    // Skip separator, ma segna che l'header e' stato processato
                    headerProcessed = true;
                    continue;
                }

                if (!inTable) {
                    result.push('<table class="table table-sm table-bordered mb-2">');
                    inTable = true;
                    headerProcessed = false;
                }

                const cells = line.split('|').filter(c => c.trim() !== '');
                const tag = !headerProcessed ? 'th' : 'td';

                if (!headerProcessed) {
                    result.push('<thead><tr>');
                    cells.forEach(cell => {
                        result.push(`<${tag}>${cell.trim()}</${tag}>`);
                    });
                    result.push('</tr></thead><tbody>');
                } else {
                    result.push('<tr>');
                    cells.forEach(cell => {
                        result.push(`<${tag}>${cell.trim()}</${tag}>`);
                    });
                    result.push('</tr>');
                }
            } else {
                if (inTable) {
                    result.push('</tbody></table>');
                    inTable = false;
                    headerProcessed = false;
                }
                result.push(line);
            }
        }

        if (inTable) {
            result.push('</tbody></table>');
        }

        return result.join('\n');
    }

    /**
     * Renderizza liste markdown (- item, 1. item).
     * @param {string} html
     * @returns {string}
     */
    _renderLists(html) {
        const lines = html.split('\n');
        let inUl = false;
        let inOl = false;
        const result = [];

        for (const line of lines) {
            const trimmed = line.trim();

            // Lista non ordinata
            const ulMatch = trimmed.match(/^[-*]\s+(.+)$/);
            if (ulMatch) {
                if (!inUl) {
                    if (inOl) { result.push('</ol>'); inOl = false; }
                    result.push('<ul>');
                    inUl = true;
                }
                result.push(`<li>${ulMatch[1]}</li>`);
                continue;
            }

            // Lista ordinata
            const olMatch = trimmed.match(/^\d+\.\s+(.+)$/);
            if (olMatch) {
                if (!inOl) {
                    if (inUl) { result.push('</ul>'); inUl = false; }
                    result.push('<ol>');
                    inOl = true;
                }
                result.push(`<li>${olMatch[1]}</li>`);
                continue;
            }

            // Riga normale - chiudi liste aperte
            if (inUl) { result.push('</ul>'); inUl = false; }
            if (inOl) { result.push('</ol>'); inOl = false; }
            result.push(line);
        }

        // Chiudi liste rimaste aperte
        if (inUl) result.push('</ul>');
        if (inOl) result.push('</ol>');

        return result.join('\n');
    }
}
