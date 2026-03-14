<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella iscrizione.
 *
 * @property int $id
 * @property int $modulo_id
 * @property string $allievo_codice_fiscale
 * @property string $data_iscrizione
 * @property string|null $data_ritiro
 * @property string|null $motivo_ritiro
 * @property string $status
 * @property float|null $percentuale_frequenza
 * @property string|null $esito_finale
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Modulo $modulo
 * @property Allievo $allievo
 */
class Iscrizione extends BaseActiveRecord
{
    const STATUS_CANDIDATO = 'candidato';
    const STATUS_IN_VALUTAZIONE = 'in_valutazione';
    const STATUS_AMMESSO = 'ammesso';
    const STATUS_AMMESSO_CON_RISERVA = 'ammesso_con_riserva';
    const STATUS_NON_AMMESSO = 'non_ammesso';
    const STATUS_ISCRITTO = 'iscritto'; // Retrocompatibilita
    const STATUS_FREQUENTANTE = 'frequentante';
    const STATUS_COMPLETATO = 'completato';
    const STATUS_RITIRATO = 'ritirato';

    const ESITO_IDONEO = 'idoneo';
    const ESITO_NON_IDONEO = 'non_idoneo';
    const ESITO_RITIRATO = 'ritirato';

    public static function tableName()
    {
        return '{{%iscrizione}}';
    }

    public function rules()
    {
        return [
            [['allievo_codice_fiscale', 'data_iscrizione'], 'required'],
            [['avviso_id'], 'required', 'when' => function ($model) {
                return empty($model->modulo_id);
            }, 'message' => 'Indicare il bando o il modulo di iscrizione'],
            [['avviso_id', 'modulo_id', 'posizione_graduatoria'], 'integer'],
            [['allievo_codice_fiscale'], 'string', 'max' => 16],
            [['data_iscrizione', 'data_ritiro', 'data_ammissione', 'data_completamento', 'data_attestato'], 'date', 'format' => 'php:Y-m-d'],
            [['motivo_ritiro'], 'string', 'max' => 255],
            [['motivo_non_ammissione'], 'string'],
            [['status'], 'in', 'range' => [
                self::STATUS_CANDIDATO,
                self::STATUS_IN_VALUTAZIONE,
                self::STATUS_AMMESSO,
                self::STATUS_AMMESSO_CON_RISERVA,
                self::STATUS_NON_AMMESSO,
                self::STATUS_ISCRITTO,
                self::STATUS_FREQUENTANTE,
                self::STATUS_COMPLETATO,
                self::STATUS_RITIRATO,
            ]],
            [['percentuale_frequenza', 'punteggio_totale', 'punteggio_finale'], 'number'],
            [['esito_finale'], 'in', 'range' => [self::ESITO_IDONEO, self::ESITO_NON_IDONEO, self::ESITO_RITIRATO]],
            [['note'], 'string'],
            [['numero_attestato'], 'string', 'max' => 50],
            [['titolo_studio'], 'in', 'range' => ['licenza_media', 'diploma', 'laurea_triennale', 'laurea_magistrale', 'master', 'dottorato']],
            [['stato_occupazionale'], 'in', 'range' => ['occupato', 'disoccupato', 'inoccupato', 'studente', 'pensionato']],
            [['anno_conseguimento'], 'integer', 'min' => 1950, 'max' => date('Y')],
            [['istituto'], 'string', 'max' => 255],
            [['settore_attivita'], 'string', 'max' => 100],
            [['accetta_privacy', 'accetta_marketing', 'dichiarazione_veridicita'], 'boolean'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'avviso_id' => 'Bando',
            'modulo_id' => 'Modulo',
            'allievo_codice_fiscale' => 'Allievo',
            'data_iscrizione' => 'Data Iscrizione',
            'data_ritiro' => 'Data Ritiro',
            'motivo_ritiro' => 'Motivo Ritiro',
            'status' => 'Stato',
            'percentuale_frequenza' => 'Percentuale Frequenza',
            'esito_finale' => 'Esito Finale',
            'note' => 'Note',
            'accetta_privacy' => 'Accettazione Privacy',
            'accetta_marketing' => 'Consenso Marketing',
            'dichiarazione_veridicita' => 'Dichiarazione di Veridicità',
            'titolo_studio' => 'Titolo di Studio',
            'anno_conseguimento' => 'Anno di Conseguimento',
            'istituto' => 'Istituto / Università',
            'stato_occupazionale' => 'Stato Occupazionale',
            'settore_attivita' => 'Settore di Attività',
            'punteggio_totale' => 'Punteggio Totale',
            'posizione_graduatoria' => 'Posizione',
            'motivo_non_ammissione' => 'Motivo Non Ammissione',
            'data_ammissione' => 'Data Ammissione',
            'data_completamento' => 'Data Completamento',
            'punteggio_finale' => 'Punteggio Finale',
            'numero_attestato' => 'N. Attestato',
            'data_attestato' => 'Data Attestato',
        ];
    }

    public function getAvviso()
    {
        return $this->hasOne(Avviso::class, ['id' => 'avviso_id']);
    }

    public function getModulo()
    {
        return $this->hasOne(Modulo::class, ['id' => 'modulo_id']);
    }

    public function getAllievo()
    {
        return $this->hasOne(Allievo::class, ['codice_fiscale' => 'allievo_codice_fiscale']);
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_CANDIDATO => 'Candidato',
            self::STATUS_IN_VALUTAZIONE => 'In Valutazione',
            self::STATUS_AMMESSO => 'Ammesso',
            self::STATUS_AMMESSO_CON_RISERVA => 'Ammesso con Riserva',
            self::STATUS_NON_AMMESSO => 'Non Ammesso',
            self::STATUS_ISCRITTO => 'Iscritto',
            self::STATUS_FREQUENTANTE => 'Frequentante',
            self::STATUS_COMPLETATO => 'Completato',
            self::STATUS_RITIRATO => 'Ritirato',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_CANDIDATO => 'bg-azure text-azure-fg',
            self::STATUS_IN_VALUTAZIONE => 'bg-orange text-orange-fg',
            self::STATUS_AMMESSO => 'bg-green text-green-fg',
            self::STATUS_AMMESSO_CON_RISERVA => 'bg-lime text-lime-fg',
            self::STATUS_NON_AMMESSO => 'bg-red text-red-fg',
            self::STATUS_ISCRITTO => 'bg-azure text-azure-fg',
            self::STATUS_FREQUENTANTE => 'bg-blue text-blue-fg',
            self::STATUS_COMPLETATO => 'bg-teal text-teal-fg',
            self::STATUS_RITIRATO => 'bg-default text-default-fg',
            default => 'bg-default text-default-fg',
        };
    }

