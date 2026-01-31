<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Model per la tabella audit_log.
 *
 * NOTA: Questo model NON estende BaseActiveRecord perché:
 * - Non deve avere soft delete (i log sono permanenti)
 * - Non deve loggare se stesso (loop infinito)
 *
 * @property int $id
 * @property string $table_name
 * @property string $record_id
 * @property string $action
 * @property string|null $old_values
 * @property string|null $new_values
 * @property string|null $changed_fields
 * @property int|null $user_id
 * @property string|null $user_type
 * @property string|null $username
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $request_url
 * @property string|null $request_method
 * @property string|null $note
 * @property string|null $session_id
 * @property string $created_at
 *
 * @property Utente $user
 */
class AuditLog extends ActiveRecord
{
    const ACTION_INSERT = 'INSERT';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';
    const ACTION_SOFT_DELETE = 'SOFT_DELETE';
    const ACTION_RESTORE = 'RESTORE';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%audit_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['table_name', 'record_id', 'action'], 'required'],
            [['old_values', 'new_values', 'changed_fields', 'note'], 'string'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['table_name', 'session_id'], 'string', 'max' => 100],
            [['record_id', 'user_type'], 'string', 'max' => 50],
            [['username'], 'string', 'max' => 100],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent', 'request_url'], 'string', 'max' => 500],
            [['request_method'], 'string', 'max' => 10],
            [['action'], 'in', 'range' => ['INSERT', 'UPDATE', 'DELETE', 'SOFT_DELETE', 'RESTORE']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'table_name' => 'Tabella',
            'record_id' => 'ID Record',
            'action' => 'Azione',
            'old_values' => 'Valori Precedenti',
            'new_values' => 'Nuovi Valori',
            'changed_fields' => 'Campi Modificati',
            'user_id' => 'ID Utente',
            'user_type' => 'Tipo Utente',
            'username' => 'Username',
            'ip_address' => 'Indirizzo IP',
            'user_agent' => 'User Agent',
            'request_url' => 'URL Richiesta',
            'request_method' => 'Metodo HTTP',
            'note' => 'Note',
            'session_id' => 'ID Sessione',
            'created_at' => 'Data/Ora',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Utente::class, ['id' => 'user_id']);
    }

    /**
     * Ottiene i vecchi valori come array
     * @return array
     */
    public function getOldValuesArray()
    {
        return $this->old_values ? Json::decode($this->old_values) : [];
    }

    /**
     * Ottiene i nuovi valori come array
     * @return array
     */
    public function getNewValuesArray()
    {
        return $this->new_values ? Json::decode($this->new_values) : [];
    }

    /**
     * Ottiene i campi modificati come array
     * @return array
     */
    public function getChangedFieldsArray()
    {
        return $this->changed_fields ? Json::decode($this->changed_fields) : [];
    }

    /**
     * Ottiene la cronologia di un record specifico
     * @param string $tableName
     * @param string $recordId
     * @return AuditLog[]
     */
    public static function getRecordHistory($tableName, $recordId)
    {
        return static::find()
            ->where([
                'table_name' => $tableName,
                'record_id' => (string)$recordId,
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }

    /**
     * Ottiene le azioni di un utente
     * @param int $userId
     * @param int $limit
     * @return AuditLog[]
     */
    public static function getUserActivity($userId, $limit = 100)
    {
        return static::find()
            ->where(['user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Ottiene il label colorato per l'azione
     * @return string
     */
    public function getActionBadge()
    {
        $badges = [
            'INSERT' => '<span class="badge badge-success">Creato</span>',
            'UPDATE' => '<span class="badge badge-info">Modificato</span>',
            'DELETE' => '<span class="badge badge-danger">Eliminato</span>',
            'SOFT_DELETE' => '<span class="badge badge-warning">Archiviato</span>',
            'RESTORE' => '<span class="badge badge-primary">Ripristinato</span>',
        ];

        return $badges[$this->action] ?? $this->action;
    }
}
