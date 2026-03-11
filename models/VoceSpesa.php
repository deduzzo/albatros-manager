<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella voce_spesa.
 *
 * @property int $id
 * @property int $categoria_id
 * @property string|null $codice
 * @property string $nome
 * @property string|null $descrizione
 * @property int $ordinamento
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property VoceSpesaCategoria $categoria
 * @property MovimentoSpesa[] $movimenti
 */
class VoceSpesa extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%voce_spesa}}';
    }

    public function rules()
    {
        return [
            [['categoria_id', 'nome'], 'required'],
            [['categoria_id', 'ordinamento'], 'integer'],
            [['codice'], 'string', 'max' => 20],
            [['nome'], 'string', 'max' => 255],
            [['descrizione'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'categoria_id' => 'Categoria',
            'codice' => 'Codice',
            'nome' => 'Nome',
            'descrizione' => 'Descrizione',
            'ordinamento' => 'Ordinamento',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
            'deleted_at' => 'Eliminato il',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(VoceSpesaCategoria::class, ['id' => 'categoria_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMovimenti()
    {
        return $this->hasMany(MovimentoSpesa::class, ['voce_spesa_id' => 'id']);
    }

    /**
     * Restituisce la label composta codice - nome
     * @return string
     */
    public function getLabel()
    {
        return ($this->codice ? $this->codice . ' - ' : '') . $this->nome;
    }

    /**
     * Restituisce la label completa con categoria
     * @return string
     */
    public function getLabelCompleta()
    {
        $cat = $this->categoria;
        $prefix = $cat ? $cat->codice . ' > ' : '';
        return $prefix . $this->getLabel();
    }

    /**
     * Lista per dropdown raggruppata per categoria
     * @return array [categoria_label => [id => voce_label]]
     */
    public static function getListGrouped()
    {
        $voci = static::find()
            ->with('categoria')
            ->orderBy(['categoria_id' => SORT_ASC, 'ordinamento' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($voci as $voce) {
            $catLabel = $voce->categoria ? $voce->categoria->getLabel() : 'Senza categoria';
            $result[$catLabel][$voce->id] = $voce->getLabel();
        }
        return $result;
    }

    /**
     * Lista piatta per dropdown
     * @return array
     */
    public static function getList()
    {
        return static::find()
            ->select(["CONCAT(COALESCE(codice, ''), ' - ', nome)", 'id'])
            ->orderBy(['categoria_id' => SORT_ASC, 'ordinamento' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }
}
