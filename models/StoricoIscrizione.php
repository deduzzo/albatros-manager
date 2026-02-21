<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model per la tabella storico_iscrizione.
 * Traccia ogni cambiamento di stato di un'iscrizione studente.
 * NON estende BaseActiveRecord (no soft delete, come StoricoEsito).
 *
 * @property int $id
 * @property int $iscrizione_id
 * @property string|null $status_precedente
 * @property string $status_nuovo
 * @property string|null $note
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property Iscrizione $iscrizione
 * @property User $createdByUser
 */
class StoricoIscrizione extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%storico_iscrizione}}';
    }

    public function rules()
    {
        return [
            [['iscrizione_id', 'status_nuovo'], 'required'],
            [['iscrizione_id', 'created_by'], 'integer'],
            [['status_precedente', 'status_nuovo'], 'string', 'max' => 30],
            [['note'], 'string'],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iscrizione_id' => 'Iscrizione',
            'status_precedente' => 'Stato Precedente',
            'status_nuovo' => 'Nuovo Stato',
            'note' => 'Note',
            'created_at' => 'Data',
            'created_by' => 'Operatore',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && !$this->created_by && !Yii::$app->user->isGuest) {
                $this->created_by = Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }

    public function getIscrizione()
    {
        return $this->hasOne(Iscrizione::class, ['id' => 'iscrizione_id']);
    }

    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Registra un cambio di stato per un'iscrizione.
     * @param Iscrizione $iscrizione
     * @param string $nuovoStatus
     * @param string|null $note
     * @return bool
     */
    public static function registra($iscrizione, $nuovoStatus, $note = null)
    {
        $storico = new static();
        $storico->iscrizione_id = $iscrizione->id;
        $storico->status_precedente = $iscrizione->status;
        $storico->status_nuovo = $nuovoStatus;
        $storico->note = $note;
        return $storico->save();
    }

    /**
     * Restituisce la label per lo status.
     * @param string $status
     * @return string
     */
    public function getStatusNuovoLabel()
    {
        $labels = Iscrizione::getStatusLabels();
        return $labels[$this->status_nuovo] ?? $this->status_nuovo;
    }

    public function getStatusPrecedenteLabel()
    {
        if (!$this->status_precedente) return '-';
        $labels = Iscrizione::getStatusLabels();
        return $labels[$this->status_precedente] ?? $this->status_precedente;
    }
}
