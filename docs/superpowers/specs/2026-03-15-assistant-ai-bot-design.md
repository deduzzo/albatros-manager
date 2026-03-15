# Assistente AI Bot — Specifica di Design

> **Data**: 2026-03-15
> **Branch**: `feature/assistant-ai-bot`
> **Stato**: Approvato

## Obiettivo

Integrare un assistente AI conversazionale nel portale admin di Albatros Manager, basato su **WebLLM** (inferenza LLM nel browser via WebGPU). L'assistente risponde a domande in linguaggio naturale sui dati del sistema (docenti, progetti, pagamenti, studenti) interrogando API server read-only.

## Decisioni di design

| Aspetto | Decisione |
|---------|-----------|
| Approccio | WebLLM puro — LLM gira nel browser, zero costi server per inferenza |
| Modello default | Qwen2.5-3B-Instruct (q4f16_1, ~2GB) |
| Modello avanzato | Qwen2.5-7B-Instruct (~5GB) — opzione esplicita nelle impostazioni del widget |
| Utenti target | Solo admin |
| UI | Widget flottante (basso-destra) + pagina full-screen espandibile |
| Contesto | Globale — riepilogo dati caricato all'avvio nel system prompt |
| Lingua | Tutto italiano (system prompt + risposte) |
| Operazioni | Solo lettura (v1) |
| Libreria | `@mlc-ai/web-llm` via CDN |
| Inferenza | In Web Worker (non blocca la UI) |

## Architettura

```
┌──────────────────────────────────────────────────┐
│  BROWSER (Admin)                                 │
│                                                  │
│  ┌──────────┐    ┌───────────────────────────┐   │
│  │ Chat UI  │◄──►│ Web Worker                │   │
│  │ (widget  │    │ ┌───────────────────────┐ │   │
│  │  + full) │    │ │ WebLLM Engine         │ │   │
│  └──────────┘    │ │ Qwen2.5-3B-Instruct  │ │   │
│                  │ └───────────────────────┘ │   │
│                  └──────┬────────────────────┘   │
│                         │ function call          │
│                  ┌──────▼───────────────┐        │
│                  │ API Client (JS)      │        │
│                  │ fetch + fallback     │        │
│                  │ deterministico       │        │
│                  └──────┬───────────────┘        │
└─────────────────────────┼────────────────────────┘
                          │ HTTP GET / JSON
┌─────────────────────────▼────────────────────────┐
│  SERVER (Yii2/PHP)                               │
│                                                  │
│  ┌───────────────────────────────────────────┐   │
│  │ AssistantApiController                    │   │
│  │ 10 endpoint read-only GET                 │   │
│  │ CSRF disabilitato, VerbFilter GET-only    │   │
│  └───────────────────────────────────────────┘   │
└──────────────────────────────────────────────────┘
```

### Flusso di una query

1. Utente digita domanda nel widget chat
2. Chat UI invia messaggio al Web Worker
3. WebLLM (nel worker) riceve messaggio + system prompt (con riepilogo globale pre-caricato) + definizioni tools
4. WebLLM genera una `function_call` (es: `cerca_docenti({competenza: "informatica", valutazione_minima: 4})`)
5. Worker comunica al main thread → JS esegue `fetch('/assistant-api/cerca-docenti?...')`
6. Server risponde JSON
7. JS passa il risultato al worker → WebLLM genera risposta in italiano
8. **Se function calling fallisce** (JSON malformato, function inesistente): il layer JS deterministico analizza la domanda originale con keyword matching, chiama l'endpoint più probabile, e passa i dati grezzi al modello chiedendo solo di formattare la risposta

**Limitazione nota:** WebLLM supporta solo 1 round di function calling. Per domande che richiedono 2+ API, il riepilogo globale pre-caricato nel system prompt copre la maggior parte dei casi. Per query specifiche, il JS può concatenare i risultati di più fetch prima di passarli al modello.

### Fallback deterministico (keyword routing)

Quando il function calling del modello fallisce (JSON malformato, function name errato), il layer `assistant-tools.js` usa un routing deterministico basato su keyword:

| Keywords nella domanda | Endpoint chiamato |
|------------------------|-------------------|
| "liquidare", "pagare", "saldo" | `/assistant-api/saldi-da-liquidare` |
| "manca", "scopert", "senza docente" | `/assistant-api/moduli-senza-docente` |
| "esperto", "competenz", "tag" | `/assistant-api/cerca-docenti` |
| "valutazion", "voto", "stellin" | `/assistant-api/valutazioni-docenti` |
| "ore", "rendicont" | `/assistant-api/ore-rendicontate` |
| "studenti", "iscritt", "posti" | `/assistant-api/studenti-per-progetto` |
| "scadenz", "prossim" | `/assistant-api/scadenze` |
| "progett", "stato", "generale" | `/assistant-api/progetti` |
| "graduatoria", "idoneo" | `/assistant-api/graduatoria` |

Il risultato viene poi passato al modello con un prompt semplificato: "Ecco i dati. Rispondi alla domanda dell'utente in italiano: {dati_json}". Questo approccio non richiede function calling, solo generazione di testo.

