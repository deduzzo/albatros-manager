# Albatros Manager - Gestione Docenti FSE/PNRR

Sistema di gestione per docenti e corsi finanziati da FSE (Fondo Sociale Europeo), PNRR e MIM.

> **REGOLA FONDAMENTALE - LEGGERE PRIMA DI QUALSIASI AZIONE**:
> - Questo e' l'UNICO file CLAUDE.md del progetto.
> - **NON CREARE MAI** altri file CLAUDE.md in NESSUNA sottocartella del progetto (controllers/, views/, models/, import/, docs/, components/, web/, ecc.).
> - **NON CREARE MAI** file .md di documentazione nelle sottocartelle (niente README.md locali, niente NOTE.md, ecc.).
> - Tutta la documentazione di progetto va scritta ESCLUSIVAMENTE in questo file.
> - Se trovi altri CLAUDE.md nelle sottocartelle, **ELIMINALI IMMEDIATAMENTE** senza chiedere conferma.

## Stack Tecnologico

- **Framework**: Yii2 Basic Template
- **PHP**: 8.4
- **Database**: MySQL 8.0
- **UI**: Tabler Dashboard Theme v1.4.0 (@tabler/core)
- **Icons**: Tabler Icons v3.36.1 (@tabler/icons-webfont)
- **Container**: Docker

### Tabler UI Kit

Tabler v1.4.0 è installato via npm e gli asset sono in `web/assets/tabler/`:
- CSS: `tabler.min.css`, `tabler-vendors.min.css`, `tabler-icons.min.css`
- JS: `tabler.min.js`
- Fonts: `css/fonts/tabler-icons.*`

Asset Bundle: `app\components\assets\TablerAsset`

Icone: usare `<i class="ti ti-{nome}"></i>` (es: `ti ti-home`, `ti ti-settings`)
Documentazione icone: https://tabler.io/icons

### Badge Tabler v1.4.0

**IMPORTANTE**: NON usare classi Bootstrap (`bg-success`, `bg-warning`, ecc.). Usare SOLO classi Tabler native.

**Tre formati disponibili:**

| Tipo | Classi | Esempio |
|------|--------|---------|
| **Solid** | `badge bg-{colore} text-{colore}-fg` | `<span class="badge bg-green text-green-fg">Attivo</span>` |
| **Light** | `badge bg-{colore}-lt` | `<span class="badge bg-green-lt">Attivo</span>` |
| **Outline** | `badge badge-outline text-{colore}` | `<span class="badge badge-outline text-green">Attivo</span>` |

**Colori Tabler disponibili**: `blue`, `azure`, `indigo`, `purple`, `pink`, `red`, `orange`, `yellow`, `lime`, `green`, `teal`, `cyan`, `default`, `muted`

**Mapping Bootstrap → Tabler:**

| Bootstrap (NON usare) | Tabler (usare questo) |
|------------------------|-----------------------|
| `bg-success` | `bg-green text-green-fg` |
| `bg-warning` | `bg-orange text-orange-fg` |
| `bg-danger` | `bg-red text-red-fg` |
| `bg-primary` | `bg-blue text-blue-fg` |
| `bg-info` | `bg-azure text-azure-fg` |
| `bg-secondary` | `bg-default text-default-fg` |
| `bg-muted` | `bg-default text-default-fg` |

**Nei Model** (metodi `getStatusBadgeClass()`, `getEsitoBadgeClass()`, ecc.):
```php
// CORRETTO
public function getStatusBadgeClass()
{
    return match($this->status) {
        'attivo' => 'bg-green text-green-fg',
        'in_attesa' => 'bg-orange text-orange-fg',
        'rifiutato' => 'bg-red text-red-fg',
        default => 'bg-default text-default-fg',
    };
}
```

**Nelle View** - NON usare colori inline (`style="background-color: #hex"`). Usare il metodo `getTablerColor()` sui model che hanno un colore hex (es. `Avviso`):
```php
// CORRETTO - usare classi Tabler dinamiche
$tc = $avviso->getTablerColor(); // Ritorna 'blue', 'green', 'purple', ecc.
'<span class="badge bg-' . $tc . ' text-' . $tc . '-fg">' . $testo . '</span>'
'<div class="card-header bg-' . $tc . '-lt">...</div>'
```

## Autenticazione

### Credenziali Default
- **Username**: `admin`
- **Password**: `admin`
- **Email**: `admin@albatros.it`
- **Ruolo**: Amministratore

### Sistema Autenticazione
L'autenticazione usa la tabella `utente` del database con password hashate (bcrypt).

```php
// Model: app\models\User (tabella utente)
// Ruoli disponibili: admin, segreteria, coordinatore, operatore

// Verificare ruolo utente
if (Yii::$app->user->identity->isAdmin()) { ... }
$ruolo = Yii::$app->user->identity->getRuoloLabel(); // "Amministratore"

// Creare nuovo utente
$user = new User();
$user->username = 'nuovo';
$user->email = 'nuovo@example.com';
$user->setPassword('password123');
$user->generateAuthKey();
$user->ruolo = User::RUOLO_OPERATORE;
$user->save();
```

### Protezione Controller
Tutti i controller devono avere il filtro AccessControl:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                ['allow' => true, 'roles' => ['@']], // Solo utenti autenticati
            ],
        ],
    ];
}
```

## Area Docenti (Portale Riservato)

Area self-service per i docenti, separata dall'area amministrativa. Ha autenticazione propria, layout dedicato e funzionalita specifiche.

### Accesso

| Campo | Valore |
|-------|--------|
| **URL Login** | http://localhost:8000/area-docenti/login |
| **URL Registrazione** | http://localhost:8000/area-docenti/registrazione |
| **Autenticazione** | Codice Fiscale + Password (tabella `docente_auth`) |
| **Layout** | `views/layouts/docente.php` (separato dal layout admin) |
| **Controller** | `controllers/DocenteAreaController.php` |

### Login Test (solo DEBUG mode)

In modalita sviluppo (`YII_DEBUG=true`), i docenti possono accedere inserendo solo il codice fiscale e lasciando la password vuota. Se non esiste un record `docente_auth`, viene creato automaticamente al primo login.

### Autenticazione Docente

L'area docenti usa un sistema di autenticazione separato dagli utenti admin:
- **Component Yii**: `Yii::$app->docenteUser` (NON `Yii::$app->user`)
- **Identity**: `app\models\DocenteAuth` (tabella `docente_auth`)
- **Docente**: `Yii::$app->docenteUser->identity->docente` (relazione con tabella `docente`)
- **Codice Fiscale**: `Yii::$app->docenteUser->identity->docente_codice_fiscale`

```php
// Nel controller area docente
$auth = Yii::$app->docenteUser->identity;
$docente = $auth->docente;
$cf = $auth->docente_codice_fiscale;

