<?php

namespace app\models;

use app\components\base\BaseActiveRecord;
use Yii;

/**
 * Model per la tabella contratto.
 *
 * @property int $id
 * @property int $assegnazione_id
 * @property string $tipo
 * @property string|null $numero_contratto
 * @property string $data_stipula
 * @property string $data_inizio
 * @property string $data_fine
 * @property string|null $data_inizio_lezioni
 * @property string|null $data_fine_lezioni
 * @property float $compenso_orario
 * @property int $ore_contrattualizzate
 * @property float $compenso_totale
 * @property float|null $ritenute
 * @property float|null $netto_docente
 * @property string|null $documento_path
 * @property string $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Assegnazione $assegnazione
 * @property Pagamento[] $pagamenti
 */
class Contratto extends BaseActiveRecord
{
    // Costanti tipo contratto
    const TIPO_COCOCO = 'cococo';
    const TIPO_PARTITA_IVA = 'partita_iva';
    const TIPO_OCCASIONALE = 'occasionale';

    // Costanti status
    const STATUS_BOZZA = 'bozza';
    const STATUS_FIRMATO = 'firmato';
    const STATUS_ATTIVO = 'attivo';
    const STATUS_SOSPESO = 'sospeso';
    const STATUS_CONCLUSO = 'concluso';
    const STATUS_ANNULLATO = 'annullato';

    public static function tableName()
    {
        return '{{%contratto}}';
    }