## Endpoint API

| # | Endpoint | Cosa restituisce | Domande coperte |
|---|----------|-----------------|-----------------|
| 1 | `GET /assistant-api/riepilogo-generale` | Contatori globali: tot progetti, docenti, studenti, moduli, budget totale, pagato, da pagare | "Come siamo messi?", "Quadro generale" |
| 2 | `GET /assistant-api/progetti` | Lista progetti con stato finanziario (contrattualizzato, pagato, residuo) | "Stato progetto X?" |
| 3 | `GET /assistant-api/moduli-senza-docente` | Moduli senza assegnazione attiva, raggruppati per progetto | "Quanti docenti mancano?", "Moduli scoperti?" |
| 4 | `GET /assistant-api/cerca-docenti` | Cerca docenti per tag/competenze + media valutazioni. Params: `competenza`, `valutazione_minima` | "Chi è esperto di informatica?" |
| 5 | `GET /assistant-api/saldi-da-liquidare` | Contratti con saldo > 0, importi e docenti | "Quanto dobbiamo pagare?", "Chi liquidare?" |
| 6 | `GET /assistant-api/ore-rendicontate` | Ore approvate vs contrattualizzate per modulo | "Quante ore mancano?" |
| 7 | `GET /assistant-api/graduatoria` | Graduatoria docenti per modulo. Params: `modulo_id` | "Prossimo idoneo per quel modulo?" |
| 8 | `GET /assistant-api/studenti-per-progetto` | Iscrizioni studenti con status e posti. Params: `progetto_id` | "Quanti studenti iscritti?" |
| 9 | `GET /assistant-api/valutazioni-docenti` | Medie valutazioni per docente. Params: `codice_fiscale` | "Come è valutato il docente Rossi?" |
| 10 | `GET /assistant-api/scadenze` | Contratti in scadenza, bandi aperti/chiusi, pagamenti programmati | "Cosa scade a breve?" |

### Specifiche controller

```php
class AssistantApiController extends Controller
{
    public $enableCsrfValidation = false; // Solo GET read-only

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['*' => ['GET']],
            ],
        ];
    }
}
```

Tutti gli endpoint rispondono `Response::FORMAT_JSON` e usano metodi model esistenti. Nessuna query SQL diretta.

### Esempi risposta JSON

**`/assistant-api/riepilogo-generale`:**
```json
{
  "progetti": {"totale": 5, "attivi": 3},
  "docenti": {"totale": 45, "con_assegnazione": 32},
  "studenti": {"totale": 120, "frequentanti": 95},
  "moduli": {"totale": 28, "senza_docente": 4, "completati": 8},
  "finanziario": {
    "budget_totale": 850000.00,
    "contrattualizzato": 620000.00,
    "pagato": 380000.00,
    "da_pagare": 240000.00
  }
}
```

**`/assistant-api/cerca-docenti?competenza=informatica&valutazione_minima=4`:**
```json
{
  "risultati": [
    {
      "codice_fiscale": "RSSMRA80A01H501Z",
      "cognome": "Rossi",
      "nome": "Mario",
      "provenienza": "TP",
      "tags": ["informatica", "programmazione", "database"],
      "valutazione_media": 8.5,
      "num_valutazioni": 12,
      "moduli_assegnati": 2
    }
  ],
  "totale": 3
}
```

**`/assistant-api/saldi-da-liquidare`:**
```json
{
  "contratti": [
    {
      "contratto_id": 15,
      "docente": "Rossi Mario",
      "modulo": "Programmazione Web",
      "progetto": "FSE-2024-DIGI",
      "compenso_totale": 4200.00,
      "pagato": 2100.00,
      "saldo": 2100.00,
      "ore_contrattualizzate": 60,
      "ore_rendicontate_approvate": 45
    }
  ],
  "totale_da_liquidare": 48500.00,
  "num_contratti": 12
}
```

## Chat UI

### Widget flottante
- Pallina in basso a destra con icona `ti ti-robot`
- Click apre pannello chat ~400x500px
- Pulsante espandi → pagina intera `/site/assistente`
- Badge stato modello: "Caricamento modello..." / "Pronto"
- Conversazione persistita in `localStorage` (max 50 messaggi)
- Icona ingranaggio per selezione modello (3B default / 7B avanzato)

### Prima apertura (onboarding)
- Check `navigator.gpu` → se assente, widget non compare + banner opzionale
- Check spazio disponibile prima del download
- Progress bar download modello con percentuale e dimensione
- Messaggio: "Sto scaricando il modello AI (~2GB). Solo la prima volta, poi resterà in cache."
- Dopo download → carica riepilogo generale da API
- Benvenuto: "Ciao! Ho caricato i dati aggiornati. Chiedimi quello che vuoi."

### Interfaccia chat
- Messaggi utente a destra (blu), bot a sinistra (grigio)
- Typing indicator animato durante generazione (streaming token dal worker)
- Supporto markdown nelle risposte (tabelle, grassetto, liste)
- Pulsante "Aggiorna dati" per ricaricare riepilogo generale
- Pulsante "Cancella conversazione"
- Suggerimenti rapidi cliccabili:
  - "Quadro generale"
  - "Docenti da liquidare"
  - "Moduli senza docente"

