# Albatros Manager - Gestione Docenti FSE/PNRR

Sistema di gestione per docenti e corsi finanziati da FSE, PNRR e MIM della Fondazione ITS Academy Albatros.

**Ambiente online**: https://albatros.robertodedomenico.it (ospitato dallo sviluppatore in attesa del server definitivo della Fondazione)

## Conformità ai Requisiti dell'Incarico

Web application realizzata per l'incarico conferito con Determina Presidenziale prot. n. 118 del 30/12/2025: *"web application con architettura scalabile, sicura e conforme alle normative vigenti (GDPR, ISO 9001), per la gestione integrata di anagrafiche, contratti, posizioni fiscali e contributive di docenti, allievi, collaboratori e consulenti esterni, con interfaccia intuitiva e accesso multi-utente"*.

Tutti i requisiti funzionali dell'offerta tecnico-economica sono implementati e in esercizio:

| # | Requisito (offerta tecnica) | Stato | Implementazione |
|---|-----------------------------|:-----:|-----------------|
| 1 | **Gestione Anagrafica** di collaboratori, docenti, allievi con campi personalizzabili | ✅ | Anagrafiche Docenti (tipo docente/tutor/coordinatore, provenienza TP/RU), Allievi, Utenti, Sedi, Conti correnti. Personalizzazione tramite categorie e tag curriculum, tipi documento, criteri di valutazione e parametri della Fondazione configurabili da interfaccia |
| 2 | **Assegnazione Docenza**: durata contratto, titolo modulo, progetto, importo orario | ✅ | Gerarchia Fonte → Progetto → Corso → Modulo; candidature docenti, valutazione CTS, graduatorie, long list idonei, assegnazioni, subentri; contratti con date di inizio e fine lezioni, ore e compenso orario (TP €60/h, RU €70/h, compensi variabili) |
| 3 | **Posizione Fiscale e Contributiva**: P.IVA, co.co.co., ritenuta d'acconto | ✅ | Contratti per tipologia (Co.Co.Co., Partita IVA, prestazione occasionale) con modelli PDF dedicati FSE/PNRR; pagamenti con importo lordo, ritenute e netto (acconto, rata, saldo); richieste di fatturazione; rimborsi spese; export per il consulente del lavoro; bonifici SEPA XML (pain.001.001.03) |
| 4 | **Reportistica Avanzata**: export PDF ed Excel | ✅ | Contratti, verbali e documenti in PDF (DOMPDF); export Excel/CSV di report, bilancio, borse di studio, dati per il consulente; rendicontazione finanziaria per progetto/corso/modulo; dashboard con grafici e alert |
| 5 | **Accesso Sicuro**: autenticazione a più fattori, ruoli e permessi | ✅ | 2FA TOTP (app Authenticator) per admin, docenti e studenti; ruoli Amministratore, Segreteria, Coordinatore, Operatore; tre aree separate (Admin, Docenti, Studenti); rate-limiting dei tentativi, cambio password obbligatorio, password hashate |
| 6 | **Compatibilità**: web responsive, desktop e mobile | ✅ | Interfaccia Tabler/Bootstrap 5 responsive; aree docenti e studenti utilizzabili da smartphone |
| 7 | **Integrazione** con sistemi esistenti e API | ✅ | Import massivo da Excel (progetti, moduli, docenti, assegnazioni, calendari); export standard (Excel, CSV, PDF, SEPA XML); endpoint JSON interni; invio email SMTP |

### Conformità normativa (GDPR / ISO 9001)

- **Tracciabilità**: audit log di ogni modifica (chi, quando, cosa), con storico consultabile per record.
- **Conservazione**: cancellazione logica (soft delete) su tutte le tabelle, nessuna perdita di dati accidentale.
- **Riservatezza**: mascheramento dei dati sensibili (IBAN) nei log, segreti 2FA cifrati, contenuti HTML sanitizzati, accesso per ruolo.
- **Privacy**: consenso privacy nelle candidature degli studenti; questionario di valutazione dei docenti anonimo, con codici monouso.

### Fasi di esecuzione

