# Albatros Manager - Gestione Docenti FSE/PNRR

Sistema di gestione per docenti e corsi finanziati da FSE (Fondo Sociale Europeo), PNRR e MIM.

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

## Struttura Database

### Gerarchia Principale
```
FONTE_FINANZIAMENTO (FSE, PNRR, MIM)
    └── PROGETTO
        └── AVVISO (bando pubblicato)
            └── MODULO (= corso = materia)
                ├── ISTANZA (domande docenti)
                ├── ASSEGNAZIONE → CONTRATTO → PAGAMENTO
                ├── CALENDARIO → PRESENZA_ALLIEVO
                ├── ISCRIZIONE (allievi)
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
| progetto | id | Progetti finanziati |
| avviso | id | Bandi/avvisi pubblicati |
| **docente** | **codice_fiscale** | Docenti (PK = CF) |
| **allievo** | **codice_fiscale** | Allievi (PK = CF) |
| modulo | id | Corsi/materie |
| documentazione | id | Documenti docenti/allievi |
| istanza | id | Domande presentate |
| assegnazione | id | Assegnazioni CTS |
| contratto | id | Contratti (CoCoCo/P.IVA) |
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
├── docs/
│   ├── schema-er.html                # Diagramma ER interattivo
│   └── CRUD_GUIDELINES.md            # Linee guida sviluppo CRUD
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

## Link Utili

- Schema ER: `docs/schema-er.html`
- phpMyAdmin: http://localhost:8080
- App: http://localhost:8000
- Tabler Icons: https://tabler.io/icons
- Tabler Docs: https://tabler.io/docs
