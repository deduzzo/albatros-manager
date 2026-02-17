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

**Nelle View** con colore inline (es. badge categoria):
```php
// CORRETTO - aggiungere sempre color: #fff per sfondo colorato inline
'<span class="badge" style="background-color: ' . Html::encode($colore) . '; color: #fff;">' . $testo . '</span>'
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

### Tabelle (21 totali)

| Tabella | PK | Descrizione |
|---------|-----|-------------|
| fonte_finanziamento | id | FSE, PNRR, MIM |
| conto_corrente | id | Conti bancari progetti |
| sede | id | Sedi operative |
| utente | id | Utenti sistema (login) |
| tipo_documento | id | Tipologie documenti |
| progetto | id | Progetti finanziati ("corsi" per studenti) |
| avviso | id | Bandi/avvisi selezione docenti |
| **docente** | **codice_fiscale** | Docenti (PK = CF, campo `provenienza`: TP/RU) |
| **allievo** | **codice_fiscale** | Allievi/Studenti (PK = CF) |
| modulo | id | Materie/Insegnamenti del progetto |
| documentazione | id | Documenti docenti/allievi |
| istanza | id | Candidature docenti (campo `esito_cts`) |
| assegnazione | id | Assegnazioni deliberate da CTS |
| contratto | id | Contratti (CoCoCo/P.IVA, date lezioni) |
| calendario | id | Lezioni programmate |
| iscrizione | id | Iscrizioni allievi |
| presenza_allievo | id | Presenze per lezione |
| pagamento | id | Pagamenti docenti |
| valutazione_docente | id | Valutazioni anonime |
| audit_log | id | Log modifiche (sistema) |
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
├── migrations/                        # 21 migration files
├── models/
│   ├── User.php                      # Model utente (autenticazione DB)
│   ├── LoginForm.php                 # Form login
│   ├── AuditLog.php                  # Model audit log
│   ├── FonteFinanziamento.php        # Model fonte finanziamento
│   ├── Sede.php                      # Model sede
│   └── *Search.php                   # Model di ricerca per GridView
├── views/
│   ├── layouts/
│   │   ├── main.php                  # Layout principale Tabler
│   │   ├── login.php                 # Layout pagina login (no menu)
│   │   ├── header.php                # Header con notifiche/profilo
│   │   ├── menu.php                  # Menu navigazione
│   │   └── footer.php                # Footer
│   └── site/
│       ├── index.php                 # Dashboard con grafici
│       └── login.php                 # Pagina login Tabler
├── controllers/
│   └── ImportController.php          # Controller import dati da Excel
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
├── views/
│   └── import/
│       └── index.php                 # Interfaccia web import Excel
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

## Link Utili

- Schema ER: `docs/schema-er.html`
- Changelog DB: `docs/modifiche-database-feb-2025.md`
- CRUD Guidelines: `docs/CRUD_GUIDELINES.md`
- Team Agenti: `docs/AGENT_TEAM_CONFIG.md`
- phpMyAdmin: http://localhost:8080
- App (Admin): http://localhost:8000
- Area Docenti: http://localhost:8000/area-docenti/login
- Import Dati: http://localhost:8000/import
- Tabler Icons: https://tabler.io/icons
- Tabler Docs: https://tabler.io/docs