| Fase | Stato | Evidenze |
|------|:-----:|----------|
| 1. Analisi e raccolta requisiti | ✅ | Quattro riunioni di analisi con la Fondazione e una specifica funzionale |
| 2. Progettazione e sviluppo | ✅ | 44 controller, 84 model, 77 migration del database, 22 release pubblicate su GitHub (ultima v1.0.32) |
| 3. Test e collaudo | ✅ | Applicazione in esercizio online, con i dati reali forniti dalla Fondazione già importati |
| 4. Formazione e rilascio | ✅ | Manuali utente HTML stampabili (Admin, Docente, Studente), guide interattive in app, changelog delle novità in app |
| 5. Assistenza post go-live (12 mesi) | 🔄 | Supporto in corso, con form di assistenza (`/site/supporto`) e sistema di notifiche |

### Funzionalità aggiuntive (oltre l'offerta)

Area docenti con candidatura online e rendicontazione delle ore, area studenti con candidature e graduatorie a criteri pesati, vista semplificata "Gestione" per corso, registro presenze dei tutor, borse di studio con controllo della frequenza minima dell'80%, bilancio e movimenti di spesa, firma digitale, notifiche interne ed email.

## Stack Tecnologico

- **Framework**: Yii2 Basic Template, PHP 8.4
- **Database**: MySQL 8.0 / MariaDB
- **UI**: Tabler Dashboard Theme v1.4.0, Tabler Icons, Tom Select
- **Container**: Docker
- **PDF**: DOMPDF
- **Excel**: PhpSpreadsheet
- **2FA**: robthree/twofactorauth (TOTP)

## Ambiente di Sviluppo (Docker)

```bash
docker-compose up -d

# App: http://localhost:8000
# phpMyAdmin: http://localhost:8080
# MySQL: localhost:3307 (user: albatros, pass: albatros, db: albatros, root: root)

# Eseguire migration
docker exec -w /app albatros-php php yii migrate

# Installare pacchetti
docker exec -w /app albatros-php composer require package
```

## Server di Produzione

- **Dominio**: albatros.robertodedomenico.it
- **Server**: Plesk, PHP 8.4
- **DB**: MariaDB su server.robertodedomenico.it
- **FTP**: server.robertodedomenico.it:21
- **Credenziali**: vedi `PRODUCTION_SERVER.md` (nel `.gitignore`)

## Deploy in Produzione

Il deploy avviene tramite il comando console Yii2 `DeployController` (`commands/DeployController.php`).

Configurazione FTP in `config/ftp.php`. File versione in `config/version.php`.

```bash
# Verifica stato (confronta commit locale vs server vs GitHub)
php yii deploy/status

# Deploy incrementale (solo file modificati)
php yii deploy/update

# Deploy con versione e messaggio specifici
php yii deploy/update --tag=v1.0.27 --message="Descrizione release"

# Deploy completo (forza upload di TUTTI i file)
php yii deploy/full --tag=v1.0.27

# Dry-run (simula senza eseguire)
php yii deploy/update -n

# Inizializzazione server (solo prima volta)
php yii deploy/init
```

### Flusso deploy/update

1. Verifica branch = `main` e working directory pulita
2. Calcola file modificati (diff tra commit locale e server)
3. Auto-incrementa versione semver
4. Aggiorna `CHANGELOG.md` (rinomina `[Unreleased]` con versione e data)
5. Commit + push su GitHub
6. Crea release GitHub con elenco file modificati
7. Upload via FTP solo i file nuovi/modificati
8. Elimina dal server i file cancellati da git
9. Esegue `web/production.php` via HTTP (composer install + migration)
10. Carica `config/mail.php` se mancante sul server

### File esclusi dal deploy

docker-compose.yml, Dockerfile, vagrant/, PRODUCTION_SERVER.md, .claude/, CLAUDE.md, docs/superpowers/, .github/, tests/

### Post-deploy automatico (production.php)

Il file `web/production.php` viene invocato via HTTP dopo l'upload FTP e:
1. Esegue `composer install --no-dev --optimize-autoloader`
2. Applica le migration Yii2 pendenti sul database
3. Rileva automaticamente il path PHP CLI di Plesk

