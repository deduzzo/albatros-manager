# Gestione Utenti + 2FA + Changelog — Piano di Implementazione

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Aggiungere gestione utenti admin, 2FA TOTP per tutte le utenze, e changelog in-app filtrato per tipologia utente.

**Architecture:** Il sistema estende i 3 auth esistenti (User, DocenteAuth, AllievoAuth) con trait condivisi per TOTP e changelog. Due behavior Yii2 (ForcePasswordChange, TotpPending) intercettano le request per forzare cambio password e verifica 2FA. Il changelog vive in `CHANGELOG.md` e viene parsato da un helper PHP.

**Tech Stack:** Yii2, PHP 8.4, MySQL 8.0, robthree/twofactorauth, Tabler UI

**Spec:** `docs/superpowers/specs/2026-03-14-gestione-utenti-2fa-design.md`

---

## Chunk 1: Database + Dipendenze + Refactoring User Model

### Task 1: Installare dipendenza TOTP

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Installare robthree/twofactorauth**

```bash
docker exec -w /app albatros-php composer require robthree/twofactorauth
```

- [ ] **Step 2: Verificare installazione**

```bash
docker exec -w /app albatros-php php -r "require 'vendor/autoload.php'; \$tfa = new \RobThree\Auth\TwoFactorAuth(); echo 'OK';"
```

Expected: `OK`

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "deps: add robthree/twofactorauth for TOTP 2FA support"
```

---

### Task 2: Migration — campi gestione utenti + 2FA + changelog su `utente`

**Files:**
- Create: `migrations/m260314_100000_add_user_management_fields.php`

- [ ] **Step 1: Creare migration**

```php
<?php

use yii\db\Migration;

class m260314_100000_add_user_management_fields extends Migration
{
    public function safeUp()
    {
        // Utente (admin)
        $this->addColumn('utente', 'force_password_change', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('password_reset_token'));
        $this->addColumn('utente', 'totp_secret', $this->string(255)->null()->after('force_password_change'));
        $this->addColumn('utente', 'totp_enabled', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('totp_secret'));
        $this->addColumn('utente', 'totp_enabled_at', $this->dateTime()->null()->after('totp_enabled'));
        $this->addColumn('utente', 'last_changelog_seen', $this->string(20)->null()->after('totp_enabled_at'));
    }

    public function safeDown()
    {
        $this->dropColumn('utente', 'last_changelog_seen');
        $this->dropColumn('utente', 'totp_enabled_at');
        $this->dropColumn('utente', 'totp_enabled');
        $this->dropColumn('utente', 'totp_secret');
        $this->dropColumn('utente', 'force_password_change');
    }
}
```

- [ ] **Step 2: Eseguire migration**

```bash
docker exec -w /app albatros-php php yii migrate --interactive=0
```

Expected: `1 migration was applied.`

- [ ] **Step 3: Commit**

```bash
git add migrations/m260314_100000_add_user_management_fields.php
git commit -m "db: add force_password_change, TOTP and changelog fields to utente table"
```

---

### Task 3: Migration — campi 2FA + gestione su `docente_auth` e `allievo_auth`

**Files:**
- Create: `migrations/m260314_100001_add_auth_management_fields.php`

- [ ] **Step 1: Creare migration**

```php
<?php

use yii\db\Migration;

class m260314_100001_add_auth_management_fields extends Migration
{
    public function safeUp()
    {
        $tables = ['docente_auth', 'allievo_auth'];

        foreach ($tables as $table) {
            $this->addColumn($table, 'force_password_change', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('status'));
            $this->addColumn($table, 'totp_secret', $this->string(255)->null()->after('force_password_change'));
            $this->addColumn($table, 'totp_enabled', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('totp_secret'));
            $this->addColumn($table, 'totp_enabled_at', $this->dateTime()->null()->after('totp_enabled'));
            $this->addColumn($table, 'last_changelog_seen', $this->string(20)->null()->after('totp_enabled_at'));
        }
    }

    public function safeDown()
    {
        $tables = ['docente_auth', 'allievo_auth'];
        $columns = ['last_changelog_seen', 'totp_enabled_at', 'totp_enabled', 'totp_secret', 'force_password_change'];

        foreach ($tables as $table) {
            foreach ($columns as $column) {
                $this->dropColumn($table, $column);
            }
        }
    }
}
```

- [ ] **Step 2: Eseguire migration**

```bash
docker exec -w /app albatros-php php yii migrate --interactive=0
```

Expected: `1 migration was applied.`

- [ ] **Step 3: Commit**

```bash
git add migrations/m260314_100001_add_auth_management_fields.php
git commit -m "db: add force_password_change, TOTP and changelog fields to docente_auth and allievo_auth"
```

---

### Task 4: Migration — tabella rate-limiting 2FA

**Files:**
- Create: `migrations/m260314_100002_create_totp_attempt_table.php`

- [ ] **Step 1: Creare migration**

```php
<?php

use yii\db\Migration;

class m260314_100002_create_totp_attempt_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('totp_attempt', [
            'id' => $this->primaryKey(),
            'user_type' => "ENUM('admin','docente','studente') NOT NULL",
            'user_id' => $this->string(50)->notNull(),
            'attempts' => $this->integer()->notNull()->defaultValue(0),
            'locked_until' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('uk_totp_attempt_user', 'totp_attempt', ['user_type', 'user_id'], true);
    }

    public function safeDown()
    {
        $this->dropTable('totp_attempt');
    }
}
```

- [ ] **Step 2: Eseguire migration**

```bash
docker exec -w /app albatros-php php yii migrate --interactive=0
```

Expected: `1 migration was applied.`

- [ ] **Step 3: Commit**

```bash
git add migrations/m260314_100002_create_totp_attempt_table.php
git commit -m "db: create totp_attempt table for 2FA rate limiting"
```

---

### Task 5: Aggiungere chiave TOTP ai params

**Files:**
- Modify: `config/params.php`

- [ ] **Step 1: Aggiungere parametro**

Aggiungere a `config/params.php`:

```php
'totpEncryptionKey' => getenv('TOTP_ENCRYPTION_KEY') ?: 'albatros-dev-totp-key-change-in-production',
'totpIssuer' => 'Albatros Manager',
```

- [ ] **Step 2: Commit**

```bash
git add config/params.php
git commit -m "config: add TOTP encryption key and issuer params"
```

---

### Task 6: Refactoring User → BaseActiveRecord

**Files:**
- Modify: `models/User.php`

- [ ] **Step 1: Refactor User model**

Modificare `models/User.php`:

1. Cambiare `extends ActiveRecord` → `extends \app\components\base\BaseActiveRecord`
2. Rimuovere `use yii\db\ActiveRecord;`
3. Rimuovere il `TimestampBehavior` dal metodo `behaviors()` (gestito da BaseActiveRecord)
4. Mantenere `implements IdentityInterface`
5. Aggiungere i nuovi campi alle `rules()`:
   ```php
   ['force_password_change', 'integer'],
   ['totp_enabled', 'integer'],
   [['totp_secret', 'totp_enabled_at', 'last_changelog_seen'], 'safe'],
   ```
6. Aggiungere i nuovi campi a `attributeLabels()`
7. Aggiungere a `auditMaskedAttributes()`:
   ```php
   protected function auditMaskedAttributes()
   {
       return ['password_hash', 'auth_key', 'password_reset_token', 'totp_secret'];
   }
   ```
8. Override `findIdentity()` per usare `deleted_at IS NULL`:
   ```php
   public static function findIdentity($id)
   {
       return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
   }
   ```
   (Il `find()` di SoftDeleteTrait esclude già i deleted_at, quindi `findOne` funziona correttamente)

9. Aggiungere metodi:
   ```php
   public function resetPassword(string $newPassword, bool $forceChange = true): void
   {
       $this->setPassword($newPassword);
       $this->force_password_change = $forceChange ? 1 : 0;
       $this->removePasswordResetToken();
   }

   public function getStatusLabel(): string
   {
       return match ($this->status) {
           self::STATUS_ACTIVE => 'Attivo',
           self::STATUS_INACTIVE => 'Disabilitato',
           self::STATUS_DELETED => 'Eliminato',
           default => 'Sconosciuto',
       };
   }

   public function getStatusBadgeClass(): string
   {
       return match ($this->status) {
           self::STATUS_ACTIVE => 'badge bg-green text-green-fg',
           self::STATUS_INACTIVE => 'badge bg-orange text-orange-fg',
           self::STATUS_DELETED => 'badge bg-red text-red-fg',
           default => 'badge bg-default',
       };
   }

   public function canBeDeletedBy(User $admin): bool
   {
       return $this->id !== $admin->id;
   }

   public function canChangeRoleBy(User $admin): bool
   {
       return $this->id !== $admin->id;
   }
   ```

- [ ] **Step 2: Verificare che il login admin funzioni**

```bash
docker exec -w /app albatros-php php -r "
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
\$config = require 'config/web.php';
new \yii\web\Application(\$config);
\$user = \app\models\User::findByUsername('admin');
echo \$user ? 'User found: ' . \$user->nominativo : 'NOT FOUND';
echo PHP_EOL;
"
```

Expected: Trova l'utente admin (o uno degli utenti reali)

- [ ] **Step 3: Commit**

```bash
git add models/User.php
git commit -m "refactor: User model extends BaseActiveRecord with soft delete and audit support"
```

---

## Chunk 2: Trait TOTP + Behavior ForcePasswordChange + Behavior TotpPending

### Task 7: Creare TotpTrait

**Files:**
- Create: `components/traits/TotpTrait.php`

- [ ] **Step 1: Creare il trait**

```php
<?php

