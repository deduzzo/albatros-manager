<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model per la tabella configurazione.
 * Gestisce le impostazioni chiave-valore per la fondazione e il sistema.
 * NON estende BaseActiveRecord (no soft delete).
 *
 * @property int $id
 * @property string $chiave
 * @property string|null $valore
 * @property string $tipo
 * @property string|null $gruppo
 * @property string|null $etichetta
 * @property string|null $descrizione
 * @property int $ordine
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class Configurazione extends ActiveRecord
{
    const TIPO_TEXT = 'text';
    const TIPO_EMAIL = 'email';
    const TIPO_URL = 'url';
    const TIPO_PHONE = 'phone';
    const TIPO_IMAGE = 'image';
    const TIPO_JSON = 'json';

    private static $_cache = [];

    public static function tableName()
    {
        return '{{%configurazione}}';
    }

    public function rules()
    {
        return [
            [['chiave'], 'required'],
            [['chiave'], 'string', 'max' => 100],
            [['chiave'], 'unique'],
            [['valore', 'descrizione'], 'string'],
            [['tipo'], 'in', 'range' => [self::TIPO_TEXT, self::TIPO_EMAIL, self::TIPO_URL, self::TIPO_PHONE, self::TIPO_IMAGE, self::TIPO_JSON]],
            [['gruppo'], 'string', 'max' => 50],
            [['etichetta'], 'string', 'max' => 255],
            [['ordine'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chiave' => 'Chiave',
            'valore' => 'Valore',
            'tipo' => 'Tipo',
            'gruppo' => 'Gruppo',
            'etichetta' => 'Etichetta',
            'descrizione' => 'Descrizione',
            'ordine' => 'Ordine',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = date('Y-m-d H:i:s');
                if (!Yii::$app->user->isGuest) {
                    $this->created_by = Yii::$app->user->id;
                }
            }
            $this->updated_at = date('Y-m-d H:i:s');
            if (!Yii::$app->user->isGuest) {
                $this->updated_by = Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        // Invalida cache
        unset(self::$_cache[$this->chiave]);
    }

    /**
     * Restituisce il valore di una chiave di configurazione.
     * @param string $chiave
     * @param mixed $default Valore di default se non trovata
     * @return string|null
     */
    public static function get($chiave, $default = null)
    {
        if (isset(self::$_cache[$chiave])) {
            return self::$_cache[$chiave];
        }

        $model = static::find()->where(['chiave' => $chiave])->one();
        if ($model) {
            self::$_cache[$chiave] = $model->valore;
            return $model->valore;
        }

        return $default;
    }

    /**
     * Imposta il valore di una chiave di configurazione.
     * @param string $chiave
     * @param string $valore
     * @return bool
     */
    public static function set($chiave, $valore)
    {
        $model = static::find()->where(['chiave' => $chiave])->one();
        if (!$model) {
            $model = new static();
            $model->chiave = $chiave;
        }
        $model->valore = $valore;
        $result = $model->save(false);
        if ($result) {
            self::$_cache[$chiave] = $valore;
        }
        return $result;
    }

    /**
     * Restituisce tutte le configurazioni raggruppate per gruppo.
     * @return array [gruppo => [Configurazione, ...]]
     */
    public static function getByGruppo()
    {
        $items = static::find()->orderBy(['gruppo' => SORT_ASC, 'ordine' => SORT_ASC])->all();
        $grouped = [];
        foreach ($items as $item) {
            $gruppo = $item->gruppo ?: 'altro';
            $grouped[$gruppo][] = $item;
        }
        return $grouped;
    }

    /**
     * Svuota la cache.
     */
    public static function clearCache()
    {
        self::$_cache = [];
    }
}
