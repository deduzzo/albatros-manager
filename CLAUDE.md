# Albatros Manager - Gestione Docenti FSE/PNRR

Sistema di gestione per docenti e corsi finanziati da FSE (Fondo Sociale Europeo), PNRR e MIM.

## Stack Tecnologico

- **Framework**: Yii2 Basic Template
- **PHP**: 8.4
- **Database**: MySQL 8.0
- **UI**: Tabler Dashboard Theme (synamen/yii2-tabler-theme)
- **Container**: Docker

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

## Struttura Codice

```
albatros-manager-yii2/
├── components/
│   ├── base/
│   │   └── BaseActiveRecord.php      # Classe base (soft delete + audit)
│   ├── behaviors/
│   │   ├── SoftDeleteBehavior.php    # Behavior soft delete
│   │   └── AuditLogBehavior.php      # Behavior audit logging
│   └── traits/
│       └── SoftDeleteTrait.php       # Query scope soft delete
├── config/
│   ├── db.php                        # Configurazione database
│   └── web.php                       # Configurazione app
├── migrations/                        # 21 migration files
├── models/
│   └── AuditLog.php                  # Model audit log
├── views/
│   ├── layouts/
│   │   ├── main.php                  # Layout principale Tabler
│   │   ├── header.php                # Header con notifiche/profilo
│   │   ├── menu.php                  # Menu navigazione
│   │   └── footer.php                # Footer
│   └── site/
│       └── index.php                 # Dashboard demo
├── docs/
│   └── schema-er.html                # Diagramma ER interattivo
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

## Link Utili

- Schema ER: `docs/schema-er.html`
- phpMyAdmin: http://localhost:8080
- App: http://localhost:8000
