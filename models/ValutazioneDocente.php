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
 * @property string|null $studente_email
 * @property string|null $studente_nome
 * @property string|null $docente_nome
 * @property string|null $sorgente_url
 * @property string $sorgente_tipo
 * @property string|null $note
 * @property string $created_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property int|null $valutazione_codice_id
 *
 * @property Modulo $modulo
 * @property Docente $docente
 * @property ValutazioneCodice $valutazioneCodice
 * @property ValutazioneDocentePunteggio[] $punteggiDettagliati
 */
class ValutazioneDocente extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%valutazione_docente}}';
    }

    /**
     * Costanti per sorgente_tipo
     */
    const SORGENTE_MANUALE = 'manuale';
    const SORGENTE_CODICE_MONOUSO = 'codice_monouso';
    const SORGENTE_IMPORT_CSV = 'import_csv';

    public function rules()
    {
        return [
            [['data_valutazione'], 'required'],
            [['modulo_id', 'punteggio_chiarezza', 'punteggio_competenza', 'punteggio_disponibilita', 'punteggio_materiali', 'punteggio_globale'], 'integer'],
            [['punteggio_chiarezza', 'punteggio_competenza', 'punteggio_disponibilita', 'punteggio_materiali', 'punteggio_globale'], 'integer', 'min' => 1, 'max' => 10],
            [['commento', 'note'], 'string'],
            [['data_valutazione'], 'date', 'format' => 'php:Y-m-d'],
            [['docente_codice_fiscale'], 'string', 'max' => 16],
            [['studente_email'], 'string', 'max' => 255],
            [['studente_email'], 'email', 'skipOnEmpty' => true],
            [['studente_nome', 'docente_nome'], 'string', 'max' => 255],
            [['sorgente_url'], 'string', 'max' => 500],
            [['sorgente_tipo'], 'in', 'range' => [self::SORGENTE_MANUALE, self::SORGENTE_CODICE_MONOUSO, self::SORGENTE_IMPORT_CSV]],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['valutazione_codice_id'], 'integer'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
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
            'studente_email' => 'Email Studente',
            'studente_nome' => 'Nome Studente',
            'docente_nome' => 'Nome Docente',
            'sorgente_url' => 'URL Sorgente',
            'sorgente_tipo' => 'Sorgente',
            'note' => 'Note',
            'created_at' => 'Creato il',
        ];
    }

    public function getModulo()
    {
        return $this->hasOne(Modulo::class, ['id' => 'modulo_id']);
    }

    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
    }

    public function getValutazioneCodice()
    {
        return $this->hasOne(ValutazioneCodice::class, ['id' => 'valutazione_codice_id']);
    }

    /**
     * Relazione con i punteggi dettagliati per criterio.
     * @return \yii\db\ActiveQuery
     */
    public function getPunteggiDettagliati()
    {
        return $this->hasMany(ValutazioneDocentePunteggio::class, ['valutazione_docente_id' => 'id']);
    }

    /**
     * Calcola la media dei punteggi dettagliati.
     * @return float|null
     */
    public function getMediaPunteggi()
    {
        $punteggi = $this->punteggiDettagliati;
        if (empty($punteggi)) {
            return null;
        }
        $somma = 0;
        $count = 0;
        foreach ($punteggi as $p) {
            if ($p->punteggio !== null) {
                $somma += $p->punteggio;
                $count++;
            }
        }
        return $count > 0 ? round($somma / $count, 1) : null;
    }

    /**
     * Restituisce il punteggio per un criterio specifico.
     * @param int $criterioId
     * @return int|null
     */
    public function getPunteggioPerCriterio($criterioId)
    {
        foreach ($this->punteggiDettagliati as $p) {
            if ($p->criterio_id == $criterioId) {
                return $p->punteggio;
            }
        }
        return null;
    }

    /**
     * Restituisce etichetta per sorgente_tipo.
     * @return string
     */
    public function getSorgenteTipoLabel()
    {
        $labels = [
            self::SORGENTE_MANUALE => 'Manuale',
            self::SORGENTE_CODICE_MONOUSO => 'Codice Monouso',
            self::SORGENTE_IMPORT_CSV => 'Import CSV',
        ];
        return $labels[$this->sorgente_tipo] ?? $this->sorgente_tipo;
    }

    /**
     * Badge per sorgente_tipo.
     * @return string
     */
    public function getSorgenteTipoBadge()
    {
        $badges = [
            self::SORGENTE_MANUALE => '<span class="badge bg-blue-lt">Manuale</span>',
            self::SORGENTE_CODICE_MONOUSO => '<span class="badge bg-purple-lt">Codice Monouso</span>',
            self::SORGENTE_IMPORT_CSV => '<span class="badge bg-orange-lt">Import CSV</span>',
        ];
        return $badges[$this->sorgente_tipo] ?? '<span class="badge bg-default-lt">' . $this->sorgente_tipo . '</span>';
    }

    /**
     * Restituisce il nome del docente (da relazione o campo testuale).
     * @return string
     */
    public function getDocenteLabel()
    {
        if ($this->docente) {
            return $this->docente->cognome . ' ' . $this->docente->nome;
        }
        return $this->docente_nome ?: '-';
    }

    /**
     * Restituisce le stelle HTML per un punteggio (scala 1-10)
     * @param int $punteggio
     * @param int $maxStelle Numero massimo di stelle da mostrare
     * @return string
     */
    public static function getStarsHtml($punteggio, $maxStelle = 5)
    {
        if (!$punteggio) {
            return '<span class="text-muted">-</span>';
        }

        $html = '';
        for ($i = 1; $i <= $maxStelle; $i++) {
            if ($i <= $punteggio) {
                $html .= '<i class="ti ti-star-filled text-yellow"></i>';
            } else {
                $html .= '<i class="ti ti-star text-muted"></i>';
            }
        }
        return $html;
    }

    /**
     * Restituisce barra di progresso HTML per un punteggio 1-10.
     * @param int|null $punteggio
     * @return string
     */
    public static function getProgressBarHtml($punteggio)
    {
        if ($punteggio === null || $punteggio === '') {
            return '<span class="text-muted">-</span>';
        }
        $percent = ($punteggio / 10) * 100;
        $color = $punteggio >= 8 ? 'green' : ($punteggio >= 5 ? 'orange' : 'red');
        return '<div class="d-flex align-items-center gap-2">'
            . '<span class="fw-bold" style="min-width:25px">' . $punteggio . '</span>'
            . '<div class="progress flex-grow-1" style="height:8px">'
            . '<div class="progress-bar bg-' . $color . '" style="width:' . $percent . '%"></div>'
            . '</div></div>';
    }
}