namespace app\components\traits;

use Yii;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

/**
 * Trait per gestione TOTP (Google Authenticator).
 * Usato da User, DocenteAuth, AllievoAuth.
 *
 * Richiede che il model abbia i campi:
 * - totp_secret (VARCHAR 255, nullable)
 * - totp_enabled (TINYINT, default 0)
 * - totp_enabled_at (DATETIME, nullable)
 */
trait TotpTrait
{
    private ?TwoFactorAuth $_tfa = null;

    protected function getTfa(): TwoFactorAuth
    {
        if ($this->_tfa === null) {
            $issuer = Yii::$app->params['totpIssuer'] ?? 'Albatros Manager';
            $this->_tfa = new TwoFactorAuth(issuer: $issuer, qrcodeprovider: new QRServerProvider());
        }
        return $this->_tfa;
    }

    /**
     * Genera un nuovo TOTP secret e lo salva criptato nel model.
     * NON salva il record (chiamare save() separatamente).
     */
    public function generateTotpSecret(): string
    {
        $tfa = $this->getTfa();
        $secret = $tfa->createSecret();
        $this->totp_secret = $this->encryptTotpSecret($secret);
        return $secret;
    }

    /**
     * Restituisce il secret TOTP decriptato.
     */
    public function getDecryptedTotpSecret(): ?string
    {
        if (empty($this->totp_secret)) {
            return null;
        }
        return $this->decryptTotpSecret($this->totp_secret);
    }

    /**
     * Genera URL per QR code dato un secret in chiaro.
     */
    public function getTotpQrCodeUrl(string $plainSecret): string
    {
        $tfa = $this->getTfa();
        $label = $this->getTotpLabel();
        return $tfa->getQRCodeImageAsDataUri($label, $plainSecret);
    }

    /**
     * Abilita TOTP verificando il codice fornito.
     * Salva il record.
     */
    public function enableTotp(string $code): bool
    {
        $secret = $this->getDecryptedTotpSecret();
        if ($secret === null) {
            return false;
        }

        $tfa = $this->getTfa();
        if (!$tfa->verifyCode($secret, $code, 1)) {
            return false;
        }

        $this->totp_enabled = 1;
        $this->totp_enabled_at = date('Y-m-d H:i:s');
        return $this->save(false, ['totp_enabled', 'totp_enabled_at']);
    }

    /**
     * Disabilita TOTP e rimuove il secret.
     */
    public function disableTotp(): bool
    {
        $this->totp_secret = null;
        $this->totp_enabled = 0;
        $this->totp_enabled_at = null;
        return $this->save(false, ['totp_secret', 'totp_enabled', 'totp_enabled_at']);
    }

    /**
     * Verifica un codice TOTP.
     * discrepancy=1 → tolleranza ±30 secondi per clock drift.
     */
    public function verifyTotp(string $code): bool
    {
        $secret = $this->getDecryptedTotpSecret();
        if ($secret === null) {
            return false;
        }

        $tfa = $this->getTfa();
        return $tfa->verifyCode($secret, $code, 1);
    }

    public function isTotpEnabled(): bool
    {
        return (int)$this->totp_enabled === 1;
    }

    /**
     * Label per il QR code (es: "email" o "codice_fiscale").
     */
    protected function getTotpLabel(): string
    {
        if (isset($this->email) && !empty($this->email)) {
            return $this->email;
        }
        if (isset($this->docente_codice_fiscale)) {
            return $this->docente_codice_fiscale;
        }
        if (isset($this->allievo_codice_fiscale)) {
            return $this->allievo_codice_fiscale;
        }
        return (string)$this->getPrimaryKey();
    }

    private function encryptTotpSecret(string $secret): string
    {
        $key = Yii::$app->params['totpEncryptionKey'] ?? 'default-key';
        return base64_encode(Yii::$app->security->encryptByKey($secret, $key));
    }

    private function decryptTotpSecret(string $encrypted): ?string
    {
        $key = Yii::$app->params['totpEncryptionKey'] ?? 'default-key';
        $decrypted = Yii::$app->security->decryptByKey(base64_decode($encrypted), $key);
        return $decrypted === false ? null : $decrypted;
    }
}
```

- [ ] **Step 2: Aggiungere `use TotpTrait` a User, DocenteAuth, AllievoAuth**

In `models/User.php` aggiungere:
```php
use app\components\traits\TotpTrait;

class User extends BaseActiveRecord implements IdentityInterface
{
    use TotpTrait;
    // ...
}
```

In `models/DocenteAuth.php` aggiungere:
```php
use app\components\traits\TotpTrait;
// dentro la classe:
use TotpTrait;
```

Aggiungere anche i nuovi campi alle `rules()` di DocenteAuth e AllievoAuth:
```php
['force_password_change', 'integer'],
['totp_enabled', 'integer'],
[['totp_secret', 'totp_enabled_at', 'last_changelog_seen'], 'safe'],
```

E a `auditMaskedAttributes()`:
```php
protected function auditMaskedAttributes()
{
    return ['password_hash', 'auth_key', 'email_verify_token', 'password_reset_token', 'totp_secret'];
}
```

In `models/AllievoAuth.php` stessa cosa.

Aggiungere anche `resetPassword()` a DocenteAuth e AllievoAuth se non presente:
```php
public function resetPassword(string $newPassword, bool $forceChange = true): void
{
    $this->setPassword($newPassword);
    $this->force_password_change = $forceChange ? 1 : 0;
    $this->removePasswordResetToken();
}
```

- [ ] **Step 3: Verificare che il trait funzioni**

```bash
docker exec -w /app albatros-php php -r "
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
\$config = require 'config/web.php';
new \yii\web\Application(\$config);
\$user = \app\models\User::find()->one();
if (\$user) {
    \$secret = \$user->generateTotpSecret();
    echo 'Secret generated: ' . strlen(\$secret) . ' chars' . PHP_EOL;
    echo 'Encrypted stored: ' . strlen(\$user->totp_secret) . ' chars' . PHP_EOL;
    \$decrypted = \$user->getDecryptedTotpSecret();
    echo 'Decrypted matches: ' . (\$decrypted === \$secret ? 'YES' : 'NO') . PHP_EOL;
    echo 'Enabled: ' . (\$user->isTotpEnabled() ? 'YES' : 'NO') . PHP_EOL;
}
"
```

Expected: Secret generated, encrypted, decrypted matches YES, enabled NO

- [ ] **Step 4: Commit**

```bash
git add components/traits/TotpTrait.php models/User.php models/DocenteAuth.php models/AllievoAuth.php
git commit -m "feat: add TotpTrait for TOTP 2FA support on all auth models"
```

---

### Task 8: Creare ForcePasswordChangeBehavior

**Files:**
- Create: `components/behaviors/ForcePasswordChangeBehavior.php`

- [ ] **Step 1: Creare il behavior**

```php
<?php

namespace app\components\behaviors;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;

/**
 * Behavior che forza il cambio password al prossimo login.
 * Intercetta beforeAction e redirige a cambio password se il flag è attivo.
 */
class ForcePasswordChangeBehavior extends Behavior
{
    /**
     * @var string Nome dell'action per il cambio password
     */
    public string $changePasswordAction = 'cambia-password';

