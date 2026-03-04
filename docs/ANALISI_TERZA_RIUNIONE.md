# Analisi Terza Riunione - Nuovi Requisiti e Feature

> Data riunione: ~Marzo 2026
> Partecipanti: Roberto (sviluppatore), Presidente, Segreteria Amministrativa, Responsabile Contabilita'
> Fonti: `docs/terza riunione.txt`, `docs/DATI REALI/bilancio-rendiconti-contratti-documenti/`

---

## 1. GENERAZIONE CONTRATTI PDF

### Stato attuale
Il sistema crea record contratto nel DB ma NON genera PDF.

### Requisiti emersi
- **2 tipologie di contratto** con template diversi:
  - **Prestazione Professionale (P.IVA)**: contratto piu' semplice, 2 pagine (vedi `contabilita.pdf` pag.1-2)
  - **Collaborazione Coordinata e Continuativa (CoCoCo)**: contratto piu' articolato, ~6 pagine con articoli dettagliati (vedi `contabilita.pdf` pag.3-11)
- Campi variabili nel template: dati docente (nome, CF, indirizzo, P.IVA), modulo, ore, compenso orario, importo totale, sede, CIP, CUP, date inizio/fine
- **Priorita' alta**: la presidente vuole filtrare docenti per tipo contratto PRIMA dell'assegnazione per ridurre i CoCoCo

### Azione
- Creare template PDF (HTML->PDF con dompdf/wkhtmltopdf) per entrambe le tipologie
- Aggiungere pulsante "Genera Contratto PDF" nella view contratto
- I dati vengono pre-compilati dal DB (docente, modulo, assegnazione)

---

## 2. FIRMA DIGITALE / FIRMA CONTRATTI

### Opzioni discusse (in ordine di preferenza)
1. **Firma digitale P7M** - Costo zero per la fondazione, il docente carica il PDF firmato. Il sistema verifica la validita' del P7M. Da mettere come requisito nei prossimi bandi.
2. **Firma OTP via SMS** - Gateway SMS (~10€/10.000 SMS). Il docente riceve OTP, lo inserisce, il contratto risulta firmato. Validita' legale da verificare con avvocato.
3. **Ente certificatore (es. InfoCert)** - ~3-5€ a firma. Firma certificata, nessun dubbio legale.

### Decisione
- **Fase 1**: Implementare upload documento firmato (P7M o scansione) sul contratto
- **Fase 2**: Valutare firma OTP dopo parere legale
- **Da bando**: Richiedere ai docenti di dotarsi di firma digitale

### Campi DB da aggiungere
- `contratto.documento_firmato` (path file)
- `contratto.tipo_firma` ENUM('nessuna','scansione','p7m','otp')
- `contratto.data_firma`
- `contratto.firmato` BOOLEAN

---

## 3. WORKFLOW PAGAMENTI: RICHIESTA FATTURAZIONE / CEDOLINI

### Stato attuale
Il flusso e': Rendicontazione ore -> Pagamento. Manca uno step intermedio.

### Nuovo workflow richiesto
```
Docente rendiconta ore
    -> Admin verifica/approva ore
    -> SE P.IVA: Richiesta fatturazione al docente (email con dettagli importo)
       SE CoCoCo: Richiesta cedolino al consulente del lavoro (email con lista docenti)
    -> Docente emette fattura / Consulente elabora cedolino
    -> Upload fattura/cedolino nel sistema
    -> Pagamento effettivo
```

### Dettagli richiesta fatturazione (da email esempio pag.8-9 contabilita.pdf)
La comunicazione al docente deve contenere:
- Importo lordo da fatturare
- Modulo e ore di riferimento
- Sede di erogazione
- Riferimenti progetto (CIP, CUP, Avviso)
- Dati intestazione fattura (Fondazione ITS Albatros, Viale Giostra 2, 98121 Messina, P.IVA 03110100835, codice destinatario KRRH6B9)
- Richiesta IBAN in calce

