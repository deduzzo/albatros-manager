<?php

namespace app\models;

use app\components\base\BaseActiveRecord;
use Yii;

/**
 * Model per la tabella rendicontazione_ore.
 *
 * @property int $id
 * @property int $assegnazione_id
 * @property string $data
 * @property float $ore
 * @property string|null $ora_inizio
 * @property string|null $ora_fine
 * @property int $firma_digitale
 * @property string $status
 * @property string|null $note
 * @property string|null $motivo_rifiuto
 * @property int|null $approvato_da
 * @property string|null $approvato_at
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Assegnazione $assegnazione
 * @property Modulo $modulo
 * @property Docente $docente
 * @property User $approvatoDaUser
 */
class RendicontazioneOre extends BaseActiveRecord
{
    const STATUS_INSERITO = 'inserito';
    const STATUS_APPROVATO = 'approvato';
    const STATUS_RIFIUTATO = 'rifiutato';

    public static function tableName()
    {
        return '{{%rendicontazione_ore}}';
    }

    public function rules()
    {
        return [
            [['assegnazione_id', 'data', 'ore'], 'required'],
            [['assegnazione_id', 'firma_digitale', 'approvato_da'], 'integer'],
            [['ore'], 'number', 'min' => 0.25, 'max' => 24],
            [['data'], 'date', 'format' => 'php:Y-m-d'],
            [['ora_inizio', 'ora_fine'], 'time', 'format' => 'php:H:i:s'],
            [['status'], 'in', 'range' => [self::STATUS_INSERITO, self::STATUS_APPROVATO, self::STATUS_RIFIUTATO]],
            [['note', 'motivo_rifiuto'], 'string'],
            [['firma_digitale'], 'boolean'],
            [['approvato_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            // ora_fine deve essere dopo ora_inizio
            ['ora_fine', 'compare', 'compareAttribute' => 'ora_inizio', 'operator' => '>', 'message' => 'L\'ora fine deve essere successiva all\'ora inizio', 'when' => function ($model) {
                return !empty($model->ora_inizio) && !empty($model->ora_fine);
            }],
            // motivo_rifiuto obbligatorio se status = rifiutato
            ['motivo_rifiuto', 'required', 'when' => function ($model) {
                return $model->status === self::STATUS_RIFIUTATO;
            }, 'message' => 'Il motivo del rifiuto e\' obbligatorio'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'assegnazione_id' => 'Assegnazione',
            'data' => 'Data',
            'ore' => 'Ore',
            'ora_inizio' => 'Ora Inizio',
            'ora_fine' => 'Ora Fine',
            'firma_digitale' => 'Firma Digitale',
            'status' => 'Stato',
            'note' => 'Note',
            'motivo_rifiuto' => 'Motivo Rifiuto',
            'approvato_da' => 'Approvato Da',
            'approvato_at' => 'Approvato Il',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    // --- Relazioni ---

    public function getAssegnazione()
    {
        return $this->hasOne(Assegnazione::class, ['id' => 'assegnazione_id']);
    }

    public function getApprovatoDaUser()
    {
        return $this->hasOne(User::class, ['id' => 'approvato_da']);
    }

    /**
     * Relazione through: modulo via assegnazione
     */
    public function getModulo()
    {
        return $this->hasOne(Modulo::class, ['id' => 'modulo_id'])
            ->via('assegnazione');
    }

    /**
     * Relazione through: docente via assegnazione
     */
    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale'])
            ->via('assegnazione');
    }

    // --- Status helpers ---

    public static function getStatusLabels()
    {
        return [
            self::STATUS_INSERITO => 'Inserito',
            self::STATUS_APPROVATO => 'Approvato',
            self::STATUS_RIFIUTATO => 'Rifiutato',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_INSERITO => 'bg-warning',
            self::STATUS_APPROVATO => 'bg-success',
            self::STATUS_RIFIUTATO => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    // --- Metodi statici per calcolo ore ---

    /**
     * Somma ore approvate per una assegnazione
     * Usa leftJoin per compatibilita' con soft delete
     * @param int $assegnazioneId
     * @return float
     */
    public static function getTotaleOreApprovate($assegnazioneId)
    {
        return (float) static::find()
            ->where([
                'assegnazione_id' => $assegnazioneId,
                'status' => self::STATUS_APPROVATO,
            ])
            ->sum('ore') ?: 0;
    }

    /**
     * Somma ore in attesa di approvazione per una assegnazione
     * @param int $assegnazioneId
     * @return float
     */
    public static function getTotaleOreInserite($assegnazioneId)
    {
        return (float) static::find()
            ->where([
                'assegnazione_id' => $assegnazioneId,
                'status' => self::STATUS_INSERITO,
            ])
            ->sum('ore') ?: 0;
    }

    /**
     * Verifica se il totale ore approvate supera le ore contrattualizzate
     * @return bool
     */
    public function isOreSuperiorContratto()
    {
        $contratto = $this->assegnazione->contratto ?? null;
        if (!$contratto) {
            return false;
        }

        $totaleApprovate = static::getTotaleOreApprovate($this->assegnazione_id);
        return $totaleApprovate > $contratto->ore_contrattualizzate;
    }
}
