<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella punteggio_iscrizione.
 *
 * @property int $id
 * @property int $iscrizione_id
 * @property int $criterio_id
 * @property float|null $punteggio
 * @property string|null $note
 *
 * @property Iscrizione $iscrizione
 * @property CriterioValutazioneStudente $criterio
 */
class PunteggioIscrizione extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%punteggio_iscrizione}}';
    }

    public function rules()
    {
        return [
            [['iscrizione_id', 'criterio_id'], 'required'],
            [['iscrizione_id', 'criterio_id'], 'integer'],
            [['punteggio'], 'number'],
            [['punteggio'], 'validatePunteggioInRange'],
            [['note'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['iscrizione_id', 'criterio_id'], 'unique', 'targetAttribute' => ['iscrizione_id', 'criterio_id'], 'message' => 'Punteggio gia assegnato per questo criterio.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iscrizione_id' => 'Iscrizione',
            'criterio_id' => 'Criterio',
            'punteggio' => 'Punteggio',
            'note' => 'Note',
        ];
    }

    /**
     * Valida che il punteggio sia nel range del criterio.
     */
    public function validatePunteggioInRange($attribute, $params)
    {
        if ($this->punteggio !== null && $this->criterio) {
            if ($this->punteggio < $this->criterio->punteggio_min || $this->punteggio > $this->criterio->punteggio_max) {
                $this->addError($attribute, "Il punteggio deve essere tra {$this->criterio->punteggio_min} e {$this->criterio->punteggio_max}.");
            }
        }
    }

    public function getIscrizione()
    {
        return $this->hasOne(Iscrizione::class, ['id' => 'iscrizione_id']);
    }

    public function getCriterio()
    {
        return $this->hasOne(CriterioValutazioneStudente::class, ['id' => 'criterio_id']);
    }

    /**
     * Calcola il punteggio pesato (punteggio * peso del criterio).
     * @return float
     */
    public function getPunteggioPesato()
    {
        if ($this->punteggio === null || !$this->criterio) {
            return 0;
        }
        return $this->punteggio * $this->criterio->peso;
    }
}