    public static function getEsitoLabels()
    {
        return [
            self::ESITO_IDONEO => 'Idoneo',
            self::ESITO_NON_IDONEO => 'Non Idoneo',
            self::ESITO_RITIRATO => 'Ritirato',
        ];
    }

    public function getEsitoLabel()
    {
        return self::getEsitoLabels()[$this->esito_finale] ?? null;
    }

    // --- Relazioni nuove ---

    public function getPunteggi()
    {
        return $this->hasMany(PunteggioIscrizione::class, ['iscrizione_id' => 'id']);
    }

    public function getStoricoIscrizioni()
    {
        return $this->hasMany(StoricoIscrizione::class, ['iscrizione_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Verifica se la transizione di status e' consentita.
     * @param string $newStatus
     * @return bool
     */
    public function canTransitionTo($newStatus)
    {
        $allowedTransitions = [
            self::STATUS_CANDIDATO => [self::STATUS_IN_VALUTAZIONE, self::STATUS_NON_AMMESSO, self::STATUS_RITIRATO],
            self::STATUS_IN_VALUTAZIONE => [self::STATUS_AMMESSO, self::STATUS_AMMESSO_CON_RISERVA, self::STATUS_NON_AMMESSO],
            self::STATUS_AMMESSO => [self::STATUS_FREQUENTANTE, self::STATUS_RITIRATO],
            self::STATUS_AMMESSO_CON_RISERVA => [self::STATUS_FREQUENTANTE, self::STATUS_RITIRATO],
            self::STATUS_FREQUENTANTE => [self::STATUS_COMPLETATO, self::STATUS_RITIRATO],
            self::STATUS_COMPLETATO => [],
            self::STATUS_RITIRATO => [],
            self::STATUS_NON_AMMESSO => [],
        ];

        $allowed = $allowedTransitions[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    // --- Metodi candidatura ---

    /**
     * Copia i dati formazione/lavoro dall'allievo come snapshot nella candidatura.
     */
    public function snapshotDaAllievo()
    {
        if ($this->allievo) {
            $this->titolo_studio = $this->allievo->titolo_studio;
            $this->anno_conseguimento = $this->allievo->anno_conseguimento;
            $this->istituto = $this->allievo->istituto;
            $this->stato_occupazionale = $this->allievo->stato_occupazionale;
            $this->settore_attivita = $this->allievo->settore_attivita;
        }
    }

    /**
     * Calcola il punteggio totale dalla somma dei punteggi pesati per criterio.
     * @return float
     */
    public function calcolaPunteggioTotale()
    {
        $totale = 0;
        foreach ($this->punteggi as $punteggio) {
            $totale += $punteggio->getPunteggioPesato();
        }
        $this->punteggio_totale = round($totale, 2);
        return $this->punteggio_totale;
    }

    /**
     * Cambia lo status e registra nello storico.
     * @param string $nuovoStatus
     * @param string|null $note
     * @return bool
     */
    public function cambiaStatus($nuovoStatus, $note = null)
    {
        $vecchioStatus = $this->status;
        $this->status = $nuovoStatus;

        if ($this->save(false)) {
            StoricoIscrizione::registra($this, $nuovoStatus, $note);
            return true;
        }
        return false;
    }

    /**
     * Titoli studio per dropdown.
     */
    public static function getTitoloStudioLabels()
    {
        return Allievo::getTitoloStudioLabels();
    }

    /**
     * Stati occupazionali per dropdown.
     */
    public static function getStatoOccupazionaleLabels()
    {
        return Allievo::getStatoOccupazionaleLabels();
    }
}