// Verificare ownership di un record
if ($assegnazione->docente_codice_fiscale !== $cf) {
    throw new ForbiddenHttpException('Non hai accesso.');
}
```

### Funzionalita Disponibili

| Sezione | URL | Descrizione |
|---------|-----|-------------|
| Dashboard | `/area-docenti` | Statistiche personali, azioni rapide |
| Bandi Aperti | `/area-docenti/bandi` | Lista bandi disponibili per iscrizione |
| Dettaglio Bando | `/area-docenti/bandi/{id}` | Moduli del bando con pulsante iscrizione |
| Iscrizione Modulo | `/area-docenti/iscrizione/{moduloId}` | Wizard iscrizione con tag e documenti |
| Le Mie Iscrizioni | `/area-docenti/le-mie-iscrizioni` | Storico candidature presentate |
| Le Mie Assegnazioni | `/area-docenti/le-mie-assegnazioni` | Moduli assegnati con stato ore |
| Inserisci Ore | `/area-docenti/inserisci-ore/{assegnazione_id}` | Form inserimento ore giornaliere |
| Le Mie Ore | `/area-docenti/le-mie-ore/{assegnazione_id}` | Lista ore con stato validazione |
| Modifica Ore | `/area-docenti/modifica-ore/{id}` | Modifica ore (solo se da validare) |
| Profilo | `/area-docenti/profilo` | Modifica dati anagrafici |
| Password Dimenticata | `/area-docenti/password-dimenticata` | Reset password via email |

### Rendicontazione Ore Docente

Workflow di inserimento e validazione ore:

```
1. Docente accede a "Le Mie Assegnazioni" → vede i moduli assegnati con ore contrattualizzate
2. Clicca "Inserisci Ore" → compila: data, ore, ora inizio/fine, note, firma digitale
3. Ore salvate con status = "inserito" (da validare)
4. Docente puo modificare/eliminare ore solo se status = "inserito"
5. Amministratore valida le ore (singolarmente o in blocco) → status = "approvato" / "rifiutato"
6. Se ore approvate superano le ore contrattualizzate → workflow ore extra su Pagamento
```

**Stati rendicontazione ore:**
- `inserito` (giallo) = Da validare - inserite dal docente, in attesa di approvazione
- `approvato` (verde) = Validate dall'amministrazione
- `rifiutato` (rosso) = Rifiutate con motivazione obbligatoria

**Validazione lato admin** (RendicontazioneOreController):
- `/rendicontazione-ore/index?assegnazione_id={id}` - Lista ore per assegnazione
- Azione "Approva" singola / "Approva Tutto" in blocco
- Azione "Rifiuta" con motivo obbligatorio
- Riepilogo mensile ore

### Route Area Docenti (config/web.php)

```php
'area-docenti' => 'docente-area/index',
'area-docenti/login' => 'docente-area/login',
'area-docenti/registrazione' => 'docente-area/registrazione',
'area-docenti/logout' => 'docente-area/logout',
'area-docenti/profilo' => 'docente-area/profilo',
'area-docenti/bandi' => 'docente-area/bandi',
'area-docenti/bandi/<id:\d+>' => 'docente-area/bando',
'area-docenti/iscrizione/<moduloId:\d+>' => 'docente-area/iscrizione',
'area-docenti/le-mie-iscrizioni' => 'docente-area/le-mie-iscrizioni',
'area-docenti/le-mie-assegnazioni' => 'docente-area/le-mie-assegnazioni',
'area-docenti/inserisci-ore/<assegnazione_id:\d+>' => 'docente-area/inserisci-ore',
'area-docenti/le-mie-ore/<assegnazione_id:\d+>' => 'docente-area/le-mie-ore',
'area-docenti/modifica-ore/<id:\d+>' => 'docente-area/modifica-ore',
'area-docenti/elimina-ore/<id:\d+>' => 'docente-area/elimina-ore',
```

## Area Studenti (Portale Riservato)

Area self-service per gli studenti, separata dall'area amministrativa e dall'area docenti. Ha autenticazione propria, layout dedicato (viola) e funzionalita specifiche.

### Accesso

| Campo | Valore |
|-------|--------|
| **URL Login** | http://localhost:8000/area-studenti/login |
| **URL Registrazione** | http://localhost:8000/area-studenti/registrazione |
| **URL Bandi Pubblici** | http://localhost:8000/studenti |
| **Autenticazione** | Codice Fiscale + Password (tabella `allievo_auth`) |
| **Layout** | `views/layouts/studente.php` (tema viola) |
| **Controller Autenticato** | `controllers/StudenteAreaController.php` |
| **Controller Pubblico** | `controllers/StudentiController.php` |

### Login Test (solo DEBUG mode)

Come per i docenti, in modalita sviluppo gli studenti possono accedere con solo il CF (password vuota). Se non esiste un record `allievo_auth`, viene creato automaticamente.

### Autenticazione Studente

L'area studenti usa un sistema di autenticazione separato:
- **Component Yii**: `Yii::$app->studenteUser` (NON `Yii::$app->user` ne `docenteUser`)
- **Identity**: `app\models\AllievoAuth` (tabella `allievo_auth`)
- **Allievo**: `Yii::$app->studenteUser->identity->allievo`
- **Codice Fiscale**: `Yii::$app->studenteUser->identity->allievo_codice_fiscale`

### Funzionalita Disponibili

| Sezione | URL | Descrizione |
|---------|-----|-------------|
| Dashboard | `/area-studenti` | Stat cards, bandi in scadenza, azioni rapide |
| Bandi Aperti | `/area-studenti/bandi` | Lista bandi disponibili per candidatura |
| Dettaglio Bando | `/area-studenti/bandi/{id}` | Moduli, requisiti, pulsante "Candidati" |
| Candidatura | `/area-studenti/candidatura/{id}` | Form 4 sezioni (anagrafica, formazione, lavoro, privacy) |
| Le Mie Candidature | `/area-studenti/le-mie-candidature` | Storico candidature con stato e punteggio |
| Dettaglio Candidatura | `/area-studenti/candidatura-view/{id}` | Punteggi per criterio, storico, motivazione |
| Profilo | `/area-studenti/profilo` | Modifica dati anagrafici |

### Workflow Candidature Studenti

```
Studente si registra → Login area riservata → Consulta bandi aperti
→ Compila candidatura (anagrafica + formazione + lavoro + privacy)
→ Status: "candidato" (snapshot dati formazione/lavoro)
→ Admin valuta → Assegna punteggi per criterio → Calcola punteggio totale
→ Admin pubblica graduatoria
→ Status: "ammesso" / "ammesso_con_riserva" / "non_ammesso" (con motivazione)
→ Studente ammesso → Inizia corso → "frequentante"
→ Fine corso → "completato" con valutazione finale e attestato
```

**Stati iscrizione**:
- `candidato` (azure) - Candidatura presentata
- `in_valutazione` (orange) - In fase di valutazione admin
- `ammesso` (green) - Ammesso in graduatoria
- `ammesso_con_riserva` (lime) - Ammesso con condizioni
- `non_ammesso` (red) - Non ammesso (con motivazione)
- `frequentante` (blue) - Sta frequentando il corso
- `completato` (teal) - Corso completato
- `ritirato` (default) - Ritirato

### Pagine Pubbliche Bandi (no login)

Le pagine `/studenti` e `/studenti/bando/{id}` sono accessibili senza login e mostrano i bandi aperti con tutti i dettagli (moduli, requisiti, certificazioni, scadenze).

### Route Area Studenti (config/web.php)

```php
'area-studenti' => 'studente-area/index',
'area-studenti/login' => 'studente-area/login',
'area-studenti/registrazione' => 'studente-area/registrazione',
'area-studenti/logout' => 'studente-area/logout',
'area-studenti/profilo' => 'studente-area/profilo',
'area-studenti/bandi' => 'studente-area/bandi',
'area-studenti/bandi/<id:\d+>' => 'studente-area/bando',
'area-studenti/candidatura/<id:\d+>' => 'studente-area/candidatura',
'area-studenti/le-mie-candidature' => 'studente-area/le-mie-candidature',
'area-studenti/candidatura-view/<id:\d+>' => 'studente-area/candidatura-view',
```

## Criteri Valutazione Studenti

Sistema di valutazione candidature con criteri pesati (globali + per bando).

**Tabelle**: `criterio_valutazione_studente` → `punteggio_iscrizione`
**Controller**: `CriterioValutazioneController` (CRUD criteri), azioni in `IscrizioneController` (valutazione, graduatoria, ammissione)

**5 criteri globali default**: Titolo di Studio (peso 2.0), Esperienza Lavorativa (1.5), Motivazione (1.0), Requisiti Specifici (1.5), Situazione Occupazionale (1.0)

**Workflow admin**:
- `/iscrizione/candidature` - Lista bandi con conteggio candidature
- `/iscrizione/candidature?avviso_id={id}` - Lista candidature per bando
- `/iscrizione/valuta/{id}` - Form punteggi per criterio (calcolo live JS)
- `/iscrizione/graduatoria-studenti?avviso_id={id}` - Graduatoria ordinata + ammissione singola/massiva
- `/iscrizione/storico/{id}` - Timeline cambi stato

## Configurazione Fondazione

Gestione dati fondazione tramite tabella `configurazione` (chiave-valore).

**Controller**: `ConfigurazioneController` (`/configurazione/index`)
**Model**: `Configurazione` con metodi statici `get(chiave)`, `set(chiave, valore)`, `getByGruppo()`
**Dati**: nome_fondazione, indirizzo, telefono, email, pec, partita_iva, codice_fiscale_ente, sito_web, logo_url

## Subentro Docenti Idonei

Sistema per sostituire un docente revocato/rinunciatario con un idoneo dalla graduatoria.

**Workflow**:
1. Admin revoca assegnazione (`AssegnazioneController::actionRevoca`) con motivazione
2. Sistema registra `StoricoEsito::ESITO_RINUNCIA` sull'istanza
3. Se ci sono idonei → redirect a pagina subentro (`actionSubentro`)
4. Admin seleziona un idoneo dalla lista ordinata per punteggio
5. Sistema crea nuova assegnazione + registra `ESITO_SUBENTRO`

**Model**: `Modulo::getIstanzeIdonee()`, `getProssimoIdoneo()`, `getGraduatoria()`, `getIdoneiDisponibiliCount()`
**Model**: `Assegnazione::isRevocabile()`, `revoca()`, `isAttiva()`
**Viste**: `views/assegnazione/subentro.php`, `views/istanza/graduatoria.php`

## Ambiente Docker

```bash
# Avvio ambiente
docker-compose up -d

