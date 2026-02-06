<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Model per la tabella istanza_tag.
 * Tag selezionati per ogni iscrizione, con possibilità di promozione al profilo docente.
 *
 * @property int $id
 * @property int $istanza_id
 * @property int $tag_id
 * @property int|null $livello
 * @property string|null $note
 * @property string $created_at
 * @property string|null $promosso_at
 * @property int|null $promosso_by
 *
 * @property Istanza $istanza
 * @property TagCurriculum $tag
 * @property User $promossoDaUser
 */
class IstanzaTag extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%istanza_tag}}';
    }

    public function rules()
    {
        return [
            [['istanza_id', 'tag_id'], 'required'],
            [['istanza_id', 'tag_id', 'livello', 'promosso_by'], 'integer'],
            [['note'], 'string', 'max' => 255],
            [['livello'], 'integer', 'min' => 1, 'max' => 5],
            [['created_at', 'promosso_at'], 'safe'],
            [['istanza_id', 'tag_id'], 'unique', 'targetAttribute' => ['istanza_id', 'tag_id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'istanza_id' => 'Istanza',
            'tag_id' => 'Tag',
            'livello' => 'Livello',
            'note' => 'Note',
            'created_at' => 'Aggiunto il',
            'promosso_at' => 'Promosso il',
            'promosso_by' => 'Promosso da',
        ];
    }

    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'istanza_id']);
    }

    public function getTag()
    {
        return $this->hasOne(TagCurriculum::class, ['id' => 'tag_id']);
    }

    public function getPromossoDaUser()
    {
        return $this->hasOne(User::class, ['id' => 'promosso_by']);
    }

    /**
     * Verifica se il tag è stato promosso al profilo docente
     */
    public function isPromosso()
    {
        return $this->promosso_at !== null;
    }

    /**
     * Promuove il tag al profilo docente
     */
    public function promuovi()
    {
        $istanza = $this->istanza;
        if (!$istanza || !$istanza->docente) {
            return false;
        }

        // Verifica se il tag esiste già nel profilo docente
        $exists = DocenteTag::find()
            ->where([
                'docente_codice_fiscale' => $istanza->docente_codice_fiscale,
                'tag_id' => $this->tag_id,
            ])
            ->exists();

        if (!$exists) {
            $docenteTag = new DocenteTag();
            $docenteTag->docente_codice_fiscale = $istanza->docente_codice_fiscale;
            $docenteTag->tag_id = $this->tag_id;
            $docenteTag->livello = $this->livello;
            $docenteTag->note = $this->note;
            if (!$docenteTag->save()) {
                return false;
            }
        }

        // Segna come promosso
        $this->promosso_at = date('Y-m-d H:i:s');
        $this->promosso_by = \Yii::$app->user->id ?? null;
        return $this->save(false);
    }
}
