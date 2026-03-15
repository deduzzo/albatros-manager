/**
 * Assistente AI - Web Worker per inferenza WebLLM
 *
 * Esegue il modello LLM nel browser usando WebGPU tramite la libreria WebLLM.
 * Comunica con il thread principale via postMessage.
 */

import { CreateMLCEngine } from 'https://esm.run/@mlc-ai/web-llm';

let engine = null;

// ── Message Handler ────────────────────────────────────────────────────────

self.onmessage = async function (e) {
    const { type, data } = e.data;

    switch (type) {
        case 'init':
            await handleInit(data);
            break;

        case 'chat':
            await handleChat(data);
            break;

        case 'abort':
            handleAbort();
            break;

        default:
            self.postMessage({ type: 'error', data: `Tipo messaggio sconosciuto: ${type}` });
    }
};

// ── Init: Carica il modello ────────────────────────────────────────────────

async function handleInit(data) {
    const { model } = data;

    try {
        self.postMessage({
            type: 'progress',
            data: { text: 'Inizializzazione motore AI...', progress: 0 }
        });

        engine = await CreateMLCEngine(model, {
            initProgressCallback: (report) => {
                self.postMessage({
                    type: 'progress',
                    data: {
                        text: report.text || 'Caricamento...',
                        progress: report.progress || 0
                    }
                });
            }
        });

        self.postMessage({ type: 'status', data: 'ready' });
    } catch (error) {
        self.postMessage({
            type: 'error',
            data: `Errore inizializzazione modello: ${error.message}`
        });
    }
}

// ── Chat: Genera risposta con streaming ────────────────────────────────────

async function handleChat(data) {
    const { messages, tools } = data;

    if (!engine) {
        self.postMessage({ type: 'error', data: 'Modello non inizializzato' });
        return;
    }

    try {
        const requestOptions = {
            messages,
            stream: true,
            temperature: 0.7,
            max_tokens: 2048
        };

        // Aggiungi tools solo se forniti
        if (tools && tools.length > 0) {
            requestOptions.tools = tools;
        }

        const stream = await engine.chat.completions.create(requestOptions);

        let fullResponse = '';
        let toolCalls = [];
        let hasToolCalls = false;

        for await (const chunk of stream) {
            const delta = chunk.choices[0]?.delta;

            if (!delta) continue;

            // Gestisci tool calls
            if (delta.tool_calls) {
                hasToolCalls = true;
                for (const tc of delta.tool_calls) {
                    const idx = tc.index ?? 0;
                    if (!toolCalls[idx]) {
                        toolCalls[idx] = {
                            id: tc.id || `call_${idx}`,
                            type: 'function',
                            function: {
                                name: tc.function?.name || '',
                                arguments: ''
                            }
                        };
                    }
                    if (tc.function?.name) {
                        toolCalls[idx].function.name = tc.function.name;
                    }
                    if (tc.function?.arguments) {
                        toolCalls[idx].function.arguments += tc.function.arguments;
                    }
                }
            }

            // Gestisci contenuto testuale
            if (delta.content) {
                fullResponse += delta.content;
                self.postMessage({ type: 'token', data: delta.content });
            }
        }

        // Risultato finale
        if (hasToolCalls && toolCalls.length > 0) {
            // Parsa gli argomenti JSON di ogni tool call
            const parsedToolCalls = toolCalls.map(tc => {
                let args = {};
                try {
                    if (tc.function.arguments) {
                        args = JSON.parse(tc.function.arguments);
                    }
                } catch (e) {
                    console.warn('Errore parsing argomenti tool call:', e);
                }
                return {
                    id: tc.id,
                    type: tc.type,
                    function: {
                        name: tc.function.name,
                        arguments: args
                    }
                };
            });

            self.postMessage({ type: 'tool_calls', data: parsedToolCalls });
        } else {
            self.postMessage({ type: 'done', data: fullResponse });
        }
    } catch (error) {
        // Distingui abort da errori reali
        if (error.message?.includes('abort') || error.message?.includes('interrupt')) {
            self.postMessage({ type: 'done', data: fullResponse || '' });
        } else {
            self.postMessage({
                type: 'error',
                data: `Errore generazione: ${error.message}`
            });
        }
    }
}

// ── Abort: Interrompi la generazione ───────────────────────────────────────

function handleAbort() {
    if (engine) {
        try {
            engine.interruptGenerate();
        } catch (e) {
            console.warn('Errore interruzione generazione:', e);
        }
    }
}
