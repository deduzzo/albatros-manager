# Albatros Manager - Gestione Docenti FSE/PNRR

Sistema di gestione per docenti e corsi finanziati da FSE, PNRR e MIM.

> **REGOLA FONDAMENTALE**:
> - Questo e' l'UNICO file CLAUDE.md del progetto.
> - **NON CREARE MAI** altri file CLAUDE.md o .md nelle sottocartelle.
> - Se trovi altri CLAUDE.md nelle sottocartelle, **ELIMINALI IMMEDIATAMENTE**.

## Stack Tecnologico

- **Framework**: Yii2 Basic Template, **PHP** 8.4, **DB**: MySQL 8.0, **Container**: Docker
- **UI**: Tabler Dashboard Theme v1.4.0 (`web/assets/tabler/`), Asset Bundle: `app\components\assets\TablerAsset`
- **Select con ricerca**: Tom Select 2.4.3 (CDN), Asset Bundle: `app\components\assets\TomSelectAsset` — auto-init su tutti `select.form-select` con 5+ opzioni. Escludere: classe `no-tom-select`. Forzare: classe `tom-select`. Re-init dinamico: `window.initTomSelectOn(selectEl)`.
- **Icons**: Tabler Icons v3.36.1 → `<i class="ti ti-{nome}"></i>` ([catalogo](https://tabler.io/icons))

### Badge e Colori Tabler

**NON usare classi Bootstrap** (`bg-success`, `bg-warning`). Usare SOLO classi Tabler native.

| Tipo | Pattern | Esempio |
|------|---------|---------|
| Solid | `badge bg-{colore} text-{colore}-fg` | `bg-green text-green-fg` |
| Light | `badge bg-{colore}-lt` | `bg-green-lt` |
| Outline | `badge badge-outline text-{colore}` | `badge-outline text-green` |

**Colori**: `blue`, `azure`, `indigo`, `purple`, `pink`, `red`, `orange`, `yellow`, `lime`, `green`, `teal`, `cyan`, `default`, `muted`

**Mapping Bootstrap → Tabler**: success→`bg-green`, warning→`bg-orange`, danger→`bg-red`, primary→`bg-blue`, info→`bg-azure`, secondary→`bg-default` (aggiungere sempre `text-{colore}-fg` per solid)

**Nei Model**: usare `match()` per `getStatusBadgeClass()`. **Nelle View**: NON usare colori inline, usare `$model->getTablerColor()` per model con colore hex (es. `Avviso`).

## Autenticazione (3 sistemi separati)

| Area | Component Yii | Identity Model | Tabella | Credenziali Dev |
|------|---------------|----------------|---------|-----------------|
| **Admin** | `Yii::$app->user` | `User` | `utente` | Vedi utenti in DB (no credenziali di prova) |
| **Docenti** | `Yii::$app->docenteUser` | `DocenteAuth` | `docente_auth` | CF + password (debug: solo CF) |
| **Studenti** | `Yii::$app->studenteUser` | `AllievoAuth` | `allievo_auth` | CF + password (debug: solo CF) |

Tutti i controller devono avere `AccessControl` con `'roles' => ['@']`.
In DEBUG mode, docenti/studenti accedono con solo CF (password vuota), auto-creazione auth.

## Area Docenti

| Campo | Valore |
|-------|--------|
| **Login** | `/area-docenti/login` |
| **Layout** | `views/layouts/docente.php` |
| **Controller** | `DocenteAreaController.php` |

**Funzionalita**: Dashboard, Bandi Aperti, Iscrizione Modulo (wizard tag+documenti), Le Mie Iscrizioni/Assegnazioni, Inserimento/Modifica Ore, Profilo, Password Dimenticata.

**Rendicontazione Ore**: Docente inserisce ore giornaliere (data, ore, inizio/fine, note, firma) → status `inserito` → Admin approva/rifiuta → se ore > contrattualizzate → workflow ore extra su Pagamento.
- Stati: `inserito` (giallo), `approvato` (verde), `rifiutato` (rosso, motivazione obbligatoria)

## Area Studenti

| Campo | Valore |
|-------|--------|
| **Login** | `/area-studenti/login` |
| **Bandi Pubblici** | `/studenti` (no login) |
| **Layout** | `views/layouts/studente.php` (tema viola) |
| **Controller** | `StudenteAreaController.php` (autenticato), `StudentiController.php` (pubblico) |

**Funzionalita**: Dashboard, Bandi Aperti, Candidatura (4 sezioni: anagrafica, formazione, lavoro, privacy), Le Mie Candidature, Dettaglio Candidatura (punteggi), Profilo.

**Workflow Candidature**: Registrazione → Login → Candidatura → `candidato` → Admin valuta con criteri pesati → Graduatoria → `ammesso`/`non_ammesso` → `frequentante` → `completato`

**Stati iscrizione**: `candidato` (azure), `in_valutazione` (orange), `ammesso` (green), `ammesso_con_riserva` (lime), `non_ammesso` (red), `frequentante` (blue), `completato` (teal), `ritirato` (default)

## Criteri Valutazione Studenti

Tabelle `criterio_valutazione_studente` → `punteggio_iscrizione`. Controller: `CriterioValutazioneController` + azioni in `IscrizioneController`.
5 criteri default: Titolo di Studio (2.0), Esperienza Lavorativa (1.5), Motivazione (1.0), Requisiti Specifici (1.5), Situazione Occupazionale (1.0).
Admin: `/iscrizione/candidature`, `/iscrizione/valuta/{id}`, `/iscrizione/graduatoria-studenti?avviso_id={id}`, `/iscrizione/storico/{id}`.

## Configurazione Fondazione

Tabella `configurazione` (chiave-valore). Controller: `ConfigurazioneController` (`/configurazione/index`).
Model `Configurazione` con metodi statici `get(chiave)`, `set(chiave, valore)`, `getByGruppo()`.
Dati: nome_fondazione, indirizzo, telefono, email, pec, partita_iva, codice_fiscale_ente, sito_web, logo_url.

## Subentro Docenti Idonei

Admin revoca assegnazione (con motivazione) → `StoricoEsito::ESITO_RINUNCIA` → se idonei disponibili → pagina subentro → scelta idoneo per punteggio → nuova assegnazione + `ESITO_SUBENTRO`.
Model: `Modulo::getIstanzeIdonee()`, `getProssimoIdoneo()`, `getGraduatoria()`. Viste: `views/assegnazione/subentro.php`, `views/istanza/graduatoria.php`.

## Server di Produzione

Credenziali e configurazione in `PRODUCTION_SERVER.md` (nel `.gitignore`, NON committare).
- **Dominio**: albatros.robertodedomenico.it
- **PHP**: 8.4 (obbligatorio — i pacchetti Symfony nel composer.lock richiedono >= 8.4)
- **Server**: Plesk, PHP CLI: `/opt/plesk/php/8.4/bin/php`
- **DB**: MariaDB su server.robertodedomenico.it (utente: albatros)
- **FTP**: server.robertodedomenico.it:21 (utente: albatros)
- **Post-deploy**: `production.php` esegue `composer install --no-dev` + migration automaticamente
- **Composer**: `/opt/psa/var/modules/composer/composer.phar` (Plesk built-in)

## Ambiente Docker

```bash
docker-compose up -d
# App: http://localhost:8000 | phpMyAdmin: http://localhost:8080 | MySQL: localhost:3307
# DB: albatros/albatros/albatros (root: root)
docker exec -w /app albatros-php php yii migrate
docker exec -w /app albatros-php composer require package
```

## Terminologia

| Termine | Significato | Tabella DB | Contesto |
|---------|-------------|------------|----------|
| **CORSO** | Percorso formativo per studenti | `progetto` | Vista studente |
| **PROGETTO** | Contenitore finanziato (FSE/PNRR/MIM) | `progetto` | Vista admin |
| **MATERIA/MODULO** | Singola disciplina | `modulo` | Vista docente |
| **BANDO/AVVISO** | Selezione pubblica docenti | `avviso` | Vista docente |

## Struttura Database

### Gerarchia Principale
```
FONTE_FINANZIAMENTO (FSE, PNRR, MIM)
 └── PROGETTO (= "corso" per studenti)
     └── AVVISO (bando selezione docenti)
         └── MODULO (= materia/insegnamento)
             ├── ISTANZA (candidature docenti) → ASSEGNAZIONE → CONTRATTO → PAGAMENTO
             │                                    └── RIMBORSO_SPESA → RIMBORSO_SPESA_DETTAGLIO (→ VOCE_SPESA)
             ├── CALENDARIO (lezioni) → PRESENZA_ALLIEVO
             ├── ISCRIZIONE (studenti) → PUNTEGGIO_ISCRIZIONE
             └── VALUTAZIONE_DOCENTE
```

**37+ tabelle totali**. PK persone: `codice_fiscale` (CHAR 16). Altre PK: `id` (INT auto-increment).
Tabelle principali: fonte_finanziamento, conto_corrente, sede, utente, tipo_documento, progetto, avviso, modulo, docente (PK=CF, campo `provenienza` TP/RU), allievo (PK=CF), istanza (campo `esito_cts`), assegnazione, contratto (campi `data_inizio/fine_lezioni`), pagamento, calendario, iscrizione, rendicontazione_ore, rimborso_spesa, rimborso_spesa_dettaglio.
Tabelle supporto: docente_auth, allievo_auth, docente_tag, istanza_documento, istanza_tag, istanza_snapshot, storico_esito, storico_iscrizione, avviso_documento_tipo, criterio_valutazione_studente, punteggio_iscrizione, configurazione, presenza_allievo, valutazione_docente, tag_categoria, tag_curriculum, documentazione, audit_log, voce_spesa, voce_spesa_categoria, movimento_spesa.
**Eccezioni BaseActiveRecord**: `AuditLog`, `Configurazione`, `StoricoIscrizione` NON estendono BaseActiveRecord.

### Soft Delete + Audit
Tutte le tabelle hanno `deleted_at`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_by`.
```php
$model->delete();          // Soft delete
$model->hardDelete();      // Eliminazione fisica
Model::find();             // Solo attivi (default)
Model::findWithDeleted();  // Tutti
$model->withAuditNote('nota')->save(); // Audit con nota
AuditLog::getRecordHistory('tabella', $pk);
```

### Campi Business Specifici
- `docente.provenienza` ENUM('TP','RU'): TP=Professionista €60/h, RU=Ricercatore €70/h
- `istanza.esito_cts` ENUM('assegnato','idoneo','non_idoneo'): delibera CTS, separato da `status`
- `contratto.data_inizio_lezioni`, `data_fine_lezioni`: date effettive lezioni
- `progetto.numero_studenti` INT (default 20)

### Workflow CTS
Docente → ISTANZA per MODULO → CTS valuta → esito_cts: assegnato (→ ASSEGNAZIONE → CONTRATTO) / idoneo (long list) / non_idoneo (tracciato).

## Convenzioni

### Database
- PK persone: `codice_fiscale` (CHAR 16), FK: `{tabella}_{campo}`, ENUM per status/tipo

### Codice
- Namespace: `app\models`, `app\components`, etc. Model estendono `BaseActiveRecord`
- Lingua UI + commenti: Italiano
- **SoftDeleteTrait + JOIN**: NON usare `joinWith()` (conflitti alias). Usare `findWithDeleted()->alias('x')->leftJoin(...)` + `->andWhere(['x.deleted_at' => null])`
- **CRUD**: Leggere `docs/CRUD_GUIDELINES.md` prima di creare pagine

### Creazione Model
```php
class NuovoModel extends BaseActiveRecord {
    public static function tableName() { return '{{%nome_tabella}}'; }
    protected function auditExcludedAttributes() { return ['created_at', 'updated_at']; }
    protected function auditMaskedAttributes() { return ['iban']; }
}
```

## Struttura Codice (essenziale)

```
components/  → assets/ (TablerAsset, ApexChartsAsset), base/ (BaseActiveRecord),
               behaviors/ (SoftDelete, AuditLog, Audit), traits/ (SoftDeleteTrait),
               widgets/ (AppGridView, AppActiveForm, Alert)
config/      → db.php, web.php
models/      → 23 model + *Search.php + forms/ (AllievoLoginForm, AllievoRegistrazioneForm)
views/       → layouts/ (main, login, docente, studente, studenti-demo, header, menu, footer)
               site/, {entita}/, docente-area/, studente-area/, studenti/,
               configurazione/, criterio-valutazione/, audit-log/, rendicontazione-ore/, report/
controllers/ → 25 controller (Site, Import, CRUD entita, DocenteArea, StudenteArea, Studenti,
               Configurazione, CriterioValutazione, AuditLog, Report, TagCategoria, TagCurriculum,
               RendicontazioneOre, ValutazionePublic)
docs/        → schema-er.html, CRUD_GUIDELINES.md, AGENT_TEAM_CONFIG.md, MAPPA_FEATURE_MANUALE.md
import/      → README.md, TEMPLATE_IMPORT_DATI.xlsx, file esempio
```

## Import Dati Excel

Importazione massiva progetti/moduli/docenti da Excel. **Dettagli in `import/README.md`**.
Controller: `ImportController` (actionIndex, actionUpload, actionDownloadTemplate).
Template 4 fogli: ISTRUZIONI, INFO PROGETTO, MODULI, DOCENTI & ESITI.
Supporta rinunce `(Rinuncia)`, compensi variabili, moduli senza assegnatari, piu docenti per modulo.
Libreria: `phpoffice/phpspreadsheet`.

## Feature Specifiche

### Liquidazione Massiva Pagamenti
Pulsante in `views/pagamento/index.php` → Modal AJAX con contratti saldo > 0 → multi-selezione → creazione pagamenti in transazione.
Endpoint: `GET /pagamento/contratti-con-saldo`, `POST /pagamento/liquida-massiva`.

### Tag Curriculum
Tabelle: `tag_categoria` → `tag_curriculum` → `docente_tag` (profilo) / `istanza_tag` (candidatura).
Promozione tag: `IstanzaController::actionPromuoviTag()` promuove tag da candidatura a profilo.

### Questionario Anonimo Valutazione Docenti
Admin genera codici monouso per modulo → stampa foglietti → studente accede a `/valutazione/{CODICE}` (pubblico) → 5 criteri stelline + commento → codice invalidato.
Tabella: `valutazione_codice`. Controller: `ValutazionePublicController`. Route: `'valutazione/<codice:[A-Z0-9]{8}>'`.

### Rendicontazione Finanziaria
Pattern: `getTotaleContrattualizzato()`, `getTotalePagato()`, `getTotaleInAttesa()`, `getRendicontoModuli()` su Progetto/Avviso/Modulo. Usare leftJoin con `AND deleted_at IS NULL`.

### Gestione Utenti Admin
Controller: `UtenteController` (`/utente/index`). Solo ruolo `admin`. Menu sotto "Configurazione".
3 tab: Utenti Admin (CRUD completo), Docenti (gestione auth), Studenti (gestione auth).
Azioni: create, update, delete (soft), toggle-status, reset-password, reset-2fa, force-change-password.
Search models: `UserSearch`, `DocenteAuthSearch`, `AllievoAuthSearch`.

### Autenticazione a Due Fattori (2FA TOTP)
Opzionale per tutti e 3 i tipi di utente. Libreria: `robthree/twofactorauth`.
- `TotpTrait` (`components/traits/`): encrypt/decrypt secret, generate QR, verify code. Usato da User, DocenteAuth, AllievoAuth.
- `TwoFactorActionsTrait` (`components/traits/`): actions condivise attiva/disattiva/verifica 2FA + cambio password.
- `ForcePasswordChangeBehavior` (`components/behaviors/`): intercetta navigazione se flag attivo.
- `TotpPendingBehavior` (`components/behaviors/`): blocca navigazione in stato 2FA pendente.
- `TotpAttempt` model: rate-limiting (5 tentativi, blocco 5 min). NON estende BaseActiveRecord.
- Secret criptato con `Yii::$app->params['totpEncryptionKey']` (env: `TOTP_ENCRYPTION_KEY`).
- Login flow: credenziali OK → se 2FA attivo → sessione pendente → verifica codice → login effettivo.

### Changelog In-App
File `CHANGELOG.md` nel root, parsato e mostrato nel portale. Filtrato per tipo utente con tag `{tutti}`, `{admin}`, `{docente}`, `{studente}`.
- `ChangelogParser` (`components/`): parse, filter, count unread.
- `ChangelogActionsTrait` (`components/traits/`): actions condivise pagina + modal + segna letto.
- Badge novità nell'header di tutti e 3 i layout. Modal AJAX + pagina completa `/site/changelog`.
- Tracking: campo `last_changelog_seen` su utente/docente_auth/allievo_auth.

> **REGOLA**: Ad ogni nuova feature, aggiornare `CHANGELOG.md` con le voci appropriate e i tag per tipo utente.

### Sistema Notifiche
Admin invia notifiche HTML (Summernote Lite) a tutti/categoria/singolo utente. Controller: `NotificaController` (admin CRUD), `NotificheActionsTrait` (tutti).
Tabelle: `notifica` (BaseActiveRecord) + `notifica_lettura` (plain AR). Identificativo utenti: admin=`id` (int), docente/studente=`codice_fiscale` (CF).
Campanella header con dropdown preview ultime 5 + badge (99+ se > 99). Pagina `/site/notifiche` (admin), `/area-docenti/notifiche`, `/area-studenti/notifiche`.
Email opzionale con `Yii::$app->mailer`, template `mail/notifica-html.php` + `mail/notifica-text.php`. HTML sanitizzato con `HtmlPurifier::process()` in `beforeSave()`.
Destinatario: `tutti`, `admin`, `docente`, `studente`, `singolo_admin`, `singolo_docente`, `singolo_studente`. `destinatario_id` = ID/CF per singoli.

## Manuali Utente

3 manuali HTML navigabili e stampabili: Admin (`/site/manuale-admin`), Docente (`/site/manuale-docente`), Studente (`/site/manuale-studente`).
Layout `login`, sidebar sticky con scrollspy, pulsante stampa PDF, accessibili senza login.

> **REGOLA**: Ad ogni nuova feature, aggiornare i manuali corrispondenti. Mappa completa: `docs/MAPPA_FEATURE_MANUALE.md`

## Team di Agenti

Config: `docs/AGENT_TEAM_CONFIG.md`. Avviare con: `Avvia il team di agenti come da docs/AGENT_TEAM_CONFIG.md`

| Agente | Ruolo |
|--------|-------|
| **db-agent** | Migration, schema, model |
| **full-stack** (1-6 paralleli) | Controller + views + logica |
| **tester** (on-demand) | QA via Chrome |
| **reviewer** (on-demand) | Code review |

## Documentazione

Usare Context7 per docs aggiornate:
- Yii2: `/yiisoft/yii2`
- Tabler: `/websites/tabler_io`
- Bootstrap: `/twbs/bootstrap`

## Link Utili

| Risorsa | URL |
|---------|-----|
| App Admin | http://localhost:8000 |
| Area Docenti | http://localhost:8000/area-docenti/login |
| Area Studenti | http://localhost:8000/area-studenti/login |
| Bandi Pubblici | http://localhost:8000/studenti |
| phpMyAdmin | http://localhost:8080 |
| Import Dati | http://localhost:8000/import |
| Configurazione | http://localhost:8000/configurazione |
| Graduatoria | http://localhost:8000/istanza/graduatoria |
| Schema ER | `docs/schema-er.html` |
| CRUD Guidelines | `docs/CRUD_GUIDELINES.md` |
| Report Stato Progetto | `docs/REPORT_STATO_PROGETTO.md` |
| Mappa Feature-Manuale | `docs/MAPPA_FEATURE_MANUALE.md` |
| Tabler Icons | https://tabler.io/icons |
