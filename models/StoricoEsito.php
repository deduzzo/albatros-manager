<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model per la tabella storico_esito.
 * Traccia ogni cambiamento di stato di un'istanza docente.
 *
 * @property int $id
 * @property int $istanza_id
 * @property string $esito
 * @property string|null $data_esito
 * @property string|null $note
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property Istanza $istanza
 * @property User $createdByUser
 */
class StoricoEsito extends ActiveRecord
{
    const ESITO_ASSEGNATO = 'assegnato';
    const ESITO_IDONEO = 'idoneo';
    const ESITO_NON_IDONEO = 'non_idoneo';
    const ESITO_RINUNCIA = 'rinuncia';
    const ESITO_SUBENTRO = 'subentro';

    public static function tableName()
    {
        return '{{%storico_esito}}';
    }

    public function rules()
    {
        return [
            [['istanza_id', 'esito'], 'required'],
            [['istanza_id', 'created_by'], 'integer'],
            [['esito'], 'in', 'range' => [self::ESITO_ASSEGNATO, self::ESITO_IDONEO, self::ESITO_NON_IDONEO, self::ESITO_RINUNCIA, self::ESITO_SUBENTRO]],
            [['data_esito'], 'date', 'format' => 'php:Y-m-d'],
            [['note'], 'string'],
            [['created_at'], 'safe'],
            [['istanza_id'], 'exist', 'targetClass' => Istanza::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'istanza_id' => 'Istanza',
            'esito' => 'Esito',
            'data_esito' => 'Data Esito',
            'note' => 'Note',
            'created_at' => 'Registrato il',
            'created_by' => 'Registrato da',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && !$this->created_by && !Yii::$app->user->isGuest) {
                $this->created_by = Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }

    /**
     * Dopo il salvataggio, aggiorna esito_cts e status sull'istanza
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && $this->istanza) {
            // Aggiorna esito_cts sull'istanza se è un esito CTS
            if (in_array($this->esito, [self::ESITO_ASSEGNATO, self::ESITO_IDONEO, self::ESITO_NON_IDONEO])) {
                $this->istanza->esito_cts = $this->esito;
                $this->istanza->save(false);
            }
            // Se è una rinuncia, aggiorna lo status
            if ($this->esito === self::ESITO_RINUNCIA) {
                $this->istanza->status = 'rinuncia';
                $this->istanza->save(false);
            }
        }
    }

    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'istanza_id']);
    }

    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public static function getEsitoLabels()
    {
        return [
            self::ESITO_ASSEGNATO => 'Assegnato',
            self::ESITO_IDONEO => 'Idoneo',
            self::ESITO_NON_IDONEO => 'Non Idoneo',
            self::ESITO_RINUNCIA => 'Rinuncia',
            self::ESITO_SUBENTRO => 'Subentro',
        ];
    }

    public function getEsitoLabel()
    {
        return self::getEsitoLabels()[$this->esito] ?? $this->esito;
    }

    public function getEsitoBadgeClass()
    {
        return match ($this->esito) {
            self::ESITO_ASSEGNATO => 'bg-green text-green-fg',
            self::ESITO_IDONEO => 'bg-azure text-azure-fg',
            self::ESITO_NON_IDONEO => 'bg-red text-red-fg',
            self::ESITO_RINUNCIA => 'bg-default text-default-fg',
            self::ESITO_SUBENTRO => 'bg-orange text-orange-fg',
            default => 'bg-default text-default-fg',
        };
    }

    public function getEsitoBadgeHtml()
    {
        return '<span class="badge ' . $this->getEsitoBadgeClass() . '">' . $this->getEsitoLabel() . '</span>';
    }

    public function getEsitoIconClass()
    {
        return match ($this->esito) {
            self::ESITO_ASSEGNATO => 'ti ti-check text-success',
            self::ESITO_IDONEO => 'ti ti-thumb-up text-info',
            self::ESITO_NON_IDONEO => 'ti ti-thumb-down text-danger',
            self::ESITO_RINUNCIA => 'ti ti-arrow-back-up text-secondary',
            self::ESITO_SUBENTRO => 'ti ti-replace text-warning',
            default => 'ti ti-point text-muted',
        };
    }
}
