<?php

namespace app\models;

use app\components\base\BaseActiveRecord;
use yii\db\Expression;

/**
 * Model per la tabella progetto.
 *
 * @property int $id
 * @property int $fonte_finanziamento_id
 * @property int|null $conto_corrente_id
 * @property string $codice_progetto
 * @property string $titolo
 * @property string|null $descrizione
 * @property float|null $budget_totale
 * @property int|null $numero_studenti
 * @property string|null $data_inizio
 * @property string|null $data_fine
 * @property string $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property FonteFinanziamento $fonteFinanziamento
 * @property Avviso[] $avvisi
 */
class Progetto extends BaseActiveRecord
{
    const STATUS_BOZZA = 'bozza';
    const STATUS_ATTIVO = 'attivo';
    const STATUS_SOSPESO = 'sospeso';
    const STATUS_CONCLUSO = 'concluso';

    public static function tableName()
    {
        return '{{%progetto}}';
    }

    public function rules()
    {
        return [
            [['fonte_finanziamento_id', 'codice_progetto', 'titolo'], 'required'],
            [['fonte_finanziamento_id', 'conto_corrente_id', 'numero_studenti'], 'integer'],
            [['descrizione', 'note'], 'string'],
            [['budget_totale'], 'number'],
            [['numero_studenti'], 'integer', 'min' => 1, 'max' => 100],
            [['data_inizio', 'data_fine'], 'date', 'format' => 'php:Y-m-d'],
            [['codice_progetto'], 'string', 'max' => 100],
            [['titolo'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_BOZZA, self::STATUS_ATTIVO, self::STATUS_SOSPESO, self::STATUS_CONCLUSO]],
            [['codice_progetto'], 'unique'],
            [['data_fine'], 'compare', 'compareAttribute' => 'data_inizio', 'operator' => '>=', 'message' => 'La data fine deve essere successiva alla data inizio'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fonte_finanziamento_id' => 'Fonte Finanziamento',
            'conto_corrente_id' => 'Conto Corrente',
            'codice_progetto' => 'Codice Progetto',
            'titolo' => 'Titolo',
            'descrizione' => 'Descrizione',
            'budget_totale' => 'Budget Totale',
            'numero_studenti' => 'Numero Studenti',
            'data_inizio' => 'Data Inizio',
            'data_fine' => 'Data Fine',
            'status' => 'Stato',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    public function getFonteFinanziamento()
    {
        return $this->hasOne(FonteFinanziamento::class, ['id' => 'fonte_finanziamento_id']);
    }

    public function getAvvisi()
    {
        return $this->hasMany(Avviso::class, ['progetto_id' => 'id']);
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_BOZZA => 'Bozza',
            self::STATUS_ATTIVO => 'Attivo',
            self::STATUS_SOSPESO => 'Sospeso',
            self::STATUS_CONCLUSO => 'Concluso',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        $classes = [
            self::STATUS_BOZZA => 'bg-warning',
            self::STATUS_ATTIVO => 'bg-success',
            self::STATUS_SOSPESO => 'bg-secondary',
            self::STATUS_CONCLUSO => 'bg-info',
        ];
        return $classes[$this->status] ?? 'bg-secondary';
    }

    public static function getList()
    {
        return static::find()
            ->select([new Expression("CONCAT(codice_progetto, ' - ', titolo)"), 'id'])
            ->orderBy(['codice_progetto' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    public function getAvvisiCount()
    {
        return $this->getAvvisi()->count();
    }
}
