<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella tag_curriculum.
 *
 * @property int $id
 * @property int $categoria_id
 * @property string $codice
 * @property string $nome
 * @property string|null $descrizione
 * @property string|null $keywords
 * @property int $ordine
 * @property bool $attivo
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property TagCategoria $categoria
 * @property DocenteTag[] $docenteTags
 * @property Docente[] $docenti
 * @property User $createdByUser
 * @property User $updatedByUser
 * @property User $deletedByUser
 */
class TagCurriculum extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tag_curriculum}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categoria_id', 'codice', 'nome'], 'required'],
            [['categoria_id', 'ordine'], 'integer'],
            [['descrizione'], 'string'],
            [['attivo'], 'boolean'],
            [['codice'], 'string', 'max' => 30],
            [['nome'], 'string', 'max' => 100],
            [['keywords'], 'string', 'max' => 255],
            [['codice'], 'unique'],
            [['codice'], 'match', 'pattern' => '/^[a-z0-9-]+$/', 'message' => 'Il codice può contenere solo lettere minuscole, numeri e trattini'],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => TagCategoria::class, 'targetAttribute' => ['categoria_id' => 'id']],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'categoria_id' => 'Categoria',
            'codice' => 'Codice',
            'nome' => 'Nome',
            'descrizione' => 'Descrizione',
            'keywords' => 'Keywords',
            'ordine' => 'Ordine',
            'attivo' => 'Attivo',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
            'deleted_at' => 'Eliminato il',
            'created_by' => 'Creato da',
            'updated_by' => 'Modificato da',
            'deleted_by' => 'Eliminato da',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(TagCategoria::class, ['id' => 'categoria_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocenteTags()
    {
        return $this->hasMany(DocenteTag::class, ['tag_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocenti()
    {
        return $this->hasMany(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale'])
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
     * Restituisce lista per dropdown raggruppata per categoria
     * @return array
     */
    public static function getListByCategory()
    {
        $tags = static::find()
            ->with('categoria')
            ->where(['attivo' => true])
            ->orderBy(['categoria_id' => SORT_ASC, 'ordine' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($tags as $tag) {
            $categoryName = $tag->categoria ? $tag->categoria->nome : 'Altro';
            $result[$categoryName][$tag->id] = $tag->nome;
        }

        return $result;
    }

    /**
     * Restituisce lista semplice per dropdown
     * @return array
     */
    public static function getList()
    {
        return static::find()
            ->select(['nome', 'id'])
            ->where(['attivo' => true])
            ->orderBy('nome')
            ->indexBy('id')
            ->column();
    }

    /**
     * Conta i docenti che hanno questo tag
     * @return int
     */
    public function getDocentiCount()
    {
        return $this->getDocenteTags()->count();
    }

    /**
     * Restituisce il badge HTML del tag
     * @return string
     */
    public function getBadgeHtml()
    {
        $colore = $this->categoria ? $this->categoria->colore : '#206bc4';
        return sprintf(
            '<span class="badge" style="background-color: %s; color: #fff;">%s</span>',
            htmlspecialchars($colore),
            htmlspecialchars($this->nome)
        );
    }
}
