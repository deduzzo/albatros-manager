<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella voce_spesa_categoria.
 *
 * @property int $id
 * @property string $codice
 * @property string $nome
 * @property string $sezione
 * @property int $ordinamento
 * @property int|null $fonte_finanziamento_id
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property VoceSpesa[] $vociSpesa
 * @property FonteFinanziamento $fonteFinanziamento
 */
class VoceSpesaCategoria extends BaseActiveRecord
{
    const SEZIONE_COSTI_DIRETTI = 'costi_diretti';
    const SEZIONE_COSTI_INDIRETTI = 'costi_indiretti';
    const SEZIONE_ATTIVITA_ACCESSORIE = 'attivita_accessorie';

    public static function tableName()
    {
        return '{{%voce_spesa_categoria}}';
    }

    public function rules()
    {
        return [
            [['codice', 'nome', 'sezione'], 'required'],
            [['codice'], 'string', 'max' => 20],
            [['nome'], 'string', 'max' => 255],
            [['sezione'], 'in', 'range' => [self::SEZIONE_COSTI_DIRETTI, self::SEZIONE_COSTI_INDIRETTI, self::SEZIONE_ATTIVITA_ACCESSORIE]],
            [['ordinamento', 'fonte_finanziamento_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codice' => 'Codice',
            'nome' => 'Nome',
            'sezione' => 'Sezione',
            'ordinamento' => 'Ordinamento',
            'fonte_finanziamento_id' => 'Fonte Finanziamento',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
            'deleted_at' => 'Eliminato il',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVociSpesa()
    {
        return $this->hasMany(VoceSpesa::class, ['categoria_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFonteFinanziamento()
    {
        return $this->hasOne(FonteFinanziamento::class, ['id' => 'fonte_finanziamento_id']);
    }

    /**
     * Restituisce le opzioni per il dropdown sezione
     * @return array
     */
    public static function getSezioneList()
    {
        return [
            self::SEZIONE_COSTI_DIRETTI => 'Costi Diretti',
            self::SEZIONE_COSTI_INDIRETTI => 'Costi Indiretti',
            self::SEZIONE_ATTIVITA_ACCESSORIE => 'Attività Accessorie',
        ];
    }

    /**
     * Restituisce la label della sezione
     * @return string
     */
    public function getSezioneLabel()
    {
        return self::getSezioneList()[$this->sezione] ?? $this->sezione;
    }

    /**
     * Restituisce il badge HTML della sezione
     * @return string
     */
    public function getSezioneBadge()
    {
        $class = match ($this->sezione) {
            self::SEZIONE_COSTI_DIRETTI => 'bg-blue text-blue-fg',
            self::SEZIONE_COSTI_INDIRETTI => 'bg-purple text-purple-fg',
            self::SEZIONE_ATTIVITA_ACCESSORIE => 'bg-teal text-teal-fg',
            default => 'bg-default text-default-fg',
        };
        return '<span class="badge ' . $class . '">' . $this->getSezioneLabel() . '</span>';
    }

    /**
     * Restituisce la label composta codice - nome
     * @return string
     */
    public function getLabel()
    {
        return $this->codice . ' - ' . $this->nome;
    }

    /**
     * Restituisce le categorie raggruppate per sezione
     * @return array [sezione => VoceSpesaCategoria[]]
     */
    public static function getVociBySezione()
    {
        $categorie = static::find()
            ->orderBy(['ordinamento' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($categorie as $cat) {
            $result[$cat->sezione][] = $cat;
        }
        return $result;
    }

    /**
     * Lista per dropdown
     * @return array
     */
    public static function getList()
    {
        return static::find()
            ->select(["CONCAT(codice, ' - ', nome)", 'id'])
            ->orderBy(['ordinamento' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }
}
