<?php

namespace app\models;

use Yii;
use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella borsa_studio.
 *
 * @property int $id
 * @property string $allievo_codice_fiscale
 * @property int $progetto_id
 * @property string $anno_formativo
 * @property string $tipologia_sede
 * @property float|null $isee_importo
 * @property float|null $ispe_importo
 * @property string|null $isee_protocollo
 * @property float $importo_base
 * @property string|null $maggiorazione_tipo
 * @property float $maggiorazione_percentuale
 * @property float|null $importo_maggiorato
 * @property float $importo_finale
 * @property float $importo_q1
 * @property float $importo_q2
 * @property float $importo_q3
 * @property float $importo_q4
 * @property float $totale_erogato
 * @property float $percentuale_frequenza
 * @property int $verifiche_superate
 * @property string|null $iban_studente
 * @property string $status
 * @property string|null $motivo_revoca
 * @property string|null $data_approvazione
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Allievo $allievo
 * @property Progetto $progetto
 */
class BorsaStudio extends BaseActiveRecord
{
    // Costanti status
    const STATUS_RICHIESTA = 'richiesta';
    const STATUS_APPROVATA = 'approvata';
    const STATUS_IN_EROGAZIONE = 'in_erogazione';
    const STATUS_COMPLETATA = 'completata';
    const STATUS_REVOCATA = 'revocata';
    const STATUS_SOSPESA = 'sospesa';
    const STATUS_EROGAZIONE_BLOCCATA = 'erogazione_bloccata';

    // Soglia minima di frequenza per erogazione
    const SOGLIA_FREQUENZA = 80;

    // Costanti tipologia sede
    const TIPOLOGIA_IN_SEDE = 'in_sede';
    const TIPOLOGIA_PENDOLARE = 'pendolare';
    const TIPOLOGIA_FUORI_SEDE = 'fuori_sede';

    // Importi base per tipologia sede (bando PNRR)
    const IMPORTO_BASE_IN_SEDE = 2481.75;
    const IMPORTO_BASE_PENDOLARE = 3598.51;
    const IMPORTO_BASE_FUORI_SEDE = 6157.74;

    // Maggiorazioni percentuali
    const MAGGIORAZIONE_ISEE_BASSO = 15;     // +15%
    const MAGGIORAZIONE_STUDENTESSA = 20;     // +20%
    const MAGGIORAZIONE_DISABILITA = 40;      // +40%

    // Limiti ISEE/ISPE
    const ISEE_LIMITE = 27948.60;
    const ISPE_LIMITE = 60757.87;

    public static function tableName()
    {
        return '{{%borsa_studio}}';
    }

