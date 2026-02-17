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
 * @property string|null $filiera
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
 * @property Assegnazione[] $assegnazioni
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
            [['filiera'], 'string', 'max' => 100],
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
            'filiera' => 'Filiera',
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

    public function getAssegnazioni()
    {
        return $this->hasMany(Assegnazione::class, ['modulo_id' => 'id']);
    }

    public function getCalendari()
    {
        return $this->hasMany(Calendario::class, ['modulo_id' => 'id']);
    }

    public function getIscrizioni()
    {
        return $this->hasMany(Iscrizione::class, ['modulo_id' => 'id']);
    }

    public function getValutazioni()
    {
        return $this->hasMany(ValutazioneDocente::class, ['modulo_id' => 'id']);
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
            self::STATUS_BOZZA => 'bg-orange text-orange-fg',
            self::STATUS_APERTO => 'bg-green text-green-fg',
            self::STATUS_IN_CORSO => 'bg-blue text-blue-fg',
            self::STATUS_CONCLUSO => 'bg-azure text-azure-fg',
            self::STATUS_ANNULLATO => 'bg-red text-red-fg',
        ];
        return $classes[$this->status] ?? 'bg-default text-default-fg';
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

    // --- Metodi di rendicontazione ---

    /**
     * Restituisce il contratto attivo del modulo, attraversando Assegnazione → Contratto.
     * Prende la prima assegnazione non annullata che ha un contratto associato.
     * @return Contratto|null
     */
    public function getContrattoAttivo()
    {
        $assegnazione = $this->getAssegnazioni()
            ->andWhere(['not', ['status' => Assegnazione::STATUS_REVOCATO]])
            ->one();

        if ($assegnazione === null) {
            return null;
        }

        return $assegnazione->contratto;
    }

    /**
     * Restituisce il compenso contrattualizzato (dal contratto attivo).
     * @return float|null
     */
    public function getCompensoContrattualizzato()
    {
        $contratto = $this->getContrattoAttivo();
        return $contratto ? (float) $contratto->compenso_totale : null;
    }

    /**
     * Restituisce il totale pagato per questo modulo.
     * @return float
     */
    public function getTotalePagatoModulo()
    {
        $contratto = $this->getContrattoAttivo();
        return $contratto ? $contratto->getTotalePagato() : 0.0;
    }

    /**
     * Calcola la percentuale di pagamento rispetto al contrattualizzato.
     * @return float|null null se non c'e' contratto
     */
    public function getPercentualePagamento()
    {
        $contrattualizzato = $this->getCompensoContrattualizzato();
        if (!$contrattualizzato || $contrattualizzato <= 0) {
            return null;
        }

        return ($this->getTotalePagatoModulo() / $contrattualizzato) * 100;
    }
}
