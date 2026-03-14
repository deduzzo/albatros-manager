# Sistema Notifiche — Piano di Implementazione

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Aggiungere sistema notifiche in-app con invio da admin (HTML rich text), campanella header, pagina notifiche per tutti gli utenti, e invio email opzionale.

**Architecture:** Tabella `notifica` (BaseActiveRecord) + `notifica_lettura` (plain AR). Admin invia tramite form con Summernote. Trait condiviso `NotificheActionsTrait` per visualizzazione in tutti e 3 i controller. Campanella header con dropdown preview + badge conteggio. Email opzionale via `Yii::$app->mailer` con chunking.

**Tech Stack:** Yii2, PHP 8.4, MySQL 8.0, Summernote Lite (CDN), HtmlPurifier, Tabler UI

**Spec:** `docs/superpowers/specs/2026-03-14-sistema-notifiche-design.md`

---

## Chunk 1: Database + Dipendenze + Models

### Task 1: Installare HtmlPurifier

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Installare ezyang/htmlpurifier**

```bash
docker exec -w /app albatros-php composer require ezyang/htmlpurifier
```

- [ ] **Step 2: Commit**

```bash
git add composer.json composer.lock
git commit -m "deps: add ezyang/htmlpurifier for HTML sanitization"
```

---

### Task 2: Migration — tabella `notifica`

**Files:**
- Create: `migrations/m260314_200000_create_notifica_table.php`

- [ ] **Step 1: Creare migration**

```php
<?php

use yii\db\Migration;

class m260314_200000_create_notifica_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('notifica', [
            'id' => $this->primaryKey(),
            'titolo' => $this->string(255)->notNull(),
            'contenuto' => $this->text()->notNull(),
            'link' => $this->string(500)->null(),
            'destinatario_tipo' => "ENUM('tutti','admin','docente','studente','singolo_admin','singolo_docente','singolo_studente') NOT NULL",
            'destinatario_id' => $this->string(50)->null(),
            'invia_email' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'email_inviata' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->createIndex('idx_notifica_tipo', 'notifica', 'destinatario_tipo');
        $this->createIndex('idx_notifica_deleted', 'notifica', 'deleted_at');
    }

    public function safeDown()
    {
        $this->dropTable('notifica');
    }
}
```

- [ ] **Step 2: Eseguire migration**

```bash
docker exec -w /app albatros-php php yii migrate --interactive=0
```

- [ ] **Step 3: Commit**

```bash
git add migrations/m260314_200000_create_notifica_table.php
git commit -m "db: create notifica table"
```

---

### Task 3: Migration — tabella `notifica_lettura`

**Files:**
- Create: `migrations/m260314_200001_create_notifica_lettura_table.php`

- [ ] **Step 1: Creare migration**

```php
<?php

use yii\db\Migration;

class m260314_200001_create_notifica_lettura_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('notifica_lettura', [
            'id' => $this->primaryKey(),
            'notifica_id' => $this->integer()->notNull(),
            'user_type' => "ENUM('admin','docente','studente') NOT NULL",
            'user_id' => $this->string(50)->notNull(),
            'letta_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('uk_notifica_lettura', 'notifica_lettura', ['notifica_id', 'user_type', 'user_id'], true);
        $this->addForeignKey('fk_notifica_lettura_notifica', 'notifica_lettura', 'notifica_id', 'notifica', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('notifica_lettura');
    }
}
```

- [ ] **Step 2: Eseguire migration**

```bash
docker exec -w /app albatros-php php yii migrate --interactive=0
```

- [ ] **Step 3: Commit**

```bash
git add migrations/m260314_200001_create_notifica_lettura_table.php
git commit -m "db: create notifica_lettura table with unique constraint"
```

---

### Task 4: Model Notifica

**Files:**
- Create: `models/Notifica.php`

- [ ] **Step 1: Creare il model**

