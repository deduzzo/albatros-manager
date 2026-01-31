<?php

namespace app\components\traits;

use yii\db\ActiveQuery;

/**
 * SoftDeleteTrait aggiunge scope di query per gestire i record soft-deleted.
 *
 * Uso nel Model:
 * ```php
 * class MyModel extends ActiveRecord
 * {
 *     use SoftDeleteTrait;
 *
 *     public static function find()
 *     {
 *         return parent::find()->notDeleted(); // Default: escludi deleted
 *     }
 * }
 * ```
 */
trait SoftDeleteTrait
{
    /**
     * @var string nome della colonna per soft delete
     */
    protected static $softDeleteAttribute = 'deleted_at';

    /**
     * Query che include solo i record NON cancellati
     * @return ActiveQuery
     */
    public static function findActive()
    {
        return parent::find()->andWhere([static::tableName() . '.' . static::$softDeleteAttribute => null]);
    }

    /**
     * Query che include solo i record cancellati
     * @return ActiveQuery
     */
    public static function findDeleted()
    {
        return parent::find()->andWhere(['NOT', [static::tableName() . '.' . static::$softDeleteAttribute => null]]);
    }

    /**
     * Query che include TUTTI i record (attivi e cancellati)
     * @return ActiveQuery
     */
    public static function findWithDeleted()
    {
        return parent::find();
    }

    /**
     * Override find() per escludere di default i record soft-deleted
     * @return ActiveQuery
     */
    public static function find()
    {
        return static::findActive();
    }
}