# Servizi
- App:        http://localhost:8000
- phpMyAdmin: http://localhost:8080
- MySQL:      localhost:3307

# Credenziali Database
- Host: db (interno) / localhost:3307 (esterno)
- Database: albatros
- User: albatros
- Password: albatros
- Root Password: root

# Comandi utili
docker exec -w /app albatros-php php yii migrate              # Esegui migration
docker exec -w /app albatros-php php yii migrate/down 1       # Rollback ultima migration
docker exec -w /app albatros-php composer require package     # Installa pacchetto
```

## Terminologia

**IMPORTANTE**: Dalla riunione del 16/02/2025 è emersa la terminologia corretta da usare:

| Termine | Significato | Tabella DB | Contesto |
|---------|-------------|------------|----------|
| **CORSO** | Percorso formativo completo offerto agli studenti | `progetto` | Vista studente/pubblico |
| **PROGETTO** | Contenitore finanziato (FSE, PNRR, MIM) | `progetto` | Vista amministrativa |
| **MATERIA** / **MODULO** | Singola disciplina/insegnamento | `modulo` | Vista docente |
| **BANDO** / **AVVISO** | Selezione pubblica per docenti | `avviso` | Vista docente |

**Esempio pratico**:
- Un **PROGETTO** finanziato da PNRR chiamato "Health Food"
- Genera un **BANDO** (avviso) per la selezione docenti
- È composto da ~60 **MODULI** (materie come "Chimica degli Alimenti", "Sicurezza Alimentare", ecc.)
- Dal punto di vista studente è un **CORSO** di formazione con 20 posti disponibili

## Struttura Database

### Gerarchia Principale
```
FONTE_FINANZIAMENTO (FSE, PNRR, MIM)
    └── PROGETTO (= "corso" per studenti)
        └── AVVISO (bando selezione docenti)
            └── MODULO (= materia/insegnamento)
                ├── ISTANZA (candidature docenti)
                ├── ASSEGNAZIONE (esiti CTS) → CONTRATTO → PAGAMENTO
                ├── CALENDARIO (lezioni) → PRESENZA_ALLIEVO
                ├── ISCRIZIONE (studenti)
                └── VALUTAZIONE_DOCENTE
