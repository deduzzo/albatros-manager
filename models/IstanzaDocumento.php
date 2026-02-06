<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

/**
 * Model per la tabella istanza_documento.
 * Documenti caricati dai docenti nelle iscrizioni.
 *
 * @property int $id
 * @property int $istanza_id
 * @property int $avviso_documento_tipo_id
 * @property string $nome_file_originale
 * @property string $nome_file_salvato
 * @property string $path
 * @property string $mime_type
 * @property int $dimensione_bytes
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property Istanza $istanza
 * @property AvvisoDocumentoTipo $documentoTipo
 */
class IstanzaDocumento extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%istanza_documento}}';
    }

    public function rules()
    {
        return [
            [['istanza_id', 'avviso_documento_tipo_id', 'nome_file_originale', 'nome_file_salvato', 'path', 'mime_type', 'dimensione_bytes'], 'required'],
            [['istanza_id', 'avviso_documento_tipo_id', 'dimensione_bytes', 'created_by'], 'integer'],
            [['nome_file_originale'], 'string', 'max' => 255],
            [['nome_file_salvato', 'mime_type'], 'string', 'max' => 100],
            [['path'], 'string', 'max' => 255],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'istanza_id' => 'Istanza',
            'avviso_documento_tipo_id' => 'Tipo Documento',
            'nome_file_originale' => 'Nome File',
            'nome_file_salvato' => 'Nome Salvato',
            'path' => 'Percorso',
            'mime_type' => 'Tipo MIME',
            'dimensione_bytes' => 'Dimensione',
            'created_at' => 'Caricato il',
        ];
    }

    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'istanza_id']);
    }

    public function getDocumentoTipo()
    {
        return $this->hasOne(AvvisoDocumentoTipo::class, ['id' => 'avviso_documento_tipo_id']);
    }

    /**
     * Restituisce il percorso completo del file
     */
    public function getFullPath()
    {
        return Yii::getAlias('@webroot/uploads/') . $this->path;
    }

    /**
     * Restituisce la dimensione formattata
     */
    public function getDimensioneFormattata()
    {
        $bytes = $this->dimensione_bytes;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Verifica se il file esiste
     */
    public function fileExists()
    {
        return file_exists($this->getFullPath());
    }
}
