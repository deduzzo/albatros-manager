<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella valutazione_docente_punteggio.
 *
 * @property int $id
 * @property int $valutazione_docente_id
 * @property int $criterio_id
 * @property int $punteggio
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property ValutazioneDocente $valutazioneDocente
 * @property CriterioValutazioneDocente $criterio
 */
class ValutazioneDocentePunteggio extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%valutazione_docente_punteggio}}';
    }

    public function rules()
    {
        return [
            [['valutazione_docente_id', 'criterio_id', 'punteggio'], 'required'],
            [['valutazione_docente_id', 'criterio_id', 'punteggio'], 'integer'],
            [['punteggio'], 'integer', 'min' => 1, 'max' => 10],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'valutazione_docente_id' => 'Valutazione',
            'criterio_id' => 'Criterio',
            'punteggio' => 'Punteggio',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    protected function auditExcludedAttributes()
    {
        return ['created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_by'];
    }

    protected function auditMaskedAttributes()
    {
        return [];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValutazioneDocente()
    {
        return $this->hasOne(ValutazioneDocente::class, ['id' => 'valutazione_docente_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCriterio()
    {
        return $this->hasOne(CriterioValutazioneDocente::class, ['id' => 'criterio_id']);
    }
}
