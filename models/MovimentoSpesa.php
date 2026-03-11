<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella movimento_spesa.
 *
 * @property int $id
 * @property int $progetto_id
 * @property int $voce_spesa_id
 * @property string $descrizione
 * @property float $importo
 * @property string $data_movimento
 * @property string|null $data_documento
 * @property string|null $numero_documento
 * @property string|null $fornitore
 * @property string $tipo
 * @property string|null $riferimento_tipo
 * @property int|null $riferimento_id
 * @property string|null $allegato_path
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Progetto $progetto
 * @property VoceSpesa $voceSpesa
 */
class MovimentoSpesa extends BaseActiveRecord
{
    const TIPO_SPESA = 'spesa';
    const TIPO_ENTRATA = 'entrata';

    // Tipi di riferimento automatico
    const RIFERIMENTO_PAGAMENTO = 'pagamento';
    const RIFERIMENTO_RIMBORSO = 'rimborso';

    public static function tableName()
    {
        return '{{%movimento_spesa}}';
    }

    public function rules()
    {
        return [
            [['voce_spesa_id', 'descrizione', 'importo', 'data_movimento'], 'required'],
            [['progetto_id', 'voce_spesa_id', 'riferimento_id'], 'integer'],
            [['progetto_id'], 'default', 'value' => null],
            [['importo'], 'number'],
            [['data_movimento', 'data_documento'], 'date', 'format' => 'php:Y-m-d'],
            [['descrizione'], 'string', 'max' => 500],
            [['numero_documento'], 'string', 'max' => 100],
            [['fornitore'], 'string', 'max' => 255],
            [['tipo'], 'in', 'range' => [self::TIPO_SPESA, self::TIPO_ENTRATA]],
            [['riferimento_tipo'], 'string', 'max' => 50],
            [['allegato_path'], 'string', 'max' => 500],
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
            'voce_spesa_id' => 'Voce di Spesa',
            'descrizione' => 'Descrizione',
            'importo' => 'Importo (€)',
            'data_movimento' => 'Data Movimento',
            'data_documento' => 'Data Documento',
            'numero_documento' => 'N. Documento',
            'fornitore' => 'Fornitore',
            'tipo' => 'Tipo',
            'riferimento_tipo' => 'Tipo Riferimento',
            'riferimento_id' => 'ID Riferimento',
            'allegato_path' => 'Allegato',
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
     * @return \yii\db\ActiveQuery
     */
    public function getVoceSpesa()
    {
        return $this->hasOne(VoceSpesa::class, ['id' => 'voce_spesa_id']);
    }

    /**
     * Restituisce le opzioni per il dropdown tipo
     * @return array
     */
    public static function getTipoList()
    {
        return [
            self::TIPO_SPESA => 'Spesa',
            self::TIPO_ENTRATA => 'Entrata',
        ];
    }

    /**
     * Restituisce la label del tipo
     * @return string
     */
    public function getTipoLabel()
    {
        return self::getTipoList()[$this->tipo] ?? $this->tipo;
    }

    /**
     * Restituisce il badge HTML del tipo
     * @return string
     */
    public function getTipoBadge()
    {
        $class = match ($this->tipo) {
            self::TIPO_SPESA => 'bg-red text-red-fg',
            self::TIPO_ENTRATA => 'bg-green text-green-fg',
            default => 'bg-default text-default-fg',
        };
        return '<span class="badge ' . $class . '">' . $this->getTipoLabel() . '</span>';
    }

    /**
     * Verifica se il movimento e' automatico (collegato a pagamento/rimborso)
     * @return bool
     */
    public function isAutomatico()
    {
        return $this->riferimento_tipo !== null;
    }

    /**
     * Restituisce il badge per il riferimento
     * @return string
     */
    public function getRiferimentoBadge()
    {
        if (!$this->isAutomatico()) {
            return '<span class="badge bg-default text-default-fg">Manuale</span>';
        }
        $label = match ($this->riferimento_tipo) {
            self::RIFERIMENTO_PAGAMENTO => 'Da Pagamento',
            self::RIFERIMENTO_RIMBORSO => 'Da Rimborso',
            default => $this->riferimento_tipo,
        };
        return '<span class="badge bg-azure text-azure-fg">' . $label . '</span>';
    }

    // ---- Scopes ----

    /**
     * Filtra per progetto
     * @param int $progettoId
     * @return \yii\db\ActiveQuery
     */
    public static function perProgetto($progettoId)
    {
        return static::find()->where(['progetto_id' => $progettoId]);
    }

    /**
     * Filtra per anno
     * @param int $anno
     * @return \yii\db\ActiveQuery
     */
    public static function perAnno($anno)
    {
        return static::find()->andWhere(['YEAR(data_movimento)' => $anno]);
    }

    /**
     * Filtra per voce di spesa
     * @param int $voceId
     * @return \yii\db\ActiveQuery
     */
    public static function perVoce($voceId)
    {
        return static::find()->where(['voce_spesa_id' => $voceId]);
    }

    /**
     * Calcola il totale spese per un progetto
     * @param int $progettoId
     * @return float
     */
    public static function getTotaleSpese($progettoId)
    {
        return (float) static::find()
            ->where(['progetto_id' => $progettoId, 'tipo' => self::TIPO_SPESA])
            ->sum('importo') ?: 0;
    }

    /**
     * Calcola il totale entrate per un progetto
     * @param int $progettoId
     * @return float
     */
    public static function getTotaleEntrate($progettoId)
    {
        return (float) static::find()
            ->where(['progetto_id' => $progettoId, 'tipo' => self::TIPO_ENTRATA])
            ->sum('importo') ?: 0;
    }
}
