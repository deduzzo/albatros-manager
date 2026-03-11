<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella tranche_finanziamento.
 *
 * @property int $id
 * @property int $progetto_id
 * @property float $importo
 * @property string|null $data_accredito
 * @property string|null $data_prevista
 * @property int|null $numero_tranche
 * @property string|null $riferimento
 * @property string $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Progetto $progetto
 */
class TrancheFinanziamento extends BaseActiveRecord
{
    const STATUS_PREVISTA = 'prevista';
    const STATUS_ACCREDITATA = 'accreditata';
    const STATUS_PARZIALE = 'parziale';

    public static function tableName()
    {
        return '{{%tranche_finanziamento}}';
    }

    public function rules()
    {
        return [
            [['progetto_id', 'importo'], 'required'],
            [['progetto_id', 'numero_tranche'], 'integer'],
            [['importo'], 'number', 'min' => 0.01],
            [['data_accredito', 'data_prevista'], 'date', 'format' => 'php:Y-m-d'],
            [['riferimento'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_PREVISTA, self::STATUS_ACCREDITATA, self::STATUS_PARZIALE]],
            [['note'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'progetto_id' => 'Progetto',
            'importo' => 'Importo (€)',
            'data_accredito' => 'Data Accredito',
            'data_prevista' => 'Data Prevista',
            'numero_tranche' => 'N. Tranche',
            'riferimento' => 'Riferimento',
            'status' => 'Stato',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProgetto()
    {
        return $this->hasOne(Progetto::class, ['id' => 'progetto_id']);
    }

    /**
     * Restituisce le opzioni per il dropdown status
     * @return array
     */
    public static function getStatusLabels()
    {
        return [
            self::STATUS_PREVISTA => 'Prevista',
            self::STATUS_ACCREDITATA => 'Accreditata',
            self::STATUS_PARZIALE => 'Parziale',
        ];
    }

    /**
     * Restituisce la label dello status
     * @return string
     */
    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Restituisce la classe badge CSS per lo status
     * @return string
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_PREVISTA => 'bg-default text-default-fg',
            self::STATUS_ACCREDITATA => 'bg-green text-green-fg',
            self::STATUS_PARZIALE => 'bg-orange text-orange-fg',
            default => 'bg-default text-default-fg',
        };
    }

    /**
     * Restituisce il badge HTML dello status
     * @return string
     */
    public function getStatusBadge()
    {
        return '<span class="badge ' . $this->getStatusBadgeClass() . '">' . $this->getStatusLabel() . '</span>';
    }

    /**
     * Verifica se la tranche e' stata accreditata
     * @return bool
     */
    public function isAccreditata()
    {
        return $this->status === self::STATUS_ACCREDITATA;
    }

    /**
     * Restituisce la label della tranche
     * @return string
     */
    public function getLabel()
    {
        $parts = [];
        if ($this->numero_tranche) {
            $parts[] = 'Tranche #' . $this->numero_tranche;
        }
        if ($this->riferimento) {
            $parts[] = $this->riferimento;
        }
        return implode(' - ', $parts) ?: 'Tranche #' . $this->id;
    }

    /**
     * Calcola il totale accreditato per un progetto
     * @param int $progettoId
     * @return float
     */
    public static function getTotaleAccreditato($progettoId)
    {
        return (float) static::find()
            ->where(['progetto_id' => $progettoId, 'status' => self::STATUS_ACCREDITATA])
            ->sum('importo') ?: 0;
    }

    /**
     * Calcola il totale previsto per un progetto
     * @param int $progettoId
     * @return float
     */
    public static function getTotalePrevisto($progettoId)
    {
        return (float) static::find()
            ->where(['progetto_id' => $progettoId])
            ->sum('importo') ?: 0;
    }
}
