# Gestione Utenti Admin + 2FA (TOTP) + Changelog

## Obiettivo

Aggiungere al portale Albatros Manager:
1. Gestione centralizzata delle utenze (admin, docenti, studenti) con CRUD completo, reset password, disabilita/riattiva, cambio password obbligatorio
2. Autenticazione a due fattori opzionale via Google Authenticator (TOTP)
3. Changelog in-app con notifiche novità, filtrato per tipologia utente

## Decisioni di Design

- **Nessun nuovo ruolo**: il ruolo `admin` esistente gestisce le utenze
- **2FA opzionale** per tutti e 3 i tipi di utente (admin, docenti, studenti)
- **Nessun codice di recupero**: reset 2FA tramite admin
- **Menu**: voce "Gestione Utenti" sotto "Configurazione"
- **Changelog**: file `CHANGELOG.md` nel repo, parsato e mostrato nel portale, filtrato per area utente

---

## 1. Database

### Migration 1: Campi gestione utenti + 2FA + changelog

Su `utente`:

```sql
ALTER TABLE utente ADD COLUMN force_password_change TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE utente ADD COLUMN totp_secret VARCHAR(255) NULL;
ALTER TABLE utente ADD COLUMN totp_enabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE utente ADD COLUMN totp_enabled_at DATETIME NULL;
ALTER TABLE utente ADD COLUMN last_changelog_seen VARCHAR(20) NULL;
```

### Migration 2: Stessi campi su `docente_auth` e `allievo_auth`

```sql
ALTER TABLE docente_auth ADD COLUMN force_password_change TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE docente_auth ADD COLUMN totp_secret VARCHAR(255) NULL;
ALTER TABLE docente_auth ADD COLUMN totp_enabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE docente_auth ADD COLUMN totp_enabled_at DATETIME NULL;
ALTER TABLE docente_auth ADD COLUMN last_changelog_seen VARCHAR(20) NULL;

ALTER TABLE allievo_auth ADD COLUMN force_password_change TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE allievo_auth ADD COLUMN totp_secret VARCHAR(255) NULL;
ALTER TABLE allievo_auth ADD COLUMN totp_enabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE allievo_auth ADD COLUMN totp_enabled_at DATETIME NULL;
ALTER TABLE allievo_auth ADD COLUMN last_changelog_seen VARCHAR(20) NULL;
```

### Migration 3: Tabella rate-limiting 2FA

```sql
CREATE TABLE totp_attempt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin','docente','studente') NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user (user_type, user_id)
);
```

---

## 2. Model `User` — Refactoring

### Estendere `BaseActiveRecord`

`User` attualmente estende `ActiveRecord` direttamente. Va refactored per estendere `BaseActiveRecord` (come tutti gli altri model), ottenendo:
- Soft delete (`deleted_at`)
- Audit automatico (`created_by`, `updated_by`, `deleted_by`)
- Compatibilità con i pattern del progetto

Il `TimestampBehavior` attuale viene rimosso (gestito da `BaseActiveRecord`).

### Nuove proprietà/metodi

- `use TotpTrait`
- `force_password_change` nelle rules
- `resetPassword(string $newPassword, bool $forceChange = true)` — setta hash + flag
- `getStatusBadgeClass(): string` — badge Tabler per stato
- `getStatusLabel(): string` — etichetta stato
- `canBeDeletedBy(User $admin): bool` — impedisce auto-eliminazione
- `canChangeRoleBy(User $admin): bool` — impedisce auto-declassamento

---

## 3. Trait `TotpTrait`

File: `components/traits/TotpTrait.php`

Usato da `User`, `DocenteAuth`, `AllievoAuth`.

### Metodi

