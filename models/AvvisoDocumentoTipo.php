<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella avviso_documento_tipo.
 * Definisce i documenti richiesti per ogni bando.
 *
 * @property int $id
 * @property int $avviso_id
 * @property string $nome
 * @property string|null $descrizione
 * @property string $formati_ammessi
 * @property int $dimensione_max_mb
 * @property bool $obbligatorio
 * @property int $ordine
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 *
 * @property Avviso $avviso
 * @property IstanzaDocumento[] $documentiCaricati
 */
class AvvisoDocumentoTipo extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%avviso_documento_tipo}}';
    }

    public function rules()
    {
        return [
            [['avviso_id', 'nome'], 'required'],
            [['avviso_id', 'dimensione_max_mb', 'ordine'], 'integer'],
            [['descrizione'], 'string'],
            [['obbligatorio'], 'boolean'],
            [['nome'], 'string', 'max' => 100],
            [['formati_ammessi'], 'string', 'max' => 100],
            [['dimensione_max_mb'], 'integer', 'min' => 1, 'max' => 50],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'avviso_id' => 'Avviso',
            'nome' => 'Nome Documento',
            'descrizione' => 'Descrizione',
            'formati_ammessi' => 'Formati Ammessi',
            'dimensione_max_mb' => 'Dimensione Max (MB)',
            'obbligatorio' => 'Obbligatorio',
            'ordine' => 'Ordine',
        ];
    }

    public function getAvviso()
    {
        return $this->hasOne(Avviso::class, ['id' => 'avviso_id']);
    }

    public function getDocumentiCaricati()
    {
        return $this->hasMany(IstanzaDocumento::class, ['avviso_documento_tipo_id' => 'id']);
    }

    /**
     * Restituisce array dei formati ammessi
     */
    public function getFormatiArray()
    {
        return array_map('trim', explode(',', $this->formati_ammessi));
    }

    /**
     * Verifica se un formato è ammesso
     */
    public function isFormatoAmmesso($formato)
    {
        $formato = strtolower(trim($formato));
        return in_array($formato, $this->getFormatiArray());
    }
}
