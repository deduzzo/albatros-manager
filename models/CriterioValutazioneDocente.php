<?php

namespace app\models;

use app\components\base\BaseActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Model per la tabella criterio_valutazione_docente.
 *
 * @property int $id
 * @property string $codice
 * @property string $nome
 * @property string|null $descrizione
 * @property int $scala_min
 * @property int $scala_max
 * @property int $ordinamento
 * @property int $attivo
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property ValutazioneDocentePunteggio[] $punteggi
 */
class CriterioValutazioneDocente extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%criterio_valutazione_docente}}';
    }

    public function rules()
    {
        return [
            [['codice', 'nome'], 'required'],
            [['codice'], 'string', 'max' => 50],
            [['nome'], 'string', 'max' => 255],
            [['descrizione'], 'string'],
            [['scala_min', 'scala_max', 'ordinamento'], 'integer'],
            [['attivo'], 'boolean'],
            [['scala_max'], 'compare', 'compareAttribute' => 'scala_min', 'operator' => '>=', 'message' => 'La scala massima deve essere >= alla scala minima'],
            [['codice'], 'unique'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codice' => 'Codice',
            'nome' => 'Nome Criterio',
            'descrizione' => 'Descrizione',
            'scala_min' => 'Scala Min',
            'scala_max' => 'Scala Max',
            'ordinamento' => 'Ordinamento',
            'attivo' => 'Attivo',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    protected function auditExcludedAttributes()
    {
        return ['created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_by'];
    }

    protected function auditMaskedAttributes()
    {
        return [];
    }

    /**
     * Relazione con i punteggi assegnati.
     */
    public function getPunteggi()
    {
        return $this->hasMany(ValutazioneDocentePunteggio::class, ['criterio_id' => 'id']);
    }

    /**
     * Restituisce lista per dropdown: id => nome
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(
            static::find()->where(['attivo' => 1])->orderBy(['ordinamento' => SORT_ASC])->all(),
            'id',
            'nome'
        );
    }

    /**
     * Scope: solo criteri attivi, ordinati.
     * @return \yii\db\ActiveQuery
     */
    public static function findAttivi()
    {
        return static::find()->where(['attivo' => 1])->orderBy(['ordinamento' => SORT_ASC]);
    }

    /**
     * Restituisce tutti i criteri attivi come array di model.
     * @return static[]
     */
    public static function getAllAttivi()
    {
        return static::findAttivi()->all();
    }

    /**
     * Cerca un criterio per descrizione (match fuzzy per import CSV).
     * Restituisce il criterio con la descrizione più simile.
     *
     * @param string $testo Testo dell'intestazione CSV
     * @return static|null
     */
    public static function findByDescrizione($testo)
    {
        $testo = mb_strtolower(trim($testo));
        $criteri = static::findAttivi()->all();
        $bestMatch = null;
        $bestScore = 0;

        foreach ($criteri as $criterio) {
            $desc = mb_strtolower(trim($criterio->descrizione));
            // Match esatto sulla descrizione
            if ($desc === $testo || strpos($testo, $desc) !== false || strpos($desc, $testo) !== false) {
                return $criterio;
            }
            // Match parziale: calcoliamo similarità
            similar_text($testo, $desc, $percent);
            if ($percent > $bestScore && $percent > 70) {
                $bestScore = $percent;
                $bestMatch = $criterio;
            }
        }

        return $bestMatch;
    }

    /**
     * Badge attivo/disattivo per vista.
     * @return string
     */
    public function getAttivoBadge()
    {
        if ($this->attivo) {
            return '<span class="badge bg-green text-green-fg">Attivo</span>';
        }
        return '<span class="badge bg-red text-red-fg">Disattivo</span>';
    }
}
