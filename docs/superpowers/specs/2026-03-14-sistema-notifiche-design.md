# Sistema Notifiche In-App + Email

## Obiettivo

Aggiungere un sistema di notifiche al portale Albatros Manager. L'admin può inviare notifiche con contenuto HTML a singoli utenti, categorie o tutti. Gli utenti vedono le notifiche nell'header (campanella) e in una pagina dedicata con stato letta/non letta. Invio email opzionale.

## Decisioni di Design

- **Rich text**: Summernote (Bootstrap-compatibile, leggero)
- **Destinatari**: tutti, per categoria (admin/docente/studente), singolo utente
- **Stato**: letta/non letta (no archiviazione)
- **Email**: opzionale, l'admin sceglie con checkbox
- **Contenuto**: titolo + HTML + link opzionale
- **Identificativo utenti**: admin → `id` (int), docenti → `codice_fiscale` (CF), studenti → `codice_fiscale` (CF)
- **Menu admin**: voce "Invia Notifica" sotto "Gestione" nel menu principale
- **Date relative**: `Yii::$app->formatter->asRelativeTime()`
- **Badge**: troncato a "99+" se > 99
- **Paginazione**: 20 per pagina, lista card custom (non AppGridView)

---

## 1. Database

### Tabella `notifica` (BaseActiveRecord)

| Campo | Tipo | Note |
|-------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `titolo` | VARCHAR(255) NOT NULL | |
| `contenuto` | TEXT NOT NULL | HTML sanitizzato con HtmlPurifier |
| `link` | VARCHAR(500) NULL | URL opzionale nel portale |
| `destinatario_tipo` | ENUM('tutti','admin','docente','studente','singolo_admin','singolo_docente','singolo_studente') | |
| `destinatario_id` | VARCHAR(50) NULL | ID (int) per admin, CF (char 16) per docenti/studenti |
| `invia_email` | TINYINT(1) DEFAULT 0 | |
| `email_inviata` | TINYINT(1) DEFAULT 0 | Flag completamento invio |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |
| `deleted_at` | DATETIME NULL | Soft delete |
| `created_by` | INT NULL | |
| `updated_by` | INT NULL | |
| `deleted_by` | INT NULL | |

### Tabella `notifica_lettura` (plain ActiveRecord, NO BaseActiveRecord)

| Campo | Tipo | Note |
|-------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `notifica_id` | INT NOT NULL FK → notifica.id | |
| `user_type` | ENUM('admin','docente','studente') | |
| `user_id` | VARCHAR(50) NOT NULL | ID (int) per admin, CF per docenti/studenti |
| `letta_at` | DATETIME NOT NULL | |

**Indice univoco**: `uk_notifica_lettura` su (`notifica_id`, `user_type`, `user_id`)

---

## 2. Model `Notifica`

File: `models/Notifica.php` — estende `BaseActiveRecord`

### Costanti

```php
const TIPO_TUTTI = 'tutti';
const TIPO_ADMIN = 'admin';
const TIPO_DOCENTE = 'docente';
const TIPO_STUDENTE = 'studente';
const TIPO_SINGOLO_ADMIN = 'singolo_admin';
const TIPO_SINGOLO_DOCENTE = 'singolo_docente';
const TIPO_SINGOLO_STUDENTE = 'singolo_studente';
```

### Metodi

- `getLetture()` — hasMany NotificaLettura
- `isLettaDa(string $userType, string $userId): bool`
- `segnaComeLetta(string $userType, string $userId): void` — usa `INSERT IGNORE` o try/catch su unique constraint per evitare race condition
- `getDestinatariLabel(): string` — label leggibile per la griglia admin
- `static queryPerUtente(string $userType, string $userId): ActiveQuery` — vedi sezione Query Logic
- `static countNonLette(string $userType, string $userId): int` — conta notifiche non lette per badge
- `static getUltimeNonLette(string $userType, string $userId, int $limit = 5): array` — ultime N non lette per dropdown header
- `getCreatoDaLabel(): string` — nominativo dell'admin che ha creato
- `beforeSave()` — sanitizza `contenuto` con `HtmlPurifier::process()`

### Query Logic — `queryPerUtente()`

```php
// Per un docente con CF 'RSSMRA80A01H501Z':
Notifica::find()
    ->andWhere(['deleted_at' => null])
    ->andWhere(['or',
        ['destinatario_tipo' => 'tutti'],
        ['destinatario_tipo' => 'docente'],
        ['and',
            ['destinatario_tipo' => 'singolo_docente'],
            ['destinatario_id' => 'RSSMRA80A01H501Z'],
        ],
    ])
    ->orderBy(['created_at' => SORT_DESC]);
```

Per admin: match `tutti`, `admin`, `singolo_admin` (con `destinatario_id` = user ID int).
Per studenti: match `tutti`, `studente`, `singolo_studente` (con `destinatario_id` = CF).

### `countNonLette()` — con LEFT JOIN