    public function rules()
    {
        return [
            [['allievo_codice_fiscale', 'progetto_id', 'anno_formativo', 'tipologia_sede', 'importo_base', 'importo_finale'], 'required'],
            [['progetto_id'], 'integer'],
            [['allievo_codice_fiscale'], 'string', 'max' => 16],
            [['anno_formativo'], 'string', 'max' => 9],
            [['tipologia_sede'], 'in', 'range' => [self::TIPOLOGIA_IN_SEDE, self::TIPOLOGIA_PENDOLARE, self::TIPOLOGIA_FUORI_SEDE]],
            [['isee_importo', 'ispe_importo', 'importo_base', 'importo_maggiorato', 'importo_finale'], 'number'],
            [['importo_q1', 'importo_q2', 'importo_q3', 'importo_q4', 'totale_erogato'], 'number'],
            [['maggiorazione_percentuale', 'percentuale_frequenza'], 'number'],
            [['isee_protocollo'], 'string', 'max' => 100],
            [['maggiorazione_tipo'], 'string', 'max' => 50],
            [['iban_studente'], 'string', 'max' => 34],
            [['verifiche_superate'], 'boolean'],
            [['status'], 'in', 'range' => [self::STATUS_RICHIESTA, self::STATUS_APPROVATA, self::STATUS_IN_EROGAZIONE, self::STATUS_COMPLETATA, self::STATUS_REVOCATA, self::STATUS_SOSPESA, self::STATUS_EROGAZIONE_BLOCCATA]],
            [['motivo_revoca', 'note'], 'string'],
            [['motivo_revoca'], 'required', 'when' => function ($model) {
                return $model->status === self::STATUS_REVOCATA;
            }],
            [['data_approvazione'], 'date', 'format' => 'php:Y-m-d'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'allievo_codice_fiscale' => 'Studente',
            'progetto_id' => 'Progetto/Corso',
            'anno_formativo' => 'Anno Formativo',
            'tipologia_sede' => 'Tipologia Sede',
            'isee_importo' => 'ISEE (€)',
            'ispe_importo' => 'ISPE (€)',
            'isee_protocollo' => 'Protocollo ISEE',
            'importo_base' => 'Importo Base (€)',
            'maggiorazione_tipo' => 'Tipo Maggiorazione',
            'maggiorazione_percentuale' => 'Maggiorazione (%)',
            'importo_maggiorato' => 'Importo Maggiorato (€)',
            'importo_finale' => 'Importo Finale (€)',
            'importo_q1' => 'Q1 Erogato (€)',
            'importo_q2' => 'Q2 Erogato (€)',
            'importo_q3' => 'Q3 Erogato (€)',
            'importo_q4' => 'Q4 Erogato (€)',
            'totale_erogato' => 'Totale Erogato (€)',
            'percentuale_frequenza' => 'Frequenza (%)',
            'verifiche_superate' => 'Verifiche Superate',
            'iban_studente' => 'IBAN Studente',
            'status' => 'Stato',
            'motivo_revoca' => 'Motivo Revoca',
            'data_approvazione' => 'Data Approvazione',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    /**
     * Attributi sensibili da mascherare nel audit log
     */
    protected function auditMaskedAttributes()
    {
        return ['iban_studente'];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAllievo()
    {
        return $this->hasOne(Allievo::class, ['codice_fiscale' => 'allievo_codice_fiscale']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProgetto()
    {
        return $this->hasOne(Progetto::class, ['id' => 'progetto_id']);
    }

    // ---- Tipologia sede ----

    /**
     * Restituisce le opzioni per il dropdown tipologia sede
     * @return array
     */
    public static function getTipologiaSedeList()
    {
        return [
            self::TIPOLOGIA_IN_SEDE => 'In sede',
            self::TIPOLOGIA_PENDOLARE => 'Pendolare',
            self::TIPOLOGIA_FUORI_SEDE => 'Fuori sede',
        ];
    }

    /**
     * Restituisce la label della tipologia sede
     * @return string
     */
    public function getTipologiaSedeLabel()
    {
        return self::getTipologiaSedeList()[$this->tipologia_sede] ?? $this->tipologia_sede;
    }

    /**
     * Restituisce il badge HTML della tipologia sede
     * @return string
     */
    public function getTipologiaSedeBadge()
    {
        $class = match ($this->tipologia_sede) {
            self::TIPOLOGIA_IN_SEDE => 'bg-blue text-blue-fg',
            self::TIPOLOGIA_PENDOLARE => 'bg-azure text-azure-fg',
            self::TIPOLOGIA_FUORI_SEDE => 'bg-purple text-purple-fg',
            default => 'bg-default text-default-fg',
        };
        return '<span class="badge ' . $class . '">' . $this->getTipologiaSedeLabel() . '</span>';
    }

    // ---- Status ----

    /**
     * Restituisce le opzioni per il dropdown status
     * @return array
     */
    public static function getStatusLabels()
    {
        return [
            self::STATUS_RICHIESTA => 'Richiesta',
            self::STATUS_APPROVATA => 'Approvata',
            self::STATUS_IN_EROGAZIONE => 'In Erogazione',
            self::STATUS_COMPLETATA => 'Completata',
            self::STATUS_REVOCATA => 'Revocata',
            self::STATUS_SOSPESA => 'Sospesa',
            self::STATUS_EROGAZIONE_BLOCCATA => 'Erogazione Bloccata',
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
            self::STATUS_RICHIESTA => 'bg-azure text-azure-fg',
            self::STATUS_APPROVATA => 'bg-green text-green-fg',
            self::STATUS_IN_EROGAZIONE => 'bg-blue text-blue-fg',
            self::STATUS_COMPLETATA => 'bg-teal text-teal-fg',
            self::STATUS_REVOCATA => 'bg-red text-red-fg',
            self::STATUS_SOSPESA => 'bg-orange text-orange-fg',
            self::STATUS_EROGAZIONE_BLOCCATA => 'bg-orange text-orange-fg',
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

    // ---- Calcolo importi ----

    /**
     * Restituisce l'importo base per tipologia sede
     * @param string $tipologiaSede
     * @return float
     */
    public static function getImportoBase($tipologiaSede)
    {
        return match ($tipologiaSede) {
            self::TIPOLOGIA_IN_SEDE => self::IMPORTO_BASE_IN_SEDE,
            self::TIPOLOGIA_PENDOLARE => self::IMPORTO_BASE_PENDOLARE,
            self::TIPOLOGIA_FUORI_SEDE => self::IMPORTO_BASE_FUORI_SEDE,
            default => 0,
        };
    }

    /**
     * Calcola la maggiorazione in base a ISEE e altri criteri
     * @param float|null $isee Importo ISEE
     * @param bool $isStudentessa Se lo studente e' femmina
     * @param bool $isDisabile Se lo studente ha disabilita'
     * @return array [tipo => percentuale]
     */
    public static function getMaggiorazione($isee = null, $isStudentessa = false, $isDisabile = false)
    {
        // Priorita: disabilita > isee_basso > studentessa
        if ($isDisabile) {
            return ['disabilita' => self::MAGGIORAZIONE_DISABILITA];
        }
        if ($isee !== null && $isee <= (self::ISEE_LIMITE / 2)) {
            return ['isee_basso' => self::MAGGIORAZIONE_ISEE_BASSO];
        }
        if ($isStudentessa) {
            return ['studentessa' => self::MAGGIORAZIONE_STUDENTESSA];
        }
        return [];
    }

    /**
     * Calcola e imposta gli importi della borsa di studio
     * @param bool $isStudentessa
     * @param bool $isDisabile
     * @return void
     */
    public function calcolaImporto($isStudentessa = false, $isDisabile = false)
    {
        $this->importo_base = self::getImportoBase($this->tipologia_sede);

        $maggiorazione = self::getMaggiorazione($this->isee_importo, $isStudentessa, $isDisabile);

        if (!empty($maggiorazione)) {
            $this->maggiorazione_tipo = array_key_first($maggiorazione);
            $this->maggiorazione_percentuale = array_values($maggiorazione)[0];
            $this->importo_maggiorato = round($this->importo_base * (1 + $this->maggiorazione_percentuale / 100), 2);
            $this->importo_finale = $this->importo_maggiorato;
        } else {
            $this->maggiorazione_tipo = null;
            $this->maggiorazione_percentuale = 0;
            $this->importo_maggiorato = null;
            $this->importo_finale = $this->importo_base;
        }
    }

    // ---- Erogazione ----

    /**
     * Eroga un trimestre
     * @param int $trimestre Numero del trimestre (1-4)
     * @param float $importo Importo da erogare
     * @return bool
     */
    public function erogaTrimestre($trimestre, $importo)
    {
        if ($trimestre < 1 || $trimestre > 4) {
            return false;
        }

        $campo = 'importo_q' . $trimestre;
        $this->$campo = $importo;
        $this->totale_erogato = $this->importo_q1 + $this->importo_q2 + $this->importo_q3 + $this->importo_q4;

        // Aggiorna status se necessario
        if ($this->status === self::STATUS_APPROVATA) {
            $this->status = self::STATUS_IN_EROGAZIONE;
        }
        if (abs($this->totale_erogato - $this->importo_finale) < 0.01) {
            $this->status = self::STATUS_COMPLETATA;
        }

        return $this->save(false);
    }

    /**
     * Restituisce il totale gia' erogato
     * @return float
     */
    public function getTotaleErogato()
    {
        return (float) ($this->importo_q1 + $this->importo_q2 + $this->importo_q3 + $this->importo_q4);
    }

    /**
     * Restituisce il residuo da erogare
     * @return float
     */
    public function getResiduoDaErogare()
    {
        return max(0, $this->importo_finale - $this->getTotaleErogato());
    }

    /**
     * Restituisce l'importo previsto per trimestre
     * @return float
     */
    public function getImportoPerTrimestre()
    {
        return round($this->importo_finale / 4, 2);
    }

    /**
     * Restituisce la percentuale di erogazione
     * @return float
     */
    public function getPercentualeErogazione()
    {
        if ($this->importo_finale <= 0) {
            return 0;
        }
        return round(($this->getTotaleErogato() / $this->importo_finale) * 100, 1);
    }

    // ---- Requisiti di merito ----

    /**
     * Verifica se i requisiti di merito sono soddisfatti
     * @param float $frequenzaMinima Percentuale minima di frequenza (default 80%)
     * @return bool
     */
    public function verificaRequisitiMerito($frequenzaMinima = 80.0)
    {
        return $this->percentuale_frequenza >= $frequenzaMinima && $this->verifiche_superate;
    }

    /**
     * Ricalcola la percentuale di frequenza dello studente nel progetto
     * e aggiorna lo status in base alla soglia minima.
     * @return bool
     */
    public function ricalcolaFrequenza()
    {
        // BorsaStudio.progetto_id punta a progetto, risaliamo agli avvisi
        $avvisoIds = \app\models\Avviso::find()
            ->select('id')
            ->where(['progetto_id' => $this->progetto_id, 'deleted_at' => null])
            ->column();

        $oreTotali = \app\models\Calendario::find()
            ->alias('c')
            ->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')
            ->where(['m.avviso_id' => $avvisoIds, 'c.deleted_at' => null])
            ->sum('c.ore') ?: 0;

        $orePresenza = \app\models\PresenzaAllievo::find()
            ->alias('pa')
            ->innerJoin('{{%calendario}} c', 'c.id = pa.calendario_id AND c.deleted_at IS NULL')
            ->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')
            ->where([
                'pa.allievo_codice_fiscale' => $this->allievo_codice_fiscale,
                'pa.presente' => 1,
                'pa.deleted_at' => null,
                'm.avviso_id' => $avvisoIds,
            ])
            ->sum('pa.ore_presenza') ?: 0;

        $this->percentuale_frequenza = $oreTotali > 0 ? round($orePresenza / $oreTotali * 100, 1) : 0;

        if ($this->status === self::STATUS_APPROVATA && $this->percentuale_frequenza < self::SOGLIA_FREQUENZA) {
            $this->status = self::STATUS_EROGAZIONE_BLOCCATA;
        } elseif ($this->status === self::STATUS_EROGAZIONE_BLOCCATA && $this->percentuale_frequenza >= self::SOGLIA_FREQUENZA) {
            $this->status = self::STATUS_APPROVATA;
        }

        return $this->save(false);
    }
}