### Azione
- Aggiungere stato pagamento: `richiesta_fattura` / `richiesta_cedolino`
- Funzionalita' "Richiedi fatturazione" massiva: seleziona docenti pronti, genera email
- Funzionalita' "Richiedi elaborazione cedolini" al consulente: lista docenti CoCoCo da pagare
- Upload fattura/cedolino nel record pagamento
- Notifica al docente (area personale + email opzionale)

---

## 4. AREA DOCENTE: DOCUMENTI SCARICABILI

### Requisiti
Il docente nella sua area personale deve poter:
- Visualizzare e scaricare il proprio **contratto** (PDF generato o caricato)
- Visualizzare e scaricare i **cedolini** (caricati dall'admin)
- Ricevere **notifiche** quando un nuovo documento e' disponibile

### Azione
- Sezione "I Miei Documenti" nell'area docente
- Tabella `documento_docente` (tipo: contratto/cedolino/altro, file_path, docente_cf, data_caricamento)
- Notifica email opzionale al caricamento

---

## 5. TIMESHEET MENSILE (Allegato 12)

### Requisiti (dal template regionale `rendicontazione.pdf`)
La Regione Sicilia richiede un timesheet mensile (Allegato 12 del Vademecum) con:
- Intestazione: Titolo Progetto, CIP, CUP, Codice SI, Beneficiario
- Dati docente: Nominativo, Codice Fiscale
- Griglia: Descrizione attivita' x Giorni del mese (1-31) con ore per giorno
- Totale ore per attivita' e per giorno
- Firma docente + Timbro/firma Rappresentante Legale

### Azione
- Generare il timesheet PDF dal sistema (dati rendicontazione ore gia' presenti)
- Export in formato conforme all'Allegato 12
- Possibilita' di generarlo per mese e per docente

---

## 6. DICHIARAZIONE DOCENTI (Allegato 14)

### Requisiti (dal template `All_14_Dichiarazione docenti`)
Documento di autocertificazione esperienza professionale e didattica:
- Dati progetto (titolo, ID, CIP, CUP)
- Dati docente e moduli assegnati
- Dichiarazione anni esperienza professionale (tabella esperienze con durata)
- Dichiarazione anni esperienza didattica (tabella esperienze con durata)
- Allegato CV
- Firma docente + Visto Rappresentante Legale

### Azione
- Generare PDF pre-compilato con dati dal sistema
- Il docente completa le parti mancanti (esperienze) e firma
- Upload nel sistema come documento allegato all'istanza

---

## 7. COORDINATORI E TUTOR

### Requisiti
Oltre ai docenti, esistono figure di sistema:
- **Coordinatore didattico**: 1 per progetto (max), collegato al progetto (non al modulo), rendiconta ore, viene pagato
- **Tutor**: 1-2 per progetto, collegato al progetto, rendiconta ore, viene pagato
- Possono essere sia P.IVA che CoCoCo
- Necessitano di documentazione specifica (autorizzazioni provveditorato/dirigente scolastico)
- Devono avere un **albo** (come i docenti)

### Azione
- Aggiungere campo `ruolo` ENUM('docente','coordinatore','tutor') alla tabella `docente` o creare tabella separata `figura_progetto`
- Rendicontazione ore legata al progetto (non al modulo)
- Sezione dedicata nell'admin per gestire coordinatori/tutor per progetto
- Documentazione allegabile con flag "validato"

---

## 8. ALBO DOCENTI (Long List)

### Situazione attuale
Il docente si candida per un bando specifico -> CTS valuta -> assegnato/idoneo/non_idoneo.

### Nuovo modello
- I docenti idonei da tutti i bandi vengono inseriti in un **albo unico** (long list)
- L'albo e' **cross-progetto**: un idoneo puo' essere assegnato a moduli di altri progetti
- L'admin "pesca" dall'albo per assegnare docenti a nuovi progetti senza nuovo bando
- L'albo ha una **validita' temporale** con scadenza e possibilita' di aggiornamento/cancellazione
- L'iscrizione all'albo e' gestita dall'admin (non dal docente direttamente)

### Azione
- Aggiungere tabella `albo_docente` (docente_cf, data_iscrizione, data_scadenza, status, note)
- Vista admin: "Albo Docenti" con filtri per competenze, sede, tipo contratto, valutazione
- Azione "Assegna da Albo" nel modulo: cerca nell'albo docenti idonei per quel tipo di insegnamento
- Scadenza automatica con notifica

---

## 9. CONTABILITA' E BILANCIO

### Requisiti emersi
- **Report liquidazioni per periodo**: filtro per anno, per avviso, per docente. Necessario per bilancio annuale (competenza vs cassa)
- **Tracciamento accrediti/tranche**: per ogni progetto, registrare quanto arriva dalla regione (tranche parziali)
- **Spese vive/generali**: gestione nota cassa digitale per spese non legate a docenza (affitti, utenze, materie prime, visite)
  - Spese per progetto specifico
  - Spese generali (non legate a un progetto)
- **Quadro economico**: dare (accrediti) - avere (pagamenti docenti + spese) = saldo
- **Export per consulente**: report in formato simile al template regionale

### Azione
- Tabella `accredito_progetto` (progetto_id, importo, data, tranche_n, note)
- Tabella `spesa` (progetto_id nullable, categoria, descrizione, importo, data, fornitore, documento)
- Tabella `categoria_spesa` (nome, tipo: progetto/generale)
- Dashboard bilancio: entrate vs uscite per progetto e generali
- Report annuale filtrabile per periodo

---

## 10. BORSE DI STUDIO (PNRR)

### Requisiti (da `Borse di studio.pdf`)
Sistema di borse di studio per allievi dei percorsi ITS Academy finanziati con PNRR:
- Requisiti: ISEE < 27.948,60, ISPE < 60.757,87, frequenza >= 80%
- Importi variabili per tipologia studente:
  - In sede: 2.481,75 - 3.474,45 EUR
  - Pendolare: 3.598,51 - 5.037,91 EUR
  - Fuori sede: 6.157,74 - 8.620,83 EUR
- Maggiorazioni: ISEE basso (+15%), studentesse (+20%), disabilita' (+40%), tirocinio estero (+600/mese)
- Erogazione trimestrale, subordinata a frequenza >= 80%
- Possibilita' di revoca per mancato raggiungimento requisiti

### Azione (fase futura)
- Modulo gestione borse di studio nell'area studenti
- Calcolo automatico importo in base a tipologia e maggiorazioni
- Tracciamento erogazioni trimestrali
- Verifica automatica requisiti frequenza

---

## 11. FILTRI E REPORT PRIORITARI

### Richieste esplicite dalla presidente
1. **Filtro docenti per tipo contratto** (P.IVA vs CoCoCo) - URGENTE
2. **Filtro docenti per sede**
3. **Filtro docenti per valutazione**
4. **Report per consulente del lavoro**: elenco docenti da contrattualizzare con tipo contratto

### Azione
- Aggiungere filtro `provenienza` (TP/RU) e `tipo_contratto_preferito` nella ricerca docenti
- Report export XLSX per consulente

---

## PRIORITA' IMPLEMENTAZIONE

| # | Feature | Priorita' | Complessita' |
|---|---------|-----------|-------------|
| 1 | Filtro docenti per tipo contratto | URGENTE | Bassa |
| 2 | Generazione contratto PDF (2 template) | Alta | Media |
| 3 | Workflow richiesta fatturazione/cedolini | Alta | Media |
| 4 | Upload documenti firmati su contratto | Alta | Bassa |
| 5 | Area docente: documenti scaricabili | Media | Bassa |
| 6 | Timesheet Allegato 12 (PDF export) | Media | Media |
| 7 | Coordinatori e Tutor | Media | Media |
| 8 | Albo Docenti (Long List) | Media | Alta |
| 9 | Contabilita' (spese, accrediti, bilancio) | Media | Alta |
| 10 | Borse di Studio | Bassa | Alta |
| 11 | Firma digitale (OTP/P7M) | Bassa | Alta |
| 12 | Dichiarazione Allegato 14 | Bassa | Bassa |
