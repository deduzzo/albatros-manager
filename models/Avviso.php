<?php

namespace app\models;

use app\components\base\BaseActiveRecord;
use yii\db\Expression;

/**
 * Model per la tabella avviso (bando).
 *
 * @property int $id
 * @property int $progetto_id
 * @property string $codice_avviso
 * @property string $titolo
 * @property string|null $descrizione
 * @property string|null $data_pubblicazione
 * @property string|null $data_scadenza
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
 * @property Modulo[] $moduli
 * @property Istanza[] $istanze
 * @property AvvisoDocumentoTipo[] $documentiRichiesti
 */
class Avviso extends BaseActiveRecord
{
    const STATUS_BOZZA = 'bozza';
    const STATUS_PUBBLICATO = 'pubblicato';
    const STATUS_CHIUSO = 'chiuso';
    const STATUS_ANNULLATO = 'annullato';

    public static function tableName()
    {
        return '{{%avviso}}';
    }

    public function rules()
    {
        return [
            [['progetto_id', 'codice_avviso', 'titolo'], 'required'],
            [['progetto_id'], 'integer'],
            [['descrizione', 'note'], 'string'],
            [['data_pubblicazione', 'data_scadenza'], 'date', 'format' => 'php:Y-m-d'],
            [['codice_avviso'], 'string', 'max' => 100],
            [['titolo'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_BOZZA, self::STATUS_PUBBLICATO, self::STATUS_CHIUSO, self::STATUS_ANNULLATO]],
            [['data_scadenza'], 'compare', 'compareAttribute' => 'data_pubblicazione', 'operator' => '>=', 'message' => 'La scadenza deve essere successiva alla pubblicazione'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'progetto_id' => 'Progetto',
            'codice_avviso' => 'Codice Avviso',
            'titolo' => 'Titolo',
            'descrizione' => 'Descrizione',
            'data_pubblicazione' => 'Data Pubblicazione',
            'data_scadenza' => 'Data Scadenza',
            'status' => 'Stato',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    public function getProgetto()
    {
        return $this->hasOne(Progetto::class, ['id' => 'progetto_id']);
    }

    public function getModuli()
    {
        return $this->hasMany(Modulo::class, ['avviso_id' => 'id']);
    }

    public function getIstanze()
    {
        return $this->hasMany(Istanza::class, ['avviso_id' => 'id']);
    }

    public function getDocumentiRichiesti()
    {
        return $this->hasMany(AvvisoDocumentoTipo::class, ['avviso_id' => 'id'])
            ->orderBy(['ordine' => SORT_ASC]);
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_BOZZA => 'Bozza',
            self::STATUS_PUBBLICATO => 'Pubblicato',
            self::STATUS_CHIUSO => 'Chiuso',
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
            self::STATUS_PUBBLICATO => 'bg-green text-green-fg',
            self::STATUS_CHIUSO => 'bg-default text-default-fg',
            self::STATUS_ANNULLATO => 'bg-red text-red-fg',
        ];
        return $classes[$this->status] ?? 'bg-default text-default-fg';
    }

    /**
     * Verifica se il bando è aperto per le iscrizioni
     */
    public function isAperto()
    {
        if ($this->status !== self::STATUS_PUBBLICATO) {
            return false;
        }
        if ($this->data_scadenza && strtotime($this->data_scadenza) < strtotime('today')) {
            return false;
        }
        return true;
    }

    /**
     * Restituisce solo i bandi aperti per le iscrizioni
     */
    public static function findAperti()
    {
        return static::find()
            ->where(['status' => self::STATUS_PUBBLICATO])
            ->andWhere(['>=', 'data_scadenza', date('Y-m-d')]);
    }

    public static function getList()
    {
        return static::find()
            ->select([new Expression("CONCAT(codice_avviso, ' - ', titolo)"), 'id'])
            ->orderBy(['codice_avviso' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    public function getModuliCount()
    {
        return $this->getModuli()->count();
    }

    public function getIstanzeCount()
    {
        return $this->getIstanze()->count();
    }

    // --- Metodi di rendicontazione finanziaria ---

    /**
     * Totale compenso previsto (ore_totali * compenso_orario) di tutti i moduli dell'avviso.
     * @return float
     */
    public function getTotaleCompensoPrevisto()
    {
        $result = Modulo::find()
            ->alias('m')
            ->where(['m.avviso_id' => $this->id])
            ->andWhere(['m.deleted_at' => null])
            ->sum(new Expression('m.ore_totali * m.compenso_orario'));

        return (float) ($result ?: 0);
    }

    /**
     * Totale compensi contrattualizzati di tutti i moduli dell'avviso.
     * @return float
     */
    public function getTotaleContrattualizzato()
    {
        $result = Contratto::find()
            ->alias('c')
            ->leftJoin('assegnazione ass', 'ass.id = c.assegnazione_id AND ass.deleted_at IS NULL')
            ->leftJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
            ->where(['m.avviso_id' => $this->id])
            ->andWhere(['c.deleted_at' => null])
            ->sum('c.compenso_totale');

        return (float) ($result ?: 0);
    }

    /**
     * Totale pagamenti effettuati (status = 'pagato') per tutti i moduli dell'avviso.
     * @return float
     */
    public function getTotalePagato()
    {
        $result = Pagamento::find()
            ->alias('p')
            ->leftJoin('contratto c', 'c.id = p.contratto_id AND c.deleted_at IS NULL')
            ->leftJoin('assegnazione ass', 'ass.id = c.assegnazione_id AND ass.deleted_at IS NULL')
            ->leftJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
            ->where(['m.avviso_id' => $this->id])
            ->andWhere(['p.status' => Pagamento::STATUS_PAGATO])
            ->andWhere(['p.deleted_at' => null])
            ->sum('p.importo_netto');

        return (float) ($result ?: 0);
    }

    /**
     * Totale pagamenti in attesa o programmati per tutti i moduli dell'avviso.
     * @return float
     */
    public function getTotaleInAttesa()
    {
        $result = Pagamento::find()
            ->alias('p')
            ->leftJoin('contratto c', 'c.id = p.contratto_id AND c.deleted_at IS NULL')
            ->leftJoin('assegnazione ass', 'ass.id = c.assegnazione_id AND ass.deleted_at IS NULL')
            ->leftJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
            ->where(['m.avviso_id' => $this->id])
            ->andWhere(['p.status' => [Pagamento::STATUS_IN_ATTESA, Pagamento::STATUS_PROGRAMMATO]])
            ->andWhere(['p.deleted_at' => null])
            ->sum('p.importo_netto');

        return (float) ($result ?: 0);
    }

    /**
     * Restituisce dati di rendicontazione per ogni modulo dell'avviso.
     * @return array Array di array con chiavi: modulo, docente, contratto, contrattualizzato, pagato, percentuale
     */
    public function getRendicontoModuli()
    {
        $moduli = $this->moduli;
        $result = [];

        foreach ($moduli as $modulo) {
            $assegnazione = $modulo->getAssegnazioni()->one();
            $docente = $assegnazione ? $assegnazione->docente : null;
            $contratto = $modulo->getContrattoAttivo();
            $contrattualizzato = $modulo->getCompensoContrattualizzato();
            $pagato = $modulo->getTotalePagatoModulo();

            // Ore rendicontate
            $oreContrattualizzate = $contratto ? (int) $contratto->ore_contrattualizzate : 0;
            $oreApprovate = $assegnazione ? RendicontazioneOre::getTotaleOreApprovate($assegnazione->id) : 0;

            $result[] = [
                'modulo' => $modulo,
                'docente' => $docente,
                'assegnazione' => $assegnazione,
                'contratto' => $contratto,
                'contrattualizzato' => $contrattualizzato,
                'pagato' => $pagato,
                'percentuale' => $modulo->getPercentualePagamento(),
                'ore_contrattualizzate' => $oreContrattualizzate,
                'ore_approvate' => $oreApprovate,
                'saldo_rimanente' => $contratto ? $contratto->getSaldoRimanente() : 0,
            ];
        }

        return $result;
    }
}
