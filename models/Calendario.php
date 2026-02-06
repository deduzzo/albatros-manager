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
 *
 * @property Docente $docente
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

    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
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
}
