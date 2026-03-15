/**
 * Assistente AI - Tool Definitions & Keyword Fallback Routing
 *
 * Definisce i tool OpenAI-compatible per le API dell'assistente,
 * il routing deterministico per keyword e l'estrazione parametri.
 */

// ── Tool Definitions (OpenAI function calling format) ──────────────────────

export const toolDefinitions = [
    {
        type: 'function',
        function: {
            name: 'riepilogo_generale',
            description: 'Ottieni un riepilogo generale della situazione: progetti attivi, budget, docenti, studenti e statistiche principali.',
            parameters: { type: 'object', properties: {}, required: [] }
        }
    },
    {
        type: 'function',
        function: {
            name: 'lista_progetti',
            description: 'Ottieni la lista di tutti i progetti con budget, stato, numero moduli e avvisi.',
            parameters: { type: 'object', properties: {}, required: [] }
        }
    },
    {
        type: 'function',
        function: {
            name: 'moduli_senza_docente',
            description: 'Ottieni la lista dei moduli che non hanno ancora un docente assegnato.',
            parameters: { type: 'object', properties: {}, required: [] }
        }
    },
    {
        type: 'function',
        function: {
            name: 'cerca_docenti',
            description: 'Cerca docenti per competenza/tag curriculum e valutazione minima opzionale.',
            parameters: {
                type: 'object',
                properties: {
                    competenza: {
                        type: 'string',
                        description: 'Competenza o tag da cercare (es. "informatica", "inglese")'
                    },
                    valutazione_minima: {
                        type: 'number',
                        description: 'Punteggio minimo di valutazione (da 1 a 5)'
                    }
                },
                required: []
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'saldi_da_liquidare',
            description: 'Ottieni la lista dei docenti con saldi da liquidare (pagamenti in attesa).',
            parameters: { type: 'object', properties: {}, required: [] }
        }
    },
    {
        type: 'function',
        function: {
            name: 'ore_rendicontate',
            description: 'Ottieni il riepilogo delle ore rendicontate dai docenti, con stato approvazione.',
            parameters: { type: 'object', properties: {}, required: [] }
        }
    },
    {
        type: 'function',
        function: {
            name: 'graduatoria_docenti',
            description: 'Ottieni la graduatoria dei docenti candidati per un modulo specifico.',
            parameters: {
                type: 'object',
                properties: {
                    modulo_id: {
                        type: 'number',
                        description: 'ID del modulo per cui visualizzare la graduatoria'
                    }
                },
                required: []
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'studenti_per_progetto',
            description: 'Ottieni la lista degli studenti iscritti a un progetto con il loro stato.',
            parameters: {
                type: 'object',
                properties: {
                    progetto_id: {
                        type: 'number',
                        description: 'ID del progetto per cui visualizzare gli studenti'
                    }
                },
                required: []
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'valutazioni_docenti',
            description: 'Ottieni le valutazioni ricevute da un docente tramite questionari anonimi.',
            parameters: {
                type: 'object',
                properties: {
                    codice_fiscale: {
                        type: 'string',
                        description: 'Codice fiscale del docente (16 caratteri)'
                    }
                },
                required: []
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'scadenze',
            description: 'Ottieni le prossime scadenze di bandi, contratti e attivita in programma.',
            parameters: { type: 'object', properties: {}, required: [] }
        }
    }
];

// ── Endpoint Map ───────────────────────────────────────────────────────────

const ENDPOINT_MAP = {
    riepilogo_generale:   '/assistant-api/riepilogo-generale',
    lista_progetti:       '/assistant-api/progetti',
    moduli_senza_docente: '/assistant-api/moduli-senza-docente',
    cerca_docenti:        '/assistant-api/cerca-docenti',
    saldi_da_liquidare:   '/assistant-api/saldi-da-liquidare',
    ore_rendicontate:     '/assistant-api/ore-rendicontate',
    graduatoria_docenti:  '/assistant-api/graduatoria',
    studenti_per_progetto:'/assistant-api/studenti-per-progetto',
    valutazioni_docenti:  '/assistant-api/valutazioni-docenti',
    scadenze:             '/assistant-api/scadenze'
};

// ── Execute Tool Call ──────────────────────────────────────────────────────

/**
 * Esegue una chiamata API per il tool specificato.
 * @param {string} functionName - Nome della funzione/tool
 * @param {object} args - Parametri da passare come query string
 * @returns {Promise<object>} Risposta JSON dall'API
 */
export async function executeToolCall(functionName, args = {}) {
    const endpoint = ENDPOINT_MAP[functionName];
    if (!endpoint) {
        throw new Error(`Tool sconosciuto: ${functionName}`);
    }

    const url = new URL(endpoint, window.location.origin);

    // Aggiungi parametri come query string
    for (const [key, value] of Object.entries(args)) {
        if (value !== undefined && value !== null && value !== '') {
            url.searchParams.set(key, value);
        }
    }

    const response = await fetch(url.toString(), {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    if (!response.ok) {
        throw new Error(`Errore API (${response.status}): ${response.statusText}`);
    }

    return await response.json();
}

// ── Keyword Routing (Fallback deterministico) ──────────────────────────────

/**
 * Routing deterministico basato su keyword nel messaggio utente.
 * Usato come fallback quando il modello non riesce a generare tool calls.
 * @param {string} userMessage - Messaggio dell'utente
 * @returns {string|null} Nome della funzione corrispondente, o null
 */
export function keywordRoute(userMessage) {
    const msg = userMessage.toLowerCase();

    const routes = [
        { keywords: ['liquidare', 'pagare', 'saldo'],                  fn: 'saldi_da_liquidare' },
        { keywords: ['manca', 'scopert', 'senza docente'],             fn: 'moduli_senza_docente' },
        { keywords: ['esperto', 'competenz', 'tag'],                   fn: 'cerca_docenti' },
        { keywords: ['valutazion', 'voto', 'stellin'],                 fn: 'valutazioni_docenti' },
        { keywords: ['ore', 'rendicont'],                              fn: 'ore_rendicontate' },
        { keywords: ['student', 'iscritt', 'posti'],                   fn: 'studenti_per_progetto' },
        { keywords: ['scadenz', 'prossim', 'scade'],                   fn: 'scadenze' },
        { keywords: ['progett', 'budget', 'finanzia'],                 fn: 'lista_progetti' },
        { keywords: ['graduatoria', 'idoneo'],                         fn: 'graduatoria_docenti' },
        { keywords: ['generale', 'riepilogo', 'panoramica', 'quadro', 'situazione'], fn: 'riepilogo_generale' },
    ];

    for (const route of routes) {
        for (const keyword of route.keywords) {
            if (msg.includes(keyword)) {
                return route.fn;
            }
        }
    }

    return null;
}

// ── Extract Params ─────────────────────────────────────────────────────────

/**
 * Estrae parametri dal linguaggio naturale per funzioni specifiche.
 * @param {string} userMessage - Messaggio dell'utente
 * @param {string} functionName - Nome della funzione
 * @returns {object} Parametri estratti
 */
export function extractParams(userMessage, functionName) {
    const params = {};

    if (functionName === 'cerca_docenti') {
        // Cerca pattern "esperto di X", "competenza in X", "tag X"
        const competenzaMatch = userMessage.match(
            /(?:esperto\s+(?:di|in)\s+|competenz\w*\s+(?:in|di|su)?\s*|tag\s+)["']?([a-zA-ZàèéìòùÀÈÉÌÒÙ\s]+?)["']?(?:\s*[,.]|\s+con|\s+e\s|\s*$)/i
        );
        if (competenzaMatch) {
            params.competenza = competenzaMatch[1].trim();
        }

        // Cerca pattern "valutazione > N", "valutazione minima N", "almeno N"
        const valutazioneMatch = userMessage.match(
            /(?:valutazione\s*(?:>|>=|minima|superiore\s+a|almeno)\s*|almeno\s+)(\d+(?:\.\d+)?)/i
        );
        if (valutazioneMatch) {
            params.valutazione_minima = parseFloat(valutazioneMatch[1]);
        }
    }

    return params;
}