## Struttura Progetto

```
commands/        -> Console commands (DeployController)
components/      -> Assets, behaviors, traits, widgets
config/          -> Configurazioni (db, web, ftp, mail, version)
controllers/     -> 25+ controller
models/          -> 23+ model + Search + forms
views/           -> Layouts (main, docente, studente), viste CRUD
docs/            -> Schema ER, linee guida, piani
import/          -> Template Excel per import dati
web/             -> Entry point, assets pubblici, uploads
```

## Aree del Sistema

| Area | URL | Autenticazione |
|------|-----|----------------|
| Admin | http://localhost:8000 | utente/password (tabella `utente`) |
| Area Docenti | http://localhost:8000/area-docenti/login | CF + password (tabella `docente_auth`) |
| Area Studenti | http://localhost:8000/area-studenti/login | CF + password (tabella `allievo_auth`) |
| Vista Semplificata (Gestione) | http://localhost:8000/gestione | utente/password (tabella `utente`) |
| Bandi Pubblici | http://localhost:8000/studenti | Nessuna |
| Questionario Valutazione | http://localhost:8000/valutazione/{CODICE} | Codice monouso |

L'autenticazione a due fattori (TOTP) è disponibile, in modo facoltativo, per tutti e tre i tipi di utente.

## Import Dati

Pagina `/import` con 3 tipi di import da Excel:

1. **Import Completo** - Progetto + moduli + docenti + assegnazioni (template scaricabile)
2. **Import Assegnazioni** - Assegnazioni standalone su moduli esistenti (con preview/conferma)
3. **Import Calendari** - Lezioni/calendario su moduli esistenti (con preview/conferma)

## Generazione Contratti

Il sistema genera contratti PDF differenziati per:

| Tipo Contratto | Fonte | Template |
|----------------|-------|----------|
| Prestazione Professionale | FSE | `views/contratto/templates/fse.php` |
| Prestazione Professionale | PNRR | `views/contratto/templates/pnrr.php` |
| Co.Co.Co. | FSE | `views/contratto/templates/cococo-fse.php` |
| Co.Co.Co. | PNRR | `views/contratto/templates/cococo-pnrr.php` |

## Documentazione

- **CLAUDE.md** - Istruzioni dettagliate per sviluppo (stack, convenzioni, database, feature)
- **PRODUCTION_SERVER.md** - Credenziali server (gitignored)
- **CHANGELOG.md** - Log di tutte le release
- **docs/CRUD_GUIDELINES.md** - Linee guida per pagine CRUD
- **docs/schema-er.html** - Schema ER del database
- **import/README.md** - Documentazione import Excel
- **docs/MAPPA_FEATURE_MANUALE.md** - Mappa feature ↔ manuali utente
- **Manuali utente** - `/site/manuale-admin`, `/site/manuale-docente`, `/site/manuale-studente`

## Dati Sensibili

Il repository **non deve contenere** dati reali, credenziali o documenti riservati. Sono esclusi tramite `.gitignore` e restano solo in locale:

| Percorso | Contenuto |
|----------|-----------|
| `PRODUCTION_SERVER.md` | Credenziali server di produzione |
| `config/ftp.php`, `config/mail.php`, `config/db-local.php` | Credenziali FTP, SMTP, DB locale |
| `docs/DATI REALI/` | Bilanci, prospetti docenti, valutazioni |
| `docs/TEMPLATE_REGISTRAZIONE_UTENTI.xlsx` | Anagrafica utenti reali |
| `import/*.xlsx` (tranne `TEMPLATE_IMPORT_DATI.xlsx`), `import/definitivo/` | File di import con dati reali |
| `docs/*riunione*.txt`, `docs/*transcript*.txt`, `doc/` | Trascrizioni delle riunioni |
| `web/uploads/*` | Contratti, fatture, firme, istanze caricate |

> La storia git è stata ripulita da questi file e dalle password in chiaro (novembre 2026). Non inserire mai password nelle migration: usare variabili d'ambiente o password generate con cambio obbligatorio al primo accesso.
