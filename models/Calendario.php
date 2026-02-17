<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella calendario.
 *
 * @property int $id
 * @property int $modulo_id
 * @property string $docente_codice_fiscale
 * @property string $data
 * @property string $ora_inizio
 * @property string $ora_fine
 * @property float $ore
 * @property string|null $aula
 * @property string|null $argomento
 * @property string|null $descrizione_attivita
 * @property string $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Modulo $modulo
 * @property Docente $docente
 * @property PresenzaAllievo[] $presenze
 */
class Calendario extends BaseActiveRecord
{
    const STATUS_PROGRAMMATA = 'programmata';
    const STATUS_COMPLETATA = 'completata';
    const STATUS_ANNULLATA = 'annullata';
    const STATUS_RECUPERO = 'recupero';

    public static function tableName()
    {
        return '{{%calendario}}';
    }

    public function rules()
    {
        return [
            [['modulo_id', 'docente_codice_fiscale', 'data', 'ora_inizio', 'ora_fine', 'ore'], 'required'],
            [['modulo_id'], 'integer'],
            [['data'], 'date', 'format' => 'php:Y-m-d'],
            [['ora_inizio', 'ora_fine'], 'safe'],
            [['ore'], 'number'],
            [['descrizione_attivita', 'note'], 'string'],
            [['docente_codice_fiscale'], 'string', 'max' => 16],
            [['aula'], 'string', 'max' => 50],
            [['argomento'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => ['programmata', 'completata', 'annullata', 'recupero']],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modulo_id' => 'Modulo',
            'docente_codice_fiscale' => 'Docente',
            'data' => 'Data',
            'ora_inizio' => 'Ora Inizio',
            'ora_fine' => 'Ora Fine',
            'ore' => 'Ore',
            'aula' => 'Aula',
            'argomento' => 'Argomento',
            'descrizione_attivita' => 'Descrizione Attività',
            'status' => 'Stato',
            'note' => 'Note',
        ];
    }

    public function getModulo()
    {
        return $this->hasOne(Modulo::class, ['id' => 'modulo_id']);
    }

    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
    }

    public function getPresenze()
    {
        return $this->hasMany(PresenzaAllievo::class, ['calendario_id' => 'id']);
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_PROGRAMMATA => 'Programmata',
            self::STATUS_COMPLETATA => 'Completata',
            self::STATUS_ANNULLATA => 'Annullata',
            self::STATUS_RECUPERO => 'Recupero',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_PROGRAMMATA => 'bg-azure text-azure-fg',
            self::STATUS_COMPLETATA => 'bg-green text-green-fg',
            self::STATUS_ANNULLATA => 'bg-red text-red-fg',
            self::STATUS_RECUPERO => 'bg-orange text-orange-fg',
            default => 'bg-default text-default-fg',
        };
    }

    /**
     * Calcola le ore dalla differenza tra ora_inizio e ora_fine
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->ora_inizio && $this->ora_fine) {
                $inizio = strtotime($this->ora_inizio);
                $fine = strtotime($this->ora_fine);
                $this->ore = round(($fine - $inizio) / 3600, 2);
            }
            return true;
        }
        return false;
    }
}
