<?php

namespace app\models;

use app\components\base\BaseActiveRecord;
use Yii;

/**
 * Model per la tabella valutazione_codice.
 *
 * @property int $id
 * @property int $modulo_id
 * @property string $codice
 * @property string|null $usato_at
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property Modulo $modulo
 * @property ValutazioneDocente $valutazioneDocente
 */
class ValutazioneCodice extends BaseActiveRecord
{
    // Caratteri ammessi (esclusi O/0/I/1 per evitare ambiguita)
    const CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function tableName()
    {
        return '{{%valutazione_codice}}';
    }

    public function rules()
    {
        return [
            [['modulo_id', 'codice'], 'required'],
            [['modulo_id', 'created_by'], 'integer'],
            [['codice'], 'string', 'max' => 8],
            [['codice'], 'unique'],
            [['usato_at', 'created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modulo_id' => 'Modulo',
            'codice' => 'Codice',
            'usato_at' => 'Utilizzato il',
            'created_at' => 'Creato il',
            'created_by' => 'Creato da',
        ];
    }

    public function getModulo()
    {
        return $this->hasOne(Modulo::class, ['id' => 'modulo_id']);
    }

    public function getValutazioneDocente()
    {
        return $this->hasOne(ValutazioneDocente::class, ['valutazione_codice_id' => 'id']);
    }

    /**
     * Verifica se il codice e' stato utilizzato
     */
    public function isUsato()
    {
        return $this->usato_at !== null;
    }

    /**
     * Segna il codice come utilizzato
     */
    public function invalida()
    {
        $this->usato_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Genera codici univoci per un modulo
     * @param int $moduloId
     * @param int $quantita
     * @return array Codici generati
     */
    public static function generaCodici($moduloId, $quantita)
    {
        $codici = [];
        $chars = self::CHARS;
        $charsLen = strlen($chars);

        for ($i = 0; $i < $quantita; $i++) {
            $maxTentativi = 100;
            $tentativo = 0;

            do {
                $codice = '';
                for ($j = 0; $j < 8; $j++) {
                    $codice .= $chars[random_int(0, $charsLen - 1)];
                }
                $tentativo++;
            } while (
                $tentativo < $maxTentativi &&
                (in_array($codice, $codici) || static::find()->where(['codice' => $codice])->exists())
            );

            $model = new static();
            $model->modulo_id = $moduloId;
            $model->codice = $codice;
            $model->created_by = Yii::$app->user->isGuest ? null : Yii::$app->user->id;

            if ($model->save()) {
                $codici[] = $codice;
            }
        }

        return $codici;
    }
}