```sql
SELECT COUNT(*) FROM notifica n
LEFT JOIN notifica_lettura nl ON nl.notifica_id = n.id
    AND nl.user_type = :userType AND nl.user_id = :userId
WHERE n.deleted_at IS NULL
    AND nl.id IS NULL
    AND (n.destinatario_tipo = 'tutti'
         OR n.destinatario_tipo = :userType
         OR (n.destinatario_tipo = :singolo AND n.destinatario_id = :userId))
```

### Rules

- `titolo`, `contenuto`, `destinatario_tipo` required
- `destinatario_id` required quando tipo è `singolo_*` (conditional validation)
- `link` url validator (opzionale)
- `invia_email` boolean

---

## 3. Model `NotificaLettura`

File: `models/NotificaLettura.php` — estende plain `ActiveRecord` (NO BaseActiveRecord)

Relazione: `getNotifica()` → hasOne Notifica (con `andWhere(['notifica.deleted_at' => null])` per escludere soft-deleted)

---

## 4. Controller `NotificaController` (admin)

File: `controllers/NotificaController.php`

### AccessControl

Solo admin (`isAdmin()` matchCallback).

### Azioni

| Azione | Metodo | Descrizione |
|--------|--------|-------------|
| `index` | GET | Lista notifiche inviate con filtri (griglia) |
| `create` | GET/POST | Form creazione con Summernote |
| `view` | GET | Dettaglio notifica + lista chi ha letto |
| `delete` | POST | Soft delete |

### Form Creazione

- **Titolo**: text input
- **Contenuto**: Summernote editor (HTML)
- **Link**: text input opzionale (placeholder "es. /contratto/view/5")
- **Destinatario**: dropdown con opzioni:
  - Tutti gli utenti
  - Solo Amministratori
  - Solo Docenti
  - Solo Studenti
  - Singolo Amministratore → mostra select utenti admin (nominativo)
  - Singolo Docente → mostra select docenti (cognome nome + CF)
  - Singolo Studente → mostra select studenti (cognome nome + CF)
- **Invia anche email**: checkbox
- JS: mostra/nasconde il campo destinatario singolo in base alla selezione

### Invio Email nel Controller

Dopo il save della notifica, se `invia_email = 1`:
- Raccoglie le email destinatarie (max 500)
- Invia in chunks di 10 con `set_time_limit(0)` per evitare timeout
- Se errore su singola email: log e continua
- Alla fine: `email_inviata = 1` e flash con conteggio invii riusciti/falliti
- Per notifiche `singolo_*`: invio singola email (immediato, nessun chunk)

### Search Model

`NotificaSearch` — filtri: titolo (ricerca), destinatario_tipo, created_at range

---

## 5. Trait `NotificheActionsTrait`

File: `components/traits/NotificheActionsTrait.php`

Usato da `SiteController`, `DocenteAreaController`, `StudenteAreaController`.

### Metodi richiesti dal controller

- `getNotificheUserType(): string` — 'admin', 'docente', 'studente'
- `getNotificheUserId(): string`:
  - **Admin**: `(string) Yii::$app->user->identity->id` (int → string)
  - **Docente**: `Yii::$app->docenteUser->identity->docente_codice_fiscale` (CF)
  - **Studente**: `Yii::$app->studenteUser->identity->allievo_codice_fiscale` (CF)

### Actions

- `actionNotifiche()` — pagina lista notifiche con tab "Non lette" / "Tutte", 20 per pagina
- `actionNotificaView($id)` — dettaglio notifica, segna come letta automaticamente, verifica che sia destinata all'utente
- `actionSegnaLetta($id)` — POST AJAX, segna singola come letta, ritorna JSON
- `actionSegnaTutteLette()` — POST, segna tutte come lette, redirect a notifiche

### AccessControl

Aggiungere le actions `notifiche`, `notifica-view`, `segna-letta`, `segna-tutte-lette` alle regole di accesso in tutti e 3 i controller:
- **SiteController**: aggiungere alla lista delle actions autenticate (non guest)
- **DocenteAreaController**: aggiungere alle actions permesse per utenti autenticati
- **StudenteAreaController**: idem

---

## 6. Campanella Header

### Sostituzione placeholder

In tutti e 3 i layout (`header.php`, `docente.php`, `studente.php`):

- Rimuovere il dropdown statico placeholder con "Benvenuto in Albatros Manager"
- Icona `ti ti-bell` con badge rosso conteggio non lette (troncato a "99+" se > 99)
- Dropdown con ultime 5 non lette: titolo troncato + data relativa (`Yii::$app->formatter->asRelativeTime()`)
- Link "Vedi tutte le notifiche" in fondo al dropdown → pagina notifiche
- Se admin: link aggiuntivo "Invia notifica" → `/notifica/create`

### Conteggio

```php
$notificheCount = \app\models\Notifica::countNonLette($userType, $userId);
```

Badge visibile solo se `$notificheCount > 0`. Mostra `99+` se > 99.

---

## 7. Pagina Notifiche Utente

