<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella criterio_valutazione_studente.
 *
 * @property int $id
 * @property int|null $avviso_id
 * @property string $nome
 * @property string|null $descrizione
 * @property float $peso
 * @property float $punteggio_min
 * @property float $punteggio_max
 * @property int $ordine
 * @property int $attivo
 *
 * @property Avviso $avviso
 * @property PunteggioIscrizione[] $punteggi
 */
class CriterioValutazioneStudente extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%criterio_valutazione_studente}}';
    }

    public function rules()
    {
        return [
            [['nome'], 'required'],
            [['avviso_id', 'ordine'], 'integer'],
            [['attivo'], 'boolean'],
            [['nome'], 'string', 'max' => 255],
            [['descrizione'], 'string'],
            [['peso', 'punteggio_min', 'punteggio_max'], 'number'],
            [['punteggio_max'], 'compare', 'compareAttribute' => 'punteggio_min', 'operator' => '>=', 'message' => 'Il punteggio massimo deve essere >= al minimo'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'avviso_id' => 'Bando',
            'nome' => 'Nome Criterio',
            'descrizione' => 'Descrizione',
            'peso' => 'Peso',
            'punteggio_min' => 'Punteggio Min',
            'punteggio_max' => 'Punteggio Max',
            'ordine' => 'Ordine',
            'attivo' => 'Attivo',
        ];
    }

    public function getAvviso()
    {
        return $this->hasOne(Avviso::class, ['id' => 'avviso_id']);
    }

    public function getPunteggi()
    {
        return $this->hasMany(PunteggioIscrizione::class, ['criterio_id' => 'id']);
    }

    /**
     * Scope: trova solo criteri globali (avviso_id IS NULL).
     */
    public static function findGlobali()
    {
        return static::find()->where(['avviso_id' => null, 'attivo' => 1])->orderBy(['ordine' => SORT_ASC]);
    }

    /**
     * Scope: trova criteri per un bando specifico + globali.
     * @param int $avvisoId
     * @return static[]
     */
    public static function findPerBando($avvisoId)
    {
        return static::find()
            ->where(['attivo' => 1])
            ->andWhere([
                'or',
                ['avviso_id' => null],
                ['avviso_id' => $avvisoId],
            ])
            ->orderBy(['avviso_id' => SORT_ASC, 'ordine' => SORT_ASC])
            ->all();
    }

    /**
     * Indica se il criterio e' globale (non legato a un bando specifico).
     * @return bool
     */
    public function isGlobale()
    {
        return $this->avviso_id === null;
    }

    public function getAvvisoLabel()
    {
        return $this->isGlobale() ? 'Globale (tutti i bandi)' : ($this->avviso ? $this->avviso->titolo : 'Bando #' . $this->avviso_id);
    }
}
