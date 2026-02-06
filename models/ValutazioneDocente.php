<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella valutazione_docente.
 *
 * @property int $id
 * @property int $modulo_id
 * @property string $docente_codice_fiscale
 * @property int|null $punteggio_chiarezza
 * @property int|null $punteggio_competenza
 * @property int|null $punteggio_disponibilita
 * @property int|null $punteggio_materiali
 * @property int $punteggio_globale
 * @property string|null $commento
 * @property string $data_valutazione
 * @property string $created_at
 *
 * @property Docente $docente
 */
class ValutazioneDocente extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%valutazione_docente}}';
    }

    public function rules()
    {
        return [
            [['modulo_id', 'docente_codice_fiscale', 'punteggio_globale', 'data_valutazione'], 'required'],
            [['modulo_id', 'punteggio_chiarezza', 'punteggio_competenza', 'punteggio_disponibilita', 'punteggio_materiali', 'punteggio_globale'], 'integer'],
            [['punteggio_chiarezza', 'punteggio_competenza', 'punteggio_disponibilita', 'punteggio_materiali', 'punteggio_globale'], 'in', 'range' => [1, 2, 3, 4, 5]],
            [['commento'], 'string'],
            [['data_valutazione'], 'date', 'format' => 'php:Y-m-d'],
            [['docente_codice_fiscale'], 'string', 'max' => 16],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modulo_id' => 'Modulo',
            'docente_codice_fiscale' => 'Docente',
            'punteggio_chiarezza' => 'Chiarezza',
            'punteggio_competenza' => 'Competenza',
            'punteggio_disponibilita' => 'Disponibilità',
            'punteggio_materiali' => 'Materiali',
            'punteggio_globale' => 'Valutazione Globale',
            'commento' => 'Commento',
            'data_valutazione' => 'Data Valutazione',
            'created_at' => 'Creato il',
        ];
    }

    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
    }

    /**
     * Restituisce le stelle HTML per un punteggio
     * @param int $punteggio
     * @return string
     */
    public static function getStarsHtml($punteggio)
    {
        if (!$punteggio) {
            return '<span class="text-muted">-</span>';
        }

        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $punteggio) {
                $html .= '<i class="ti ti-star-filled text-yellow"></i>';
            } else {
                $html .= '<i class="ti ti-star text-muted"></i>';
            }
        }
        return $html;
    }
}
