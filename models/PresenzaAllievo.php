<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella presenza_allievo.
 *
 * @property int $id
 * @property int $calendario_id
 * @property string $allievo_codice_fiscale
 * @property bool $presente
 * @property string|null $ora_ingresso
 * @property string|null $ora_uscita
 * @property float|null $ore_presenza
 * @property bool|null $giustificato
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Calendario $calendario
 * @property Allievo $allievo
 */
class PresenzaAllievo extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%presenza_allievo}}';
    }

    public function rules()
    {
        return [
            [['calendario_id', 'allievo_codice_fiscale'], 'required'],
            [['calendario_id'], 'integer'],
            [['allievo_codice_fiscale'], 'string', 'max' => 16],
            [['presente'], 'in', 'range' => [null, 0, 1], 'strict' => false],
            [['giustificato'], 'boolean'],
            [['ora_ingresso', 'ora_uscita'], 'match', 'pattern' => '/^\d{2}:\d{2}(:\d{2})?$/', 'message' => 'Formato orario non valido (HH:MM o HH:MM:SS)'],
            [['ore_presenza'], 'number', 'min' => 0],
            [['note'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'calendario_id' => 'Lezione',
            'allievo_codice_fiscale' => 'Allievo',
            'presente' => 'Presente',
            'ora_ingresso' => 'Ora Ingresso',
            'ora_uscita' => 'Ora Uscita',
            'ore_presenza' => 'Ore Presenza',
            'giustificato' => 'Giustificato',
            'note' => 'Note',
        ];
    }

    public function getCalendario()
    {
        return $this->hasOne(Calendario::class, ['id' => 'calendario_id']);
    }

    public function getAllievo()
    {
        return $this->hasOne(Allievo::class, ['codice_fiscale' => 'allievo_codice_fiscale']);
    }

    public function getPresenzaBadgeClass()
    {
        if ($this->presente === null) {
            return 'bg-default-lt';
        }
        if ($this->presente) {
            return $this->giustificato ? 'bg-orange-lt' : 'bg-green-lt';
        }
        return 'bg-red-lt';
    }

    public function getPresenzaLabel()
    {
        if ($this->presente === null) {
            return 'Non acquisito';
        }
        if ($this->presente) {
            return 'Presente';
        }
        return $this->giustificato ? 'Assente Giustificato' : 'Assente';
    }

    /**
     * Calcola automaticamente le ore di presenza dalla differenza tra ingresso e uscita
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->ora_ingresso && $this->ora_uscita) {
                $ingresso = strtotime($this->ora_ingresso);
                $uscita = strtotime($this->ora_uscita);
                $differenza = ($uscita - $ingresso) / 3600; // Converti secondi in ore
                $this->ore_presenza = round($differenza, 2);
            }
            return true;
        }
        return false;
    }
}
