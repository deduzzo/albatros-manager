<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella documentazione.
 *
 * @property int $id
 * @property int $tipo_documento_id
 * @property string|null $docente_codice_fiscale
 * @property string|null $allievo_codice_fiscale
 * @property string $titolo
 * @property string|null $descrizione
 * @property string $file_path
 * @property string|null $file_nome_originale
 * @property int|null $file_size
 * @property string|null $file_mime_type
 * @property string|null $data_documento
 * @property string|null $data_scadenza
 * @property string $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property TipoDocumento $tipoDocumento
 * @property Docente $docente
 * @property Allievo $allievo
 */
class Documentazione extends BaseActiveRecord
{
    const STATUS_ATTIVO = 'attivo';
    const STATUS_ARCHIVIATO = 'archiviato';
    const STATUS_SCADUTO = 'scaduto';

    public static function tableName()
    {
        return '{{%documentazione}}';
    }

    public function rules()
    {
        return [
            [['tipo_documento_id', 'titolo', 'file_path'], 'required'],
            [['tipo_documento_id', 'file_size'], 'integer'],
            [['descrizione', 'note'], 'string'],
            [['docente_codice_fiscale', 'allievo_codice_fiscale'], 'string', 'max' => 16],
            // Almeno uno dei due (docente o allievo) deve essere specificato
            ['docente_codice_fiscale', 'required', 'when' => function($model) {
                return empty($model->allievo_codice_fiscale);
            }, 'message' => 'Specificare almeno un docente o un allievo'],
            ['allievo_codice_fiscale', 'required', 'when' => function($model) {
                return empty($model->docente_codice_fiscale);
            }, 'message' => 'Specificare almeno un docente o un allievo'],
            [['titolo', 'file_nome_originale'], 'string', 'max' => 255],
            [['file_path'], 'string', 'max' => 500],
            [['file_mime_type'], 'string', 'max' => 100],
            [['data_documento', 'data_scadenza'], 'date', 'format' => 'php:Y-m-d'],
            [['status'], 'in', 'range' => [self::STATUS_ATTIVO, self::STATUS_ARCHIVIATO, self::STATUS_SCADUTO]],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo_documento_id' => 'Tipo Documento',
            'docente_codice_fiscale' => 'Docente',
            'allievo_codice_fiscale' => 'Allievo',
            'titolo' => 'Titolo',
            'descrizione' => 'Descrizione',
            'file_path' => 'File',
            'file_nome_originale' => 'Nome File Originale',
            'file_size' => 'Dimensione File',
            'file_mime_type' => 'Tipo File',
            'data_documento' => 'Data Documento',
            'data_scadenza' => 'Data Scadenza',
            'status' => 'Stato',
            'note' => 'Note',
        ];
    }

    public function getTipoDocumento()
    {
        return $this->hasOne(TipoDocumento::class, ['id' => 'tipo_documento_id']);
    }

    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
    }

    public function getAllievo()
    {
        return $this->hasOne(Allievo::class, ['codice_fiscale' => 'allievo_codice_fiscale']);
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_ATTIVO => 'Attivo',
            self::STATUS_ARCHIVIATO => 'Archiviato',
            self::STATUS_SCADUTO => 'Scaduto',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_ATTIVO => 'bg-success',
            self::STATUS_ARCHIVIATO => 'bg-secondary',
            self::STATUS_SCADUTO => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function isScaduto()
    {
        if (!$this->data_scadenza) {
            return false;
        }
        return strtotime($this->data_scadenza) < time();
    }
}
