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
