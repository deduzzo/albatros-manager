# Design: Login Unificato + Feature Mancanti

**Data**: 2026-02-21
**Stato**: Approvato

## 1. Pagina di Startup/Login Unificata

### Obiettivo
Sostituire la pagina di login admin (`/site/login`) con una landing page completa che funziona da punto di ingresso unico per tutti gli utenti del sistema (admin, docenti, studenti) e mostra anche contenuti pubblici (bandi aperti, corsi disponibili).

### Architettura

**URL**: `/site/login` (stessa route, nuova vista)
**Layout**: `views/layouts/login.php` (esistente, senza menu sidebar)
**Controller**: `SiteController::actionLogin()` - esteso per gestire anche login docente
**Vista**: `views/site/login.php` - riscritta completamente

### Struttura Pagina

#### Sezione 1: Hero
- Logo Albatros (`logo-badge-small.png`)
- Titolo "Albatros Academy"
- Sottotitolo "Sistema di Gestione Docenti FSE/PNRR"

#### Sezione 2: Login Cards (3 card affiancate)

**Card Amministratore** (icona `ti-shield-lock`, bordo blu):
- Form: username + password
- Submit → `Yii::$app->user->login()` (esistente)
- Redirect → `/site/index`

**Card Docente** (icona `ti-school`, bordo verde):
- Form: codice fiscale + password
- Link: "Registrati" → `/area-docenti/registrazione`
- Link: "Password dimenticata" → `/area-docenti/password-dimenticata`
- Submit → `Yii::$app->docenteUser->login()` (esistente)
- Redirect → `/area-docenti`

**Card Studente** (icona `ti-users`, bordo viola):
- Form: codice fiscale + password (futuro)
- Link: "Registrati" → `/demo-studenti/registrazione`
- Per ora: messaggio "Area in fase di attivazione" con link alla demo
- Redirect → `/demo-studenti`

**Comportamento UI**:
- Default: 3 card visibili con icona, titolo e breve descrizione
- Click su card → espande il form di login con animazione CSS
- Le altre card si comprimono
- Click fuori o su altra card → switch

#### Sezione 3: Contenuti Pubblici

**Bandi Aperti per Docenti**:
- Query: `Avviso::find()->where(['status' => 'pubblicato'])->andWhere(['>', 'data_scadenza', date('Y-m-d')])`
- Card per ogni bando con: titolo, progetto, scadenza, numero moduli
- Link "Vedi dettagli e iscriviti" → `/area-docenti/bandi/{id}` (richiede login)

**Corsi Disponibili per Studenti**:
- Query: `Progetto::find()->where(['status' => 'attivo'])`
- Card per ogni progetto con: titolo, fonte finanziamento, numero studenti, sedi
- Link "Scopri di piu" → `/demo-studenti/bando?id={id}`

#### Comportamento se gia autenticato
- Se `Yii::$app->user->isGuest === false` → banner "Sei collegato come Amministratore" + link dashboard
- Se `Yii::$app->docenteUser->isGuest === false` → banner "Sei collegato come Docente" + link area docenti

### File da Modificare/Creare

| File | Azione |
|------|--------|
| `views/site/login.php` | Riscrivere completamente |
| `controllers/SiteController.php` | Estendere `actionLogin()` per gestire POST docente |
| `config/web.php` | Aggiungere 'login' alle azioni guest del SiteController (gia OK) |

---

## 2. Feature Mancanti

### 2.1 Alert Docente Sovra-impegnato

**Cosa**: Warning visuale quando un docente ha troppi moduli/contratti attivi contemporaneamente.

**Implementazione**:
- `Docente::getModuliAttivi()` - count assegnazioni con contratto attivo (data_fine >= oggi)
- `Docente::isSovraimpegnato($soglia = 5)` - restituisce true se moduli attivi > soglia
- Badge rosso "Sovra-impegnato (N moduli)" in `views/docente/view.php`
- Colonna "Moduli attivi" in `views/docente/index.php` con ordinamento
- Warning nell'assegnazione: se il docente selezionato e' sovra-impegnato, mostra alert

**File**:
- `models/Docente.php` - aggiungere metodi
- `views/docente/view.php` - aggiungere badge
- `views/docente/index.php` - aggiungere colonna

### 2.2 Calcolo Compenso Automatico TP/RU

**Cosa**: Metodo che restituisce il compenso orario default in base a provenienza docente e tipo progetto.