    /**
     * @var array Actions sempre permesse anche con flag attivo
     */
    public array $allowedActions = ['logout', 'cambia-password'];

    /**
     * @var string Nome del componente user Yii (user, docenteUser, studenteUser)
     */
    public string $userComponent = 'user';

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction(ActionEvent $event): void
    {
        $user = Yii::$app->get($this->userComponent);

        if ($user->isGuest) {
            return;
        }

        $identity = $user->identity;
        if ($identity === null) {
            return;
        }

        if (!isset($identity->force_password_change) || (int)$identity->force_password_change !== 1) {
            return;
        }

        $currentAction = $event->action->id;
        if (in_array($currentAction, $this->allowedActions, true)) {
            return;
        }

        $controller = $event->action->controller;
        $event->isValid = false;
        Yii::$app->session->setFlash('warning', 'Devi cambiare la password prima di continuare.');
        $controller->redirect([$this->changePasswordAction]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add components/behaviors/ForcePasswordChangeBehavior.php
git commit -m "feat: add ForcePasswordChangeBehavior to enforce password change on login"
```

---

### Task 9: Creare TotpPendingBehavior

**Files:**
- Create: `components/behaviors/TotpPendingBehavior.php`

- [ ] **Step 1: Creare il behavior**

```php
<?php

namespace app\components\behaviors;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;

/**
 * Behavior che impedisce la navigazione quando la verifica 2FA è pendente.
 * Se in sessione esiste totp_pending_user_id + totp_pending_type,
 * redirige a verifica-2fa per tutte le action non consentite.
 */
class TotpPendingBehavior extends Behavior
{
    /**
     * @var string Action per la verifica 2FA
     */
    public string $verifyAction = 'verifica-2fa';

    /**
     * @var array Actions sempre permesse in stato 2FA pendente
     */
    public array $allowedActions = ['login', 'logout', 'verifica-2fa'];

    /**
     * @var string Tipo utente per il filtro sessione (admin, docente, studente)
     */
    public string $userType = 'admin';

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction(ActionEvent $event): void
    {
        $session = Yii::$app->session;
        $pendingType = $session->get('totp_pending_type');
        $pendingUserId = $session->get('totp_pending_user_id');

        if ($pendingType !== $this->userType || $pendingUserId === null) {
            return;
        }

        $currentAction = $event->action->id;
        if (in_array($currentAction, $this->allowedActions, true)) {
            return;
        }

        $controller = $event->action->controller;
        $event->isValid = false;
        $controller->redirect([$this->verifyAction]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add components/behaviors/TotpPendingBehavior.php
git commit -m "feat: add TotpPendingBehavior to block navigation during pending 2FA verification"
```

---

### Task 10: Creare TotpAttempt model

**Files:**
- Create: `models/TotpAttempt.php`

- [ ] **Step 1: Creare il model**

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Model per la tabella "totp_attempt" (rate-limiting 2FA).
 * NON estende BaseActiveRecord (no soft delete, no audit).
 *
 * @property int $id
 * @property string $user_type
 * @property string $user_id
 * @property int $attempts
 * @property string|null $locked_until
 * @property string $updated_at
 */
class TotpAttempt extends ActiveRecord
{
    const MAX_ATTEMPTS = 5;
    const LOCK_MINUTES = 5;

    public static function tableName()
    {
        return 'totp_attempt';
    }

    /**
     * Verifica se l'utente è bloccato.
     */
    public static function isLocked(string $userType, string $userId): bool
    {
        $record = static::findOne(['user_type' => $userType, 'user_id' => $userId]);
        if ($record === null) {
            return false;
        }
        if ($record->locked_until === null) {
            return false;
        }
        return strtotime($record->locked_until) > time();
    }

    /**
     * Registra un tentativo fallito. Blocca dopo MAX_ATTEMPTS.
     */
    public static function registerFailedAttempt(string $userType, string $userId): int
    {
        $record = static::findOne(['user_type' => $userType, 'user_id' => $userId]);

        if ($record === null) {
            $record = new static();
            $record->user_type = $userType;
            $record->user_id = $userId;
            $record->attempts = 0;
        }

        // Reset se il blocco è scaduto
        if ($record->locked_until !== null && strtotime($record->locked_until) <= time()) {
            $record->attempts = 0;
            $record->locked_until = null;
        }

        $record->attempts++;
        $record->updated_at = date('Y-m-d H:i:s');

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            $record->locked_until = date('Y-m-d H:i:s', time() + self::LOCK_MINUTES * 60);
        }

        $record->save(false);

        return self::MAX_ATTEMPTS - $record->attempts;
    }

    /**
     * Resetta i tentativi dopo un login riuscito.
     */
    public static function resetAttempts(string $userType, string $userId): void
    {
        static::deleteAll(['user_type' => $userType, 'user_id' => $userId]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add models/TotpAttempt.php
git commit -m "feat: add TotpAttempt model for 2FA rate limiting"
```

---

## Chunk 3: CRUD Gestione Utenti Admin

### Task 11: Creare UserSearch model

**Files:**
- Create: `models/UserSearch.php`

- [ ] **Step 1: Creare search model**

```php
<?php

namespace app\models;

use yii\data\ActiveDataProvider;

class UserSearch extends User
{
    public $ricerca;

    public function rules()
    {
        return [
            [['ricerca', 'ruolo'], 'safe'],
            [['status', 'totp_enabled'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'ricerca' => 'Cerca',
        ]);
    }

    public function search($params)
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['nominativo' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['status' => $this->status]);
        $query->andFilterWhere(['ruolo' => $this->ruolo]);
        $query->andFilterWhere(['totp_enabled' => $this->totp_enabled]);

        if (!empty($this->ricerca)) {
            $query->andWhere(['or',
                ['like', 'username', $this->ricerca],
                ['like', 'nominativo', $this->ricerca],
                ['like', 'email', $this->ricerca],
            ]);
        }

        return $dataProvider;
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add models/UserSearch.php
git commit -m "feat: add UserSearch model for admin user grid filtering"
```

---

### Task 11b: Creare DocenteAuthSearch e AllievoAuthSearch

**Files:**
- Create: `models/DocenteAuthSearch.php`
- Create: `models/AllievoAuthSearch.php`

- [ ] **Step 1: Creare DocenteAuthSearch**

```php
<?php

namespace app\models;

use yii\data\ActiveDataProvider;

class DocenteAuthSearch extends DocenteAuth
{
    public $ricerca;
    public $cognome;
    public $nome;

    public function rules()
    {
        return [
            [['ricerca', 'status'], 'safe'],
            [['totp_enabled'], 'integer'],
        ];
    }

    public function search($params)
    {
        $query = DocenteAuth::find()
            ->alias('da')
            ->leftJoin('docente d', 'da.docente_codice_fiscale = d.codice_fiscale AND d.deleted_at IS NULL');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['da.status' => $this->status]);
        $query->andFilterWhere(['da.totp_enabled' => $this->totp_enabled]);

        if (!empty($this->ricerca)) {
            $query->andWhere(['or',
                ['like', 'da.docente_codice_fiscale', $this->ricerca],
                ['like', 'd.cognome', $this->ricerca],
                ['like', 'd.nome', $this->ricerca],
                ['like', 'd.email', $this->ricerca],
            ]);
        }

        return $dataProvider;
    }
}
```

- [ ] **Step 2: Creare AllievoAuthSearch** (stessa struttura con `allievo`)

- [ ] **Step 3: Commit**

```bash
git add models/DocenteAuthSearch.php models/AllievoAuthSearch.php
git commit -m "feat: add DocenteAuthSearch and AllievoAuthSearch for filtered grids"
```

---

### Task 12: Creare UtenteController

**Files:**
- Create: `controllers/UtenteController.php`

- [ ] **Step 1: Creare controller con azioni CRUD**

```php
<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserSearch;
use app\models\DocenteAuth;
use app\models\AllievoAuth;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class UtenteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
                        },
                    ],
                ],
                'denyCallback' => function () {
                    throw new ForbiddenHttpException('Accesso riservato agli amministratori.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'toggle-status' => ['POST'],
                    'reset-2fa' => ['POST'],
                    'toggle-status-auth' => ['POST'],
                    'force-change-auth' => ['POST'],
                    'reset-2fa-auth' => ['POST'],
                ],
            ],
        ];
    }

    // --- CRUD Utenti Admin ---

    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $docentiSearchModel = new \app\models\DocenteAuthSearch();
        $docentiDataProvider = $docentiSearchModel->search(Yii::$app->request->queryParams);

        $studentiSearchModel = new \app\models\AllievoAuthSearch();
        $studentiDataProvider = $studentiSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'docentiSearchModel' => $docentiSearchModel,
            'docentiDataProvider' => $docentiDataProvider,
            'studentiSearchModel' => $studentiSearchModel,
            'studentiDataProvider' => $studentiDataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionCreate()
    {
        $model = new User();
        $model->status = User::STATUS_ACTIVE;
        $model->generateAuthKey();

        if ($model->load(Yii::$app->request->post())) {
            $password = Yii::$app->request->post('password');
            if (!empty($password)) {
                $model->setPassword($password);
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Utente creato con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Impedisce auto-declassamento
            if (!$model->canChangeRoleBy(Yii::$app->user->identity)) {
                $model->ruolo = User::RUOLO_ADMIN;
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Utente aggiornato con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!$model->canBeDeletedBy(Yii::$app->user->identity)) {
            Yii::$app->session->setFlash('danger', 'Non puoi eliminare il tuo stesso account.');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Utente eliminato con successo.');
        return $this->redirect(['index']);
    }

    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);

        if (!$model->canBeDeletedBy(Yii::$app->user->identity)) {
            Yii::$app->session->setFlash('danger', 'Non puoi disabilitare il tuo stesso account.');
            return $this->redirect(['index']);
        }

        $model->status = $model->status === User::STATUS_ACTIVE
            ? User::STATUS_INACTIVE
            : User::STATUS_ACTIVE;
        $model->save(false, ['status']);

        $stato = $model->status === User::STATUS_ACTIVE ? 'attivato' : 'disabilitato';
        Yii::$app->session->setFlash('success', "Utente {$stato} con successo.");
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionResetPassword($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $password = Yii::$app->request->post('new_password');
            $forceChange = (bool)Yii::$app->request->post('force_change', 1);

            if (!empty($password)) {
                $model->resetPassword($password, $forceChange);
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Password reimpostata con successo.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } else {
                Yii::$app->session->setFlash('danger', 'Inserisci una nuova password.');
            }
        }

        return $this->render('reset-password', ['model' => $model]);
    }

    public function actionReset2fa($id)
    {
        $model = $this->findModel($id);
        $model->disableTotp();
        Yii::$app->session->setFlash('success', 'Autenticazione a due fattori disabilitata per ' . ($model->nominativo ?: $model->username) . '.');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    // --- Gestione Auth Docenti/Studenti ---

    public function actionViewAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);
        return $this->render('view-auth', [
            'model' => $model,
            'type' => $type,
        ]);
    }

    public function actionResetPasswordAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);

        if (Yii::$app->request->isPost) {
            $password = Yii::$app->request->post('new_password');
            $forceChange = (bool)Yii::$app->request->post('force_change', 1);

            if (!empty($password)) {
                $model->resetPassword($password, $forceChange);
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Password reimpostata con successo.');
                    return $this->redirect(['view-auth', 'type' => $type, 'id' => $id]);
                }
            } else {
                Yii::$app->session->setFlash('danger', 'Inserisci una nuova password.');
            }
        }

        return $this->render('reset-password-auth', [
            'model' => $model,
            'type' => $type,
        ]);
    }

    public function actionToggleStatusAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);

        $activeStatus = ($type === 'docente') ? DocenteAuth::STATUS_ACTIVE : AllievoAuth::STATUS_ACTIVE;
        $suspendedStatus = ($type === 'docente') ? DocenteAuth::STATUS_SUSPENDED : AllievoAuth::STATUS_SUSPENDED;

        $model->status = $model->status === $activeStatus ? $suspendedStatus : $activeStatus;
        $model->save(false, ['status']);

        $stato = $model->status === $activeStatus ? 'attivato' : 'sospeso';
        Yii::$app->session->setFlash('success', "Account {$stato} con successo.");
        return $this->redirect(['view-auth', 'type' => $type, 'id' => $id]);
    }

    public function actionForceChangeAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);
        $model->force_password_change = 1;
        $model->save(false, ['force_password_change']);

        Yii::$app->session->setFlash('success', 'Cambio password obbligatorio impostato.');
        return $this->redirect(['view-auth', 'type' => $type, 'id' => $id]);
    }

    public function actionReset2faAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);
        $model->disableTotp();
        Yii::$app->session->setFlash('success', 'Autenticazione a due fattori disabilitata.');
        return $this->redirect(['view-auth', 'type' => $type, 'id' => $id]);
    }

    // --- Helpers ---

    protected function findModel($id): User
    {
        $model = User::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Utente non trovato.');
        }
        return $model;
    }

    protected function findAuthModel(string $type, $id)
    {
        $model = match ($type) {
            'docente' => DocenteAuth::findOne($id),
            'studente' => AllievoAuth::findOne($id),
            default => throw new NotFoundHttpException('Tipo utente non valido.'),
        };

        if ($model === null) {
            throw new NotFoundHttpException('Account non trovato.');
        }
        return $model;
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add controllers/UtenteController.php
git commit -m "feat: add UtenteController for admin user management CRUD"
```

---

### Task 13: Creare views gestione utenti — index con 3 tab

**Files:**
- Create: `views/utente/index.php`

- [ ] **Step 1: Creare vista index con tab Admin/Docenti/Studenti**

La view mostra 3 tab con griglie separate:
- Tab "Utenti Admin": griglia `UserSearch` con colonne nominativo, username, email, ruolo (badge), stato (badge), 2FA (icona), azioni
- Tab "Docenti": griglia DocenteAuth con colonne nominativo (via join), CF, email (da docente), stato (badge), 2FA (icona), azioni
- Tab "Studenti": griglia AllievoAuth con colonne nominativo (via join), CF, email (da allievo), stato (badge), 2FA (icona), azioni

Usare `AppGridView` e classi Tabler (tab nav `nav-tabs card-header-tabs`).
Pulsante "Nuovo Utente" in alto a destra (solo per tab admin).
Badge 2FA: `<i class="ti ti-shield-check text-green"></i>` se attivo, `<i class="ti ti-shield-off text-muted"></i>` se no.

Usare i pattern esistenti dal progetto (es. `views/docente/index.php` per riferimento di struttura).

- [ ] **Step 2: Commit**

```bash
git add views/utente/index.php
git commit -m "feat: add user management index view with 3 tabs (admin, docenti, studenti)"
```

---

### Task 14: Creare views gestione utenti — create, update, _form, view

**Files:**
- Create: `views/utente/create.php`
- Create: `views/utente/update.php`
- Create: `views/utente/_form.php`
- Create: `views/utente/view.php`

- [ ] **Step 1: Creare _form.php**

Form condiviso con campi: username, nominativo, email, ruolo (dropdown con `User::getRuoloOptions()`), password (solo in create obbligatorio, in update opzionale con placeholder "Lascia vuoto per non modificare").

Usare `AppActiveForm` e layout card Tabler.

- [ ] **Step 2: Creare create.php e update.php**

Wrapper semplici che includono `_form.php` con titolo appropriato.

- [ ] **Step 3: Creare view.php**

Dettaglio utente con:
- Card profilo (avatar iniziali, nominativo, ruolo badge, email, date)
- Card sicurezza: stato 2FA (badge verde/grigio), force_password_change (badge), ultimo accesso
- Pulsanti azione: Modifica, Reset Password, Toggle Stato, Reset 2FA (se attivo), Elimina (con conferma)

Se l'utente è se stesso, nascondere pulsanti pericolosi (elimina, toggle stato, cambio ruolo).

- [ ] **Step 4: Commit**

```bash
git add views/utente/create.php views/utente/update.php views/utente/_form.php views/utente/view.php
git commit -m "feat: add user management create, update, view pages"
```

---

### Task 15: Creare views — reset-password e view-auth

**Files:**
- Create: `views/utente/reset-password.php`
- Create: `views/utente/reset-password-auth.php`
- Create: `views/utente/view-auth.php`

- [ ] **Step 1: Creare reset-password.php**

Form semplice con:
- Campo "Nuova Password" (type=password)
- Checkbox "Forza cambio password al prossimo login" (checked di default)
- Pulsante "Reimposta Password"

Card Tabler con header che mostra nominativo utente.

- [ ] **Step 2: Creare reset-password-auth.php**

Stessa struttura di reset-password.php ma per docenti/studenti. Mostra il CF e nominativo del docente/studente (via relazione).

- [ ] **Step 3: Creare view-auth.php**

Dettaglio credenziali docente/studente:
- Card profilo con nominativo (da relazione docente/allievo), CF, email, stato (badge), 2FA (badge)
- Pulsanti: Reset Password, Toggle Stato, Forza Cambio Password, Reset 2FA
- Link a scheda completa docente/allievo (`/docente/view?codice_fiscale=XX` o `/allievo/view?codice_fiscale=XX`)

- [ ] **Step 4: Commit**

```bash
git add views/utente/reset-password.php views/utente/reset-password-auth.php views/utente/view-auth.php
git commit -m "feat: add reset password and auth detail views for user management"
```

---

### Task 16: Aggiungere voce menu + route

**Files:**
- Modify: `views/layouts/menu.php`
- Modify: `config/web.php`

- [ ] **Step 1: Aggiungere voce nel menu**

In `views/layouts/menu.php`, dentro il dropdown "Configurazione", dopo la riga di "Criteri Valutazione", aggiungere:

```php
<?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()): ?>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item <?= $currentController == 'utente' ? 'active' : '' ?>" href="<?= Url::to(['/utente/index']) ?>">
        <i class="ti ti-users-cog me-2"></i> Gestione Utenti
    </a>
<?php endif; ?>
```

Aggiornare anche il `$isActive` del dropdown per includere `'utente'`:
```php
$isActive(['fonte-finanziamento', 'conto-corrente', 'sede', 'tipo-documento', 'configurazione', 'criterio-valutazione', 'utente'])
```

- [ ] **Step 2: Aggiungere route in web.php**

In `config/web.php`, nella sezione `urlManager.rules`, aggiungere:

```php
'gestione-utenti' => 'utente/index',
```

- [ ] **Step 3: Verificare che la pagina sia accessibile**

Aprire `http://localhost:8000/utente/index` da browser (loggato come admin).

- [ ] **Step 4: Commit**

```bash
git add views/layouts/menu.php config/web.php
git commit -m "feat: add Gestione Utenti menu item and route (admin only)"
```

---

## Chunk 4: 2FA — Attivazione Self-Service + Login Flow

### Task 17: Creare TwoFactorActionsTrait

**Files:**
- Create: `components/traits/TwoFactorActionsTrait.php`

- [ ] **Step 1: Creare il trait**

```php
<?php

namespace app\components\traits;

use Yii;
use app\models\TotpAttempt;

/**
 * Trait condiviso per le actions 2FA nei controller.
 * Usato da SiteController, DocenteAreaController, StudenteAreaController.
 *
 * Il controller che usa questo trait deve implementare:
 * - getTwoFactorIdentity(): restituisce il model auth corrente
 * - getTwoFactorUserComponent(): restituisce il nome del componente user (es. 'user', 'docenteUser')
 * - getTwoFactorUserType(): restituisce il tipo utente ('admin', 'docente', 'studente')
 * - getTwoFactorRedirectUrl(): URL redirect dopo login 2FA riuscito
 */
trait TwoFactorActionsTrait
{
    public function actionAttiva2fa()
    {
        $identity = $this->getTwoFactorIdentity();
        if ($identity === null) {
            return $this->goHome();
        }

        $secret = null;
        $qrCodeUrl = null;
        $error = null;

        if (Yii::$app->request->isPost) {
            $code = Yii::$app->request->post('totp_code');

            if ($identity->enableTotp($code)) {
                Yii::$app->session->setFlash('success', 'Autenticazione a due fattori attivata con successo.');
                return $this->redirect(['profilo']);
            } else {
                $error = 'Codice non valido. Riprova.';
                // Ripristina il secret per mostrare di nuovo il QR
                $secret = $identity->getDecryptedTotpSecret();
                if ($secret) {
                    $qrCodeUrl = $identity->getTotpQrCodeUrl($secret);
                }
            }
        } else {
            // Genera nuovo secret
            $secret = $identity->generateTotpSecret();
            $identity->save(false, ['totp_secret']);
            $qrCodeUrl = $identity->getTotpQrCodeUrl($secret);
        }

        return $this->render('@app/views/site/attiva-2fa', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
            'error' => $error,
        ]);
    }

    public function actionDisattiva2fa()
    {
        $identity = $this->getTwoFactorIdentity();
        if ($identity === null) {
            return $this->goHome();
        }

        if (Yii::$app->request->isPost) {
            $code = Yii::$app->request->post('totp_code');

            if ($identity->verifyTotp($code)) {
                $identity->disableTotp();
                Yii::$app->session->setFlash('success', 'Autenticazione a due fattori disattivata.');
                return $this->redirect(['profilo']);
            } else {
                Yii::$app->session->setFlash('danger', 'Codice non valido.');
            }
        }

        return $this->render('@app/views/site/disattiva-2fa');
    }

    public function actionVerifica2fa()
    {
        // Usa layout login (pagina pre-autenticazione)
        $this->layout = 'login';

        $session = Yii::$app->session;
        $pendingUserId = $session->get('totp_pending_user_id');
        $pendingType = $session->get('totp_pending_type');
        $userType = $this->getTwoFactorUserType();

        if ($pendingUserId === null || $pendingType !== $userType) {
            return $this->redirect(['login']);
        }

        $error = null;

        // Verifica blocco
        if (TotpAttempt::isLocked($userType, (string)$pendingUserId)) {
            $error = 'Troppi tentativi falliti. Riprova tra 5 minuti.';
            return $this->render('@app/views/site/verifica-2fa', [
                'error' => $error,
                'locked' => true,
            ]);
        }

        if (Yii::$app->request->isPost) {
            $code = Yii::$app->request->post('totp_code');

            // Trova l'identity
            $userComponent = $this->getTwoFactorUserComponent();
            $identityClass = Yii::$app->get($userComponent)->identityClass;
            $identity = $identityClass::findIdentity($pendingUserId);

            if ($identity !== null && $identity->verifyTotp($code)) {
                // Login riuscito
                TotpAttempt::resetAttempts($userType, (string)$pendingUserId);
                $session->remove('totp_pending_user_id');
                $session->remove('totp_pending_type');

                Yii::$app->get($userComponent)->login($identity);
                return $this->redirect($this->getTwoFactorRedirectUrl());
            } else {
                $remaining = TotpAttempt::registerFailedAttempt($userType, (string)$pendingUserId);
                if ($remaining <= 0) {
                    $error = 'Troppi tentativi falliti. Account bloccato per 5 minuti.';
                } else {
                    $error = "Codice non valido. {$remaining} tentativi rimasti.";
                }
            }
        }

        return $this->render('@app/views/site/verifica-2fa', [
            'error' => $error,
            'locked' => false,
        ]);
    }

    public function actionCambiaPassword()
    {
        $identity = $this->getTwoFactorIdentity();
        if ($identity === null) {
            return $this->goHome();
        }

        if (Yii::$app->request->isPost) {
            $currentPassword = Yii::$app->request->post('current_password');
            $newPassword = Yii::$app->request->post('new_password');
            $confirmPassword = Yii::$app->request->post('confirm_password');

            if (empty($newPassword) || $newPassword !== $confirmPassword) {
                Yii::$app->session->setFlash('danger', 'Le password non coincidono.');
            } elseif (strlen($newPassword) < 6) {
                Yii::$app->session->setFlash('danger', 'La password deve essere di almeno 6 caratteri.');
            } else {
                // Per force_password_change, non richiediamo la password corrente
                if ((int)$identity->force_password_change === 1 || $identity->validatePassword($currentPassword)) {
                    $identity->setPassword($newPassword);
                    $identity->force_password_change = 0;
                    if ($identity->save(false)) {
                        Yii::$app->session->setFlash('success', 'Password cambiata con successo.');
                        return $this->redirect(['profilo']);
                    }
                } else {
                    Yii::$app->session->setFlash('danger', 'Password corrente non valida.');
                }
            }
        }

        $isForced = (int)$identity->force_password_change === 1;
        if ($isForced) {
            $this->layout = 'login'; // Pre-autenticazione effettiva
        }
        return $this->render('@app/views/site/cambia-password', [
            'isForced' => $isForced,
        ]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add components/traits/TwoFactorActionsTrait.php
git commit -m "feat: add TwoFactorActionsTrait with shared 2FA actions for all auth areas"
```

---

### Task 18: Creare views 2FA (condivise)

**Files:**
- Create: `views/site/attiva-2fa.php`
- Create: `views/site/disattiva-2fa.php`
- Create: `views/site/verifica-2fa.php`
- Create: `views/site/cambia-password.php`

- [ ] **Step 1: Creare attiva-2fa.php**

Card Tabler con:
1. Titolo "Attiva Autenticazione a Due Fattori"
2. Istruzioni: "Scansiona il QR code con Google Authenticator"
3. Immagine QR code (data URI da `$qrCodeUrl`)
4. Codice manuale in campo readonly copiabile (`$secret`)
5. Campo input per codice 6 cifre di verifica
6. Pulsante "Verifica e Attiva"
7. Messaggio errore se `$error` non null

- [ ] **Step 2: Creare disattiva-2fa.php**

Card con:
1. Avviso "Stai per disattivare il 2FA"
2. Campo codice 6 cifre per conferma
3. Pulsante "Disattiva 2FA"

- [ ] **Step 3: Creare verifica-2fa.php**

Pagina standalone (layout login) con:
1. Icona shield grande
2. Titolo "Verifica in due passaggi"
3. Se `$locked`: messaggio di blocco, nessun form
4. Altrimenti: campo codice 6 cifre, pulsante "Verifica"
5. Messaggio errore se presente
6. Link "Annulla" che fa logout

- [ ] **Step 4: Creare cambia-password.php**

Card con:
1. Se `$isForced`: avviso "Il tuo amministratore richiede il cambio password"
2. Campo "Password corrente" (nascosto se `$isForced`)
3. Campo "Nuova password"
4. Campo "Conferma password"
5. Pulsante "Cambia Password"

- [ ] **Step 5: Commit**

```bash
git add views/site/attiva-2fa.php views/site/disattiva-2fa.php views/site/verifica-2fa.php views/site/cambia-password.php
git commit -m "feat: add shared 2FA views (activate, deactivate, verify, change password)"
```

---

### Task 19: Integrare 2FA nel SiteController (admin)

**Files:**
- Modify: `controllers/SiteController.php`

- [ ] **Step 1: Aggiungere trait e metodi helper**

In `SiteController.php`:

1. Aggiungere `use TwoFactorActionsTrait;`
2. Aggiungere `use ForcePasswordChangeBehavior` ai behaviors (dopo AccessControl):
   ```php
   'forcePasswordChange' => [
       'class' => \app\components\behaviors\ForcePasswordChangeBehavior::class,
       'changePasswordAction' => 'cambia-password',
       'allowedActions' => ['login', 'logout', 'cambia-password', 'verifica-2fa', 'error'],
       'userComponent' => 'user',
   ],
   ```
3. Aggiungere `TotpPendingBehavior` ai behaviors:
   ```php
   'totpPending' => [
       'class' => \app\components\behaviors\TotpPendingBehavior::class,
       'verifyAction' => 'verifica-2fa',
       'allowedActions' => ['login', 'logout', 'verifica-2fa', 'error'],
       'userType' => 'admin',
   ],
   ```
4. Implementare i metodi richiesti dal trait:
   ```php
   protected function getTwoFactorIdentity()
   {
       return Yii::$app->user->identity;
   }

   protected function getTwoFactorUserComponent(): string
   {
       return 'user';
   }

   protected function getTwoFactorUserType(): string
   {
       return 'admin';
   }

   protected function getTwoFactorRedirectUrl(): array
   {
       return ['/site/index'];
   }
   ```
5. Aggiungere le nuove actions alla lista `'allow'` nelle access rules:
   ```php
   'attiva-2fa', 'disattiva-2fa', 'verifica-2fa', 'cambia-password'
   ```

- [ ] **Step 2: Modificare i LoginForm per separare validazione da login**

**IMPORTANTE**: I `LoginForm` attuali (`LoginForm`, `DocenteLoginForm`, `AllievoLoginForm`) fanno validate+login atomicamente nel metodo `login()`. Per supportare il 2FA, bisogna separare la validazione delle credenziali dal login Yii effettivo.

In `models/LoginForm.php`, aggiungere un metodo `validateCredentials()`:
```php
/**
 * Valida le credenziali senza effettuare il login.
 * Usato per il flusso 2FA: prima si validano le credenziali, poi se serve il 2FA.
 */
public function validateCredentials(): bool
{
    if ($this->validate()) {
        return true;
    }
    return false;
}

/**
 * Restituisce l'utente trovato (accessibile dopo validate()).
 */
public function getUser()
{
    if ($this->_user === null) {
        $this->_user = User::findByUsername($this->username);
    }
    return $this->_user;
}
```

Fare lo stesso per `DocenteLoginForm` e `AllievoLoginForm`.

Poi in `SiteController::actionLogin()`, modificare il flusso admin:
```php
// Invece di: if ($loginModel->login()) { return redirect; }
// Fare:
if ($loginModel->validateCredentials()) {
    $user = $loginModel->getUser();
    if ($user->isTotpEnabled()) {
        // NON fare login, mettere in stato 2FA pendente
        Yii::$app->session->set('totp_pending_user_id', $user->id);
        Yii::$app->session->set('totp_pending_type', 'admin');
        return $this->redirect(['verifica-2fa']);
    }
    // 2FA non attivo: login normale
    $loginModel->login();
    return $this->redirect(['/site/index']);
}
```

Stessa logica per i form docente e studente.

- [ ] **Step 3: Aggiungere sezione Sicurezza al profilo**

In `views/site/profilo.php`, dopo il form modifica, aggiungere una card "Sicurezza" con:
- Stato 2FA (badge verde/grigio)
- Se attivo: data attivazione + pulsante "Disattiva 2FA"
- Se non attivo: pulsante "Attiva 2FA"
- Link "Cambia Password"

- [ ] **Step 4: Commit**

```bash
git add controllers/SiteController.php views/site/profilo.php
git commit -m "feat: integrate 2FA flow in admin login and profile"
```

---

### Task 20: Integrare 2FA in DocenteAreaController

**Files:**
- Modify: `controllers/DocenteAreaController.php`

- [ ] **Step 1: Aggiungere trait, behaviors e metodi helper**

Stessa logica del Task 19 ma con:
- `userComponent` = `'docenteUser'`
- `userType` = `'docente'`
- `getTwoFactorIdentity()` → `Yii::$app->docenteUser->identity`
- `getTwoFactorRedirectUrl()` → `['/docente-area/index']`
- `allowedActions` adattate al controller docente (includere `login`, `logout`, `registrazione`, `verifica-2fa`, `cambia-password`, `password-dimenticata`, `reset-password`, `verifica-email`)

- [ ] **Step 2: Modificare login docente per supportare 2FA**

Nella `actionLogin()`, dopo validazione credenziali, prima del login effettivo, controllare `isTotpEnabled()` e settare sessione pendente.

- [ ] **Step 3: Aggiungere sezione Sicurezza al profilo docente**

In `views/docente-area/profilo.php`, aggiungere card sicurezza con stato 2FA e pulsanti.

- [ ] **Step 4: Commit**

```bash
git add controllers/DocenteAreaController.php views/docente-area/profilo.php
git commit -m "feat: integrate 2FA flow in docente login and profile"
```

---

### Task 21: Integrare 2FA in StudenteAreaController

**Files:**
- Modify: `controllers/StudenteAreaController.php`

- [ ] **Step 1: Aggiungere trait, behaviors e metodi helper**

Stessa logica del Task 20 ma con:
- `userComponent` = `'studenteUser'`
- `userType` = `'studente'`
- `getTwoFactorIdentity()` → `Yii::$app->studenteUser->identity`
- `getTwoFactorRedirectUrl()` → `['/studente-area/index']`

- [ ] **Step 2: Modificare login studente per supportare 2FA**

Nella `actionLogin()`, dopo validazione credenziali, prima del login effettivo, controllare `isTotpEnabled()` e settare sessione pendente.

- [ ] **Step 3: Aggiungere sezione Sicurezza al profilo studente**

In `views/studente-area/profilo.php`, aggiungere card sicurezza con stato 2FA e pulsanti.

- [ ] **Step 4: Commit**

```bash
git add controllers/StudenteAreaController.php views/studente-area/profilo.php
git commit -m "feat: integrate 2FA flow in studente login and profile"
```

---

## Chunk 5: Changelog In-App

### Task 22: Creare CHANGELOG.md

**Files:**
- Create: `CHANGELOG.md`

- [ ] **Step 1: Creare file con prima versione**

```markdown
# Changelog

Tutte le modifiche al portale Albatros Manager.

## [1.5.0] - 2026-03-14

### Novità {tutti}
- Aggiunta autenticazione a due fattori (2FA) con Google Authenticator
- Possibilità di cambiare password dal profilo

### Novità {admin}
- Nuova sezione "Gestione Utenti" per amministrare tutte le utenze del portale
- Reset password, disabilita/riattiva e forza cambio password per admin, docenti e studenti
- Gestione 2FA centralizzata: l'admin può disabilitare il 2FA per qualsiasi utente
- Changelog in-app con notifiche novità

### Novità {docente}
- Sezione sicurezza nel profilo con attivazione 2FA
- Notifiche novità del portale nell'header

### Novità {studente}
- Sezione sicurezza nel profilo con attivazione 2FA
- Notifiche novità del portale nell'header

## [1.0.0] - 2026-02-22

### Novità {tutti}
- Lancio del portale Albatros Manager
- Gestione progetti, avvisi, moduli
- Area docenti con candidature e rendicontazione ore
- Area studenti con candidature e graduatorie
- Dashboard con statistiche
- Manuali utente integrati
```

- [ ] **Step 2: Commit**

```bash
git add CHANGELOG.md
git commit -m "docs: add CHANGELOG.md with structured format for in-app display"
```

---

### Task 23: Creare ChangelogParser

**Files:**
- Create: `components/ChangelogParser.php`

- [ ] **Step 1: Creare il parser**

```php
<?php

namespace app\components;

use Yii;

/**
 * Parser per CHANGELOG.md strutturato.
 * Legge il file, lo parsa in versioni con sezioni taggate per tipo utente.
 */
class ChangelogParser
{
    private string $filePath;
    private ?array $parsed = null;

    public function __construct(?string $filePath = null)
    {
        $this->filePath = $filePath ?? Yii::getAlias('@app/CHANGELOG.md');
    }

    /**
     * Parsa CHANGELOG.md e restituisce array strutturato.
     * @return array [
     *   ['version' => '1.5.0', 'date' => '2026-03-14', 'sections' => [
     *     ['title' => 'Novità', 'tag' => 'tutti', 'items' => ['...', '...']],
     *     ['title' => 'Novità', 'tag' => 'admin', 'items' => ['...']],
     *   ]],
     * ]
     */
    public function parse(): array
    {
        if ($this->parsed !== null) {
            return $this->parsed;
        }

        if (!file_exists($this->filePath)) {
            $this->parsed = [];
            return $this->parsed;
        }

        $content = file_get_contents($this->filePath);
        $lines = explode("\n", $content);

        $versions = [];
        $currentVersion = null;
        $currentSectionIndex = -1;

        foreach ($lines as $line) {
            // Versione: ## [1.5.0] - 2026-03-14
            if (preg_match('/^## \[(.+?)\] - (\d{4}-\d{2}-\d{2})/', $line, $m)) {
                if ($currentVersion !== null) {
                    $versions[] = $currentVersion;
                }
                $currentVersion = [
                    'version' => $m[1],
                    'date' => $m[2],
                    'sections' => [],
                ];
                $currentSectionIndex = -1;
                continue;
            }

            // Sezione: ### Novità {admin}
            if (preg_match('/^### (.+?) \{(.+?)\}/', $line, $m)) {
                if ($currentVersion !== null) {
                    $currentVersion['sections'][] = [
                        'title' => trim($m[1]),
                        'tag' => trim($m[2]),
                        'items' => [],
                    ];
                    $currentSectionIndex = count($currentVersion['sections']) - 1;
                }
                continue;
            }

            // Item: - testo
            if (preg_match('/^- (.+)/', $line, $m) && $currentVersion !== null && $currentSectionIndex >= 0) {
                $currentVersion['sections'][$currentSectionIndex]['items'][] = trim($m[1]);
            }
        }

        if ($currentVersion !== null) {
            $versions[] = $currentVersion;
        }

        $this->parsed = $versions;
        return $this->parsed;
    }

    /**
     * Filtra le versioni per tipo utente.
     * Un admin vede sezioni con tag 'tutti' e 'admin'.
     */
    public function getForUserType(string $type): array
    {
        $versions = $this->parse();
        $allowedTags = ['tutti', $type];
        $filtered = [];

        foreach ($versions as $version) {
            $filteredSections = [];
            foreach ($version['sections'] as $section) {
                if (in_array($section['tag'], $allowedTags, true)) {
                    $filteredSections[] = $section;
                }
            }
            if (!empty($filteredSections)) {
                $filtered[] = [
                    'version' => $version['version'],
                    'date' => $version['date'],
                    'sections' => $filteredSections,
                ];
            }
        }

        return $filtered;
    }

    /**
     * Restituisce la versione più recente.
     */
    public function getLatestVersion(): ?string
    {
        $versions = $this->parse();
        return !empty($versions) ? $versions[0]['version'] : null;
    }

    /**
     * Conta le voci non lette per un tipo utente.
     */
    public function countUnreadItems(string $type, ?string $lastSeen): int
    {
        $versions = $this->getForUserType($type);
        $count = 0;

        foreach ($versions as $version) {
            // Se lastSeen è null, tutte le versioni sono "non lette"
            // Se la versione è > lastSeen, è non letta
            if ($lastSeen !== null && version_compare($version['version'], $lastSeen, '<=')) {
                break;
            }
            foreach ($version['sections'] as $section) {
                $count += count($section['items']);
            }
        }

        return $count;
    }
}
```

- [ ] **Step 2: Verificare il parser**

```bash
docker exec -w /app albatros-php php -r "
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
\$config = require 'config/web.php';
new \yii\web\Application(\$config);
\$parser = new \app\components\ChangelogParser();
\$versions = \$parser->parse();
echo 'Versioni: ' . count(\$versions) . PHP_EOL;
\$admin = \$parser->getForUserType('admin');
echo 'Voci admin v1.5: ' . count(\$admin[0]['sections']) . ' sezioni' . PHP_EOL;
\$unread = \$parser->countUnreadItems('admin', null);
echo 'Non lette (admin): ' . \$unread . PHP_EOL;
\$unread2 = \$parser->countUnreadItems('admin', '1.5.0');
echo 'Non lette dopo 1.5.0: ' . \$unread2 . PHP_EOL;
"
```

Expected: 2 versioni, sezioni admin filtrate, conteggio non letti corretto

- [ ] **Step 3: Commit**

```bash
git add components/ChangelogParser.php
git commit -m "feat: add ChangelogParser for reading and filtering CHANGELOG.md"
```

---

### Task 24: Creare ChangelogActionsTrait

**Files:**
- Create: `components/traits/ChangelogActionsTrait.php`

- [ ] **Step 1: Creare il trait**

```php
<?php

namespace app\components\traits;

use Yii;
use app\components\ChangelogParser;

/**
 * Trait condiviso per le actions changelog nei controller.
 *
 * Il controller che usa questo trait deve implementare:
 * - getChangelogUserType(): string — 'admin', 'docente', 'studente'
 * - getChangelogIdentity(): model auth corrente (con campo last_changelog_seen)
 */
trait ChangelogActionsTrait
{
    public function actionChangelog()
    {
        $parser = new ChangelogParser();
        $type = $this->getChangelogUserType();
        $versions = $parser->getForUserType($type);

        // Segna come letto
        $this->markChangelogSeen($parser);

        return $this->render('@app/views/site/changelog', [
            'versions' => $versions,
        ]);
    }

    public function actionChangelogModal()
    {
        $parser = new ChangelogParser();
        $type = $this->getChangelogUserType();
        $identity = $this->getChangelogIdentity();
        $lastSeen = $identity ? $identity->last_changelog_seen : null;

        // Filtra solo versioni non lette
        $versions = $parser->getForUserType($type);
        $unreadVersions = [];
        foreach ($versions as $version) {
            if ($lastSeen !== null && version_compare($version['version'], $lastSeen, '<=')) {
                break;
            }
            $unreadVersions[] = $version;
        }

        // Segna come letto
        $this->markChangelogSeen($parser);

        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        return $this->renderPartial('@app/views/site/_changelog-modal-content', [
            'versions' => !empty($unreadVersions) ? $unreadVersions : $versions,
            'isUnread' => !empty($unreadVersions),
        ]);
    }

    /**
     * POST action per segnare il changelog come letto (usato via AJAX dal badge).
     */
    public function actionSegnaChangelogLetto()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $parser = new ChangelogParser();
        $this->markChangelogSeen($parser);
        return ['success' => true];
    }

    private function markChangelogSeen(ChangelogParser $parser): void
    {
        $identity = $this->getChangelogIdentity();
        if ($identity === null) {
            return;
        }
        $latestVersion = $parser->getLatestVersion();
        if ($latestVersion !== null && $identity->last_changelog_seen !== $latestVersion) {
            $identity->last_changelog_seen = $latestVersion;
            $identity->save(false, ['last_changelog_seen']);
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add components/traits/ChangelogActionsTrait.php
git commit -m "feat: add ChangelogActionsTrait with shared changelog actions"
```

---

### Task 25: Creare views changelog

**Files:**
- Create: `views/site/changelog.php`
- Create: `views/site/_changelog-modal-content.php`

- [ ] **Step 1: Creare changelog.php (pagina completa)**

Pagina con:
- Header "Novità del Portale" con icona `ti ti-sparkles`
- Timeline verticale con card per ogni versione
- Ogni card mostra: badge versione (es. `v1.5.0`), data formattata, sezioni con badge colorati:
  - "Novità" → `badge bg-green-lt`
  - "Correzioni" → `badge bg-azure-lt`
  - "Miglioramenti" → `badge bg-yellow-lt`
- Lista bullet per ogni sezione
- Layout Tabler standard con breadcrumb

- [ ] **Step 2: Creare _changelog-modal-content.php (contenuto modal AJAX)**

HTML parziale per il modal:
- Se `$isUnread`: titolo "Novità dalla tua ultima visita"
- Lista compatta delle voci con badge versione
- Link "Vedi storico completo" che punta a `/site/changelog` (o equivalente area)

- [ ] **Step 3: Commit**

```bash
git add views/site/changelog.php views/site/_changelog-modal-content.php
git commit -m "feat: add changelog page and modal content views"
```

---

### Task 26: Integrare changelog nei controller e layouts

**Files:**
- Modify: `controllers/SiteController.php`
- Modify: `controllers/DocenteAreaController.php`
- Modify: `controllers/StudenteAreaController.php`
- Modify: `views/layouts/header.php`
- Modify: `views/layouts/docente.php`
- Modify: `views/layouts/studente.php`

- [ ] **Step 1: Aggiungere ChangelogActionsTrait ai 3 controller**

In `SiteController`:
```php
use ChangelogActionsTrait;

protected function getChangelogUserType(): string { return 'admin'; }
protected function getChangelogIdentity() { return Yii::$app->user->identity; }
```

In `DocenteAreaController`:
```php
use ChangelogActionsTrait;

protected function getChangelogUserType(): string { return 'docente'; }
protected function getChangelogIdentity() { return Yii::$app->docenteUser->identity; }
```

In `StudenteAreaController`:
```php
use ChangelogActionsTrait;

protected function getChangelogUserType(): string { return 'studente'; }
protected function getChangelogIdentity() { return Yii::$app->studenteUser->identity; }
```

Aggiungere le nuove actions alla lista delle actions permesse nell'AccessControl.

- [ ] **Step 2: Aggiungere icona novità nell'header admin**

In `views/layouts/header.php`, accanto all'icona notifiche (bell), aggiungere:

```php
<?php if (!Yii::$app->user->isGuest): ?>
    <?php
    $changelogParser = new \app\components\ChangelogParser();
    $unreadCount = $changelogParser->countUnreadItems('admin', Yii::$app->user->identity->last_changelog_seen ?? null);
    ?>
    <div class="nav-item">
        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#changelog-modal" id="changelog-trigger">
            <span class="nav-link-icon">
                <i class="ti ti-sparkles"></i>
            </span>
            <?php if ($unreadCount > 0): ?>
                <span class="badge bg-red badge-notification badge-blink"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
    </div>
<?php endif; ?>
```

Aggiungere anche il modal container e il JS per caricamento AJAX del contenuto.

- [ ] **Step 3: Aggiungere icona novità nei layout docente e studente**

Stessa logica nel layout `docente.php` e `studente.php`, usando il rispettivo `docenteUser`/`studenteUser` e tipo `docente`/`studente`.

- [ ] **Step 4: Aggiungere route changelog in web.php**

```php
'area-docenti/changelog' => 'docente-area/changelog',
'area-studenti/changelog' => 'studente-area/changelog',
```

- [ ] **Step 5: Commit**

```bash
git add controllers/SiteController.php controllers/DocenteAreaController.php controllers/StudenteAreaController.php views/layouts/header.php views/layouts/docente.php views/layouts/studente.php config/web.php
git commit -m "feat: integrate changelog with notification badge in all 3 area layouts"
```

---

## Chunk 6: Test e Verifica Finale

### Task 27: Verifica end-to-end

- [ ] **Step 1: Verificare che tutte le migration siano applicate**

```bash
docker exec -w /app albatros-php php yii migrate/history
```

Expected: le 3 nuove migration presenti

- [ ] **Step 2: Verificare che la pagina gestione utenti sia accessibile**

Aprire `http://localhost:8000/utente/index` loggato come admin.
Verificare: 3 tab visibili, griglia utenti admin popolata, pulsante "Nuovo Utente" presente.

- [ ] **Step 3: Testare CRUD utente**

1. Creare un nuovo utente (operatore)
2. Visualizzare il dettaglio
3. Modificare il nominativo
4. Reset password con flag force change
5. Toggle stato (disabilita/riattiva)
6. Eliminare l'utente

- [ ] **Step 4: Testare 2FA su un utente admin**

1. Andare su Profilo → Sicurezza → Attiva 2FA
2. Verificare QR code visibile
3. Scansionare con Google Authenticator
4. Inserire codice → 2FA attivato
5. Logout → Login → Verificare redirect a verifica-2fa
6. Inserire codice → Login completato
7. Disattivare 2FA dal profilo

- [ ] **Step 5: Testare gestione auth docente/studente da admin**

1. Dalla tab Docenti, cliccare su un docente
2. Verificare dettaglio credenziali
3. Reset password
4. Toggle stato
5. Forza cambio password
6. Reset 2FA (se attivo)

- [ ] **Step 6: Testare changelog**

1. Verificare icona sparkles nell'header con badge
2. Cliccare → modal con novità
3. Ricaricare pagina → badge scomparso
4. Andare su `/site/changelog` → pagina completa con timeline

- [ ] **Step 7: Testare force password change**

1. Da admin, forzare cambio password per un utente
2. Loggare con quell'utente
3. Verificare redirect a cambio password
4. Cambiare password → navigazione normale

- [ ] **Step 8: Commit finale**

```bash
git add -A
git commit -m "feat: complete user management, 2FA TOTP and in-app changelog implementation"
```

---

### Task 28: Aggiornare manuali e CLAUDE.md

**Files:**
- Modify: `CLAUDE.md` (aggiornare sezione feature)
- Modify: `views/site/manuale-admin.php` (aggiungere sezione gestione utenti + 2FA)
- Modify: `views/site/manuale-docente.php` (aggiungere sezione 2FA)
- Modify: `views/site/manuale-studente.php` (aggiungere sezione 2FA)

- [ ] **Step 1: Aggiornare CLAUDE.md**

Aggiungere alla sezione pertinente:
- Gestione Utenti: `UtenteController`, views `views/utente/`, solo admin
- 2FA TOTP: `TotpTrait`, `TwoFactorActionsTrait`, `ForcePasswordChangeBehavior`, `TotpPendingBehavior`
- Changelog: `ChangelogParser`, `ChangelogActionsTrait`, file `CHANGELOG.md`

- [ ] **Step 2: Aggiornare manuale admin**

Aggiungere sezione "Gestione Utenti" con:
- Come creare/modificare/eliminare utenti
- Come resettare password
- Come gestire 2FA per tutti gli utenti
- Come forzare cambio password

- [ ] **Step 3: Aggiornare manuali docente e studente**

Aggiungere sezione "Sicurezza" con:
- Come attivare/disattivare 2FA
- Come usare il QR code con Google Authenticator

- [ ] **Step 4: Commit**

```bash
git add CLAUDE.md views/site/manuale-admin.php views/site/manuale-docente.php views/site/manuale-studente.php
git commit -m "docs: update manuals and CLAUDE.md with user management, 2FA and changelog documentation"
```
