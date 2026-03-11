<?php

namespace app\models;

use Yii;
use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella rimborso_spesa.
 *
 * @property int $id
 * @property int $assegnazione_id
 * @property string $tipo
 * @property string $descrizione
 * @property float $importo
 * @property string $data_spesa
 * @property string|null $allegato_path
 * @property string $status
 * @property string|null $motivo_rifiuto
 * @property int|null $approvato_da
 * @property string|null $approvato_at
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Assegnazione $assegnazione
 * @property User $approvatoDaUser
 */
class RimborsoSpesa extends BaseActiveRecord
{
    // Costanti status
    const STATUS_RICHIESTO = 'richiesto';
    const STATUS_APPROVATO = 'approvato';
    const STATUS_RIFIUTATO = 'rifiutato';

    // Costanti tipo
    const TIPO_MATERIALE = 'materiale';
    const TIPO_CHILOMETRICO = 'chilometrico';
    const TIPO_VITTO_ALLOGGIO = 'vitto_alloggio';
    const TIPO_VIAGGIO = 'viaggio';
    const TIPO_ALTRO = 'altro';

    public static function tableName()
    {
        return '{{%rimborso_spesa}}';
    }

    public function rules()
    {
        return [
            [['assegnazione_id', 'tipo', 'descrizione', 'importo', 'data_spesa'], 'required'],
            [['assegnazione_id', 'approvato_da'], 'integer'],
            [['importo'], 'number', 'min' => 0.01],
            [['data_spesa'], 'date', 'format' => 'php:Y-m-d'],
            [['descrizione'], 'string', 'max' => 500],
            [['allegato_path'], 'string', 'max' => 500],
            [['tipo'], 'in', 'range' => [self::TIPO_MATERIALE, self::TIPO_CHILOMETRICO, self::TIPO_VITTO_ALLOGGIO, self::TIPO_VIAGGIO, self::TIPO_ALTRO]],
            [['status'], 'in', 'range' => [self::STATUS_RICHIESTO, self::STATUS_APPROVATO, self::STATUS_RIFIUTATO]],
            [['motivo_rifiuto', 'note'], 'string'],
            [['approvato_at'], 'safe'],
            // Motivo rifiuto obbligatorio se rifiutato
            [['motivo_rifiuto'], 'required', 'when' => function ($model) {
                return $model->status === self::STATUS_RIFIUTATO;
            }, 'whenClient' => "function(attribute, value) { return $('#rimborso-status').val() === 'rifiutato'; }"],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'assegnazione_id' => 'Assegnazione',
            'tipo' => 'Tipo',
            'descrizione' => 'Descrizione',
            'importo' => 'Importo (€)',
            'data_spesa' => 'Data Spesa',
            'allegato_path' => 'Allegato',
            'status' => 'Stato',
            'motivo_rifiuto' => 'Motivo Rifiuto',
            'approvato_da' => 'Approvato Da',
            'approvato_at' => 'Approvato Il',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssegnazione()
    {
        return $this->hasOne(Assegnazione::class, ['id' => 'assegnazione_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApprovatoDaUser()
    {
        return $this->hasOne(User::class, ['id' => 'approvato_da']);
    }

    // ---- Labels e badge ----

    /**
     * Restituisce le opzioni per il dropdown tipo
     * @return array
     */
    public static function getTipoList()
    {
        return [
            self::TIPO_MATERIALE => 'Materiale didattico',
            self::TIPO_CHILOMETRICO => 'Rimborso chilometrico',
            self::TIPO_VITTO_ALLOGGIO => 'Vitto e alloggio',
            self::TIPO_VIAGGIO => 'Viaggio/Trasporto',
            self::TIPO_ALTRO => 'Altro',
        ];
    }

    /**
     * Restituisce la label del tipo
     * @return string
     */
    public function getTipoLabel()
    {
        return self::getTipoList()[$this->tipo] ?? $this->tipo;
    }

    /**
     * Restituisce il badge HTML del tipo
     * @return string
     */
    public function getTipoBadge()
    {
        $class = match ($this->tipo) {
            self::TIPO_MATERIALE => 'bg-blue text-blue-fg',
            self::TIPO_CHILOMETRICO => 'bg-azure text-azure-fg',
            self::TIPO_VITTO_ALLOGGIO => 'bg-purple text-purple-fg',
            self::TIPO_VIAGGIO => 'bg-teal text-teal-fg',
            self::TIPO_ALTRO => 'bg-default text-default-fg',
            default => 'bg-default text-default-fg',
        };
        return '<span class="badge ' . $class . '">' . $this->getTipoLabel() . '</span>';
    }

    /**
     * Restituisce le opzioni per il dropdown status
     * @return array
     */
    public static function getStatusLabels()
    {
        return [
            self::STATUS_RICHIESTO => 'Richiesto',
            self::STATUS_APPROVATO => 'Approvato',
            self::STATUS_RIFIUTATO => 'Rifiutato',
        ];
    }

    /**
     * Restituisce la label dello status
     * @return string
     */
    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Restituisce la classe badge CSS per lo status
     * @return string
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_RICHIESTO => 'bg-orange text-orange-fg',
            self::STATUS_APPROVATO => 'bg-green text-green-fg',
            self::STATUS_RIFIUTATO => 'bg-red text-red-fg',
            default => 'bg-default text-default-fg',
        };
    }

    /**
     * Restituisce il badge HTML dello status
     * @return string
     */
    public function getStatusBadge()
    {
        return '<span class="badge ' . $this->getStatusBadgeClass() . '">' . $this->getStatusLabel() . '</span>';
    }

    // ---- Azioni ----

    /**
     * Approva il rimborso
     * @param int $userId ID dell'utente che approva
     * @return bool
     */
    public function approva($userId)
    {
        $this->status = self::STATUS_APPROVATO;
        $this->approvato_da = $userId;
        $this->approvato_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Rifiuta il rimborso
     * @param string $motivo Motivo del rifiuto
     * @param int $userId ID dell'utente che rifiuta
     * @return bool
     */
    public function rifiuta($motivo, $userId)
    {
        $this->status = self::STATUS_RIFIUTATO;
        $this->motivo_rifiuto = $motivo;
        $this->approvato_da = $userId;
        $this->approvato_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Verifica se il rimborso puo' essere modificato
     * @return bool
     */
    public function isModificabile()
    {
        return $this->status === self::STATUS_RICHIESTO;
    }

    /**
     * Verifica se il rimborso e' approvato
     * @return bool
     */
    public function isApprovato()
    {
        return $this->status === self::STATUS_APPROVATO;
    }
}
