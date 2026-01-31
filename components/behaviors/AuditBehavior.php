<?php

namespace app\components\behaviors;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\BaseActiveRecord;
use yii\db\Expression;

/**
 * AuditBehavior combina TimestampBehavior e BlameableBehavior
 * per gestire automaticamente i campi di audit:
 * - created_at, updated_at, deleted_at (timestamp)
 * - created_by, updated_by, deleted_by (user ID)
 *
 * Uso nel model:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         AuditBehavior::class,
 *     ];
 * }
 * ```
 */
class AuditBehavior extends \yii\base\Behavior
{
    /**
     * @var string attributo per timestamp creazione
     */
    public $createdAtAttribute = 'created_at';

    /**
     * @var string attributo per timestamp modifica
     */
    public $updatedAtAttribute = 'updated_at';

    /**
     * @var string attributo per timestamp eliminazione (soft delete)
     */
    public $deletedAtAttribute = 'deleted_at';

    /**
     * @var string attributo per utente creazione
     */
    public $createdByAttribute = 'created_by';

    /**
     * @var string attributo per utente modifica
     */
    public $updatedByAttribute = 'updated_by';

    /**
     * @var string attributo per utente eliminazione
     */
    public $deletedByAttribute = 'deleted_by';

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
        ];
    }

    /**
     * Imposta i campi di audit prima dell'inserimento.
     *
     * @param \yii\base\Event $event
     */
    public function beforeInsert($event)
    {
        $owner = $this->owner;

        // Timestamp creazione
        if ($owner->hasAttribute($this->createdAtAttribute) && empty($owner->{$this->createdAtAttribute})) {
            $owner->{$this->createdAtAttribute} = new Expression('NOW()');
        }

        // Timestamp modifica
        if ($owner->hasAttribute($this->updatedAtAttribute)) {
            $owner->{$this->updatedAtAttribute} = new Expression('NOW()');
        }

        // User creazione
        if ($owner->hasAttribute($this->createdByAttribute) && empty($owner->{$this->createdByAttribute})) {
            $owner->{$this->createdByAttribute} = $this->getCurrentUserId();
        }

        // User modifica
        if ($owner->hasAttribute($this->updatedByAttribute)) {
            $owner->{$this->updatedByAttribute} = $this->getCurrentUserId();
        }
    }

    /**
     * Imposta i campi di audit prima dell'aggiornamento.
     *
     * @param \yii\base\Event $event
     */
    public function beforeUpdate($event)
    {
        $owner = $this->owner;

        // Timestamp modifica
        if ($owner->hasAttribute($this->updatedAtAttribute)) {
            $owner->{$this->updatedAtAttribute} = new Expression('NOW()');
        }

        // User modifica
        if ($owner->hasAttribute($this->updatedByAttribute)) {
            $owner->{$this->updatedByAttribute} = $this->getCurrentUserId();
        }

        // Se è un soft delete (deleted_at viene impostato)
        if ($owner->hasAttribute($this->deletedAtAttribute) &&
            !empty($owner->{$this->deletedAtAttribute}) &&
            $owner->isAttributeChanged($this->deletedAtAttribute)) {

            // User eliminazione
            if ($owner->hasAttribute($this->deletedByAttribute)) {
                $owner->{$this->deletedByAttribute} = $this->getCurrentUserId();
            }
        }
    }

    /**
     * Restituisce l'ID dell'utente corrente.
     *
     * @return int|null
     */
    protected function getCurrentUserId()
    {
        if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
            return Yii::$app->user->id;
        }
        return null;
    }

    /**
     * Imposta i campi di soft delete.
     * Da chiamare manualmente quando si effettua un soft delete.
     */
    public function softDelete()
    {
        $owner = $this->owner;

        if ($owner->hasAttribute($this->deletedAtAttribute)) {
            $owner->{$this->deletedAtAttribute} = new Expression('NOW()');
        }

        if ($owner->hasAttribute($this->deletedByAttribute)) {
            $owner->{$this->deletedByAttribute} = $this->getCurrentUserId();
        }

        return $owner->save(false);
    }

    /**
     * Ripristina un record eliminato (soft delete).
     */
    public function restore()
    {
        $owner = $this->owner;

        if ($owner->hasAttribute($this->deletedAtAttribute)) {
            $owner->{$this->deletedAtAttribute} = null;
        }

        if ($owner->hasAttribute($this->deletedByAttribute)) {
            $owner->{$this->deletedByAttribute} = null;
        }

        return $owner->save(false);
    }
}
