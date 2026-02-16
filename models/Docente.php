<?php

namespace app\models;

use Yii;
use app\components\base\BaseActiveRecord;
use yii\db\Expression;

/**
 * Model per la tabella docente.
 *
 * @property string $codice_fiscale
 * @property bool $codice_fiscale_validato
 * @property string $cognome
 * @property string $nome
 * @property string|null $data_nascita
 * @property string|null $luogo_nascita
 * @property string|null $indirizzo
 * @property string|null $cap
 * @property string|null $citta
 * @property string|null $provincia
 * @property string|null $telefono
 * @property string|null $email
 * @property string|null $titolo_studio
 * @property string|null $specializzazioni
 * @property string|null $provenienza
 * @property string|null $partita_iva
 * @property string|null $iban
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Istanza[] $istanze
 * @property Assegnazione[] $assegnazioni
 * @property Calendario[] $calendari
 * @property ValutazioneDocente[] $valutazioni
 * @property Documentazione[] $documentazioni
 * @property DocenteTag[] $docenteTags
 * @property TagCurriculum[] $tags
 * @property User $createdByUser
 * @property User $updatedByUser
 * @property User $deletedByUser
 */
class Docente extends BaseActiveRecord
{
    // Costanti per provenienza docente
    const PROVENIENZA_TP = 'TP'; // Tipo Professionista (mondo del lavoro)
    const PROVENIENZA_RU = 'RU'; // Ricercatore Universitario

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%docente}}';
    }

    /**
     * @inheritdoc
     * Override necessario perché la PK non è 'id' ma 'codice_fiscale'
     */
    public static function primaryKey()
    {
        return ['codice_fiscale'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['codice_fiscale', 'cognome', 'nome'], 'required'],
            [['data_nascita'], 'date', 'format' => 'php:Y-m-d'],
            [['specializzazioni', 'note'], 'string'],
            [['codice_fiscale'], 'string', 'max' => 16],
            [['cognome', 'nome', 'luogo_nascita', 'citta', 'email'], 'string', 'max' => 100],
            [['indirizzo', 'titolo_studio'], 'string', 'max' => 255],
            [['cap'], 'string', 'max' => 5],
            [['provincia'], 'string', 'max' => 2],
            [['telefono'], 'string', 'max' => 20],
            [['partita_iva'], 'string', 'max' => 11],
            [['iban'], 'string', 'max' => 34],
            [['provenienza'], 'in', 'range' => [self::PROVENIENZA_TP, self::PROVENIENZA_RU]],
            [['codice_fiscale'], 'unique'],
            [['codice_fiscale'], 'match', 'pattern' => '/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i', 'message' => 'Codice fiscale non valido'],
            [['email'], 'email'],
            [['codice_fiscale_validato'], 'boolean'],
            [['cap'], 'match', 'pattern' => '/^\d{5}$/', 'message' => 'Il CAP deve essere di 5 cifre'],
            [['provincia'], 'match', 'pattern' => '/^[A-Z]{2}$/', 'message' => 'La provincia deve essere di 2 lettere maiuscole'],
            [['partita_iva'], 'match', 'pattern' => '/^\d{11}$/', 'message' => 'La partita IVA deve essere di 11 cifre'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Normalizza il codice fiscale in uppercase
            $this->codice_fiscale = strtoupper($this->codice_fiscale);
            // Normalizza la provincia in uppercase
            if ($this->provincia) {
                $this->provincia = strtoupper($this->provincia);
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'codice_fiscale' => 'Codice Fiscale',
            'codice_fiscale_validato' => 'CF Validato',
            'cognome' => 'Cognome',
            'nome' => 'Nome',
            'data_nascita' => 'Data di Nascita',
            'luogo_nascita' => 'Luogo di Nascita',
            'indirizzo' => 'Indirizzo',
            'cap' => 'CAP',
            'citta' => 'Città',
            'provincia' => 'Provincia',
            'telefono' => 'Telefono',
            'email' => 'Email',
            'titolo_studio' => 'Titolo di Studio',
            'specializzazioni' => 'Specializzazioni',
            'provenienza' => 'Provenienza',
            'partita_iva' => 'Partita IVA',
            'iban' => 'IBAN',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
            'deleted_at' => 'Eliminato il',
            'created_by' => 'Creato da',
            'updated_by' => 'Modificato da',
            'deleted_by' => 'Eliminato da',
        ];
    }

    /**
     * Attributi sensibili da mascherare nel audit log
     */
    protected function auditMaskedAttributes()
    {
        return ['iban'];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIstanze()
    {
        return $this->hasMany(Istanza::class, ['docente_codice_fiscale' => 'codice_fiscale']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssegnazioni()
    {
        return $this->hasMany(Assegnazione::class, ['docente_codice_fiscale' => 'codice_fiscale']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentazioni()
    {
        return $this->hasMany(Documentazione::class, ['docente_codice_fiscale' => 'codice_fiscale']);
    }

    public function getCalendari()
    {
        return $this->hasMany(Calendario::class, ['docente_codice_fiscale' => 'codice_fiscale']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValutazioni()
    {
        return $this->hasMany(ValutazioneDocente::class, ['docente_codice_fiscale' => 'codice_fiscale']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocenteTags()
    {
        return $this->hasMany(DocenteTag::class, ['docente_codice_fiscale' => 'codice_fiscale']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(TagCurriculum::class, ['id' => 'tag_id'])
            ->via('docenteTags');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'deleted_by']);
    }

    /**
     * Restituisce il nome completo (Cognome Nome)
     * @return string
     */
    public function getNomeCompleto()
    {
        return $this->cognome . ' ' . $this->nome;
    }

    /**
     * Restituisce l'indirizzo completo
     * @return string
     */
    public function getIndirizzoCompleto()
    {
        $parts = array_filter([
            $this->indirizzo,
            $this->cap,
            $this->citta,
            $this->provincia ? '(' . $this->provincia . ')' : null,
        ]);

        return implode(' ', $parts) ?: '-';
    }

    /**
     * Restituisce le iniziali per l'avatar
     * @return string
     */
    public function getIniziali()
    {
        return strtoupper(substr($this->nome, 0, 1) . substr($this->cognome, 0, 1));
    }

    /**
     * Restituisce lista per dropdown
     * @return array
     */
    public static function getList()
    {
        return static::find()
            ->select([new Expression("CONCAT(cognome, ' ', nome)"), 'codice_fiscale'])
            ->orderBy(['cognome' => SORT_ASC, 'nome' => SORT_ASC])
            ->indexBy('codice_fiscale')
            ->column();
    }

    /**
     * Calcola la media delle valutazioni
     * @return float|null
     */
    public function getMediaValutazioni()
    {
        return $this->getValutazioni()->average('punteggio_globale');
    }

    /**
     * Conta le valutazioni ricevute
     * @return int
     */
    public function getValutazioniCount()
    {
        return $this->getValutazioni()->count();
    }

    /**
     * Calcola le ore totali di docenza
     * @return float
     */
    public function getOreTotali()
    {
        return (float) $this->getCalendari()
            ->where(['status' => 'completata'])
            ->sum('ore') ?: 0;
    }

    /**
     * Conta i moduli assegnati
     * @return int
     */
    public function getModuliCount()
    {
        return $this->getAssegnazioni()->count();
    }

    /**
     * Conta le istanze presentate
     * @return int
     */
    public function getIstanzeCount()
    {
        return $this->getIstanze()->count();
    }

    /**
     * Restituisce i tag raggruppati per categoria
     * @return array
     */
    public function getTagsByCategoria()
    {
        $tags = $this->getDocenteTags()
            ->with(['tag', 'tag.categoria'])
            ->all();

        $result = [];
        foreach ($tags as $docenteTag) {
            if ($docenteTag->tag && $docenteTag->tag->categoria) {
                $categoriaId = $docenteTag->tag->categoria_id;
                if (!isset($result[$categoriaId])) {
                    $result[$categoriaId] = [
                        'categoria' => $docenteTag->tag->categoria,
                        'tags' => [],
                    ];
                }
                $result[$categoriaId]['tags'][] = $docenteTag;
            }
        }

        // Ordina per ordine categoria
        uasort($result, function($a, $b) {
            return $a['categoria']->ordine <=> $b['categoria']->ordine;
        });

        return $result;
    }

    /**
     * Trova docenti con lo stesso tag
     * @param int $tagId
     * @param int $limit
     * @return Docente[]
     */
    public function findDocentiConStessoTag($tagId, $limit = 10)
    {
        return static::find()
            ->innerJoin('docente_tag dt', 'dt.docente_codice_fiscale = docente.codice_fiscale')
            ->where(['dt.tag_id' => $tagId])
            ->andWhere(['!=', 'docente.codice_fiscale', $this->codice_fiscale])
            ->limit($limit)
            ->all();
    }

    /**
     * Restituisce le stelle HTML per la media valutazioni
     * @return string
     */
    public function getValutazioneStarsHtml()
    {
        $media = $this->getMediaValutazioni();
        if ($media === null) {
            return '<span class="text-muted">Nessuna valutazione</span>';
        }

        $html = '';
        $mediaArrotondata = round($media);
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $mediaArrotondata) {
                $html .= '<i class="ti ti-star-filled text-yellow"></i>';
            } else {
                $html .= '<i class="ti ti-star text-muted"></i>';
            }
        }
        $html .= ' <span class="text-muted small">(' . number_format($media, 1) . '/5)</span>';

        return $html;
    }

    /**
     * Restituisce le opzioni per il dropdown provenienza
     * @return array
     */
    public static function getProvenienzaList()
    {
        return [
            self::PROVENIENZA_TP => 'Tipo Professionista (mondo del lavoro)',
            self::PROVENIENZA_RU => 'Ricercatore Universitario',
        ];
    }

    /**
     * Restituisce la label della provenienza
     * @return string|null
     */
    public function getProvenienzaLabel()
    {
        return self::getProvenienzaList()[$this->provenienza] ?? null;
    }

    /**
     * Restituisce il badge HTML per la provenienza
     * @return string
     */
    public function getProvenienzaBadge()
    {
        if (!$this->provenienza) {
            return '<span class="badge bg-secondary">Non specificato</span>';
        }

        $class = $this->provenienza === self::PROVENIENZA_RU ? 'bg-primary' : 'bg-info';
        $label = $this->provenienza === self::PROVENIENZA_RU ? 'RU' : 'TP';
        $title = $this->getProvenienzaLabel();

        return '<span class="badge ' . $class . '" title="' . $title . '">' . $label . '</span>';
    }

    /**
     * Verifica se il docente è un ricercatore universitario
     * @return bool
     */
    public function isRicercatoreUniversitario()
    {
        return $this->provenienza === self::PROVENIENZA_RU;
    }

    /**
     * Verifica se il docente è un tipo professionista
     * @return bool
     */
    public function isTipoProfessionista()
    {
        return $this->provenienza === self::PROVENIENZA_TP;
    }
}
