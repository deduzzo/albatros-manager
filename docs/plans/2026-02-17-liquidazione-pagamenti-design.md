# Design: Sistema Liquidazione Pagamenti Docenti

**Data**: 2026-02-17
**Priorita**: Alta

## Obiettivo

Implementare un sistema di liquidazione pagamenti docenti che permetta di:
1. Creare pagamenti facilmente dalla scheda modulo con importi pre-popolati
2. Alert intelligenti sullo stato rendicontazione ore
3. Pulsante "Paga solo ore rendicontate come acconto"
4. Auto-popolamento importi dal form pagamento selezionando un contratto
5. Liquidazione massiva con checkbox dalla sezione avviso/bando
6. Mostrare ore rendicontate nella rendicontazione finanziaria avviso

## 1. Vista Modulo - Sezione Pagamenti (`views/modulo/view.php`)

### Alert ore non rendicontate
Sopra il pulsante "Nuovo Pagamento", se `ore_approvate < ore_contrattualizzate`:

```
[Alert giallo]
Attenzione: il docente ha rendicontato X/Y ore (Z%).
Le ore non sono ancora completamente rendicontate.

[Pulsante verde] "Paga solo ore rendicontate come acconto"
```

Il pulsante "Paga solo ore rendicontate come acconto":
- Calcola importo = `ore_approvate × compenso_orario`
- Linka a `/pagamento/create?contratto_id=X&tipo=acconto&importo=CALCOLATO`
- Pre-imposta tipo = Acconto

### Pulsante "Nuovo Pagamento" standard
- Passa `contratto_id` al controller
- Il controller pre-popola `importo_lordo` = saldo rimanente (`compenso_totale - totalePagato`)
- Se contratto ha `ritenute` e `netto_docente`, calcola proporzionalmente
- Tipo pagamento: vuoto ma obbligatorio
- Status default: `programmato`

### Tabella pagamenti
- Aggiungere colonna Azioni con link view/edit per ogni pagamento

## 2. Form Pagamento Smart (`views/pagamento/_form.php`)

### AJAX auto-fill su selezione contratto
Quando l'utente seleziona un contratto dalla dropdown:
- Chiamata AJAX a `PagamentoController::actionContrattoInfo($id)`
- Risposta JSON:
  ```json
  {
    "compenso_totale": 6000,
    "totale_pagato": 2000,
    "saldo_rimanente": 4000,
    "ritenute_percentuale": 20,
    "ore_contrattualizzate": 100,
    "ore_approvate": 85,
    "ore_inserite": 10,
    "compenso_orario": 60,
    "docente": "Rossi Mario",
    "modulo": "MOD-001 - Chimica"
  }
  ```

### Info card contratto
Card laterale/superiore con riepilogo:
- Docente, Modulo
- Compenso totale, Pagato, Saldo rimanente
- Ore: approvate/contrattualizzate con barra progresso

### Alert ore inline
Se ore_approvate < ore_contrattualizzate, alert giallo nel form con info.

### Pulsante "Popola saldo rimanente"
Riempie importo_lordo col saldo rimanente, calcola ritenute/netto.

## 3. Vista Avviso - Rendicontazione Finanziaria (`views/avviso/view.php`)

### Colonne aggiuntive tabella moduli
- **Ore Rend.**: `X/Y` (ore_approvate / ore_contrattualizzate) + mini barra progresso
- **Checkbox**: per selezione liquidazione massiva
- **Azione rapida**: icona pagamento singolo per ogni riga

### Liquidazione massiva
- Checkbox su ogni riga (solo moduli con contratto e saldo > 0)
- Header: checkbox "Seleziona tutti"
- Toolbar fissa: "Liquida N selezionati" (appare con >= 1 selezionato)
- Click apre modale riepilogativa:
  - Tabella: docente | modulo | saldo rimanente | ore status | warning
  - Tipo pagamento obbligatorio (dropdown: saldo/acconto/rata)
  - Status: programmato (default)
  - Warning per docenti con ore incomplete
  - Conferma -> crea N pagamenti in transazione

### Controller: `AvvisoController` passa dati ore al rendiconto
Il controller gia' calcola `$rendiconto['moduli']`, aggiungere per ogni modulo:
- `ore_contrattualizzate`
- `ore_approvate`
- `ore_inserite`

## 4. Nuovi Endpoint (PagamentoController)

### `actionContrattoInfo($id)` - GET, JSON
Restituisce dati contratto + saldo + ore per AJAX form.

### `actionLiquidaMassiva()` - POST, JSON
Input: `{ contratti_ids: [1,2,3], tipo: "saldo", status: "programmato" }`
Per ogni contratto: crea Pagamento con importo = saldo rimanente.
Risposta: `{ success: true, count: 3, totale: 18000 }`

## 5. Parametri pre-compilazione da URL

`PagamentoController::actionCreate` accetta parametri GET opzionali:
- `contratto_id` (esistente)
- `tipo` (nuovo): pre-seleziona tipo pagamento
- `importo` (nuovo): pre-popola importo_lordo
- `importo_netto` (nuovo): pre-popola importo_netto

## 6. Fix bg-secondary

Sostituire tutte le occorrenze di `bg-secondary` nei model e views con classi Tabler native:
- Fallback/default: `bg-muted`
- Bozza: `bg-muted`
- Non specificato: `bg-muted`
