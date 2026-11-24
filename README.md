# Albatros Manager - Gestione Docenti FSE/PNRR

Sistema di gestione per docenti e corsi finanziati da FSE, PNRR e MIM della Fondazione ITS Academy Albatros.

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