```

### Tabelle (36 totali)

| Tabella | PK | Descrizione |
|---------|-----|-------------|
| fonte_finanziamento | id | FSE, PNRR, MIM |
| conto_corrente | id | Conti bancari progetti |
| sede | id | Sedi operative |
| utente | id | Utenti sistema (login admin) |
| tipo_documento | id | Tipologie documenti |
| progetto | id | Progetti finanziati ("corsi" per studenti) |
| avviso | id | Bandi/avvisi selezione docenti |
| avviso_documento_tipo | id | Tipi documento richiesti per bando |
| **docente** | **codice_fiscale** | Docenti (PK = CF, campo `provenienza`: TP/RU) |
| docente_auth | id | Autenticazione area docenti (CF + password) |
| docente_tag | id | Tag/competenze associate al profilo docente |
| **allievo** | **codice_fiscale** | Allievi/Studenti (PK = CF) |
| modulo | id | Materie/Insegnamenti del progetto |
| documentazione | id | Documenti docenti/allievi |
| istanza | id | Candidature docenti (campo `esito_cts`) |
| istanza_documento | id | Documenti caricati nelle candidature |
| istanza_tag | id | Tag/competenze dichiarate in candidatura |
| istanza_snapshot | id | Snapshot dati docente al momento dell'assegnazione |
| storico_esito | id | Storico esiti CTS per istanza |
| assegnazione | id | Assegnazioni deliberate da CTS |
| contratto | id | Contratti (CoCoCo/P.IVA, date lezioni) |
| calendario | id | Lezioni programmate |
| iscrizione | id | Candidature/iscrizioni studenti (workflow candidatura→ammissione→frequenza) |
| criterio_valutazione_studente | id | Criteri valutazione candidature (globali + per bando) |
| punteggio_iscrizione | id | Punteggi per criterio per ogni candidatura |
| storico_iscrizione | id | Timeline cambi stato iscrizione (NO soft delete) |
| allievo_auth | id | Autenticazione area studenti (CF + password) |
| configurazione | id | Impostazioni chiave-valore sistema/fondazione (NO soft delete) |
| presenza_allievo | id | Presenze per lezione |
| pagamento | id | Pagamenti docenti (con ore_extra workflow) |
| rendicontazione_ore | id | Ore giornaliere rendicontate dai docenti |
| valutazione_docente | id | Valutazioni anonime |
| tag_categoria | id | Categorie per tag competenze |
| tag_curriculum | id | Tag competenze per curriculum docenti |
| audit_log | id | Log modifiche (sistema, NON soft delete) |
| migration | version | Yii2 migration tracking |

### Funzionalità Trasversali

#### Soft Delete
Tutte le tabelle hanno `deleted_at` (timestamp nullable). I record non vengono mai eliminati fisicamente.

```php
$docente->delete();           // Soft delete (imposta deleted_at)
$docente->hardDelete();       // Eliminazione fisica
$docente->restore();          // Ripristino
$docente->isDeleted();        // Verifica se eliminato

Docente::find();              // Solo record attivi (default)
Docente::findDeleted();       // Solo record eliminati
Docente::findWithDeleted();   // Tutti i record
```

#### Audit User Fields
Tutte le tabelle tracciano CHI ha eseguito le operazioni:

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `created_at` | timestamp | Data/ora creazione |
| `updated_at` | timestamp | Data/ora ultima modifica |
| `deleted_at` | timestamp | Data/ora soft delete |
| `created_by` | int FK → utente | Utente che ha creato |
| `updated_by` | int FK → utente | Utente che ha modificato |
| `deleted_by` | int FK → utente | Utente che ha eliminato |

I campi `*_by` vengono popolati automaticamente dal `AuditBehavior`.

```php
// Ottenere l'utente che ha creato il record
$fonte = FonteFinanziamento::findOne(1);
$creatore = $fonte->createdByUser;  // Relazione con User
echo $creatore->username;

// Verificare chi ha modificato
$modificatore = $fonte->updatedByUser;
```

#### Audit Log
Ogni INSERT, UPDATE, DELETE viene tracciato automaticamente nella tabella `audit_log`:
- Tabella e ID record modificato
- Azione eseguita
- Valori prima/dopo (JSON)
- Campi modificati
- Utente, IP, URL, User Agent, Session ID
- Timestamp

```php
// Aggiungere nota all'audit
$docente->withAuditNote('Modifica richiesta da segreteria')->save();

