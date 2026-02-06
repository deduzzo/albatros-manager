<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella istanza.
 *
 * @property int $id
 * @property int $avviso_id
 * @property string $docente_codice_fiscale
 * @property int $modulo_id
 * @property string $data_presentazione
 * @property float|null $punteggio_titoli
 * @property float|null $punteggio_esperienza
 * @property float|null $punteggio_totale
 * @property int|null $posizione_graduatoria
 * @property string $status
 * @property string|null $note_valutazione
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 *
 * @property Docente $docente
 * @property Avviso $avviso
 * @property Modulo $modulo
 * @property IstanzaTag[] $istanzaTags
 * @property IstanzaDocumento[] $documenti
 * @property IstanzaSnapshot $snapshot
 */
class Istanza extends BaseActiveRecord
{
    const STATUS_PRESENTATA = 'presentata';
    const STATUS_IN_VALUTAZIONE = 'in_valutazione';
    const STATUS_VALUTATA = 'valutata';
    const STATUS_ACCETTATA = 'accettata';
    const STATUS_RIFIUTATA = 'rifiutata';
    const STATUS_RINUNCIA = 'rinuncia';

    public static function tableName()
    {
        return '{{%istanza}}';
    }

    public function rules()
    {
        return [
            [['avviso_id', 'docente_codice_fiscale', 'modulo_id', 'data_presentazione'], 'required'],
            [['avviso_id', 'modulo_id', 'posizione_graduatoria'], 'integer'],
            [['data_presentazione'], 'date', 'format' => 'php:Y-m-d'],
            [['punteggio_titoli', 'punteggio_esperienza', 'punteggio_totale'], 'number'],
            [['note_valutazione', 'note'], 'string'],
            [['docente_codice_fiscale'], 'string', 'max' => 16],
            [['status'], 'in', 'range' => ['presentata', 'in_valutazione', 'valutata', 'accettata', 'rifiutata', 'rinuncia']],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'avviso_id' => 'Avviso',
            'docente_codice_fiscale' => 'Docente',
            'modulo_id' => 'Modulo',
            'data_presentazione' => 'Data Presentazione',
            'punteggio_titoli' => 'Punteggio Titoli',
            'punteggio_esperienza' => 'Punteggio Esperienza',
            'punteggio_totale' => 'Punteggio Totale',
            'posizione_graduatoria' => 'Posizione',
            'status' => 'Stato',
            'note_valutazione' => 'Note Valutazione',
            'note' => 'Note',
        ];
    }

    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
    }

    public function getAvviso()
    {
        return $this->hasOne(Avviso::class, ['id' => 'avviso_id']);
    }

    public function getModulo()
    {
        return $this->hasOne(Modulo::class, ['id' => 'modulo_id']);
    }

    public function getIstanzaTags()
    {
        return $this->hasMany(IstanzaTag::class, ['istanza_id' => 'id']);
    }

    public function getDocumenti()
    {
        return $this->hasMany(IstanzaDocumento::class, ['istanza_id' => 'id']);
    }

    public function getSnapshot()
    {
        return $this->hasOne(IstanzaSnapshot::class, ['istanza_id' => 'id']);
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_PRESENTATA => 'Presentata',
            self::STATUS_IN_VALUTAZIONE => 'In Valutazione',
            self::STATUS_VALUTATA => 'Valutata',
            self::STATUS_ACCETTATA => 'Accettata',
            self::STATUS_RIFIUTATA => 'Rifiutata',
            self::STATUS_RINUNCIA => 'Rinuncia',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_PRESENTATA => 'bg-info',
            self::STATUS_IN_VALUTAZIONE => 'bg-warning',
            self::STATUS_VALUTATA => 'bg-primary',
            self::STATUS_ACCETTATA => 'bg-success',
            self::STATUS_RIFIUTATA => 'bg-danger',
            self::STATUS_RINUNCIA => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Restituisce il numero di tag non ancora promossi
     */
    public function getTagsNonPromossiCount()
    {
        return IstanzaTag::find()
            ->where(['istanza_id' => $this->id])
            ->andWhere(['promosso_at' => null])
            ->count();
    }

    /**
     * Promuove tutti i tag non ancora promossi al profilo docente
     */
    public function promuoviTuttiTags()
    {
        $count = 0;
        foreach ($this->istanzaTags as $tag) {
            if (!$tag->isPromosso() && $tag->promuovi()) {
                $count++;
            }
        }
        return $count;
    }
}