```php
<?php

namespace app\models;

use Yii;
use app\components\base\BaseActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Model per la tabella "notifica".
 *
 * @property int $id
 * @property string $titolo
 * @property string $contenuto
 * @property string|null $link
 * @property string $destinatario_tipo
 * @property string|null $destinatario_id
 * @property int $invia_email
 * @property int $email_inviata
 */
class Notifica extends BaseActiveRecord
{
    const TIPO_TUTTI = 'tutti';
    const TIPO_ADMIN = 'admin';
    const TIPO_DOCENTE = 'docente';
    const TIPO_STUDENTE = 'studente';
    const TIPO_SINGOLO_ADMIN = 'singolo_admin';
    const TIPO_SINGOLO_DOCENTE = 'singolo_docente';
    const TIPO_SINGOLO_STUDENTE = 'singolo_studente';

    public static function tableName()
    {
        return '{{%notifica}}';
    }

    public function rules()
    {
        return [
            [['titolo', 'contenuto', 'destinatario_tipo'], 'required'],
            ['titolo', 'string', 'max' => 255],
            ['contenuto', 'string'],
            ['link', 'string', 'max' => 500],
            ['destinatario_tipo', 'in', 'range' => [
                self::TIPO_TUTTI, self::TIPO_ADMIN, self::TIPO_DOCENTE, self::TIPO_STUDENTE,
                self::TIPO_SINGOLO_ADMIN, self::TIPO_SINGOLO_DOCENTE, self::TIPO_SINGOLO_STUDENTE,
            ]],
            ['destinatario_id', 'required', 'when' => function ($model) {
                return in_array($model->destinatario_tipo, [self::TIPO_SINGOLO_ADMIN, self::TIPO_SINGOLO_DOCENTE, self::TIPO_SINGOLO_STUDENTE]);
            }],
            ['destinatario_id', 'string', 'max' => 50],
            [['invia_email', 'email_inviata'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titolo' => 'Titolo',
            'contenuto' => 'Contenuto',
            'link' => 'Link',
            'destinatario_tipo' => 'Destinatari',
            'destinatario_id' => 'Destinatario',
            'invia_email' => 'Invia anche email',
            'email_inviata' => 'Email inviata',
            'created_at' => 'Data invio',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // Sanitizza HTML
        $this->contenuto = HtmlPurifier::process($this->contenuto);
        return true;
    }

    // --- Relazioni ---

    public function getLetture()
    {
        return $this->hasMany(NotificaLettura::class, ['notifica_id' => 'id']);
    }

    // --- Metodi business ---

    public function isLettaDa(string $userType, string $userId): bool
    {
        return NotificaLettura::find()
            ->andWhere(['notifica_id' => $this->id, 'user_type' => $userType, 'user_id' => $userId])
            ->exists();
    }

    public function segnaComeLetta(string $userType, string $userId): void
    {
        try {
            $lettura = new NotificaLettura([
                'notifica_id' => $this->id,
                'user_type' => $userType,
                'user_id' => $userId,
                'letta_at' => date('Y-m-d H:i:s'),
            ]);
            $lettura->save(false);
        } catch (\yii\db\IntegrityException $e) {
            // Già letta (unique constraint) — ignora
        }
    }

    public function getDestinatariLabel(): string
    {
        return match ($this->destinatario_tipo) {
            self::TIPO_TUTTI => 'Tutti gli utenti',
            self::TIPO_ADMIN => 'Amministratori',
            self::TIPO_DOCENTE => 'Docenti',
            self::TIPO_STUDENTE => 'Studenti',
            self::TIPO_SINGOLO_ADMIN => 'Admin: ' . $this->getDestinatarioNome(),
            self::TIPO_SINGOLO_DOCENTE => 'Docente: ' . $this->getDestinatarioNome(),
            self::TIPO_SINGOLO_STUDENTE => 'Studente: ' . $this->getDestinatarioNome(),
            default => $this->destinatario_tipo,
        };
    }

    private function getDestinatarioNome(): string
    {
        if (empty($this->destinatario_id)) {
            return '?';
        }
        if (in_array($this->destinatario_tipo, [self::TIPO_SINGOLO_DOCENTE])) {
            $docente = Docente::findOne(['codice_fiscale' => $this->destinatario_id]);
            return $docente ? "{$docente->cognome} {$docente->nome}" : $this->destinatario_id;
        }
        if (in_array($this->destinatario_tipo, [self::TIPO_SINGOLO_STUDENTE])) {
            $allievo = Allievo::findOne(['codice_fiscale' => $this->destinatario_id]);
            return $allievo ? "{$allievo->cognome} {$allievo->nome}" : $this->destinatario_id;
        }
        // Singolo admin
        $user = User::findOne($this->destinatario_id);
        return $user ? ($user->nominativo ?: $user->username) : $this->destinatario_id;
    }

    public function getCreatoDaLabel(): string
    {
        if ($this->created_by) {
            $user = User::findOne($this->created_by);
            return $user ? ($user->nominativo ?: $user->username) : "ID: {$this->created_by}";
        }
        return 'Sistema';
    }

    /**
     * Query notifiche destinate a un tipo utente specifico.
     */
    public static function queryPerUtente(string $userType, string $userId): \yii\db\ActiveQuery
    {
        $singolo = 'singolo_' . $userType;

        return static::find()
            ->andWhere(['or',
                ['destinatario_tipo' => self::TIPO_TUTTI],
                ['destinatario_tipo' => $userType],
                ['and',
                    ['destinatario_tipo' => $singolo],
                    ['destinatario_id' => $userId],
                ],
            ])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Conta notifiche non lette per un utente.
     */
    public static function countNonLette(string $userType, string $userId): int
    {
        $singolo = 'singolo_' . $userType;

        return (int) static::find()
            ->alias('n')
            ->leftJoin('notifica_lettura nl', 'nl.notifica_id = n.id AND nl.user_type = :ut AND nl.user_id = :uid', [
                ':ut' => $userType,
                ':uid' => $userId,
            ])
            ->andWhere(['nl.id' => null])
            ->andWhere(['or',
                ['n.destinatario_tipo' => self::TIPO_TUTTI],
                ['n.destinatario_tipo' => $userType],
                ['and',
                    ['n.destinatario_tipo' => $singolo],
                    ['n.destinatario_id' => $userId],
                ],
            ])
            ->count();
    }

    /**
     * Ultime N notifiche non lette per il dropdown header.
     */
    public static function getUltimeNonLette(string $userType, string $userId, int $limit = 5): array
    {
        $singolo = 'singolo_' . $userType;

        return static::find()
            ->alias('n')
            ->leftJoin('notifica_lettura nl', 'nl.notifica_id = n.id AND nl.user_type = :ut AND nl.user_id = :uid', [
                ':ut' => $userType,
                ':uid' => $userId,
            ])
            ->andWhere(['nl.id' => null])
            ->andWhere(['or',
                ['n.destinatario_tipo' => self::TIPO_TUTTI],
                ['n.destinatario_tipo' => $userType],
                ['and',
                    ['n.destinatario_tipo' => $singolo],
                    ['n.destinatario_id' => $userId],
                ],
            ])
            ->orderBy(['n.created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Opzioni per il dropdown destinatario nel form admin.
     */
    public static function getDestinatarioTipoOptions(): array
    {
        return [
            self::TIPO_TUTTI => 'Tutti gli utenti',
            self::TIPO_ADMIN => 'Solo Amministratori',
            self::TIPO_DOCENTE => 'Solo Docenti',
            self::TIPO_STUDENTE => 'Solo Studenti',
            self::TIPO_SINGOLO_ADMIN => 'Singolo Amministratore',
            self::TIPO_SINGOLO_DOCENTE => 'Singolo Docente',
            self::TIPO_SINGOLO_STUDENTE => 'Singolo Studente',
        ];
    }

    /**
     * Raccoglie le email per l'invio.
     */
    public function getEmailDestinatari(): array
    {
        $emails = [];

        switch ($this->destinatario_tipo) {
            case self::TIPO_TUTTI:
                // Admin
                $admins = User::find()->andWhere(['status' => User::STATUS_ACTIVE])->all();
                foreach ($admins as $u) { if (!empty($u->email)) $emails[] = $u->email; }
                // Docenti
                $docenti = Docente::find()
                    ->alias('d')
                    ->leftJoin('docente_auth da', 'd.codice_fiscale = da.docente_codice_fiscale AND da.deleted_at IS NULL')
                    ->andWhere(['da.status' => 'active'])
                    ->all();
                foreach ($docenti as $d) { if (!empty($d->email)) $emails[] = $d->email; }
                // Studenti
                $allievi = Allievo::find()
                    ->alias('a')
                    ->leftJoin('allievo_auth aa', 'a.codice_fiscale = aa.allievo_codice_fiscale AND aa.deleted_at IS NULL')
                    ->andWhere(['aa.status' => 'active'])
                    ->all();
                foreach ($allievi as $a) { if (!empty($a->email)) $emails[] = $a->email; }
                break;

            case self::TIPO_ADMIN:
                $admins = User::find()->andWhere(['status' => User::STATUS_ACTIVE])->all();
                foreach ($admins as $u) { if (!empty($u->email)) $emails[] = $u->email; }
                break;

            case self::TIPO_DOCENTE:
                $docenti = Docente::find()
                    ->alias('d')
                    ->leftJoin('docente_auth da', 'd.codice_fiscale = da.docente_codice_fiscale AND da.deleted_at IS NULL')
                    ->andWhere(['da.status' => 'active'])
                    ->all();
                foreach ($docenti as $d) { if (!empty($d->email)) $emails[] = $d->email; }
                break;

            case self::TIPO_STUDENTE:
                $allievi = Allievo::find()
                    ->alias('a')
                    ->leftJoin('allievo_auth aa', 'a.codice_fiscale = aa.allievo_codice_fiscale AND aa.deleted_at IS NULL')
                    ->andWhere(['aa.status' => 'active'])
                    ->all();
                foreach ($allievi as $a) { if (!empty($a->email)) $emails[] = $a->email; }
                break;

            case self::TIPO_SINGOLO_ADMIN:
                $user = User::findOne($this->destinatario_id);
                if ($user && !empty($user->email)) $emails[] = $user->email;
                break;

            case self::TIPO_SINGOLO_DOCENTE:
                $docente = Docente::findOne(['codice_fiscale' => $this->destinatario_id]);
                if ($docente && !empty($docente->email)) $emails[] = $docente->email;
                break;

            case self::TIPO_SINGOLO_STUDENTE:
                $allievo = Allievo::findOne(['codice_fiscale' => $this->destinatario_id]);
                if ($allievo && !empty($allievo->email)) $emails[] = $allievo->email;
                break;
        }

        return array_unique($emails);
    }
}
```