// Consultare cronologia
AuditLog::getRecordHistory('docente', 'RSSMRA80A01F205X');
AuditLog::getUserActivity($userId);
```

#### Campi Specifici per Gestione Docenti (Feb 2025)

**Tabella `docente`**:
- `provenienza` ENUM('TP', 'RU'):
  - **TP** = Tipo Professionista (mondo del lavoro) → Compenso PNRR: €60/ora
  - **RU** = Ricercatore Universitario → Compenso PNRR: €70/ora
  - Usato per differenziare automaticamente i compensi in base al tipo di progetto

**Tabella `contratto`**:
- `data_inizio_lezioni` DATE: Data effettiva inizio lezioni (diversa da `data_inizio` contrattuale)
- `data_fine_lezioni` DATE: Data effettiva/prevista fine lezioni
  - Utile per alert automatici di scadenza
  - Permette di sapere quando un docente termina per riassegnarlo
  - Necessario per rendicontazione precisa

**Tabella `istanza`** (candidature docenti):
- `esito_cts` ENUM('assegnato', 'idoneo', 'non_idoneo'):
  - **assegnato**: Docente selezionato dalla Commissione Tecnico Scientifica per l'incarico
  - **idoneo**: Valutato positivamente ma non assegnato → entra in **long list** per future chiamate
  - **non_idoneo**: Valutazione negativa → importante per storicità (tracciare chi è stato bocciato in passato)
  - Campo separato da `status` che gestisce il workflow della domanda

**Tabella `progetto`**:
- `numero_studenti` INT: Numero studenti previsti/iscritti (default 20)

**Workflow valutazione CTS** (Commissione Tecnico Scientifica):
```
1. Docente presenta ISTANZA per MODULO in un AVVISO
2. CTS valuta curriculum e titoli → assegna punteggi
3. CTS delibera esito_cts:
   - "assegnato" → genera ASSEGNAZIONE → CONTRATTO
   - "idoneo" → docente in long list per futuri incarichi
   - "non_idoneo" → candidatura respinta (tracciato in storico)
4. Sistema pubblica solo gli assegnatari (non gli idonei)
```

## Linee Guida CRUD

**IMPORTANTE**: Prima di creare qualsiasi pagina CRUD, leggere `docs/CRUD_GUIDELINES.md`

Il documento definisce:
- Layout responsive mobile-first
- Integrazione Tabler + Yii2 widgets
- Schema pulsanti standard (colori, icone, posizioni)
- Breadcrumb obbligatorio
- Messaggi flash e conferme
- Formattazione date/numeri in italiano
- Validazione client/server
- Filtri, paginazione, ordinamento
- Stati e badge con colori consistenti
- Accessibilità base
- Checklist pre-rilascio

## Struttura Codice

```
albatros-manager-yii2/
├── components/
│   ├── assets/
│   │   ├── TablerAsset.php           # Asset bundle Tabler v1.4.0
│   │   └── ApexChartsAsset.php       # Asset bundle ApexCharts (grafici)
│   ├── base/
│   │   └── BaseActiveRecord.php      # Classe base (soft delete + audit)
│   ├── behaviors/
│   │   ├── SoftDeleteBehavior.php    # Behavior soft delete
│   │   ├── AuditLogBehavior.php      # Behavior audit logging
│   │   └── AuditBehavior.php         # Behavior campi *_by (user tracking)
│   ├── traits/
│   │   └── SoftDeleteTrait.php       # Query scope soft delete
│   └── widgets/
│       ├── AppGridView.php           # GridView stilizzato Tabler
│       ├── AppActiveForm.php         # ActiveForm stilizzato Tabler
│       └── Alert.php                 # Flash messages Tabler
├── config/
│   ├── db.php                        # Configurazione database
│   └── web.php                       # Configurazione app
├── migrations/                        # 48 migration files
├── models/
│   ├── User.php                      # Model utente (autenticazione DB)
│   ├── LoginForm.php                 # Form login
│   ├── DocenteAuth.php               # Model autenticazione docenti
│   ├── AuditLog.php                  # Model audit log (NON estende BaseActiveRecord)
│   ├── FonteFinanziamento.php        # Model fonte finanziamento
│   ├── Sede.php, ContoCorrente.php   # Model standalone
│   ├── Progetto.php, Avviso.php, Modulo.php # Gerarchia progetti
│   ├── Docente.php, Allievo.php      # Persone (PK = codice_fiscale)
│   ├── Istanza.php                   # Candidature con esito_cts + snapshot + tag
│   ├── Assegnazione.php, Contratto.php, Pagamento.php # Catena selezione
│   ├── RendicontazioneOre.php        # Ore docenti con workflow approvazione
│   ├── TagCategoria.php, TagCurriculum.php # Sistema tag competenze
│   ├── IstanzaDocumento.php, IstanzaTag.php, IstanzaSnapshot.php # Dati istanza
│   ├── StoricoEsito.php              # Timeline esiti CTS
│   ├── AllievoAuth.php               # Autenticazione area studenti
│   ├── Configurazione.php            # Impostazioni chiave-valore (NO BaseActiveRecord)
│   ├── CriterioValutazioneStudente.php # Criteri valutazione candidature
│   ├── PunteggioIscrizione.php       # Punteggi per criterio per candidatura
│   ├── StoricoIscrizione.php         # Timeline cambi stato iscrizione (NO BaseActiveRecord)
│   ├── forms/AllievoLoginForm.php    # Form login studente
│   ├── forms/AllievoRegistrazioneForm.php # Form registrazione studente
│   └── *Search.php                   # Model di ricerca per GridView
├── views/
│   ├── layouts/
│   │   ├── main.php                  # Layout principale Tabler
│   │   ├── login.php                 # Layout pagina login (no menu)
│   │   ├── docente.php               # Layout area docenti (separato)
│   │   ├── studente.php              # Layout area studenti autenticata (viola)
│   │   ├── studenti-demo.php         # Layout area studenti pubblica
│   │   ├── header.php                # Header con notifiche/profilo
│   │   ├── menu.php                  # Menu navigazione sidebar
│   │   └── footer.php                # Footer
│   ├── site/
│   │   ├── index.php                 # Dashboard con grafici + attivita reali
│   │   └── login.php                 # Pagina login Tabler
│   ├── {entita}/                     # CRUD views per ogni entita (index, view, create, update, _form)
│   ├── docente-area/                 # Views portale docenti
│   ├── studente-area/               # Views portale studenti (10 file)
│   ├── studenti/                    # Views pagine pubbliche bandi
│   ├── configurazione/              # Views dati fondazione
│   ├── criterio-valutazione/        # Views CRUD criteri valutazione
│   ├── audit-log/                    # Views visualizzazione audit
│   ├── rendicontazione-ore/          # Views ore: index, view, dashboard, riepilogo
│   └── report/                       # Views report e statistiche
├── controllers/
│   ├── SiteController.php            # Dashboard (dati reali da audit_log)
│   ├── ImportController.php          # Import dati da Excel
│   ├── FonteFinanziamentoController.php # CRUD fonte finanziamento
│   ├── SedeController.php            # CRUD sedi
│   ├── ContoCorrenteController.php   # CRUD conti correnti
│   ├── TipoDocumentoController.php   # CRUD tipi documento
│   ├── ProgettoController.php        # CRUD progetti + rendicontazione
│   ├── AvvisoController.php          # CRUD avvisi/bandi + documenti
│   ├── ModuloController.php          # CRUD moduli + rendicontazione
│   ├── DocenteController.php         # CRUD docenti + ricerca per tag
│   ├── AllievoController.php         # CRUD allievi
│   ├── IstanzaController.php         # CRUD istanze + AJAX moduli + documenti
│   ├── AssegnazioneController.php    # CRUD assegnazioni
│   ├── ContrattoController.php       # CRUD contratti
│   ├── PagamentoController.php       # CRUD pagamenti + liquidazione massiva
│   ├── CalendarioController.php      # CRUD calendario lezioni
│   ├── IscrizioneController.php      # CRUD iscrizioni allievi
│   ├── PresenzaAllievoController.php # CRUD presenze
│   ├── DocumentazioneController.php  # CRUD documentazione
│   ├── ValutazioneDocenteController.php # CRUD valutazioni
│   ├── RendicontazioneOreController.php # Ore docenti + dashboard + approvazione
│   ├── AuditLogController.php        # Visualizzazione audit log
│   ├── ReportController.php          # Report e statistiche
│   ├── TagCategoriaController.php    # CRUD categorie tag
│   ├── TagCurriculumController.php   # CRUD tag curriculum
│   ├── DocenteAreaController.php     # Portale riservato docenti
│   ├── StudenteAreaController.php   # Portale riservato studenti
│   ├── StudentiController.php       # Pagine pubbliche bandi studenti
│   ├── ConfigurazioneController.php # Dati fondazione
│   └── CriterioValutazioneController.php # CRUD criteri valutazione
├── docs/
│   ├── schema-er.html                # Diagramma ER interattivo
│   ├── CRUD_GUIDELINES.md            # Linee guida sviluppo CRUD
│   ├── modifiche-database-feb-2025.md # Changelog database
│   └── seconda riunione.txt          # Verbale riunione 16/02/2025
├── import/
│   ├── README.md                     # Guida import dati Excel
│   ├── TEMPLATE_IMPORT_DATI.xlsx     # Template standardizzato
│   ├── AGRIFOOD_AVV2_MESSINA_IMPORT.xlsx # Esempio dati reali convertiti
│   └── ORIGINALE_Agrifood_Avv2_Messina.xlsx # File originale cliente
├── web/
│   └── assets/tabler/                # Asset Tabler (CSS, JS, fonts)
├── docker-compose.yml                # Configurazione Docker
└── CLAUDE.md                         # Questo file
```

## Creazione Model

Tutti i Model devono estendere `BaseActiveRecord`:

```php
<?php
namespace app\models;

