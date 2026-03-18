<?php

namespace app\models;

use Yii;
use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella rimborso_spesa.
 *
 * @property int $id
 * @property int $assegnazione_id
 * @property string $descrizione
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
 * @property RimborsoSpesaDettaglio[] $dettagli
 */
class RimborsoSpesa extends BaseActiveRecord
{
    // Costanti status
    const STATUS_RICHIESTO = 'richiesto';
    const STATUS_APPROVATO = 'approvato';
    const STATUS_RIFIUTATO = 'rifiutato';

    public static function tableName()
    {
        return '{{%rimborso_spesa}}';
    }

    public function rules()
    {
        return [
            [['assegnazione_id', 'descrizione', 'data_spesa'], 'required'],
            [['assegnazione_id', 'approvato_da'], 'integer'],
            [['data_spesa'], 'date', 'format' => 'php:Y-m-d'],
            [['descrizione'], 'string', 'max' => 500],
            [['allegato_path'], 'string', 'max' => 500],
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
            'descrizione' => 'Descrizione',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDettagli()
    {
        return $this->hasMany(RimborsoSpesaDettaglio::class, ['rimborso_spesa_id' => 'id'])
            ->andWhere(['{{%rimborso_spesa_dettaglio}}.deleted_at' => null]);
    }

    // ---- Importo calcolato ----

    /**
     * Calcola il totale importo dai dettagli
     * @return float
     */
    public function getTotaleImporto()
    {
        return (float) RimborsoSpesaDettaglio::find()
            ->where(['rimborso_spesa_id' => $this->id, 'deleted_at' => null])
            ->sum('importo') ?: 0;
    }

    // ---- Labels e badge ----

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

    /**
     * Restituisce un riepilogo delle voci di dettaglio
     * @return string
     */
    public function getDettagliRiepilogo()
    {
        $dettagli = $this->dettagli;
        if (empty($dettagli)) {
            return '<span class="text-muted">Nessuna voce</span>';
        }

        $html = '';
        foreach ($dettagli as $d) {
            $html .= '<span class="badge bg-blue-lt me-1 mb-1">'
                . htmlspecialchars($d->descrizione) . ': '
                . Yii::$app->formatter->asCurrency($d->importo, 'EUR')
                . '</span>';
        }
        return $html;
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

    /**
     * Salva i dettagli dal form (array di dettagli)
     * @param array $dettagliData Array di ['descrizione' => ..., 'importo' => ..., 'voce_spesa_id' => ..., 'note' => ...]
     * @return bool
     */
    public function salvaDettagli($dettagliData)
    {
        // Soft-delete dettagli esistenti
        foreach ($this->dettagli as $dettaglio) {
            $dettaglio->delete();
        }

        // Salva nuovi dettagli
        foreach ($dettagliData as $data) {
            if (empty($data['descrizione']) && empty($data['importo'])) {
                continue;
            }
            $dettaglio = new RimborsoSpesaDettaglio();
            $dettaglio->rimborso_spesa_id = $this->id;
            $dettaglio->voce_spesa_id = !empty($data['voce_spesa_id']) ? $data['voce_spesa_id'] : null;
            $dettaglio->descrizione = $data['descrizione'] ?? 'Generale';
            $dettaglio->importo = $data['importo'] ?? 0;
            $dettaglio->note = $data['note'] ?? null;
            if (!$dettaglio->save()) {
                return false;
            }
        }

        return true;
    }
}
