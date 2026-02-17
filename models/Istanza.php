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
 * @property string|null $esito_cts
 * @property string|null $note_valutazione
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 *
 * @property Docente $docente
 * @property Avviso $avviso
 * @property Modulo $modulo
 * @property Assegnazione $assegnazione
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

    // Costanti esito CTS (Commissione Tecnico Scientifica)
    const ESITO_CTS_ASSEGNATO = 'assegnato';
    const ESITO_CTS_IDONEO = 'idoneo';
    const ESITO_CTS_NON_IDONEO = 'non_idoneo';

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
            [['esito_cts'], 'in', 'range' => [self::ESITO_CTS_ASSEGNATO, self::ESITO_CTS_IDONEO, self::ESITO_CTS_NON_IDONEO]],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
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
            'esito_cts' => 'Esito CTS',
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

    public function getAssegnazione()
    {
        return $this->hasOne(Assegnazione::class, ['istanza_id' => 'id']);
    }

    public function getStoricoEsiti()
    {
        return $this->hasMany(StoricoEsito::class, ['istanza_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
    }

    public function getUltimoEsito()
    {
        return $this->hasOne(StoricoEsito::class, ['istanza_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
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

    /**
     * Dopo il salvataggio, crea snapshot se esito_cts cambia a 'assegnato'
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Se esito_cts e' cambiato a 'assegnato' e non esiste gia uno snapshot
        if (isset($changedAttributes['esito_cts'])
            && $this->esito_cts === self::ESITO_CTS_ASSEGNATO
            && !IstanzaSnapshot::findOne(['istanza_id' => $this->id])
        ) {
            // Raccogli tag iscrizione
            $tagsIscrizione = [];
            foreach ($this->istanzaTags as $it) {
                $tagsIscrizione[] = [
                    'tag_id' => $it->tag_id,
                    'nome' => $it->tag->nome ?? null,
                    'livello' => $it->livello,
                ];
            }
            IstanzaSnapshot::creaSnapshot($this, $tagsIscrizione);
        }
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
            self::STATUS_PRESENTATA => 'bg-azure text-azure-fg',
            self::STATUS_IN_VALUTAZIONE => 'bg-orange text-orange-fg',
            self::STATUS_VALUTATA => 'bg-blue text-blue-fg',
            self::STATUS_ACCETTATA => 'bg-green text-green-fg',
            self::STATUS_RIFIUTATA => 'bg-red text-red-fg',
            self::STATUS_RINUNCIA => 'bg-default text-default-fg',
            default => 'bg-default text-default-fg',
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

    /**
     * Restituisce le opzioni per il dropdown esito CTS
     * @return array
     */
    public static function getEsitoCtsLabels()
    {
        return [
            self::ESITO_CTS_ASSEGNATO => 'Assegnato',
            self::ESITO_CTS_IDONEO => 'Idoneo',
            self::ESITO_CTS_NON_IDONEO => 'Non Idoneo',
        ];
    }

    /**
     * Restituisce la label dell'esito CTS
     * @return string|null
     */
    public function getEsitoCtsLabel()
    {
        return self::getEsitoCtsLabels()[$this->esito_cts] ?? null;
    }

    /**
     * Restituisce il badge HTML per l'esito CTS
     * @return string
     */
    public function getEsitoCtsBadgeHtml()
    {
        if (!$this->esito_cts) {
            return '<span class="badge bg-default text-default-fg">Non valutato</span>';
        }

        $class = match ($this->esito_cts) {
            self::ESITO_CTS_ASSEGNATO => 'bg-green text-green-fg',
            self::ESITO_CTS_IDONEO => 'bg-azure text-azure-fg',
            self::ESITO_CTS_NON_IDONEO => 'bg-red text-red-fg',
            default => 'bg-default text-default-fg',
        };

        return '<span class="badge ' . $class . '">' . $this->getEsitoCtsLabel() . '</span>';
    }

    /**
     * Verifica se il docente è stato assegnato
     * @return bool
     */
    public function isAssegnato()
    {
        return $this->esito_cts === self::ESITO_CTS_ASSEGNATO;
    }

    /**
     * Verifica se il docente è idoneo (in long list)
     * @return bool
     */
    public function isIdoneo()
    {
        return $this->esito_cts === self::ESITO_CTS_IDONEO;
    }

    /**
     * Verifica se il docente è non idoneo
     * @return bool
     */
    public function isNonIdoneo()
    {
        return $this->esito_cts === self::ESITO_CTS_NON_IDONEO;
    }

    /**
     * Verifica se il docente è nella long list (assegnato o idoneo)
     * @return bool
     */
    public function isInLongList()
    {
        return in_array($this->esito_cts, [self::ESITO_CTS_ASSEGNATO, self::ESITO_CTS_IDONEO]);
    }
}
