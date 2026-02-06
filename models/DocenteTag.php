<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Model per la tabella docente_tag.
 *
 * @property int $id
 * @property string $docente_codice_fiscale
 * @property int $tag_id
 * @property int|null $livello
 * @property string|null $note
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property Docente $docente
 * @property TagCurriculum $tag
 * @property User $createdByUser
 */
class DocenteTag extends ActiveRecord
{
    /**
     * Livelli di competenza
     */
    const LIVELLO_BASE = 1;
    const LIVELLO_INTERMEDIO = 2;
    const LIVELLO_AVANZATO = 3;
    const LIVELLO_ESPERTO = 4;
    const LIVELLO_MASTER = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%docente_tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['docente_codice_fiscale', 'tag_id'], 'required'],
            [['tag_id', 'livello', 'created_by'], 'integer'],
            [['docente_codice_fiscale'], 'string', 'max' => 16],
            [['note'], 'string', 'max' => 255],
            [['livello'], 'in', 'range' => [1, 2, 3, 4, 5]],
            [['docente_codice_fiscale', 'tag_id'], 'unique', 'targetAttribute' => ['docente_codice_fiscale', 'tag_id'], 'message' => 'Questo tag è già associato al docente'],
            [['docente_codice_fiscale'], 'exist', 'skipOnError' => true, 'targetClass' => Docente::class, 'targetAttribute' => ['docente_codice_fiscale' => 'codice_fiscale']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => TagCurriculum::class, 'targetAttribute' => ['tag_id' => 'id']],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'docente_codice_fiscale' => 'Docente',
            'tag_id' => 'Tag',
            'livello' => 'Livello',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'created_by' => 'Creato da',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(TagCurriculum::class, ['id' => 'tag_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Restituisce le etichette dei livelli
     * @return array
     */
    public static function getLivelloLabels()
    {
        return [
            self::LIVELLO_BASE => 'Base',
            self::LIVELLO_INTERMEDIO => 'Intermedio',
            self::LIVELLO_AVANZATO => 'Avanzato',
            self::LIVELLO_ESPERTO => 'Esperto',
            self::LIVELLO_MASTER => 'Master',
        ];
    }

    /**
     * Restituisce l'etichetta del livello corrente
     * @return string
     */
    public function getLivelloLabel()
    {
        $labels = self::getLivelloLabels();
        return $labels[$this->livello] ?? '-';
    }

    /**
     * Restituisce le stelle HTML per il livello
     * @return string
     */
    public function getLivelloStarsHtml()
    {
        if (!$this->livello) {
            return '<span class="text-muted">-</span>';
        }

        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->livello) {
                $html .= '<i class="ti ti-star-filled text-yellow"></i>';
            } else {
                $html .= '<i class="ti ti-star text-muted"></i>';
            }
        }
        return $html;
    }
}