**NOTA sulla SoftDeleteTrait + alias**: `countNonLette()` e `getUltimeNonLette()` usano `->alias('n')`. Dato che `Notifica::find()` aggiunge `WHERE notifica.deleted_at IS NULL` via SoftDeleteTrait, e l'alias cambia il nome tabella a `n`, bisogna usare `findWithDeleted()->alias('n')->andWhere(['n.deleted_at' => null])` al posto di `find()->alias('n')`. Lo stesso pattern documentato in CLAUDE.md.

Quindi nel codice finale, sostituire tutte le occorrenze di `static::find()->alias('n')` con:
```php
static::findWithDeleted()->alias('n')->andWhere(['n.deleted_at' => null])
```

- [ ] **Step 2: Commit**

```bash
git add models/Notifica.php
git commit -m "feat: add Notifica model with query logic and email recipients"
```

---

### Task 5: Model NotificaLettura

**Files:**
- Create: `models/NotificaLettura.php`

- [ ] **Step 1: Creare il model**

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Model per la tabella "notifica_lettura".
 * NON estende BaseActiveRecord (no soft delete, no audit).
 *
 * @property int $id
 * @property int $notifica_id
 * @property string $user_type
 * @property string $user_id
 * @property string $letta_at
 */
class NotificaLettura extends ActiveRecord
{
    public static function tableName()
    {
        return 'notifica_lettura';
    }