use app\components\base\BaseActiveRecord;

class Docente extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%docente}}';
    }

    // Override per campi da escludere dall'audit
    protected function auditExcludedAttributes()
    {
        return ['created_at', 'updated_at'];
    }

    // Override per campi sensibili da mascherare
    protected function auditMaskedAttributes()
    {
        return ['iban']; // Verrà salvato come ***MASKED***
    }
}
```

## Ordine Implementazione Model

### Fase 1 - Standalone (0 FK)
1. FonteFinanziamento
2. ContoCorrente
3. Sede
4. TipoDocumento
5. Utente
6. Docente
7. Allievo

### Fase 2 - Catena gerarchica
8. Progetto (→ fonte, conto)
9. Avviso (→ progetto)
10. Modulo (→ avviso, sede)

### Fase 3 - Documenti
11. Documentazione (→ tipo, docente, allievo)

### Fase 4 - Processo selezione
12. Istanza (→ avviso, docente, modulo)
13. Assegnazione (→ modulo, docente, istanza)
14. Contratto (→ assegnazione)
15. Pagamento (→ contratto)

### Fase 5 - Gestione corsi
16. Iscrizione (→ modulo, allievo)
17. Calendario (→ modulo, docente)
18. PresenzaAllievo (→ calendario, allievo)
19. ValutazioneDocente (→ modulo, docente)

## Convenzioni

### Database
- PK persone: `codice_fiscale` (CHAR 16)
- Altre PK: `id` (INT auto-increment)
- FK naming: `{tabella}_{campo}` (es: `docente_codice_fiscale`)
- Timestamp: `created_at`, `updated_at`, `deleted_at`
- ENUM per status/tipo con valori espliciti

### Codice
- Namespace: `app\models`, `app\components`, etc.
- Model estendono `BaseActiveRecord`
- Lingua UI: Italiano
- Commenti codice: Italiano

## Import Dati da Excel

Sistema di importazione massiva di progetti, moduli e docenti da file Excel standardizzato.

### File e Cartelle

**Cartella `import/`** - Contiene tutti i file relativi all'import:
- `TEMPLATE_IMPORT_DATI.xlsx` - Template Excel vuoto da compilare
- `README.md` - Guida completa all'utilizzo del sistema import
- File esempio e file originali clienti

**Controller**: `controllers/ImportController.php`
- `actionIndex()` - Interfaccia web upload
- `actionUpload()` - Gestione upload e import
- `actionDownloadTemplate()` - Download template

**View**: `views/import/index.php` - Interfaccia web con:
- Form upload file Excel
- Validazione in modalità test (dry-run)
- Progress bar animata
- Visualizzazione log, errori, warning
- Statistiche import

### Template Excel (4 fogli)

1. **📖 ISTRUZIONI** - Guida utente completa
2. **ℹ️ INFO PROGETTO** - Dati progetto, avviso, sede
3. **📚 MODULI** - Elenco moduli formativi (codice, titolo, ore, compenso, filiera)
4. **👥 DOCENTI & ESITI** - Assegnazioni, idonei, non idonei, esiti CTS

### Workflow Import

```bash
# 1. Utente scarica template via web
https://localhost:8000/import/download-template

# 2. Compila Excel seguendo istruzioni

