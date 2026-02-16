<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella iscrizione.
 *
 * @property int $id
 * @property int $modulo_id
 * @property string $allievo_codice_fiscale
 * @property string $data_iscrizione
 * @property string|null $data_ritiro
 * @property string|null $motivo_ritiro
 * @property string $status
 * @property float|null $percentuale_frequenza
 * @property string|null $esito_finale
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Modulo $modulo
 * @property Allievo $allievo
 */
class Iscrizione extends BaseActiveRecord
{
    const STATUS_ISCRITTO = 'iscritto';
    const STATUS_FREQUENTANTE = 'frequentante';
    const STATUS_RITIRATO = 'ritirato';
    const STATUS_COMPLETATO = 'completato';
    const STATUS_NON_AMMESSO = 'non_ammesso';

    const ESITO_IDONEO = 'idoneo';
    const ESITO_NON_IDONEO = 'non_idoneo';
    const ESITO_RITIRATO = 'ritirato';

    public static function tableName()
    {
        return '{{%iscrizione}}';
    }

    public function rules()
    {
        return [
            [['modulo_id', 'allievo_codice_fiscale', 'data_iscrizione'], 'required'],
            [['modulo_id'], 'integer'],
            [['allievo_codice_fiscale'], 'string', 'max' => 16],
            [['data_iscrizione', 'data_ritiro'], 'date', 'format' => 'php:Y-m-d'],
            [['motivo_ritiro'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [
                self::STATUS_ISCRITTO,
                self::STATUS_FREQUENTANTE,
                self::STATUS_RITIRATO,
                self::STATUS_COMPLETATO,
                self::STATUS_NON_AMMESSO
            ]],
            [['percentuale_frequenza'], 'number', 'min' => 0, 'max' => 100],
            [['esito_finale'], 'in', 'range' => [self::ESITO_IDONEO, self::ESITO_NON_IDONEO, self::ESITO_RITIRATO]],
            [['note'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modulo_id' => 'Modulo',
            'allievo_codice_fiscale' => 'Allievo',
            'data_iscrizione' => 'Data Iscrizione',
            'data_ritiro' => 'Data Ritiro',
            'motivo_ritiro' => 'Motivo Ritiro',
            'status' => 'Stato',
            'percentuale_frequenza' => 'Percentuale Frequenza',
            'esito_finale' => 'Esito Finale',
            'note' => 'Note',
        ];
    }

    public function getModulo()
    {
        return $this->hasOne(Modulo::class, ['id' => 'modulo_id']);
    }

    public function getAllievo()
    {
        return $this->hasOne(Allievo::class, ['codice_fiscale' => 'allievo_codice_fiscale']);
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_ISCRITTO => 'Iscritto',
            self::STATUS_FREQUENTANTE => 'Frequentante',
            self::STATUS_RITIRATO => 'Ritirato',
            self::STATUS_COMPLETATO => 'Completato',
            self::STATUS_NON_AMMESSO => 'Non Ammesso',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_ISCRITTO => 'bg-info',
            self::STATUS_FREQUENTANTE => 'bg-primary',
            self::STATUS_RITIRATO => 'bg-warning',
            self::STATUS_COMPLETATO => 'bg-success',
            self::STATUS_NON_AMMESSO => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public static function getEsitoLabels()
    {
        return [
            self::ESITO_IDONEO => 'Idoneo',
            self::ESITO_NON_IDONEO => 'Non Idoneo',
            self::ESITO_RITIRATO => 'Ritirato',
        ];
    }

    public function getEsitoLabel()
    {
        return self::getEsitoLabels()[$this->esito_finale] ?? null;
    }
}
