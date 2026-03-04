# Albatros Manager - Specifica Funzionale v2.0

> **BOZZA** - Documento in revisione
> Ultimo aggiornamento: 04/03/2026
> Redatto da: Roberto De Domenico
> Committente: Fondazione ITS Academy "Albatros"

---

## 1. Premessa

Il presente documento descrive le evoluzioni richieste per il sistema Albatros Manager, emerse dalla terza riunione operativa e dall'analisi della documentazione amministrativa fornita (contratti, timesheet, dichiarazioni, bandi borse di studio, comunicazioni di liquidazione).

Il sistema attualmente gestisce: progetti, avvisi, moduli, docenti, candidature, assegnazioni, contratti, pagamenti, rendicontazione ore, area docenti, area studenti, configurazione fondazione.

Le evoluzioni descritte di seguito sono organizzate per area funzionale e ordinate per priorita' di implementazione.

---

## 2. Generazione Contratti PDF

### 2.1 Descrizione
Il sistema deve generare contratti in formato PDF a partire da template precompilati con i dati presenti nel database. Sono previste due tipologie contrattuali con template distinti.

### 2.2 Tipologia A - Prestazione Professionale (Partita IVA)
Contratto breve (2 pagine) che include:
- Intestazione con loghi istituzionali (UE, FSE/PNRR, Fondazione, ITS)
- Luogo e data
- Dati del docente (nome, cognome, luogo e data nascita, residenza, codice fiscale)
- Oggetto dell'incarico con riferimento a:
  - Avviso di riferimento
  - Progetto e sede di erogazione
  - CIP e CUP
- Modulo formativo assegnato con ore e compenso orario
- Importo complessivo lordo
- Clausole standard (autonomia, nessun vincolo subordinazione, obblighi documentali)
- Documentazione da consegnare a fine modulo: relazione finale, timesheet, dispense, materiale didattico, questionari, prove, valutazione allievi
- Spazio firma docente e firma/timbro Presidente

