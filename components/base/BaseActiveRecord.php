<?php

namespace app\components\base;

use yii\db\ActiveRecord;
use app\components\behaviors\SoftDeleteBehavior;
use app\components\behaviors\AuditLogBehavior;
use app\components\behaviors\AuditBehavior;
use app\components\traits\SoftDeleteTrait;

/**
 * BaseActiveRecord è la classe base per tutti i Model dell'applicazione.
 *
 * Include automaticamente:
 * - Soft Delete
 * - Audit Logging
 *
 * Tutti i Model devono estendere questa classe invece di ActiveRecord.
 *
 * Esempio:
 * ```php
 * class Docente extends BaseActiveRecord
 * {
 *     public static function tableName()
 *     {
 *         return '{{%docente}}';
 *     }
 * }
 * ```
 */
abstract class BaseActiveRecord extends ActiveRecord
{
    use SoftDeleteTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'softDelete' => [
                'class' => SoftDeleteBehavior::class,
            ],
            'auditLog' => [
                'class' => AuditLogBehavior::class,
                'excludedAttributes' => $this->auditExcludedAttributes(),
                'maskedAttributes' => $this->auditMaskedAttributes(),
            ],
            'auditUser' => [
                'class' => AuditBehavior::class,
            ],
        ]);
    }

    /**
     * Attributi da escludere dal audit log
     * Override nei Model figli se necessario
     * @return array
     */
    protected function auditExcludedAttributes()
    {
        return ['created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_by'];
    }

    /**
     * Attributi sensibili da mascherare nel audit log
     * Override nei Model figli se necessario
     * @return array
     */
    protected function auditMaskedAttributes()
    {
        return ['password_hash', 'auth_key', 'password_reset_token'];
    }

    /**
     * Esegue soft delete
     * @return bool
     */
    public function softDelete()
    {
        return $this->getBehavior('softDelete')->softDelete();
    }

    /**
     * Ripristina un record soft-deleted
     * @return bool
     */
    public function restore()
    {
        return $this->getBehavior('softDelete')->restore();
    }

    /**
     * Elimina fisicamente il record (bypass soft delete)
     * @return false|int
     */
    public function hardDelete()
    {
        return $this->getBehavior('softDelete')->hardDelete();
    }

    /**
     * Verifica se il record è soft-deleted
     * @return bool
     */
    public function isDeleted()
    {
        return $this->getBehavior('softDelete')->isDeleted();
    }

    /**
     * Imposta una nota per l'audit log del prossimo salvataggio
     * @param string $note
     * @return $this
     */
    public function withAuditNote($note)
    {
        $this->getBehavior('audit')->setAuditNote($note);
        return $this;
    }
}