**Implementazione**:
- `Docente::getCompensoDefault($fonteFinanziamento)` - logica:
  - PNRR + TP → 60 EUR/ora
  - PNRR + RU → 70 EUR/ora
  - FSE → valore da configurazione (o null se variabile)
  - MIM → valore da configurazione
- Helper in `Contratto::calcolaCompenso($docente, $modulo)` - precompila compenso nel form contratto
- Suggerimento nel form contratto: "Compenso suggerito: EUR X/ora (basato su provenienza TP/RU)"

**File**:
- `models/Docente.php` - aggiungere metodo
- `views/contratto/_form.php` - suggerimento visuale

### 2.3 Filtri Long List Avanzati

**Cosa**: Aggiungere filtri per valutazione media e numero contratti nella griglia docenti.

**Implementazione**:
- `DocenteSearch` - aggiungere attributi virtuali `valutazione_media` e `num_contratti`
- Join con `valutazione_docente` per media punteggi
- Join con `contratto` via `assegnazione` per conteggio contratti
- Colonne ordinabili nella GridView

**File**:
- `models/DocenteSearch.php` - estendere search()
- `views/docente/index.php` - aggiungere colonne

### 2.4 Export Excel per Consulente del Lavoro

**Cosa**: Template Excel specifico con i dati necessari al consulente per generare CoCoCo.

**Formato output** (colonne):
1. Cognome, Nome
2. Codice Fiscale
3. Data Nascita, Luogo Nascita
4. Indirizzo, CAP, Citta, Provincia
5. Email, Telefono
6. Titolo di Studio
7. Provenienza (TP/RU)
8. Nome Progetto
9. Nome Modulo
10. Sede
11. Ore Contrattualizzate
12. Compenso Orario
13. Compenso Totale
14. Tipo (Nuovo CoCoCo / Integrazione)
15. Data Inizio Lezioni
16. Data Fine Lezioni

**Implementazione**:
- `ReportController::actionExportConsulente($progetto_id)` - genera XLSX
- Filtro per progetto (obbligatorio)
- Download diretto
- Link nella vista progetto/view.php: "Esporta per Consulente"

**File**:
- `controllers/ReportController.php` - nuova action
- `views/progetto/view.php` - pulsante export
- `views/report/export.php` - opzione aggiuntiva

### 2.5 Questionario Anonimo Valutazione Docenti

**Cosa**: Form anonimo accessibile da studenti per valutare docenti a fine modulo.

**Flusso**:
1. Admin genera codici accesso per un modulo (batch di N codici casuali)
2. Codici stampati su foglietti e distribuiti a studenti
3. Studente accede a `/valutazione/{codice}` (pagina pubblica, no login)
4. Compila questionario: punteggio 1-5 su criteri + commento libero
5. Dopo invio il codice viene invalidato (uso singolo)

**Tabelle**:
- `valutazione_codice`: id, modulo_id, codice (VARCHAR 8, unique), usato_at, created_at
- Riusa tabella `valutazione_docente` esistente con FK a `valutazione_codice`

**Implementazione**:
- `ValutazioneDocenteController::actionGeneraCodici($modulo_id, $quantita)` - genera codici
- `ValutazioneDocenteController::actionStampaCodici($modulo_id)` - pagina stampabile
- Nuovo controller `ValutazionePublicController` (accesso guest):
  - `actionIndex($codice)` - verifica codice, mostra form
  - `actionSubmit()` - salva valutazione, invalida codice
- Vista pubblica con layout login (no menu)

**File**:
- Migration: `m260221_create_valutazione_codice_table.php`
- `models/ValutazioneCodice.php`
- `controllers/ValutazionePublicController.php`
- `views/valutazione-public/index.php`
- `controllers/ValutazioneDocenteController.php` - azioni genera/stampa
- `views/valutazione-docente/stampa-codici.php`

---

## 3. Piano Esecuzione con Team Agenti

### Onda 1 - Login Unificato (1 agente full-stack)
- Riscrivere `views/site/login.php`
- Estendere `SiteController::actionLogin()`
- Testare tutti e 3 i flussi di login

### Onda 2 - Feature Quick (2 agenti full-stack paralleli)
- Agente A: Alert sovra-impegnato + Calcolo compenso TP/RU
- Agente B: Filtri long list avanzati

### Onda 3 - Feature Medie (2 agenti full-stack paralleli)
- Agente A: Export Excel consulente
- Agente B: Questionario anonimo (migration + model + controller + vista)

### Tester - Verifica finale via Chrome