### 2.3 Tipologia B - Collaborazione Coordinata e Continuativa (CoCoCo)
Contratto articolato (~6 pagine) ai sensi dell'Art. 409 c.p.c. e art. 2 D.Lgs. n. 81/2015, che include:
- Premesse dettagliate (descrizione Fondazione, progetto, percorso formativo, convenzioni)
- Verifica requisiti e assenza incompatibilita'
- Articoli:
  - Art. 1 - Premesse
  - Art. 2 - Natura dell'incarico e Finalita'
  - Art. 3 - Attivita' (elenco compiti dettagliati)
  - Art. 4 - Sede
  - Art. 5 - Durata (data inizio/fine con possibilita' proroghe)
  - Art. 6 - Compenso (lordo orario, totale ore, importo complessivo, esenzione IVA, contributi)
  - Art. 7 - Trattamento dati personali
  - Art. 8 - Recesso
- Doppia firma: Committente (Presidente) e Collaboratore

### 2.4 Implementazione prevista
- Template HTML con segnaposto per i dati variabili
- Generazione PDF tramite libreria (dompdf o wkhtmltopdf)
- Pulsante "Genera Contratto" nella scheda contratto
- Anteprima a schermo prima della generazione definitiva
- Archiviazione automatica del PDF generato

---

## 3. Firma Contratti

### 3.1 Descrizione
Gestire la firma dei contratti in modalita' digitale per eliminare il ciclo stampa-firma-scansione-invio.

### 3.2 Modalita' previste

**Fase 1 - Upload documento firmato**
- Il sistema genera il contratto PDF
- L'admin lo invia al docente (via email o area personale)
- Il docente lo firma (scansione o firma digitale P7M) e lo ricarica
- L'admin verifica e aggiorna lo stato a "firmato"

**Fase 2 - Firma digitale P7M (da inserire nei prossimi bandi come requisito)**
- Il docente scarica il PDF, lo firma con la propria firma digitale
- Carica il file .p7m nel sistema
- Il sistema verifica automaticamente la validita' della firma e la corrispondenza del codice fiscale

**Fase 3 - Firma OTP via SMS (subordinata a parere legale)**
- Verifica numero cellulare del docente (validazione in ufficio con OTP)
- Al momento della firma: il contratto viene visualizzato a schermo, invio OTP via SMS, il docente inserisce il codice
- Il sistema registra: timestamp, IP, numero cellulare, OTP utilizzato
- Il contratto risulta firmato digitalmente

### 3.3 Campi aggiuntivi nel database
- Stato firma del contratto (non firmato, inviato, firmato, verificato)
- Tipo di firma (scansione, P7M, OTP)
- File del documento firmato
- Data e ora della firma

---

## 4. Workflow Pagamenti: Fatturazione e Cedolini

### 4.1 Descrizione
Introdurre uno step intermedio tra la rendicontazione delle ore e il pagamento effettivo, differenziato per tipo di contratto.

### 4.2 Flusso per docenti con Partita IVA
```
Ore rendicontate e approvate
  -> Admin avvia "Richiesta Fatturazione"
  -> Il sistema genera comunicazione con:
     - Importo lordo da fatturare
     - Dettaglio modulo, ore, sede
     - Riferimenti progetto (CIP, CUP, Avviso)
     - Dati intestazione fattura della Fondazione:
       Fondazione ITS Albatros
       Viale Giostra, 2 - 98121 Messina
       P.IVA e C.F.: 03110100835
       Codice destinatario: KRRH6B9
     - Richiesta di indicare IBAN
  -> Invio al docente (email e/o notifica area personale)
  -> Docente emette fattura e la carica nel sistema
  -> Admin verifica e procede al pagamento
```

### 4.3 Flusso per docenti CoCoCo
```
Ore rendicontate e approvate
  -> Admin avvia "Richiesta Cedolini" al consulente del lavoro
  -> Il sistema genera prospetto con:
     - Elenco docenti CoCoCo da pagare nel periodo
     - Per ciascuno: dati anagrafici, modulo, ore, importo
  -> Invio al consulente (email e/o export XLSX)
  -> Consulente elabora cedolini e Unilav
  -> Admin carica cedolini nel sistema
  -> Cedolini disponibili per il docente nella sua area personale
  -> Pagamento effettivo
```

### 4.4 Funzionalita' richieste
- Azione massiva: selezionare piu' docenti e generare richieste in blocco
- Stati del pagamento aggiuntivi: `richiesta_fattura`, `fattura_ricevuta`, `richiesta_cedolino`, `cedolino_pronto`
- Upload fattura/cedolino con associazione al pagamento
- Notifica al docente quando il cedolino e' disponibile

---

## 5. Area Docente: Documenti

### 5.1 Descrizione
Nella propria area personale, il docente deve poter consultare e scaricare i documenti relativi ai propri incarichi.

### 5.2 Documenti disponibili
- **Contratto**: PDF generato dal sistema o caricato dall'admin
- **Cedolino**: caricato dall'admin dopo elaborazione del consulente
- **Comunicazione di liquidazione**: generata dal sistema
- **Timesheet mensile**: generato dal sistema (vedi sez. 6)
- **Altri documenti**: eventuali allegati caricati dall'admin

### 5.3 Notifiche
- Quando un nuovo documento viene caricato, il docente riceve notifica (visibile nell'area personale)
- Opzionalmente: invio email con avviso di disponibilita' del documento

---

## 6. Timesheet Mensile (Allegato 12)

### 6.1 Descrizione
Generare il timesheet mensile nel formato richiesto dalla Regione Sicilia (Allegato 12 al Vademecum per l'attuazione v1.0, PR Sicilia FSE+ 2021-2027).

### 6.2 Struttura del documento
**Intestazione:**
- Riferimenti normativi (PR Sicilia FSE+, CCI)
- Dipartimento, Priorita', Obiettivo specifico
- Codice settore intervento, Azione, Avviso n., Descrizione, DDG

**Dati progetto:**
- Titolo Progetto, CIP, CUP, Codice SI, Beneficiario
- Nominativo e Codice Fiscale della risorsa umana
- Mese e anno di riferimento

**Griglia ore:**
- Righe: descrizione attivita' svolta (fino a 7 righe)
- Colonne: giorni del mese (1-31)
- Celle: ore lavorate per giorno
- Totale ore per riga e per colonna
- Nota: specificare eventuale aggiornamento professionale, assemblea sindacale, permesso, malattia, ore di disponibilita'

**Firme:**
- Firma della risorsa umana (docente)
- Timbro del Beneficiario e firma del Rappresentante Legale

### 6.3 Implementazione
- Il sistema genera il timesheet a partire dai dati di rendicontazione ore gia' inseriti dal docente
- Selezione per docente e mese
- Export PDF conforme al template regionale
- Possibilita' di generazione massiva (tutti i docenti di un progetto per un dato mese)

---

## 7. Coordinatori e Tutor

### 7.1 Descrizione
Gestire le figure di sistema (coordinatore didattico e tutor) che operano a livello di progetto.

### 7.2 Caratteristiche
| | Coordinatore | Tutor |
|--|---|---|
| Collegamento | Progetto | Progetto |
| Numero per progetto | Solitamente 1 | 1-2 |
| Rendicontazione ore | Si, a livello progetto | Si, a livello progetto |
| Tipo contratto | P.IVA o CoCoCo | P.IVA o CoCoCo |
| Pagamento | Come docenti | Come docenti |
| Documentazione | Autorizzazione provveditorato/STAR | Autorizzazione dirigente scolastico |
| Albo | Si (unico con docenti o separato) | Si |

### 7.3 Funzionalita'
- Sezione dedicata nell'amministrazione per gestire coordinatori e tutor per progetto
- Associazione documenti (autorizzazioni, CV, altro) con flag "validato"
- Rendicontazione ore legata al progetto (non al singolo modulo)
- Stessa logica di pagamento dei docenti (fatturazione/cedolini)
- Possibilita' di filtrare e cercare nell'albo

---

## 8. Albo Docenti (Long List)

### 8.1 Descrizione
Creare un albo unico dei docenti idonei, trasversale a tutti i progetti e fonti di finanziamento (FSE e PNRR), da cui attingere per le assegnazioni senza necessita' di un nuovo bando per ogni progetto.

### 8.2 Funzionamento
- L'admin inserisce nell'albo i docenti risultati idonei dai bandi
- L'albo ha validita' temporale (data scadenza configurabile)
- L'admin puo' "pescare" dall'albo per assegnare docenti a nuovi moduli/progetti
- Possibilita' di cancellazione dall'albo (su richiesta del docente o d'ufficio)
- Periodicamente si richiede aggiornamento/conferma ai docenti iscritti

### 8.3 Filtri disponibili
- Per competenza/tag curriculum
- Per sede di disponibilita'
- Per tipo contratto preferibile (P.IVA / CoCoCo)
- Per valutazione media ricevuta dagli studenti
- Per fonte finanziamento di origine (FSE/PNRR)
- Per stato (attivo, scaduto, cancellato)

### 8.4 Flusso
```
Docente partecipa a bando -> CTS valuta -> Idoneo
  -> Admin aggiunge all'Albo (con data scadenza)
  -> Per nuovo progetto: Admin cerca nell'Albo
  -> Assegna docente al modulo (senza nuovo bando)
  -> Contratto e flusso standard
```

---

## 9. Contabilita' e Bilancio

### 9.1 Descrizione
Estendere il sistema con funzionalita' di contabilita' semplificata per avere un quadro economico completo di ogni progetto.

### 9.2 Entrate: Accrediti e Tranche
Per ogni progetto, registrare:
- Importo finanziamento totale previsto
- Tranche ricevute (importo, data accredito, numero tranche)
- Saldo da ricevere

### 9.3 Uscite: Spese
Due categorie:
- **Spese di progetto**: legate a un progetto specifico (materie prime, visite didattiche, materiali)
- **Spese generali**: non legate a un progetto (affitti, utenze, cancelleria, spese di gestione)

Per ogni spesa: categoria, descrizione, importo, data, fornitore, documento giustificativo allegato.

Categorie di spesa configurabili dall'admin (es: affitto, utenze, materiali didattici, visite, trasporti, alimenti, attrezzature).

### 9.4 Report e Bilancio
- **Quadro economico per progetto**: entrate (tranche) - uscite (pagamenti docenti + spese progetto)
- **Bilancio generale**: tutte le entrate - tutte le uscite (incluse spese generali)
- **Report annuale per competenza**: filtro per periodo, necessario per il commercialista
- **Report per avviso**: liquidazioni filtrate per avviso e periodo
- **Export XLSX**: formato compatibile con le richieste del consulente/commercialista

---

## 10. Borse di Studio (PNRR)

### 10.1 Descrizione
Gestire l'assegnazione e l'erogazione delle borse di studio per gli allievi dei percorsi ITS Academy finanziati con fondi PNRR, conformemente al bando di concorso della Fondazione.

### 10.2 Requisiti di accesso
- Iscrizione a corso ITS Academy finanziato PNRR
- ISEE Universitario <= 27.948,60 EUR
- ISPE <= 60.757,87 EUR
- Frequenza >= 80% delle ore

### 10.3 Importi e Maggiorazioni

| Tipologia | In Sede | Pendolare | Fuori Sede |
|-----------|---------|-----------|------------|
| Base annuo | 2.481,75 | 3.598,51 | 6.157,74 |
| ISEE basso (+15%) | 2.854,01 | 4.138,28 | 7.081,40 |
| Studentesse (+20%) | 2.978,10 | 4.318,21 | 7.389,28 |
| Disabilita' (+40%) | 3.474,45 | 5.037,91 | 8.620,83 |
| Tirocinio estero | +600/mese | +600/mese | +600/mese |

Le maggiorazioni non sono cumulabili.

### 10.4 Erogazione
- Trimestrale, in 4 quote posticipate
- Subordinata al raggiungimento dell'80% di frequenza nel trimestre
- Possibilita' di recupero nei trimestri successivi
- Erogazione su conto corrente o carta ricaricabile intestata allo studente

### 10.5 Funzionalita' previste (fase futura)
- Registrazione domande borse di studio con upload documentazione (ISEE, DSAN, contratto locazione)
- Calcolo automatico importo in base a tipologia studente e maggiorazioni
- Tracciamento erogazioni trimestrali
- Verifica automatica requisito frequenza (dati presenze)
- Gestione revoche e richieste rimborso

---

## 11. Filtri e Report

### 11.1 Filtri Docenti (priorita' alta)
- **Per tipo contratto**: P.IVA / CoCoCo - per permettere alla CTS di privilegiare le P.IVA
- **Per sede di disponibilita'**: docenti filtrabili per sede di erogazione
- **Per valutazione**: media valutazioni studenti
- **Per competenze**: tag curriculum assegnati
- **Per stato nell'albo**: attivo/scaduto

### 11.2 Report
- **Prospetto per consulente del lavoro**: elenco docenti da contrattualizzare con dati completi
- **Export consulente lavoro**: gia' implementato, da estendere con filtro tipo contratto
- **Report liquidazioni annuali**: per competenza, filtrabile per periodo e avviso
- **Timesheet mensile**: Allegato 12 (vedi sez. 6)

---

## 12. Riepilogo Priorita'

### Fase 1 - Immediata
| # | Feature | Stima |
|---|---------|-------|
| 1 | Filtro docenti per tipo contratto (P.IVA/CoCoCo) | 1 giorno |
| 2 | Upload documento firmato su contratto | 1 giorno |
| 3 | Generazione contratto PDF (2 template) | 3-4 giorni |
| 4 | Workflow richiesta fatturazione/cedolini | 3-4 giorni |

### Fase 2 - Breve termine
| # | Feature | Stima |
|---|---------|-------|
| 5 | Area docente: documenti scaricabili | 2 giorni |
| 6 | Timesheet Allegato 12 (PDF export) | 2-3 giorni |
| 7 | Coordinatori e Tutor | 3-4 giorni |
| 8 | Albo Docenti (Long List) | 4-5 giorni |

### Fase 3 - Medio termine
| # | Feature | Stima |
|---|---------|-------|
| 9 | Contabilita' (spese, accrediti, bilancio) | 5-6 giorni |
| 10 | Dichiarazione Allegato 14 (PDF) | 1-2 giorni |
| 11 | Firma digitale P7M/OTP | 4-5 giorni |
| 12 | Borse di Studio | 5-6 giorni |

---

*Documento in bozza. Da sottoporre a revisione con il committente prima dell'implementazione.*