    public function rules()
    {
        return [
            [['notifica_id', 'user_type', 'user_id', 'letta_at'], 'required'],
            ['notifica_id', 'integer'],
            ['user_type', 'in', 'range' => ['admin', 'docente', 'studente']],
            ['user_id', 'string', 'max' => 50],
        ];
    }

    public function getNotifica()
    {
        return $this->hasOne(Notifica::class, ['id' => 'notifica_id'])
            ->andWhere(['notifica.deleted_at' => null]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add models/NotificaLettura.php
git commit -m "feat: add NotificaLettura model"
```

---

### Task 6: NotificaSearch model

**Files:**
- Create: `models/NotificaSearch.php`

- [ ] **Step 1: Creare search model**

```php
<?php

namespace app\models;

use yii\data\ActiveDataProvider;

class NotificaSearch extends Notifica
{
    public $ricerca;

    public function rules()
    {
        return [
            [['ricerca', 'destinatario_tipo'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = Notifica::find()->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['destinatario_tipo' => $this->destinatario_tipo]);

        if (!empty($this->ricerca)) {
            $query->andWhere(['like', 'titolo', $this->ricerca]);
        }

        return $dataProvider;
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add models/NotificaSearch.php
git commit -m "feat: add NotificaSearch model"
```

---

## Chunk 2: Controller Admin + Views + Email

### Task 7: NotificaController (admin CRUD)

**Files:**
- Create: `controllers/NotificaController.php`

- [ ] **Step 1: Creare controller**

Controller con:
- `behaviors()`: AccessControl solo admin (matchCallback `isAdmin()`), VerbFilter per delete=POST
- `actionIndex()`: griglia con NotificaSearch, colonne: titolo, destinatari (badge), data, email inviata (icona), azioni
- `actionCreate()`: form con Summernote. Al POST: salva notifica + se `invia_email`: chiama `$this->inviaEmail($model)` con logica chunked
- `actionView($id)`: dettaglio notifica + lista letture (chi ha letto, con data)
- `actionDelete($id)`: soft delete
- `inviaEmail(Notifica $model)`: metodo privato con `set_time_limit(0)`, chunking per 10, try/catch per singola email, flash con conteggio

Leggere `controllers/UtenteController.php` come riferimento per la struttura AccessControl admin-only.

Per l'invio email, il controller chiama `$model->getEmailDestinatari()` e poi cicla con:
```php
private function inviaEmail(Notifica $model): void
{
    $emails = $model->getEmailDestinatari();
    if (empty($emails)) {
        return;
    }

    set_time_limit(0);
    $sent = 0;
    $failed = 0;

    foreach ($emails as $email) {
        try {
            Yii::$app->mailer->compose(['html' => 'notifica-html', 'text' => 'notifica-text'], [
                'notifica' => $model,
            ])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($email)
                ->setSubject($model->titolo)
                ->send();
            $sent++;
        } catch (\Throwable $e) {
            Yii::error("Email notifica #{$model->id} a {$email}: " . $e->getMessage());
            $failed++;
        }
    }

    $model->email_inviata = 1;
    $model->save(false, ['email_inviata']);
    Yii::$app->session->setFlash('info', "Email inviate: {$sent} riuscite" . ($failed > 0 ? ", {$failed} fallite" : ''));
}
```

- [ ] **Step 2: Commit**

```bash
git add controllers/NotificaController.php
git commit -m "feat: add NotificaController with admin CRUD and email sending"
```

---

### Task 8: Views admin — index, create, _form, view

**Files:**
- Create: `views/notifica/index.php`
- Create: `views/notifica/create.php`
- Create: `views/notifica/_form.php`
- Create: `views/notifica/view.php`

- [ ] **Step 1: Creare index.php**

Pagina con:
- Page header "Notifiche" con pulsante "Nuova Notifica"
- AppGridView con colonne: titolo, destinatari (badge colorati), data creazione, email (icona check/x), azioni (view, delete)
- Leggere `views/utente/index.php` per riferimento struttura

- [ ] **Step 2: Creare _form.php**

Form con AppActiveForm in card Tabler:
- Titolo: text input
- Contenuto: textarea con id `contenuto-editor` (Summernote si attacca qui)
- Link: text input opzionale con placeholder
- Destinatario tipo: dropdown `Notifica::getDestinatarioTipoOptions()`
- 3 campi select nascosti per destinatario singolo (admin/docente/studente), mostrati/nascosti via JS in base alla selezione tipo
  - Admin: `Html::dropDownList('Notifica[destinatario_id]', ..., lista utenti admin)`
  - Docente: `Html::dropDownList(...)` con lista docenti `cognome nome (CF)`
  - Studente: idem con allievi
- Checkbox "Invia anche email"
- JS per mostra/nascondi campi singolo + inizializzazione Summernote

- [ ] **Step 3: Creare create.php**

Wrapper con page header + breadcrumb + _form.php. Include CSS/JS Summernote Lite da CDN:
```html
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-it-IT.min.js"></script>
```

JS Summernote:
```javascript
$('#contenuto-editor').summernote({
    height: 250,
    toolbar: [
        ['style', ['bold', 'italic', 'underline']],
        ['para', ['ul', 'ol']],
        ['insert', ['link']],
        ['view', ['codeview']],
    ],
    lang: 'it-IT',
});
```

- [ ] **Step 4: Creare view.php**

Dettaglio notifica:
- Card info: titolo, destinatari badge, data, mittente, link, stato email
- Card contenuto: HTML renderizzato
- Card "Chi ha letto" (se destinatario non singolo): tabella con user_type, user_id, data lettura. Se nessuno: "Nessuna lettura registrata."

- [ ] **Step 5: Commit**

```bash
git add views/notifica/
git commit -m "feat: add notifica admin views (index, create, view) with Summernote editor"
```

---

### Task 9: Template email

**Files:**
- Create: `mail/notifica-html.php`
- Create: `mail/notifica-text.php`

- [ ] **Step 1: Creare template HTML**

`mail/notifica-html.php`:
```php
<?php
/** @var app\models\Notifica $notifica */
?>
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background: #1a56db; color: white; padding: 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 18px;">Albatros Manager</h1>
    </div>
    <div style="padding: 20px; background: #f8f9fa;">
        <h2 style="margin-top: 0;"><?= htmlspecialchars($notifica->titolo) ?></h2>
        <div style="background: white; padding: 15px; border-radius: 4px;">
            <?= $notifica->contenuto ?>
        </div>
        <?php if (!empty($notifica->link)): ?>
            <div style="margin-top: 15px; text-align: center;">
                <a href="<?= htmlspecialchars($notifica->link) ?>" style="background: #1a56db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
                    Vai al portale
                </a>
            </div>
        <?php endif; ?>
    </div>
    <div style="padding: 10px; text-align: center; color: #6c757d; font-size: 12px;">
        Albatros Manager - Notifica automatica
    </div>
</div>
```

- [ ] **Step 2: Creare template testo**

`mail/notifica-text.php`:
```php
<?php
/** @var app\models\Notifica $notifica */
?>
<?= $notifica->titolo ?>
========================================

<?= strip_tags($notifica->contenuto) ?>

<?php if (!empty($notifica->link)): ?>
Link: <?= $notifica->link ?>
<?php endif; ?>

--
Albatros Manager - Notifica automatica
```

- [ ] **Step 3: Commit**

```bash
git add mail/notifica-html.php mail/notifica-text.php
git commit -m "feat: add email templates for notifications"
```

---

### Task 10: Menu + Route

**Files:**
- Modify: `views/layouts/menu.php`
- Modify: `config/web.php`

- [ ] **Step 1: Aggiungere voce nel menu**

In `views/layouts/menu.php`, nel dropdown "Gestione", dopo "Criteri Valutazione Docente", aggiungere:

```php
<div class="dropdown-divider"></div>
<a class="dropdown-item <?= $currentController == 'notifica' ? 'active' : '' ?>" href="<?= Url::to(['/notifica/index']) ?>">
    <i class="ti ti-bell-plus me-2"></i> Notifiche
</a>
```

Aggiungere `'notifica'` alla lista `$isActive()` del dropdown Gestione.

- [ ] **Step 2: Aggiungere route**

In `config/web.php`, nella sezione urlManager rules:
```php
'area-docenti/notifiche' => 'docente-area/notifiche',
'area-docenti/notifica/<id:\d+>' => 'docente-area/notifica-view',
'area-studenti/notifiche' => 'studente-area/notifiche',
'area-studenti/notifica/<id:\d+>' => 'studente-area/notifica-view',
```

- [ ] **Step 3: Commit**

```bash
git add views/layouts/menu.php config/web.php
git commit -m "feat: add Notifiche menu item and routes"
```

---

## Chunk 3: Trait + Integrazione Header + Pagine Utente

### Task 11: NotificheActionsTrait

**Files:**
- Create: `components/traits/NotificheActionsTrait.php`

- [ ] **Step 1: Creare il trait**

```php
<?php

namespace app\components\traits;

use Yii;
use app\models\Notifica;
use yii\data\ActiveDataProvider;

/**
 * Trait condiviso per le actions notifiche nei controller.
 *
 * Il controller che usa questo trait deve implementare:
 * - getNotificheUserType(): string — 'admin', 'docente', 'studente'
 * - getNotificheUserId(): string — ID (admin) o CF (docente/studente)
 */
trait NotificheActionsTrait
{
    public function actionNotifiche()
    {
        $userType = $this->getNotificheUserType();
        $userId = $this->getNotificheUserId();
        $tab = Yii::$app->request->get('tab', 'non-lette');

        $query = Notifica::queryPerUtente($userType, $userId);

        if ($tab === 'non-lette') {
            $query->leftJoin('notifica_lettura nl', 'nl.notifica_id = notifica.id AND nl.user_type = :ut AND nl.user_id = :uid', [
                ':ut' => $userType,
                ':uid' => $userId,
            ])->andWhere(['nl.id' => null]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $nonLetteCount = Notifica::countNonLette($userType, $userId);

        return $this->render('@app/views/site/notifiche', [
            'dataProvider' => $dataProvider,
            'tab' => $tab,
            'nonLetteCount' => $nonLetteCount,
            'userType' => $userType,
            'userId' => $userId,
        ]);
    }

    public function actionNotificaView($id)
    {
        $userType = $this->getNotificheUserType();
        $userId = $this->getNotificheUserId();

        $notifica = Notifica::queryPerUtente($userType, $userId)
            ->andWhere(['notifica.id' => $id])
            ->one();

        if ($notifica === null) {
            throw new \yii\web\NotFoundHttpException('Notifica non trovata.');
        }

        // Segna come letta
        $notifica->segnaComeLetta($userType, $userId);

        return $this->render('@app/views/site/notifica-view', [
            'notifica' => $notifica,
            'userType' => $userType,
            'userId' => $userId,
        ]);
    }

    public function actionSegnaLetta($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $userType = $this->getNotificheUserType();
        $userId = $this->getNotificheUserId();

        $notifica = Notifica::findOne($id);
        if ($notifica) {
            $notifica->segnaComeLetta($userType, $userId);
        }

        return ['success' => true];
    }

    public function actionSegnaTutteLette()
    {
        $userType = $this->getNotificheUserType();
        $userId = $this->getNotificheUserId();

        $notifiche = Notifica::queryPerUtente($userType, $userId)->all();
        foreach ($notifiche as $notifica) {
            $notifica->segnaComeLetta($userType, $userId);
        }

        Yii::$app->session->setFlash('success', 'Tutte le notifiche segnate come lette.');
        return $this->redirect(['notifiche']);
    }
}
```

**NOTA**: `actionNotifiche()` usa `Notifica::queryPerUtente()` che chiama `Notifica::find()` senza alias — funziona correttamente con SoftDeleteTrait. Il LEFT JOIN nella tab "non lette" usa il nome tabella `notifica` (non aliasato) che è coerente.

- [ ] **Step 2: Commit**

```bash
git add components/traits/NotificheActionsTrait.php
git commit -m "feat: add NotificheActionsTrait with shared notification actions"
```

---

### Task 12: Views notifiche utente

**Files:**
- Create: `views/site/notifiche.php`
- Create: `views/site/notifica-view.php`

- [ ] **Step 1: Creare notifiche.php**

Pagina lista notifiche:
- Page header "Notifiche" con `ti ti-bell`
- Pulsante "Segna tutte come lette" (POST form, visibile se nonLetteCount > 0)
- 2 tab: "Non lette ($nonLetteCount)" / "Tutte" (link con parametro `?tab=non-lette` / `?tab=tutte`)
- Lista card per ogni notifica dal dataProvider:
  - Card con bordo sinistro blu se non letta (`border-start border-primary border-3`)
  - Titolo come link a `notifica-view`
  - Data relativa: `Yii::$app->formatter->asRelativeTime($notifica->created_at)`
  - Preview: `mb_substr(strip_tags($notifica->contenuto), 0, 100) . '...'`
  - Badge destinatario tipo (se admin)
- Paginazione con `yii\widgets\LinkPager`

- [ ] **Step 2: Creare notifica-view.php**

Dettaglio notifica:
- Breadcrumb: Notifiche → Titolo notifica
- Card con: titolo, data formattata, mittente (da `getCreatoDaLabel()`)
- Contenuto HTML renderizzato (già sanitizzato)
- Se link presente: pulsante "Vai alla pagina" con `Html::a()`
- Link "Torna alle notifiche"

- [ ] **Step 3: Commit**

```bash
git add views/site/notifiche.php views/site/notifica-view.php
git commit -m "feat: add notification list and detail views for all users"
```

---

### Task 13: Integrare trait nei 3 controller

**Files:**
- Modify: `controllers/SiteController.php`
- Modify: `controllers/DocenteAreaController.php`
- Modify: `controllers/StudenteAreaController.php`

- [ ] **Step 1: Integrare in SiteController**

Leggere il file prima. Poi:
1. Aggiungere `use app\components\traits\NotificheActionsTrait;` e dentro la classe `use NotificheActionsTrait;`
2. Implementare i metodi helper:
```php
protected function getNotificheUserType(): string { return 'admin'; }
protected function getNotificheUserId(): string { return (string) Yii::$app->user->identity->id; }
```
3. Aggiungere `'notifiche', 'notifica-view', 'segna-letta', 'segna-tutte-lette'` alle actions autenticate nell'AccessControl

- [ ] **Step 2: Integrare in DocenteAreaController**

Stessa logica:
```php
protected function getNotificheUserType(): string { return 'docente'; }
protected function getNotificheUserId(): string { return Yii::$app->docenteUser->identity->docente_codice_fiscale; }
```
Aggiungere le actions all'AccessControl.

- [ ] **Step 3: Integrare in StudenteAreaController**

```php
protected function getNotificheUserType(): string { return 'studente'; }
protected function getNotificheUserId(): string { return Yii::$app->studenteUser->identity->allievo_codice_fiscale; }
```
Aggiungere le actions all'AccessControl.

- [ ] **Step 4: Commit**

```bash
git add controllers/SiteController.php controllers/DocenteAreaController.php controllers/StudenteAreaController.php
git commit -m "feat: integrate NotificheActionsTrait in all 3 area controllers"
```

---

### Task 14: Campanella header — sostituzione placeholder

**Files:**
- Modify: `views/layouts/header.php`
- Modify: `views/layouts/docente.php`
- Modify: `views/layouts/studente.php`

- [ ] **Step 1: Sostituire placeholder in header.php (admin)**

Leggere il file. Trovare il blocco `<div class="nav-item dropdown d-none d-md-flex me-3">` con la campanella e il dropdown statico "Benvenuto in Albatros Manager". Sostituire con:

```php
<?php if (!Yii::$app->user->isGuest):
    $notificheCount = \app\models\Notifica::countNonLette('admin', (string) Yii::$app->user->identity->id);
    $ultimeNotifiche = \app\models\Notifica::getUltimeNonLette('admin', (string) Yii::$app->user->identity->id, 5);
?>
<div class="nav-item dropdown d-none d-md-flex me-3">
    <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Notifiche">
        <i class="ti ti-bell"></i>
        <?php if ($notificheCount > 0): ?>
            <span class="badge bg-red text-red-fg badge-notification badge-blink"><?= $notificheCount > 99 ? '99+' : $notificheCount ?></span>
        <?php endif; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card" style="width: 380px;">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title">Notifiche</h3>
                <?php if ($notificheCount > 0): ?>
                    <span class="badge bg-blue-lt ms-auto"><?= $notificheCount ?> nuove</span>
                <?php endif; ?>
            </div>
            <div class="list-group list-group-flush list-group-hoverable" style="max-height: 300px; overflow-y: auto;">
                <?php if (empty($ultimeNotifiche)): ?>
                    <div class="list-group-item text-center text-secondary py-3">
                        Nessuna nuova notifica
                    </div>
                <?php else: ?>
                    <?php foreach ($ultimeNotifiche as $n): ?>
                        <a href="<?= \yii\helpers\Url::to(['/site/notifica-view', 'id' => $n->id]) ?>" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="status-dot status-dot-animated bg-blue d-block"></span>
                                </div>
                                <div class="col text-truncate">
                                    <div class="text-body d-block fw-bold"><?= \yii\helpers\Html::encode(mb_substr($n->titolo, 0, 50)) ?></div>
                                    <div class="d-block text-secondary text-truncate mt-n1">
                                        <?= Yii::$app->formatter->asRelativeTime($n->created_at) ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="<?= \yii\helpers\Url::to(['/site/notifiche']) ?>" class="text-decoration-none">
                    Vedi tutte le notifiche
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
```

- [ ] **Step 2: Sostituire in docente.php**

Stessa struttura ma con:
- `$notificheCount = Notifica::countNonLette('docente', $docenteUser->identity->docente_codice_fiscale);`
- URLs: `/docente-area/notifica-view`, `/docente-area/notifiche`

- [ ] **Step 3: Sostituire in studente.php**

Stessa struttura con:
- `$notificheCount = Notifica::countNonLette('studente', $studenteUser->identity->allievo_codice_fiscale);`
- URLs: `/studente-area/notifica-view`, `/studente-area/notifiche`

- [ ] **Step 4: Commit**

```bash
git add views/layouts/header.php views/layouts/docente.php views/layouts/studente.php
git commit -m "feat: replace placeholder bell with real notification dropdown in all 3 layouts"
```

---

## Chunk 4: Verifiche + CHANGELOG + Manuali

### Task 15: Verifiche

- [ ] **Step 1: Syntax check tutti i file PHP nuovi/modificati**

```bash
docker exec -w /app albatros-php php -l models/Notifica.php
docker exec -w /app albatros-php php -l models/NotificaLettura.php
docker exec -w /app albatros-php php -l models/NotificaSearch.php
docker exec -w /app albatros-php php -l controllers/NotificaController.php
docker exec -w /app albatros-php php -l components/traits/NotificheActionsTrait.php
docker exec -w /app albatros-php php -l controllers/SiteController.php
docker exec -w /app albatros-php php -l controllers/DocenteAreaController.php
docker exec -w /app albatros-php php -l controllers/StudenteAreaController.php
```

- [ ] **Step 2: Test funzionale model**

```bash
docker exec -w /app albatros-php php -r "
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
new \yii\console\Application(['id'=>'t','basePath'=>__DIR__,'components'=>['db'=>require 'config/db.php','security'=>['class'=>'yii\base\Security']],'params'=>require 'config/params.php']);

// Test query
echo 'Count admin: ' . \app\models\Notifica::countNonLette('admin', '1') . PHP_EOL;
echo 'Count docente: ' . \app\models\Notifica::countNonLette('docente', 'TEST') . PHP_EOL;
echo 'OK' . PHP_EOL;
"
```

- [ ] **Step 3: Verificare pagina admin accessibile**

Aprire `http://localhost:8000/notifica/index` (loggato come admin).

---

### Task 16: Aggiornare CHANGELOG e CLAUDE.md

**Files:**
- Modify: `CHANGELOG.md`
- Modify: `CLAUDE.md`

- [ ] **Step 1: Aggiungere voci al CHANGELOG**

Sotto `## [Unreleased]`:

```markdown
### Novità {tutti}
- Sistema notifiche in-app con campanella e badge nell'header
- Pagina dedicata notifiche con stato letto/non letto

### Novità {admin}
- Invio notifiche con editor HTML a singoli utenti, categorie o tutti
- Invio email opzionale con le notifiche
- Pannello gestione notifiche con dettaglio letture
```

- [ ] **Step 2: Aggiornare CLAUDE.md**

Aggiungere sezione "Sistema Notifiche" sotto Feature Specifiche:

```markdown
### Sistema Notifiche
Admin invia notifiche HTML (Summernote) a tutti/categoria/singolo utente. Controller: `NotificaController` (admin), `NotificheActionsTrait` (tutti).
Tabelle: `notifica` (BaseActiveRecord) + `notifica_lettura` (plain AR). Identificativo utenti: admin=id, docente/studente=CF.
Campanella header con dropdown preview ultime 5 + badge. Pagina `/site/notifiche` (admin), `/area-docenti/notifiche`, `/area-studenti/notifiche`.
Email opzionale con `Yii::$app->mailer`, template `mail/notifica-html.php`. HTML sanitizzato con HtmlPurifier.
```

- [ ] **Step 3: Commit**

```bash
git add CHANGELOG.md CLAUDE.md
git commit -m "docs: update CHANGELOG and CLAUDE.md with notification system documentation"
```
