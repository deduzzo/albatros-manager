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
