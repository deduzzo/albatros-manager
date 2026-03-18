<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella rimborso_spesa_dettaglio.
 *
 * @property int $id
 * @property int $rimborso_spesa_id
 * @property int|null $voce_spesa_id
 * @property string $descrizione
 * @property float $importo
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property RimborsoSpesa $rimborsoSpesa
 * @property VoceSpesa $voceSpesa
 */
class RimborsoSpesaDettaglio extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%rimborso_spesa_dettaglio}}';
    }

    public function rules()
    {
        return [
            [['rimborso_spesa_id', 'descrizione', 'importo'], 'required'],
            [['rimborso_spesa_id', 'voce_spesa_id'], 'integer'],
            [['importo'], 'number', 'min' => 0],
            [['descrizione'], 'string', 'max' => 500],
            [['note'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rimborso_spesa_id' => 'Rimborso',
            'voce_spesa_id' => 'Voce di Spesa',
            'descrizione' => 'Descrizione',
            'importo' => 'Importo (€)',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRimborsoSpesa()
    {
        return $this->hasOne(RimborsoSpesa::class, ['id' => 'rimborso_spesa_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVoceSpesa()
    {
        return $this->hasOne(VoceSpesa::class, ['id' => 'voce_spesa_id']);
    }

    /**
     * Label completa della voce
     * @return string
     */
    public function getVoceLabel()
    {
        if ($this->voceSpesa) {
            return $this->voceSpesa->getLabel();
        }
        return $this->descrizione;
    }
}