# 3. Carica via web e valida (dry-run)
# Sistema verifica:
- Campi obbligatori
- Formati date/numeri
- Provenienza (TP/RU)
- Riferimenti modulo

# 4. Conferma e salva
# Sistema crea in transazione:
- FonteFinanziamento, Sede, Progetto, Avviso
- Moduli (con campo filiera opzionale)
- Docenti (genera CF fittizio se mancante)
- Istanze con esito_cts (assegnato/idoneo/non_idoneo)
- Assegnazioni e Contratti (per assegnatari)
```

### Casi Speciali Supportati

**Rinunce**: Aggiungere `(Rinuncia)` dopo nome assegnatario
```
ASSEGNATARIO: Rossi Mario (Rinuncia)
```
Sistema crea istanza ma non assegnazione/contratto.

**Compensi variabili**: Specificare importo esatto
```
COMPENSO_ORARIO: 65
```
Sovrascrive default TP=60€, RU=70€.

**Moduli senza assegnatari**: Lasciare vuoto assegnatario
```
ASSEGNATARIO:
IDONEI: Bianchi Luigi, Verdi Giuseppe
```
Crea solo istanze per idonei.

**Più docenti per stesso modulo**: Ripetere CODICE_MODULO
```
MOD-001 | Rossi Mario   | | ...
MOD-001 | | Bianchi Luigi | ...  <- stesso modulo, altro docente
MOD-001 | | Verdi Anna    | ...  <- stesso modulo, altro docente
```
**IMPORTANTE**: Ogni riga deve avere CODICE_MODULO valorizzato (nessuna riga di continuazione con celle vuote).

### Librerie Richieste

```bash
# PhpSpreadsheet per lettura/scrittura Excel
composer require phpoffice/phpspreadsheet
```

### File Utili

- `import/README.md` - Documentazione completa import
- `docs/modifiche-database-feb-2025.md` - Changelog schema database
- `/tmp/create_excel_template.py` - Script Python generazione template
- `/tmp/converti_dati_reali_in_template_v2.py` - Script conversione formati legacy

## Documentazione

**IMPORTANTE**: Usare sempre Context7 per trovare la documentazione aggiornata dei framework:

```
# Yii2 Framework
mcp__plugin_context7_context7__resolve-library-id: yiisoft/yii2
mcp__plugin_context7_context7__query-docs: /yiisoft/yii2 "query"

# Tabler UI Kit
mcp__plugin_context7_context7__resolve-library-id: tabler
mcp__plugin_context7_context7__query-docs: /websites/tabler_io "query"

