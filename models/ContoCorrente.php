<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella conto_corrente.
 *
 * @property int $id
 * @property string $banca
 * @property string $iban
 * @property string $intestatario
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Progetto[] $progetti
 */
class ContoCorrente extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%conto_corrente}}';
    }

    public function rules()
    {
        return [
            [['banca', 'iban', 'intestatario'], 'required'],
            [['note'], 'string'],
            [['banca'], 'string', 'max' => 100],
            [['iban'], 'string', 'max' => 34],
            [['iban'], 'unique'],
            [['intestatario'], 'string', 'max' => 255],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'banca' => 'Banca',
            'iban' => 'IBAN',
            'intestatario' => 'Intestatario',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    public function getProgetti()
    {
        return $this->hasMany(Progetto::class, ['conto_corrente_id' => 'id']);
    }

    /**
     * Restituisce IBAN formattato (spazi ogni 4 caratteri)
     * @return string
     */
    public function getIbanFormattato()
    {
        return implode(' ', str_split($this->iban, 4));
    }

    /**
     * Restituisce lista per dropdown
     * @return array
     */
    public static function getList()
    {
        return static::find()
            ->select(['CONCAT(banca, " - ", iban)', 'id'])
            ->orderBy(['banca' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    protected function auditMaskedAttributes()
    {
        return ['iban'];
    }
}
