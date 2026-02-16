<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella pagamento.
 *
 * @property int $id
 * @property int $contratto_id
 * @property float $importo_lordo
 * @property float|null $ritenute
 * @property float $importo_netto
 * @property string|null $data_pagamento
 * @property string|null $data_scadenza
 * @property string $tipo
 * @property string|null $riferimento_fattura
 * @property string|null $numero_mandato
 * @property string|null $cro_bonifico
 * @property string $status
 * @property string|null $note
 * @property int $ore_extra
 * @property string|null $ore_extra_status
 * @property int|null $ore_extra_approvato_da
 * @property string|null $ore_extra_approvato_at
 * @property string|null $ore_extra_note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Contratto $contratto
 * @property User $oreExtraApprovatoDaUser
 */
class Pagamento extends BaseActiveRecord
{
    const TIPO_ACCONTO = 'acconto';
    const TIPO_SALDO = 'saldo';
    const TIPO_RATA = 'rata';
    const TIPO_RIMBORSO = 'rimborso';

    const STATUS_PROGRAMMATO = 'programmato';
    const STATUS_IN_ATTESA = 'in_attesa';
    const STATUS_PAGATO = 'pagato';
    const STATUS_ANNULLATO = 'annullato';

    // Costanti ore extra status
    const ORE_EXTRA_PENDENTE = 'pendente';
    const ORE_EXTRA_APPROVATO = 'approvato';
    const ORE_EXTRA_RIFIUTATO = 'rifiutato';

    public static function tableName()
    {
        return '{{%pagamento}}';
    }

    public function rules()
    {
        return [
            [['contratto_id', 'importo_lordo', 'importo_netto', 'tipo', 'status'], 'required'],
            [['contratto_id'], 'integer'],
            [['importo_lordo', 'ritenute', 'importo_netto'], 'number'],
            [['data_pagamento', 'data_scadenza'], 'date', 'format' => 'php:Y-m-d'],
            [['tipo'], 'in', 'range' => [self::TIPO_ACCONTO, self::TIPO_SALDO, self::TIPO_RATA, self::TIPO_RIMBORSO]],
            [['status'], 'in', 'range' => [self::STATUS_PROGRAMMATO, self::STATUS_IN_ATTESA, self::STATUS_PAGATO, self::STATUS_ANNULLATO]],
            [['riferimento_fattura'], 'string', 'max' => 100],
            [['numero_mandato', 'cro_bonifico'], 'string', 'max' => 50],
            [['note', 'ore_extra_note'], 'string'],
            [['ore_extra'], 'boolean'],
            [['ore_extra_status'], 'in', 'range' => [self::ORE_EXTRA_PENDENTE, self::ORE_EXTRA_APPROVATO, self::ORE_EXTRA_RIFIUTATO]],
            [['ore_extra_approvato_da'], 'integer'],
            [['ore_extra_approvato_at'], 'safe'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contratto_id' => 'Contratto',
            'importo_lordo' => 'Importo Lordo',
            'ritenute' => 'Ritenute',
            'importo_netto' => 'Importo Netto',
            'data_pagamento' => 'Data Pagamento',
            'data_scadenza' => 'Data Scadenza',
            'tipo' => 'Tipo',
            'riferimento_fattura' => 'Riferimento Fattura',
            'numero_mandato' => 'Numero Mandato',
            'cro_bonifico' => 'CRO Bonifico',
            'status' => 'Stato',
            'note' => 'Note',
            'ore_extra' => 'Ore Extra',
            'ore_extra_status' => 'Stato Ore Extra',
            'ore_extra_approvato_da' => 'Ore Extra Approvato Da',
            'ore_extra_approvato_at' => 'Ore Extra Approvato Il',
            'ore_extra_note' => 'Note Ore Extra',
        ];
    }

    public function getContratto()
    {
        return $this->hasOne(Contratto::class, ['id' => 'contratto_id']);
    }

    public static function getTipoLabels()
    {
        return [
            self::TIPO_ACCONTO => 'Acconto',
            self::TIPO_SALDO => 'Saldo',
            self::TIPO_RATA => 'Rata',
            self::TIPO_RIMBORSO => 'Rimborso',
        ];
    }

    public function getTipoLabel()
    {
        return self::getTipoLabels()[$this->tipo] ?? $this->tipo;
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_PROGRAMMATO => 'Programmato',
            self::STATUS_IN_ATTESA => 'In Attesa',
            self::STATUS_PAGATO => 'Pagato',
            self::STATUS_ANNULLATO => 'Annullato',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_PROGRAMMATO => 'bg-secondary',
            self::STATUS_IN_ATTESA => 'bg-warning',
            self::STATUS_PAGATO => 'bg-success',
            self::STATUS_ANNULLATO => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Restituisce la classe badge CSS per il tipo di pagamento.
     * @return string
     */
    public function getTipoBadgeClass()
    {
        return match ($this->tipo) {
            self::TIPO_ACCONTO => 'bg-info',
            self::TIPO_SALDO => 'bg-primary',
            self::TIPO_RATA => 'bg-cyan',
            self::TIPO_RIMBORSO => 'bg-warning',
            default => 'bg-secondary',
        };
    }

    // --- Ore extra ---

    public function getOreExtraApprovatoDaUser()
    {
        return $this->hasOne(User::class, ['id' => 'ore_extra_approvato_da']);
    }

    /**
     * Verifica se il pagamento riguarda ore extra
     * @return bool
     */
    public function isOreExtra()
    {
        return (bool) $this->ore_extra;
    }

    /**
     * Verifica se le ore extra sono approvate
     * @return bool
     */
    public function isOreExtraApprovato()
    {
        return $this->ore_extra_status === self::ORE_EXTRA_APPROVATO;
    }

    public static function getOreExtraStatusLabels()
    {
        return [
            self::ORE_EXTRA_PENDENTE => 'Pendente',
            self::ORE_EXTRA_APPROVATO => 'Approvato',
            self::ORE_EXTRA_RIFIUTATO => 'Rifiutato',
        ];
    }

    public function getOreExtraStatusLabel()
    {
        if (!$this->ore_extra_status) {
            return null;
        }
        return self::getOreExtraStatusLabels()[$this->ore_extra_status] ?? $this->ore_extra_status;
    }

    public function getOreExtraStatusBadgeClass()
    {
        return match ($this->ore_extra_status) {
            self::ORE_EXTRA_PENDENTE => 'bg-warning',
            self::ORE_EXTRA_APPROVATO => 'bg-success',
            self::ORE_EXTRA_RIFIUTATO => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