# Bootstrap 5
mcp__plugin_context7_context7__resolve-library-id: bootstrap
mcp__plugin_context7_context7__query-docs: /twbs/bootstrap "query"
```

Prima di implementare funzionalità, cercare sempre esempi e best practices nella documentazione ufficiale tramite Context7.

## Team di Agenti

**IMPORTANTE**: All'inizio di ogni sessione di sviluppo, caricare SEMPRE la configurazione del team di agenti da `docs/AGENT_TEAM_CONFIG.md` e avviare il team automaticamente per le attivita di sviluppo, verifica e testing.

Configurazione completa: `docs/AGENT_TEAM_CONFIG.md`

Per avviare il team:
```
Avvia il team di agenti come da docs/AGENT_TEAM_CONFIG.md
```

| Agente | Ruolo | Ordine |
|--------|-------|--------|
| **model-agent** | Model, DB, migration, conformita schema | 1 (primo) |
| **controller-agent** | Controller CRUD, logica, routing | 2 |
| **ui-agent** | Design views, conformita Tabler/CRUD Guidelines | 3 |
| **tester-agent** | QA via Chrome, verifica errori PHP/JS | 4 (o on-demand) |

## Liquidazione Massiva Pagamenti

Funzionalita per creare pagamenti in blocco per tutti i contratti con saldo residuo > 0.

**UI**: Pulsante "Liquidazione Massiva" nella toolbar di `views/pagamento/index.php`
- Modal con tabella contratti caricata via AJAX (`actionContrattiConSaldo`)
- Checkbox multi-selezione con select/deselect all
- Riepilogo in tempo reale (numero contratti + totale saldo)
- Dropdown tipo pagamento (saldo/acconto/rata)
- Esecuzione via AJAX POST (`actionLiquidaMassiva`)
- Calcolo automatico ritenute proporzionali per ogni contratto

**Endpoint**:
- `GET /pagamento/contratti-con-saldo` → JSON lista contratti con saldo > 0
- `POST /pagamento/liquida-massiva` → Crea pagamenti in transazione DB

## Audit Log (Visualizzazione)

**Controller**: `AuditLogController` con index + view
**Views**: `views/audit-log/index.php`, `views/audit-log/view.php`
**Dashboard**: `views/site/index.php` mostra le ultime 8 attivita reali da `audit_log`

**NOTA**: `AuditLog` model NON estende `BaseActiveRecord` (evita loop infinito di log).
`AuditLogBehavior` salva i nomi tabella puliti (senza `{{%...}}`).

## Report e Statistiche

**Controller**: `ReportController`
**Views**: `views/report/statistiche.php`, `views/report/export.php`

## Tag Curriculum e Categorie

Sistema di tagging per competenze docenti.

**Tabelle**: `tag_categoria` → `tag_curriculum` → `docente_tag` (profilo) / `istanza_tag` (candidatura)
**Controller**: `TagCategoriaController`, `TagCurriculumController`
**Views**: CRUD complete in `views/tag-categoria/` e `views/tag-curriculum/`

**Promozione tag**: I tag dichiarati in una candidatura (`istanza_tag`) possono essere promossi al profilo docente (`docente_tag`) dall'admin tramite `IstanzaController::actionPromuoviTag()`.

## Bug Fix Noti (Feb 2026)

| Bug | Fix | File |
|-----|-----|------|
| `AuditLog::getUser()` usava `Utente::class` inesistente | Corretto in `User::class` | `models/AuditLog.php` |
| Audit log salvava nomi tabella raw `{{%tabella}}` | `preg_replace` per pulire | `components/behaviors/AuditLogBehavior.php` |
| `IstanzaDocumento` default `avviso_documento_tipo_id = 0` violava FK | Cambiato default a `null` | `models/IstanzaDocumento.php` |
| `valutazione_docente` mancava colonna `updated_at` | Aggiunta via migration | `migrations/m260217_115011_*` |
| Dashboard "Attivita Recenti" con dati hardcoded finti | Query reale da `audit_log` | `controllers/SiteController.php`, `views/site/index.php` |
| `joinWith()` + SoftDeleteTrait causava conflitti alias | Usare `leftJoin` diretto | Pattern documentato in Convenzioni |

## Manuali Utente

Il sistema include 3 manuali HTML navigabili nel browser e stampabili/scaricabili come PDF.

| Manuale | URL | Vista | Destinatario |
|---------|-----|-------|-------------|
| Amministratore | `/site/manuale-admin` | `views/site/manuale-admin.php` | Staff amministrativo |
| Docente | `/site/manuale-docente` | `views/site/manuale-docente.php` | Docenti area riservata |
| Studente | `/site/manuale-studente` | `views/site/manuale-studente.php` | Studenti/corsisti |

**Link nei layout**:
- Dashboard admin (`views/site/index.php`): pulsante "Manuale Amministratore"
- Header admin (`views/layouts/header.php`): dropdown "Manuali" con link a tutti e 3
- Dashboard docente (`views/docente-area/index.php`): link al manuale docente

**Caratteristiche**:
- Layout `login` (pagina pulita senza menu sidebar)
- Sidebar indice sticky con scrollspy
- Pulsante "Stampa/Salva PDF" con `window.print()`
- CSS `@media print` per nascondere navigazione
- Accessibili senza login (azioni guest in SiteController)

### REGOLA MANUTENZIONE MANUALI

> **OBBLIGATORIO**: Ogni volta che si implementa una nuova feature o si modifica una feature esistente,
> verificare e aggiornare i manuali utente corrispondenti.

**Checklist per ogni feature implementata**:
1. La feature riguarda l'area admin? → Aggiornare `views/site/manuale-admin.php`
2. La feature riguarda l'area docenti? → Aggiornare `views/site/manuale-docente.php`
3. La feature riguarda l'area studenti? → Aggiornare `views/site/manuale-studente.php`
4. Aggiornare la tabella "Mappa Feature-Manuale" qui sotto

### Mappa Feature-Manuale (aggiornare ad ogni release)

| Feature | Manuale Admin | Manuale Docente | Manuale Studente |
|---------|:---:|:---:|:---:|
| Login unificato | Sez. 1 | Sez. 2 | Sez. 2 |
| Dashboard | Sez. 2 | Sez. 1 | - |
| Configurazione (Fonti, Conti, Sedi) | Sez. 3 | - | - |
| Gestione Progetti | Sez. 4 | - | - |
| Avvisi/Bandi | Sez. 5 | Sez. 4 | - |
| Moduli Formativi | Sez. 6 | - | - |
| Gestione Docenti + Long List | Sez. 7 | - | - |
| Processo Selezione (CTS) | Sez. 8 | Sez. 5-6 | - |
| Contratti | Sez. 9 | - | - |
| Pagamenti + Liquidazione Massiva | Sez. 10 | - | - |
| Rendicontazione Ore | Sez. 11 | Sez. 7-9 | - |
| Gestione Allievi + Presenze | Sez. 12 | - | Sez. 3 |
| Valutazione Docenti (Questionario) | Sez. 12 | - | - |
| Report + Export | Sez. 13 | - | - |
| Import Dati Excel | Sez. 14 | - | - |
| Area Docenti (panoramica admin) | Sez. 15 | Sez. 1-10 | - |
| Iscrizione Corsi | - | - | Sez. 2 |
| Alert Sovra-impegnato | Sez. 7 | - | - |
| Compenso TP/RU | Sez. 9 | - | - |
| Export Consulente Lavoro | Sez. 13 | - | - |
| Wizard Onboarding | Sez. 1 | Sez. 1 | Sez. 1 |
| Area Studenti (portale autenticato) | Sez. 16 | - | Sez. 2-4 |
| Candidature Studenti + Graduatoria | Sez. 16 | - | Sez. 3 |
| Criteri Valutazione | Sez. 16 | - | - |
| Dati Fondazione (Configurazione) | Sez. 3 | - | - |
| Subentro Docenti Idonei | Sez. 8 | - | - |
| Graduatoria Docenti | Sez. 8 | - | - |

## Questionario Anonimo Valutazione Docenti

Sistema di valutazione anonima tramite codici monouso.

**Flusso**:
1. Admin genera codici per un modulo (`ValutazioneDocenteController::actionGeneraCodici`)
2. Stampa foglietti con codici (`actionStampaCodici`)
3. Studente accede a `/valutazione/{CODICE}` (pagina pubblica, no login)
4. Compila 5 criteri (1-5 stelle) + commento → salva + invalida codice

**Tabelle**: `valutazione_codice` (id, modulo_id, codice VARCHAR 8 UNIQUE, usato_at)
**Controller pubblico**: `ValutazionePublicController` (accesso guest)
**Viste**: `views/valutazione-public/` (index, grazie, errore)
**Route**: `'valutazione/<codice:[A-Z0-9]{8}>' => 'valutazione-public/index'`

## Link Utili

- Schema ER: `docs/schema-er.html`
- Changelog DB: `docs/modifiche-database-feb-2025.md`
- CRUD Guidelines: `docs/CRUD_GUIDELINES.md`
- Team Agenti: `docs/AGENT_TEAM_CONFIG.md`
- Manuale Admin: http://localhost:8000/site/manuale-admin
- Manuale Docente: http://localhost:8000/site/manuale-docente
- Manuale Studente: http://localhost:8000/site/manuale-studente
- phpMyAdmin: http://localhost:8080
- App (Admin): http://localhost:8000
- Area Docenti: http://localhost:8000/area-docenti/login
- Area Studenti: http://localhost:8000/area-studenti/login
- Bandi Pubblici: http://localhost:8000/studenti
- Configurazione Fondazione: http://localhost:8000/configurazione
- Criteri Valutazione: http://localhost:8000/criterio-valutazione
- Graduatoria Docenti: http://localhost:8000/istanza/graduatoria
- Import Dati: http://localhost:8000/import
- Tabler Icons: https://tabler.io/icons
- Tabler Docs: https://tabler.io/docs