### Layout

- Page header "Notifiche" con icona `ti ti-bell`
- Pulsante "Segna tutte come lette" (se ci sono non lette)
- Tab: "Non lette (N)" / "Tutte"
- Lista card per ogni notifica:
  - Indicatore non letta (bordo sinistro blu `border-start border-primary border-3`)
  - Titolo (link a dettaglio)
  - Data relativa con `Yii::$app->formatter->asRelativeTime()`
  - Preview testo (strip HTML con `strip_tags()`, troncato a 100 char)
- Paginazione: 20 per pagina con `yii\data\Pagination`

### Vista dettaglio notifica

- Card con titolo, data, mittente (nominativo admin da `created_by`)
- Contenuto HTML renderizzato (già sanitizzato al salvataggio)
- Pulsante link se presente
- Segna automaticamente come letta all'apertura

---

## 8. Invio Email

### Logica (nel controller, dopo save)

- **tutti**: query email da `utente` (status active) + `docente` (join `docente_auth` status active) + `allievo` (join `allievo_auth` status active)
- **admin**: query email da `utente` (status active)
- **docente**: query email da `docente` join `docente_auth` (status active)
- **studente**: query email da `allievo` join `allievo_auth` (status active)
- **singolo_admin**: email da `utente` con id = destinatario_id
- **singolo_docente**: email da `docente` con CF = destinatario_id
- **singolo_studente**: email da `allievo` con CF = destinatario_id

### Gestione timeout e errori

```php
set_time_limit(0);
$sent = 0;
$failed = 0;
foreach (array_chunk($emails, 10) as $chunk) {
    foreach ($chunk as $email) {
        try {
            // invio con Yii::$app->mailer
            $sent++;
        } catch (\Throwable $e) {
            Yii::error("Email notifica #{$notifica->id} a {$email}: " . $e->getMessage());
            $failed++;
        }
    }
}
$notifica->email_inviata = 1;
$notifica->save(false, ['email_inviata']);
Yii::$app->session->setFlash('info', "Email inviate: {$sent} riuscite, {$failed} fallite.");
```

### Template

View mail: `mail/notifica-html.php` + `mail/notifica-text.php`
- Subject: titolo notifica
- Body HTML: contenuto + link "Vai al portale"
- Body testo: strip_tags del contenuto
- Footer: "Albatros Manager - Notifica automatica"

---

## 9. Summernote

### Dipendenza

Aggiungere `ezyang/htmlpurifier` per la sanitizzazione HTML:
```bash
docker exec -w /app albatros-php composer require ezyang/htmlpurifier
```

Nota: `yii\helpers\HtmlPurifier` lo wrappa automaticamente, ma la libreria va installata.

### Integrazione

CDN nel form di creazione notifica (solo nella view `views/notifica/create.php`):

```html
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-it-IT.min.js"></script>
```

**Nota**: usare `summernote-lite` (senza dipendenza Bootstrap) per evitare conflitti CSS con Tabler.

Configurazione:

```javascript
$('#contenuto').summernote({
    height: 200,
    toolbar: [
        ['style', ['bold', 'italic', 'underline']],
        ['para', ['ul', 'ol']],
        ['insert', ['link']],
        ['view', ['codeview']],
    ],
    lang: 'it-IT',
});
```

---

## 10. Menu Admin

Voce "Notifiche" nel menu sotto "Gestione", con icona `ti ti-bell-plus`:

```php
<a class="dropdown-item" href="<?= Url::to(['/notifica/index']) ?>">
    <i class="ti ti-bell-plus me-2"></i> Notifiche
</a>
```

Aggiungere `'notifica'` alla lista dei controller attivi per il dropdown Gestione.

---

## 11. Route

In `config/web.php`:
```php
'area-docenti/notifiche' => 'docente-area/notifiche',
'area-docenti/notifica/<id:\d+>' => 'docente-area/notifica-view',
'area-studenti/notifiche' => 'studente-area/notifiche',
'area-studenti/notifica/<id:\d+>' => 'studente-area/notifica-view',
```

---

## 12. Sicurezza

- Solo admin può creare/eliminare notifiche
- Contenuto HTML sanitizzato con `yii\helpers\HtmlPurifier::process()` in `beforeSave()`
- Utenti vedono solo notifiche destinate a loro (filtro in `queryPerUtente()`)
- `actionNotificaView()` verifica che la notifica sia destinata all'utente corrente (403 altrimenti)
- `segnaComeLetta()` usa `INSERT IGNORE` o try/catch su unique constraint per race condition

---

## 13. Aggiornamento CHANGELOG e Manuali

- Aggiungere voci sotto `[Unreleased]` in `CHANGELOG.md`
- Aggiornare `views/site/manuale-admin.php` (sezione invio notifiche)
- Aggiornare `views/site/manuale-docente.php` (sezione visualizzazione notifiche)
- Aggiornare `views/site/manuale-studente.php` (sezione visualizzazione notifiche)
