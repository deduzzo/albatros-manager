# Design: Modifiche Quarta Riunione

**Data**: 2026-04-01
**Origine**: Verbale quarta riunione (docs/quarta riunione - prima/seconda parte.txt)
**Stato**: Approvato

---

## 1. Fix Validazione Form

### Problema
I form (docente e probabilmente altri) non mostrano il feedback di errore di validazione su alcuni campi. In riunione emersi: provincia e partita IVA nel form docente non mostrano errore e impediscono il salvataggio senza spiegazione.

### Soluzione
Audit sistematico di tutti i `_form.php`:
- Verificare che ogni campo usi `$form->field()` con binding corretto al model
- Verificare che le `rules()` del model siano coerenti con i campi del form
- Verificare che il feedback testuale di errore sia visibile (messaggio sotto il campo)
- Verificare che i campi con errore siano evidenziati visivamente (bordo rosso / classe `is-invalid`)
- I campi con input manuale (non generati da ActiveForm) vanno convertiti a `$form->field()`

### Deliverable
Fix su tutti i form che presentano il problema. Nessuna modifica strutturale.

---

## 2. Ridenominazione Terminologia UI

### Mappatura

| Vecchio | Nuovo |
|---------|-------|
| Avvisi/Bandi | Corsi |
| Avviso/Bando | Corso |
| Moduli/Corsi | Moduli/Insegnamenti |

### Scope
Tutte le stringhe visibili all'utente: label, titoli pagina, breadcrumb, menu, placeholder, messaggi flash, colonne GridView, form di altre entità che referenziano "avviso".

### Fuori scope
NON si modificano: tabelle DB (`avviso`), nomi model (`Avviso.php`), nomi controller (`AvvisoController.php`), nomi viste (`views/avviso/`), route URL (`/avviso/view`).

---

## 3. Vista Semplificata — Architettura

### Principio
Area completamente separata dall'esistente. La modalità avanzata attuale NON viene toccata.

### Struttura tecnica
- **Controller**: `GestioneController.php`
- **Layout**: `views/layouts/gestione.php`
- **Viste**: `views/gestione/`
- **Route base**: `/gestione`

### Identità visiva
Layout basato su Tabler ma con identità propria:
- Header con colore accent diverso (es. indigo/viola)
- Selettore rapido corso in alto a destra (dropdown per cambiare corso senza tornare alla home)
- Sidebar sinistra fissa con le sezioni del corso selezionato
- Link "Passa a modalità avanzata" nel footer della sidebar
- Badge numerici nella sidebar per sezioni con alert attivi

### Flusso navigazione

**Home** (`/gestione`):
Griglia di card colorate, una per corso. Ogni card mostra:
- Titolo corso, fonte finanziamento
- Conteggi: moduli, docenti, studenti
- % completamento (moduli assegnati / totali)
- Stato (bozza, pubblicato, in corso, concluso)

**Dashboard corso** (`/gestione/corso?id=X`):
Sidebar con sezioni:

| Sezione | Contenuto |
|---------|-----------|
| **Panoramica** | Riepilogo, alert (docenti non contrattualizzati, presenze mancanti, borse bloccate) |
| **Piano Formativo** | Moduli del corso con stato assegnato/scoperto, pulsante "metti a bando" |
| **Personale** | Docenti assegnati, compensi, stato contratto |
| **Studenti** | Iscritti, presenze aggregate, % frequenza |
| **Calendario** | Lezioni programmate, import |
| **Contratti** | Lista contratti, genera, upload firmato, allegati |
| **Borse di Studio** | Istanze borsa, stato erogazione |
| **Import/Export** | Import per sezione, export consulente, export SEPA |

### Principio di riuso
Ogni sezione carica un'action del `GestioneController` che fa query filtrata per il corso e renderizza le viste CRUD esistenti (o partial dedicate). I form di creazione/modifica rimandano ai controller originali con `returnUrl` per tornare alla gestione.

---

## 4. Candidature Multi-modulo Lato Admin

### Problema
Lato admin l'inserimento candidatura è uno per modulo. I docenti dal loro lato possono già selezionare più moduli.

### Soluzione
Action `GestioneController::actionNuovaCandidatura($corso_id)`:
1. Form con selezione docente (da albo o nuovo) + checkbox multipli per i moduli del corso
2. Al submit: crea N istanze (una per modulo selezionato) in un'unica transazione DB
3. Redirect alla panoramica corso con flash "Candidatura inserita per N moduli"

Stessa funzionalità anche nell'area avanzata: pulsante "Nuova candidatura multi-modulo" nella vista `avviso/view.php`.

### "Metti a bando"
Nella lista moduli (Piano Formativo), i moduli scoperti (non assegnati) hanno una checkbox. Selezionandoli e cliccando "Metti a bando":
- Lo stato del corso passa da bozza a pubblicato
- Quei moduli diventano visibili nell'area pubblica docenti

---

## 5. Area Tutor

### Autenticazione
Stessa dell'area docenti (`docenteUser`). Il tutor entra da `/area-docenti/login`.

### Dashboard differenziata
Nel `DocenteAreaController`, se `docente.tipo === 'tutor'`, la dashboard mostra voci aggiuntive:
- **I miei corsi** — corsi dove è assegnato come tutor
- **Gestione Presenze** — compilazione presenze
- **Alert presenze mancanti** — lezioni passate senza presenze compilate