- `generateTotpSecret(): string` — genera secret, lo salva criptato nel model
- `getTotpQrCodeUrl(): string` — URL per QR code (formato otpauth://)
- `enableTotp(string $code): bool` — verifica codice, se valido setta `totp_enabled=1`, `totp_enabled_at=NOW()`
- `disableTotp(): bool` — resetta secret, enabled, enabled_at
- `verifyTotp(string $code): bool` — verifica codice TOTP con discrepancy ±1 periodo (30s tolleranza per clock drift)
- `isTotpEnabled(): bool` — getter

### Crittografia Secret

Il TOTP secret viene criptato con `Yii::$app->security->encryptByKey($secret, $encryptionKey)`.
La chiave di crittografia è definita in `config/params.php`:

```php
'totpEncryptionKey' => getenv('TOTP_ENCRYPTION_KEY') ?: 'default-dev-key-change-in-production',
```

In produzione, impostare la variabile d'ambiente `TOTP_ENCRYPTION_KEY` con una chiave sicura.

### Libreria

`robthree/twofactorauth` (framework-agnostic). Usa il QR code provider integrato (`QRServerProvider` per semplicità, o `EndroidQrCodeProvider` se si preferisce generazione locale).

---

## 4. Model `DocenteAuth` / `AllievoAuth` — Modifiche

- `use TotpTrait`
- Aggiungere `force_password_change` nelle rules
- Metodo `resetPassword()`

---

## 5. Behavior `ForcePasswordChangeBehavior`

File: `components/behaviors/ForcePasswordChangeBehavior.php`

Behavior Yii2 da attaccare ai controller di tutte e 3 le aree. Si aggancia a `beforeAction` e:

1. Legge l'identity dell'utente corrente
2. Se `force_password_change == 1` e l'action corrente NON è `cambia-password` (né logout) → redirect a cambio password
3. Impedisce la navigazione su qualsiasi altra pagina finché il flag è attivo

Configurazione nei controller:

```php
public function behaviors()
{
    return [
        'forcePasswordChange' => [
            'class' => ForcePasswordChangeBehavior::class,
            'changePasswordAction' => 'cambia-password',
            'allowedActions' => ['logout', 'cambia-password'],
        ],
    ];
}
```

---

## 6. Behavior `TotpPendingBehavior`

File: `components/behaviors/TotpPendingBehavior.php`

Behavior da attaccare ai controller di tutte e 3 le aree. Si aggancia a `beforeAction` e:

1. Controlla se in sessione esiste `totp_pending_user_id` + `totp_pending_type`
2. Se esiste e l'action corrente NON è `verifica-2fa` (né login/logout) → redirect a verifica 2FA
3. Impedisce accesso a qualsiasi pagina protetta finché il 2FA non è verificato

Questo garantisce che un utente in stato "password OK, 2FA pendente" non possa navigare liberamente.

---

## 7. Controller `UtenteController`

File: `controllers/UtenteController.php`

### AccessControl

Solo `admin` (verifica `Yii::$app->user->identity->isAdmin()`).

### Azioni CRUD Admin

| Azione | Metodo | Descrizione |
|--------|--------|-------------|
| `index` | GET | Lista utenti con 3 tab (admin, docenti, studenti) |
| `view` | GET | Dettaglio utente admin |
| `create` | GET/POST | Crea nuovo utente admin |
| `update` | GET/POST | Modifica utente admin |
| `delete` | POST | Soft delete (non se stesso) |
| `toggle-status` | POST | Attiva/disattiva utente |
| `reset-password` | GET/POST | Imposta nuova password + flag force change |
| `reset-2fa` | POST | Disabilita 2FA per l'utente |

### Azioni Gestione Docenti/Studenti

Azioni parametrizzate per tipo (`type` = `docente` o `studente`):

| Azione | Metodo | Descrizione |
|--------|--------|-------------|
| `view-auth` | GET | Dettaglio credenziali docente/studente (`?type=docente&id=CF`) |
| `reset-password-auth` | GET/POST | Reset password docente/studente |
| `toggle-status-auth` | POST | Attiva/disattiva auth docente/studente |
| `force-change-auth` | POST | Forza cambio password docente/studente |
| `reset-2fa-auth` | POST | Disabilita 2FA docente/studente |

Il parametro `type` determina quale model usare (`DocenteAuth` o `AllievoAuth`).

### Search Models

- `UserSearch` — filtri: username, nominativo, email, ruolo, status, totp_enabled
- `DocenteAuthSearch` — filtri: codice_fiscale, nominativo (via join docente), status, totp_enabled
- `AllievoAuthSearch` — filtri: codice_fiscale, nominativo (via join allievo), status, totp_enabled

---

## 8. Views

### `views/utente/`

- **index.php** — 3 tab: Utenti Admin, Docenti, Studenti. Griglia con colonne: nominativo, username/CF, email, ruolo, stato (badge), 2FA (icona lucchetto), azioni
- **create.php** — form creazione utente admin
- **update.php** — form modifica utente admin
- **view.php** — dettaglio con card info + sezione sicurezza (stato 2FA, ultimo accesso, pulsanti azione)
- **_form.php** — form condiviso (username, nominativo, email, ruolo dropdown, password)
- **reset-password.php** — form nuova password + checkbox "forza cambio al prossimo login"
- **view-auth.php** — dettaglio credenziali docente/studente con azioni (vista condivisa, parametrizzata per tipo)

### Menu

In `views/layouts/menu.php`, sotto "Configurazione" (dopo "Criteri Valutazione"):

```php
<?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()): ?>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="<?= Url::to(['/utente/index']) ?>">
        <i class="ti ti-users-cog me-2"></i> Gestione Utenti
    </a>
<?php endif; ?>
```

---

## 9. 2FA — Flusso Attivazione (Self-Service)

### Tutti gli utenti (admin, docenti, studenti)

1. **Profilo → Sicurezza → "Attiva Autenticazione a Due Fattori"**
2. Sistema genera TOTP secret → mostra QR code + codice manuale da copiare
3. Utente scansiona QR con Google Authenticator → inserisce codice 6 cifre
4. Se valido → 2FA attivato, secret salvato criptato
5. **Disattivazione**: Profilo → "Disattiva 2FA" → conferma inserendo codice corrente

### Views 2FA

- Sezione "Sicurezza" aggiunta in `views/site/profilo.php` (admin)
- Sezione "Sicurezza" aggiunta in `views/docente-area/profilo.php` (docenti)
- Sezione "Sicurezza" aggiunta in `views/studente-area/profilo.php` (studenti)
- `views/site/attiva-2fa.php` — QR code + campo verifica. I controller docente/studente renderizzano la stessa view via `$this->render('@app/views/site/attiva-2fa.php')`
- `views/site/verifica-2fa.php` — campo codice 6 cifre post-login (condiviso tra le 3 aree)

### Trait `TwoFactorActionsTrait`

File: `components/traits/TwoFactorActionsTrait.php`

Trait condiviso per evitare duplicazione delle actions 2FA nei 3 controller:
- `actionAttiva2fa()` — genera secret, mostra QR, verifica codice
- `actionDisattiva2fa()` — verifica codice corrente, disattiva
- `actionVerifica2fa()` — verifica codice post-login
- `actionCambiaPassword()` — form cambio password, resetta flag

Ogni controller (`SiteController`, `DocenteAreaController`, `StudenteAreaController`) fa `use TwoFactorActionsTrait` e definisce un metodo astratto `getAuthIdentity()` che restituisce il model auth corretto.

---

## 10. Flusso Login con 2FA

### Per tutti e 3 i sistemi di login

1. Utente inserisce credenziali → password validata
2. Se `totp_enabled == 1`:
   - NON fare login Yii (`Yii::$app->user->login()`)
   - Salva ID utente + tipo in sessione temporanea (`totp_pending_user_id`, `totp_pending_type`)
   - Redirect a pagina verifica 2FA
3. Pagina verifica: campo codice 6 cifre
4. Codice valido → `Yii::$app->user->login()` effettivo → redirect dashboard
5. Codice invalido → incrementa contatore in tabella `totp_attempt` → max 5 tentativi → blocco 5 minuti
6. Se `totp_enabled == 0` → login normale come adesso

### Post-login: Force Password Change

Dopo login completato (incluso 2FA se attivo):
- `ForcePasswordChangeBehavior` intercetta ogni request
- Se `force_password_change == 1` → redirect a cambio password
- Actions cambio password:
  - `SiteController::actionCambiaPassword()` (admin)
  - `DocenteAreaController::actionCambiaPassword()` (docenti)
  - `StudenteAreaController::actionCambiaPassword()` (studenti)
- Dopo cambio password → flag resettato → navigazione normale

---

## 11. Sicurezza

- TOTP secret criptato in DB con `Yii::$app->security->encryptByKey()` + chiave da `params['totpEncryptionKey']`
- Admin non può eliminare/declassare se stesso
- `TotpPendingBehavior` impedisce accesso a pagine protette in stato 2FA pendente
- `ForcePasswordChangeBehavior` impedisce navigazione con flag cambio password attivo
- Tentativi 2FA: max 5, poi blocco 5 minuti (tracciati in tabella `totp_attempt`, non in sessione)
- Admin può resettare 2FA altrui ma non vederne il secret
- TOTP discrepancy ±1 (tolleranza 30 secondi per clock drift)

---

## 12. Dipendenze

- `robthree/twofactorauth` — generazione/validazione TOTP + QR code

---

## 13. Changelog In-App

### File `CHANGELOG.md`

File nel root del repo con formato strutturato, parsato dal portale.

#### Formato

```markdown
## [1.5.0] - 2026-03-14

### Novità {tutti}
- Aggiunta autenticazione a due fattori (2FA) con Google Authenticator

### Novità {admin}
- Gestione centralizzata utenti (admin, docenti, studenti)
- Reset password e disabilita/riattiva utenti da pannello admin

### Novità {docente}
- Sezione sicurezza nel profilo con attivazione 2FA

### Novità {studente}
- Sezione sicurezza nel profilo con attivazione 2FA

### Correzioni {tutti}
- Migliorata stabilità generale del portale
```

I tag tra `{}` indicano la visibilità: `tutti`, `admin`, `docente`, `studente`. Un utente admin vede le voci `{tutti}` + `{admin}`. Un docente vede `{tutti}` + `{docente}`. Ecc.

### Parser

Classe helper `components/ChangelogParser.php`:
- `parse(): array` — legge `CHANGELOG.md`, restituisce array strutturato di versioni con voci filtrabili
- `getForUserType(string $type): array` — filtra per tipo utente (`admin`, `docente`, `studente`)
- `getLatestVersion(): string` — restituisce versione più recente
- `countUnreadItems(string $type, ?string $lastSeen): int` — conta voci non lette per il badge (somma bullet points nelle versioni > `lastSeen`)

### Tracking "Letto"

- Campo `last_changelog_seen` (VARCHAR 20, nullable) su `utente`, `docente_auth`, `allievo_auth` (incluso nelle migration 1 e 2)
- Quando l'utente apre il changelog, salva la versione corrente in `last_changelog_seen`
- Il badge compare se `last_changelog_seen` < versione corrente (o è null)

### UI — Icona Header

In tutti e 3 i layout (`main.php`, `docente.php`, `studente.php`):
- Icona `ti ti-sparkles` nell'header/navbar
- Badge rosso con numero novità non lette (conta bullet points nelle versioni successive a `last_changelog_seen`)
- Click → modal con lista novità per le versioni non lette
- Link "Vedi tutto lo storico" → pagina completa

### UI — Pagina Changelog

Route: `/site/changelog` (admin), `/area-docenti/changelog` (docenti), `/area-studenti/changelog` (studenti)

Layout: timeline verticale con card per versione, badge colorati per tipo (Novità verde, Correzioni azzurro, Miglioramenti giallo). Ogni versione mostra data + lista voci filtrate per il tipo utente corrente.

### Actions (via `ChangelogActionsTrait`)

Trait `components/traits/ChangelogActionsTrait.php` condiviso tra i 3 controller:

- `actionChangelog()` — pagina completa
- `actionChangelogModal()` — AJAX, restituisce HTML per modal
- `actionSegnaChangelogLetto()` — POST, aggiorna `last_changelog_seen`
