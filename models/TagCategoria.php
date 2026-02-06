<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella tag_categoria.
 *
 * @property int $id
 * @property string $nome
 * @property string|null $descrizione
 * @property string $colore
 * @property string|null $icona
 * @property int $ordine
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property TagCurriculum[] $tags
 * @property User $createdByUser
 * @property User $updatedByUser
 * @property User $deletedByUser
 */
class TagCategoria extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tag_categoria}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nome'], 'required'],
            [['ordine'], 'integer'],
            [['nome'], 'string', 'max' => 50],
            [['descrizione'], 'string', 'max' => 255],
            [['colore'], 'string', 'max' => 7],
            [['icona'], 'string', 'max' => 50],
            [['nome'], 'unique'],
            [['colore'], 'match', 'pattern' => '/^#[0-9A-Fa-f]{6}$/', 'message' => 'Il colore deve essere in formato esadecimale (#RRGGBB)'],
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
            'nome' => 'Nome',
            'descrizione' => 'Descrizione',
            'colore' => 'Colore',
            'icona' => 'Icona',
            'ordine' => 'Ordine',
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
    public function getTags()
    {
        return $this->hasMany(TagCurriculum::class, ['categoria_id' => 'id']);
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
            ->select(['nome', 'id'])
            ->orderBy('ordine')
            ->indexBy('id')
            ->column();
    }

    /**
     * Conta i tag attivi in questa categoria
     * @return int
     */
    public function getTagCount()
    {
        return $this->getTags()->where(['attivo' => true])->count();
    }
}