### Pagina presenze per lezione
Il tutor vede il calendario dei propri corsi con alert visivo (badge rosso) sulle lezioni con presenze non acquisite.

Click su una lezione → schermata unica:
- **Intestazione**: data, modulo, orario, aula
- **Sezione Docente**: nome docente, 3 stati radio (Presente / Assente / Non acquisito), ore effettive
- **Sezione Studenti**: tabella iscritti al corso, per ognuno 3 stati radio (Presente / Assente / Non acquisito), ore presenza
- Default: **Non acquisito** (nessun radio selezionato)
- Pulsante "Salva presenze"

### Presenze a 3 stati

| Valore DB | Stato | UI |
|-----------|-------|-----|
| `null` | Non acquisito | Radio vuoto / badge grigio |
| `1` | Presente | Radio verde |
| `0` | Assente | Radio rosso |

### Modifiche DB

**Tabella `presenza_allievo`**: campo `presente` da booleano a TINYINT nullable. `null` = non acquisito, `1` = presente, `0` = assente.

**Tabella `calendario`**: nuovi campi:
- `docente_presente` TINYINT nullable (null/0/1)
- `docente_ore_effettive` DECIMAL(5,2) nullable

---

## 6. Borse di Studio — Miglioramenti

### Vista semplificata
Sezione "Borse di Studio" nel corso filtra automaticamente per quel corso. Area avanzata: resta la vista generale attuale.

### Stati erogazione

| Stato | Significato | Badge Tabler |
|-------|-------------|--------------|
| `richiesta` | Domanda presentata | bg-azure-lt |
| `approvata` | Ha diritto (ISEE ok) | bg-green-lt |
| `erogazione_bloccata` | Assegnatario ma < 80% frequenza | bg-orange-lt |
| `erogata` | Pagamento effettuato | bg-teal-lt |
| `revocata` | Revocata con motivazione | bg-red-lt |

### Verifica automatica 80% frequenza
Calcolo: `ore_presenze_studente / ore_totali_corso * 100`.
- Se < 80%: status → `erogazione_bloccata`
- Se ≥ 80%: status → `approvata` (pronta per erogazione)

Trigger:
- Alla compilazione presenze da parte del tutor
- Su richiesta manuale (pulsante "Ricalcola frequenze")

### Export SEPA XML (pain.001)
Dalla lista borse approvate, pulsante "Genera file pagamento banca".
Genera XML pain.001 standard con: IBAN studente, importo, beneficiario, causale.

Configurazione una tantum in tabella `configurazione`:
- `banca_iban_fondazione`
- `banca_bic`
- `banca_creditor_id`
- `banca_nome_istituto`

### Import PDF domande
Predisposta solo l'interfaccia (pulsante + upload file). L'action salva il file e crea un record borsa con status `richiesta`. I campi li compila l'admin manualmente. Parsing automatico in attesa di esempi PDF.

---

## 7. Contratti nella Vista Semplificata

### Sezione "Contratti" nel corso
Lista contratti filtrata per il corso. Riusa la vista `contratto/index` con filtro automatico.
Per ogni contratto: docente, modulo, tipo (Partita IVA / CoCoCo), stato, importo.

### Azioni
- **Genera contratto** → riusa `ContrattoController::actionGenera()` (4 template già esistenti)
- **Upload firmato** → riusa `ContrattoController::actionUploadFirmato()`
- **Allega scansione** → nuovo: upload PDF/immagine come allegato generico al contratto (per contratti firmati a mano). Salva in `uploads/contratti/allegati/`. Nuovo campo `allegati_path` (JSON array) su `Contratto`.
- **Export consulente** → riusa `ReportController::actionExportConsulente` filtrato per il corso

### Alert "docenti non contrattualizzati"
Card warning nella Panoramica corso + widget nella dashboard admin principale (aggregato per tutti i corsi).

---

## 8. Alert e Dashboard Admin

### Alert nella dashboard principale
Visibili sia nella home vista semplificata che nella dashboard avanzata (`site/index`).

| Alert | Icona | Colore | Logica |
|-------|-------|--------|--------|
| Docenti non contrattualizzati | `ti-file-alert` | orange | Assegnazione attiva senza contratto collegato |
| Presenze non acquisite | `ti-clock-exclamation` | red | Lezioni con data passata e presenze `null` |
| Borse con frequenza < 80% | `ti-alert-triangle` | yellow | Borsa approvata ma % sotto soglia |

### Widget AlertWidget
Widget riutilizzabile in `components/widgets/AlertWidget.php`.
Accetta parametri:
- `tipo`: tipo alert (contratti, presenze, borse)
- `corso_id`: filtro per corso specifico (null = tutti)
- `compact`: modalità compatta per sidebar badge

Usato nella dashboard admin principale (tutti i corsi) e nella panoramica corso (filtrato).

### Badge sidebar
Nella vista semplificata, la sidebar del corso mostra badge numerico accanto alle sezioni con alert attivi (es. "Calendario (3)" per 3 lezioni con presenze mancanti).

---

## Fuori Scope

- Hosting/server (punto 10 riunione)
- Import PDF borse di studio (in attesa esempi)
- Modifiche alle aree docenti/studenti esistenti (oltre a differenziazione tutor)
- Modifiche a tabelle DB esistenti (nomi tabelle, nomi colonne) oltre ai nuovi campi specificati
- CBI: implementiamo pain.001 standard, formato custom per banca specifica rimandato a quando arrivano specifiche
