<?php

namespace app\components\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * AuditLogBehavior registra tutte le modifiche ai record nel audit_log.
 *
 * Traccia: INSERT, UPDATE, DELETE, SOFT_DELETE, RESTORE
 *
 * Uso nel Model:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'audit' => [
 *             'class' => AuditLogBehavior::class,
 *             'excludedAttributes' => ['updated_at'], // campi da non tracciare
 *         ],
 *     ];
 * }
 * ```
 */
class AuditLogBehavior extends Behavior
{
    /**
     * @var array attributi da escludere dal log
     */
    public $excludedAttributes = ['created_at', 'updated_at'];

    /**
     * @var array attributi sensibili da mascherare
     */
    public $maskedAttributes = ['password_hash', 'auth_key', 'password_reset_token'];

    /**
     * @var string|null nota aggiuntiva per il log
     */
    public $logNote = null;

    /**
     * @var array valori originali prima dell'update
     */
    private $_oldAttributes = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * Salva gli attributi originali prima dell'update
     */
    public function beforeUpdate($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $this->_oldAttributes = $owner->getOldAttributes();
    }

    /**
     * Log dopo INSERT
     */
    public function afterInsert($event)
    {
        $this->log('INSERT', null, $this->getFilteredAttributes());
    }

    /**
     * Log dopo UPDATE
     */
    public function afterUpdate($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        $changedAttributes = $event->changedAttributes;
        $oldValues = [];
        $newValues = [];
        $changedFields = [];

        foreach ($changedAttributes as $attribute => $oldValue) {
            if (in_array($attribute, $this->excludedAttributes)) {
                continue;
            }

            $newValue = $owner->getAttribute($attribute);

            // Rileva soft delete
            if ($attribute === 'deleted_at') {
                if ($oldValue === null && $newValue !== null) {
                    $this->log('SOFT_DELETE', $this->_oldAttributes, $this->getFilteredAttributes());
                    return;
                } elseif ($oldValue !== null && $newValue === null) {
                    $this->log('RESTORE', $this->_oldAttributes, $this->getFilteredAttributes());
                    return;
                }
            }

            $oldValues[$attribute] = $this->maskValue($attribute, $oldValue);
            $newValues[$attribute] = $this->maskValue($attribute, $newValue);
            $changedFields[] = $attribute;
        }

        if (!empty($changedFields)) {
            $this->log('UPDATE', $oldValues, $newValues, $changedFields);
        }
    }

    /**
     * Log dopo DELETE (hard delete)
     */
    public function afterDelete($event)
    {
        $this->log('DELETE', $this->getFilteredAttributes(), null);
    }

    /**
     * Registra l'azione nel audit_log
     */
    protected function log($action, $oldValues, $newValues, $changedFields = null)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        try {
            $user = Yii::$app->has('user') && !Yii::$app->user->isGuest
                ? Yii::$app->user->identity
                : null;

            $request = Yii::$app->has('request') && !Yii::$app->request->isConsoleRequest
                ? Yii::$app->request
                : null;

            $data = [
                'table_name' => $owner->tableName(),
                'record_id' => $this->getRecordId(),
                'action' => $action,
                'old_values' => $oldValues ? Json::encode($oldValues) : null,
                'new_values' => $newValues ? Json::encode($newValues) : null,
                'changed_fields' => $changedFields ? Json::encode($changedFields) : null,
                'user_id' => $user ? $user->id : null,
                'user_type' => $user ? 'utente' : ($request ? 'guest' : 'sistema'),
                'username' => $user ? $user->username : null,
                'ip_address' => $request ? $request->userIP : null,
                'user_agent' => $request ? substr($request->userAgent ?? '', 0, 500) : null,
                'request_url' => $request ? substr($request->absoluteUrl ?? '', 0, 500) : null,
                'request_method' => $request ? $request->method : null,
                'note' => $this->logNote,
                'session_id' => $request && Yii::$app->has('session') ? Yii::$app->session->id : null,
            ];

            Yii::$app->db->createCommand()
                ->insert('{{%audit_log}}', $data)
                ->execute();

        } catch (\Exception $e) {
            Yii::error('AuditLog error: ' . $e->getMessage(), 'audit');
        }
    }

    /**
     * Ottiene l'ID del record (supporta PK composite e codice fiscale)
     */
    protected function getRecordId()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $pk = $owner->getPrimaryKey(true);

        if (is_array($pk)) {
            return Json::encode($pk);
        }

        return (string)$pk;
    }

    /**
     * Filtra gli attributi escludendo quelli non tracciabili
     */
    protected function getFilteredAttributes()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $attributes = $owner->getAttributes();

        foreach ($this->excludedAttributes as $excluded) {
            unset($attributes[$excluded]);
        }

        foreach ($this->maskedAttributes as $masked) {
            if (isset($attributes[$masked])) {
                $attributes[$masked] = '***MASKED***';
            }
        }

        return $attributes;
    }

    /**
     * Maschera valori sensibili
     */
    protected function maskValue($attribute, $value)
    {
        if (in_array($attribute, $this->maskedAttributes)) {
            return '***MASKED***';
        }
        return $value;
    }

    /**
     * Imposta una nota per il prossimo log
     */
    public function setAuditNote($note)
    {
        $this->logNote = $note;
    }
}