    public function rules()
    {
        return [
            [['assegnazione_id', 'tipo', 'data_stipula', 'data_inizio', 'data_fine', 'compenso_orario', 'ore_contrattualizzate', 'compenso_totale'], 'required'],
            [['assegnazione_id', 'ore_contrattualizzate'], 'integer'],
            [['data_stipula', 'data_inizio', 'data_fine', 'data_inizio_lezioni', 'data_fine_lezioni'], 'date', 'format' => 'php:Y-m-d'],
            [['compenso_orario', 'compenso_totale', 'ritenute', 'netto_docente'], 'number'],
            [['note'], 'string'],
            [['numero_contratto'], 'string', 'max' => 50],
            [['numero_contratto'], 'unique'],
            [['documento_path'], 'string', 'max' => 500],
            [['tipo'], 'in', 'range' => [self::TIPO_COCOCO, self::TIPO_PARTITA_IVA, self::TIPO_OCCASIONALE]],
            [['status'], 'in', 'range' => [self::STATUS_BOZZA, self::STATUS_FIRMATO, self::STATUS_ATTIVO, self::STATUS_SOSPESO, self::STATUS_CONCLUSO, self::STATUS_ANNULLATO]],
            // Validazioni date
            [['data_fine'], 'compare', 'compareAttribute' => 'data_inizio', 'operator' => '>=', 'message' => 'La data fine deve essere successiva alla data inizio'],
            [['data_fine_lezioni'], 'compare', 'compareAttribute' => 'data_inizio_lezioni', 'operator' => '>=', 'message' => 'La data fine lezioni deve essere successiva alla data inizio lezioni', 'when' => function($model) {
                return !empty($model->data_inizio_lezioni) && !empty($model->data_fine_lezioni);
            }],
            // Compenso totale deve essere >= compenso orario * ore
            ['compenso_totale', 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => 'Il compenso totale deve essere maggiore di zero'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'assegnazione_id' => 'Assegnazione',
            'tipo' => 'Tipo Contratto',
            'numero_contratto' => 'Numero Contratto',
            'data_stipula' => 'Data Stipula',
            'data_inizio' => 'Data Inizio Contratto',
            'data_fine' => 'Data Fine Contratto',
            'data_inizio_lezioni' => 'Data Inizio Lezioni',
            'data_fine_lezioni' => 'Data Fine Lezioni',
            'compenso_orario' => 'Compenso Orario (€)',
            'ore_contrattualizzate' => 'Ore Contrattualizzate',
            'compenso_totale' => 'Compenso Totale (€)',
            'ritenute' => 'Ritenute (€)',
            'netto_docente' => 'Netto Docente (€)',
            'documento_path' => 'Documento',
            'status' => 'Stato',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    public function getAssegnazione()
    {
        return $this->hasOne(Assegnazione::class, ['id' => 'assegnazione_id']);
    }

    public function getPagamenti()
    {
        return $this->hasMany(Pagamento::class, ['contratto_id' => 'id']);
    }

    public static function getTipoList()
    {
        return [
            self::TIPO_COCOCO => 'Co.Co.Co',
            self::TIPO_PARTITA_IVA => 'Partita IVA',
            self::TIPO_OCCASIONALE => 'Occasionale',
        ];
    }

    public function getTipoLabel()
    {
        return self::getTipoList()[$this->tipo] ?? $this->tipo;
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_BOZZA => 'Bozza',
            self::STATUS_FIRMATO => 'Firmato',
            self::STATUS_ATTIVO => 'Attivo',
            self::STATUS_SOSPESO => 'Sospeso',
            self::STATUS_CONCLUSO => 'Concluso',
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
            self::STATUS_BOZZA => 'bg-default text-default-fg',
            self::STATUS_FIRMATO => 'bg-azure text-azure-fg',
            self::STATUS_ATTIVO => 'bg-green text-green-fg',
            self::STATUS_SOSPESO => 'bg-orange text-orange-fg',
            self::STATUS_CONCLUSO => 'bg-blue text-blue-fg',
            self::STATUS_ANNULLATO => 'bg-red text-red-fg',
            default => 'bg-default text-default-fg',
        };
    }

    /**
     * Verifica se le lezioni stanno per terminare (entro N giorni)
     * @param int $giorni
     * @return bool
     */
    public function isLezioniInScadenza($giorni = 7)
    {
        if (!$this->data_fine_lezioni || $this->status !== self::STATUS_ATTIVO) {
            return false;
        }

        $dataFine = new \DateTime($this->data_fine_lezioni);
        $oggi = new \DateTime();
        $diff = $oggi->diff($dataFine)->days;

        return $diff <= $giorni && $dataFine >= $oggi;
    }

    /**
     * Calcola i giorni rimanenti alle lezioni
     * @return int|null
     */
    public function getGiorniRimanentiLezioni()
    {
        if (!$this->data_fine_lezioni) {
            return null;
        }

        $dataFine = new \DateTime($this->data_fine_lezioni);
        $oggi = new \DateTime();

        if ($dataFine < $oggi) {
            return 0;
        }

        return $oggi->diff($dataFine)->days;
    }

    /**
     * Restituisce il badge per i giorni rimanenti
     * @return string
     */
    public function getGiorniRimanentiBadge()
    {
        $giorni = $this->getGiorniRimanentiLezioni();

        if ($giorni === null) {
            return '<span class="badge bg-default text-default-fg">N/D</span>';
        }

        if ($giorni == 0) {
            return '<span class="badge bg-red text-red-fg">Concluso</span>';
        }

        if ($giorni <= 7) {
            return '<span class="badge bg-red text-red-fg">' . $giorni . ' giorni</span>';
        }

        if ($giorni <= 14) {
            return '<span class="badge bg-orange text-orange-fg">' . $giorni . ' giorni</span>';
        }

        return '<span class="badge bg-green text-green-fg">' . $giorni . ' giorni</span>';
    }

    /**
     * Verifica se il contratto è attivo
     * @return bool
     */
    public function isAttivo()
    {
        return $this->status === self::STATUS_ATTIVO;
    }

    /**
     * Verifica se il contratto è concluso
     * @return bool
     */
    public function isConcluso()
    {
        return $this->status === self::STATUS_CONCLUSO;
    }

    /**
     * Calcola il totale già pagato
     * @return float
     */
    public function getTotalePagato()
    {
        return (float) $this->getPagamenti()->andWhere(['status' => Pagamento::STATUS_PAGATO])->sum('importo_netto') ?: 0;
    }

    /**
     * Calcola il saldo rimanente da pagare
     * @return float
     */
    public function getSaldoRimanente()
    {
        return $this->compenso_totale - $this->getTotalePagato();
    }

    /**
     * Verifica se il contratto è completamente saldato
     * @return bool
     */
    public function isSaldato()
    {
        return abs($this->getSaldoRimanente()) < 0.01; // Tolleranza per arrotondamenti
    }
}
