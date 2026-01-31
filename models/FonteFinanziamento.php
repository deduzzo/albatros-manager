<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella fonte_finanziamento.
 *
 * @property int $id
 * @property string $codice
 * @property string $descrizione
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Progetto[] $progetti
 * @property User $createdByUser
 * @property User $updatedByUser
 * @property User $deletedByUser
 */
class FonteFinanziamento extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fonte_finanziamento}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['codice', 'descrizione'], 'required'],
            [['note'], 'string'],
            [['codice'], 'string', 'max' => 50],
            [['descrizione'], 'string', 'max' => 255],
            [['codice'], 'unique'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codice' => 'Codice',
            'descrizione' => 'Descrizione',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
            'deleted_at' => 'Eliminato il',
            'created_by' => 'Creato da',
            'updated_by' => 'Modificato da',
            'deleted_by' => 'Eliminato da',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProgetti()
    {
        return $this->hasMany(Progetto::class, ['fonte_finanziamento_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'deleted_by']);
    }

    /**
     * Restituisce lista per dropdown
     * @return array
     */
    public static function getList()
    {
        return static::find()
            ->select(['descrizione', 'id'])
            ->orderBy('codice')
            ->indexBy('id')
            ->column();
    }

    /**
     * Restituisce nome completo (codice - descrizione)
     * @return string
     */
    public function getNomeCompleto()
    {
        return $this->codice . ' - ' . $this->descrizione;
    }
}