### Stile
Coerente con Tabler theme, dark card con bordi sottili.

### Compatibilita browser

| Browser | Supporto WebGPU |
|---------|-----------------|
| Chrome/Edge 113+ | Completo (Windows/macOS/ChromeOS) |
| Chrome Android 121+ | Android 12+, GPU Qualcomm/ARM |
| Firefox 141+ | Windows; macOS ARM64 da v145 |
| Safari 26+ | macOS Tahoe, iOS 26, iPadOS 26 |

Detection: `navigator.gpu` + `navigator.gpu.requestAdapter()` per verificare capacita reale.

## Integrazione WebLLM

### Setup modello
- Libreria: `@mlc-ai/web-llm` via CDN
- Inferenza in **Web Worker** via `CreateWebWorkerMLCEngine` (UI non bloccata)
- Modello default: Qwen2.5-3B-Instruct (~2GB download)
- Modello avanzato: Qwen2.5-7B-Instruct (~5GB) — selezionabile dall'utente
- Cache modello nel browser (Cache API)

### System prompt
```
Sei l'assistente AI di Albatros Manager, un sistema di gestione
docenti e corsi FSE/PNRR. Rispondi sempre in italiano.
Hai a disposizione delle funzioni per interrogare il database.
Usa le funzioni per rispondere con dati reali e aggiornati.
Se non riesci a trovare l'informazione, dillo chiaramente.
Formatta le risposte in modo chiaro con elenchi e tabelle quando utile.

DATI AGGIORNATI:
{riepilogo_generale_json}
```

### Definizione tools
Formato OpenAI-compatible con una function per ogni endpoint API. Ogni function ha nome, descrizione in italiano, e parametri tipizzati.

Esempio:
```javascript
tools: [
  {
    type: "function",
    function: {
      name: "cerca_docenti",
      description: "Cerca docenti per competenze/tag e valutazione minima",
      parameters: {
        type: "object",
        properties: {
          competenza: { type: "string", description: "Tag o competenza da cercare, es: informatica" },
          valutazione_minima: { type: "number", description: "Valutazione minima (1-10)" }
        }
      }
    }
  }
  // ... una function per ogni endpoint
]
```

## Gestione errori

| Scenario | Comportamento |
|----------|--------------|
| WebGPU non disponibile | Widget non compare. Banner: "Il tuo browser non supporta l'assistente AI. Requisiti: Chrome 113+, Firefox 141+, Safari 26+" |
| Download fallito | Retry con backoff + pulsante "Riprova" |
| Spazio insufficiente | Messaggio: "Spazio insufficiente. Servono almeno 2GB liberi." |
| API server non raggiungibile | "Non riesco a raggiungere il server. Riprova tra poco." Domande basate su riepilogo continuano |
| Function calling fallito | Fallback a keyword routing deterministico (vedi sezione Fallback) |
| Generazione troppo lenta (< 5 tok/s) | Suggerire modello 3B se sta usando il 7B |
| Timeout generazione | 30 secondi max |

## Sicurezza

- Endpoint `assistant-api/*` accessibili solo con sessione admin attiva (`AccessControl`)
- CSRF disabilitato sul controller (sicuro: solo endpoint GET read-only)
- `VerbFilter` restringe a solo metodo GET
- Nessuna query SQL diretta — solo metodi model predefiniti
- Input utente sanitizzato prima di passarlo come parametro API
- Il modello non puo modificare dati (read-only)

## File da creare/modificare

### Nuovi file (~13)

```
components/
  assets/WebLlmAsset.php              # Asset bundle (JS + CSS)

controllers/
  AssistantApiController.php           # 10 endpoint JSON read-only

views/
  layouts/_assistant-widget.php        # Widget HTML
  site/assistente.php                  # Pagina full-screen

web/
  js/assistant/
    assistant-engine.js                # Init WebLLM, gestione modello
    assistant-worker.js                # Web Worker per inferenza
    assistant-chat.js                  # UI chat, messaggi, suggerimenti
    assistant-tools.js                 # Definizione functions + fetch + fallback deterministico
  css/
    assistant.css                      # Stili widget + chat
```

### File da modificare (~2)

- `views/layouts/main.php` — includere widget `_assistant-widget.php`
- `config/web.php` — rotta per controller (le regole catch-all esistenti gia gestiscono il pattern `<controller>/<action>`, ma valutare se aggiungere regola esplicita per sicurezza)

### Dipendenze

- `@mlc-ai/web-llm` via CDN (nessun build step necessario)
- Nessuna migration DB
- Nessun model nuovo
- Nessuna modifica ai model esistenti (usiamo metodi gia presenti)

## Evoluzione futura (non in scope v1)

- **Azioni in scrittura**: assegnare docenti, approvare rimborsi (con conferma utente)
- **Contesto di pagina**: il bot sa su quale pagina/entita stai navigando
- **Estensione a docenti/studenti**: con endpoint API dedicati per area
- **Modelli piu grandi**: se WebGPU migliora, supportare modelli 13B+
