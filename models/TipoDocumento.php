<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella tipo_documento.
 *
 * @property int $id
 * @property string $codice
 * @property string $descrizione
 * @property bool $richiede_scadenza
 * @property bool $attivo
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Documentazione[] $documentazioni
 */
class TipoDocumento extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%tipo_documento}}';
    }

    public function rules()
    {
        return [
            [['codice', 'descrizione'], 'required'],
            [['richiede_scadenza', 'attivo'], 'boolean'],
            [['codice'], 'string', 'max' => 50],
            [['codice'], 'unique'],
            [['descrizione'], 'string', 'max' => 255],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codice' => 'Codice',
            'descrizione' => 'Descrizione',
            'richiede_scadenza' => 'Richiede Scadenza',
            'attivo' => 'Attivo',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    /**
     * Restituisce lista per dropdown (solo attivi)
     * @return array
     */
    public static function getList()
    {
        return static::find()
            ->select(['descrizione', 'id'])
            ->where(['attivo' => 1])
            ->orderBy(['descrizione' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    public function getDocumentazioni()
    {
        return $this->hasMany(Documentazione::class, ['tipo_documento_id' => 'id']);
    }
}
