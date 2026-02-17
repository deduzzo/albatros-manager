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
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Modulo $modulo
 * @property Docente $docente
 * @property Istanza $istanza
 * @property Contratto $contratto
 * @property RendicontazioneOre[] $rendicontazioniOre
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
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
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

    public function getModulo()
    {
        return $this->hasOne(Modulo::class, ['id' => 'modulo_id']);
    }

    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
    }

    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'istanza_id']);
    }

    public function getContratto()
    {
        return $this->hasOne(Contratto::class, ['assegnazione_id' => 'id']);
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

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_ASSEGNATO => 'bg-azure text-azure-fg',
            self::STATUS_ACCETTATO => 'bg-green text-green-fg',
            self::STATUS_RIFIUTATO => 'bg-orange text-orange-fg',
            self::STATUS_REVOCATO => 'bg-red text-red-fg',
            default => 'bg-default text-default-fg',
        };
    }

    // --- Rendicontazione ore ---

    public function getRendicontazioniOre()
    {
        return $this->hasMany(RendicontazioneOre::class, ['assegnazione_id' => 'id']);
    }

    /**
     * Totale ore rendicontate (tutti gli stati, escluse rifiutate)
     * @return float
     */
    public function getTotaleOreRendicontate()
    {
        return (float) RendicontazioneOre::find()
            ->where(['assegnazione_id' => $this->id])
            ->andWhere(['!=', 'status', RendicontazioneOre::STATUS_RIFIUTATO])
            ->sum('ore') ?: 0;
    }

    /**
     * Totale ore approvate
     * @return float
     */
    public function getTotaleOreApprovate()
    {
        return RendicontazioneOre::getTotaleOreApprovate($this->id);
    }

    /**
     * Ore contrattualizzate dal contratto associato
     * @return int|null
     */
    public function getOreContrattualizzate()
    {
        return $this->contratto->ore_contrattualizzate ?? null;
    }
}
