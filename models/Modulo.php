<?php

namespace app\models;

use app\components\base\BaseActiveRecord;
use yii\db\Expression;

/**
 * Model per la tabella modulo (corso).
 *
 * @property int $id
 * @property int $avviso_id
 * @property int $sede_id
 * @property string $codice
 * @property string $titolo
 * @property string|null $descrizione
 * @property int $ore_totali
 * @property float|null $compenso_orario
 * @property string|null $data_inizio
 * @property string|null $data_fine
 * @property string|null $requisiti_docente
 * @property int|null $max_allievi
 * @property string $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Avviso $avviso
 * @property Sede $sede
 * @property Istanza[] $istanze
 */
class Modulo extends BaseActiveRecord
{
    const STATUS_BOZZA = 'bozza';
    const STATUS_APERTO = 'aperto';
    const STATUS_IN_CORSO = 'in_corso';
    const STATUS_CONCLUSO = 'concluso';
    const STATUS_ANNULLATO = 'annullato';

    public static function tableName()
    {
        return '{{%modulo}}';
    }

    public function rules()
    {
        return [
            [['avviso_id', 'sede_id', 'codice', 'titolo', 'ore_totali'], 'required'],
            [['avviso_id', 'sede_id', 'ore_totali', 'max_allievi'], 'integer'],
            [['descrizione', 'requisiti_docente', 'note'], 'string'],
            [['compenso_orario'], 'number'],
            [['data_inizio', 'data_fine'], 'date', 'format' => 'php:Y-m-d'],
            [['codice'], 'string', 'max' => 50],
            [['titolo'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_BOZZA, self::STATUS_APERTO, self::STATUS_IN_CORSO, self::STATUS_CONCLUSO, self::STATUS_ANNULLATO]],
            [['data_fine'], 'compare', 'compareAttribute' => 'data_inizio', 'operator' => '>=', 'message' => 'La data fine deve essere successiva alla data inizio'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'avviso_id' => 'Avviso/Bando',
            'sede_id' => 'Sede',
            'codice' => 'Codice',
            'titolo' => 'Titolo',
            'descrizione' => 'Descrizione',
            'ore_totali' => 'Ore Totali',
            'compenso_orario' => 'Compenso Orario (€)',
            'data_inizio' => 'Data Inizio',
            'data_fine' => 'Data Fine',
            'requisiti_docente' => 'Requisiti Docente',
            'max_allievi' => 'Max Allievi',
            'status' => 'Stato',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    public function getAvviso()
    {
        return $this->hasOne(Avviso::class, ['id' => 'avviso_id']);
    }

    public function getSede()
    {
        return $this->hasOne(Sede::class, ['id' => 'sede_id']);
    }

    public function getIstanze()
    {
        return $this->hasMany(Istanza::class, ['modulo_id' => 'id']);
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_BOZZA => 'Bozza',
            self::STATUS_APERTO => 'Aperto',
            self::STATUS_IN_CORSO => 'In Corso',
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
        $classes = [
            self::STATUS_BOZZA => 'bg-warning',
            self::STATUS_APERTO => 'bg-success',
            self::STATUS_IN_CORSO => 'bg-primary',
            self::STATUS_CONCLUSO => 'bg-info',
            self::STATUS_ANNULLATO => 'bg-danger',
        ];
        return $classes[$this->status] ?? 'bg-secondary';
    }

    /**
     * Verifica se il modulo accetta iscrizioni
     */
    public function isApertoIscrizioni()
    {
        return $this->status === self::STATUS_APERTO && $this->avviso && $this->avviso->isAperto();
    }

    /**
     * Calcola il compenso totale
     */
    public function getCompensoTotale()
    {
        if ($this->compenso_orario && $this->ore_totali) {
            return $this->compenso_orario * $this->ore_totali;
        }
        return null;
    }

    public static function getList()
    {
        return static::find()
            ->select([new Expression("CONCAT(codice, ' - ', titolo)"), 'id'])
            ->orderBy(['codice' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    /**
     * Restituisce moduli aperti per iscrizioni
     */
    public static function findApertiPerIscrizioni()
    {
        return static::find()
            ->alias('m')
            ->innerJoin('avviso a', 'a.id = m.avviso_id')
            ->where(['m.status' => self::STATUS_APERTO])
            ->andWhere(['a.status' => Avviso::STATUS_PUBBLICATO])
            ->andWhere(['>=', 'a.data_scadenza', date('Y-m-d')]);
    }

    public function getIstanzeCount()
    {
        return $this->getIstanze()->count();
    }
}
