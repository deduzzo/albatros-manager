<?php

namespace app\components\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * SoftDeleteBehavior gestisce la cancellazione logica dei record.
 *
 * Invece di eliminare fisicamente i record, imposta deleted_at con il timestamp corrente.
 *
 * Uso nel Model:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'softDelete' => [
 *             'class' => SoftDeleteBehavior::class,
 *         ],
 *     ];
 * }
 * ```
 */
class SoftDeleteBehavior extends Behavior
{
    /**
     * @var string nome della colonna per soft delete
     */
    public $deletedAtAttribute = 'deleted_at';

    /**
     * @var bool se true, delete() esegue soft delete invece di hard delete
     */
    public $replaceDelete = true;

    /**
     * @inheritdoc
     */
    public function events()
    {
        if ($this->replaceDelete) {
            return [
                ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ];
        }
        return [];
    }

    /**
     * Intercetta il delete e lo converte in soft delete
     */
    public function beforeDelete($event)
    {
        $this->softDelete();
        $event->isValid = false; // Previene il delete fisico
    }

    /**
     * Esegue il soft delete
     * @return bool
     */
    public function softDelete()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $owner->{$this->deletedAtAttribute} = new Expression('NOW()');
        return $owner->save(false, [$this->deletedAtAttribute]);
    }

    /**
     * Ripristina un record soft-deleted
     * @return bool
     */
    public function restore()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $owner->{$this->deletedAtAttribute} = null;
        return $owner->save(false, [$this->deletedAtAttribute]);
    }

    /**
     * Elimina fisicamente il record (bypass soft delete)
     * @return false|int
     */
    public function hardDelete()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $this->replaceDelete = false;
        $result = $owner->delete();
        $this->replaceDelete = true;
        return $result;
    }

    /**
     * Verifica se il record è soft-deleted
     * @return bool
     */
    public function isDeleted()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        return $owner->{$this->deletedAtAttribute} !== null;
    }
}
