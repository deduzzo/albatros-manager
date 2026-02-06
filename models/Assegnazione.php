<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella assegnazione.
 *
 * @property int $id
 * @property int $modulo_id
 * @property string $docente_codice_fiscale
 * @property int|null $istanza_id
 * @property string $data_assegnazione
 * @property string|null $delibera_cts
 * @property string|null $data_delibera
 * @property string $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 *
 * @property Docente $docente
 * @property Istanza $istanza
 */
class Assegnazione extends BaseActiveRecord
{
    const STATUS_ASSEGNATO = 'assegnato';
    const STATUS_ACCETTATO = 'accettato';
    const STATUS_RIFIUTATO = 'rifiutato';
    const STATUS_REVOCATO = 'revocato';

    public static function tableName()
    {
        return '{{%assegnazione}}';
    }

    public function rules()
    {
        return [
            [['modulo_id', 'docente_codice_fiscale', 'data_assegnazione'], 'required'],
            [['modulo_id', 'istanza_id'], 'integer'],
            [['data_assegnazione', 'data_delibera'], 'date', 'format' => 'php:Y-m-d'],
            [['note'], 'string'],
            [['docente_codice_fiscale'], 'string', 'max' => 16],
            [['delibera_cts'], 'string', 'max' => 100],
            [['status'], 'in', 'range' => ['assegnato', 'accettato', 'rifiutato', 'revocato']],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modulo_id' => 'Modulo',
            'docente_codice_fiscale' => 'Docente',
            'istanza_id' => 'Istanza',
            'data_assegnazione' => 'Data Assegnazione',
            'delibera_cts' => 'Delibera CTS',
            'data_delibera' => 'Data Delibera',
            'status' => 'Stato',
            'note' => 'Note',
        ];
    }

    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
    }

    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'istanza_id']);
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_ASSEGNATO => 'Assegnato',
            self::STATUS_ACCETTATO => 'Accettato',
            self::STATUS_RIFIUTATO => 'Rifiutato',
            self::STATUS_REVOCATO => 'Revocato',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }
}
