<?php

namespace app\models;

use Yii;
use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella pagamento.
 *
 * @property int $id
 * @property int $contratto_id
 * @property float $importo_lordo
 * @property float|null $ritenute
 * @property float $importo_netto
 * @property string|null $data_pagamento
 * @property string|null $data_scadenza
 * @property string $tipo
 * @property string|null $riferimento_fattura
 * @property string|null $numero_mandato
 * @property string|null $cro_bonifico
 * @property string $status
 * @property string|null $note
 * @property int $ore_extra
 * @property string|null $ore_extra_status
 * @property int|null $ore_extra_approvato_da
 * @property string|null $ore_extra_approvato_at
 * @property string|null $ore_extra_note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Contratto $contratto
 * @property User $oreExtraApprovatoDaUser
 */
class Pagamento extends BaseActiveRecord
{
    const TIPO_ACCONTO = 'acconto';
    const TIPO_SALDO = 'saldo';
    const TIPO_RATA = 'rata';
    const TIPO_RIMBORSO = 'rimborso';

    const STATUS_PROGRAMMATO = 'programmato';
    const STATUS_IN_ATTESA = 'in_attesa';
    const STATUS_PAGATO = 'pagato';
    const STATUS_ANNULLATO = 'annullato';

    // Costanti ore extra status
    const ORE_EXTRA_PENDENTE = 'pendente';
    const ORE_EXTRA_APPROVATO = 'approvato';
    const ORE_EXTRA_RIFIUTATO = 'rifiutato';

    public static function tableName()
    {
        return '{{%pagamento}}';
    }

    public function rules()
    {
        return [
            [['contratto_id', 'importo_lordo', 'importo_netto', 'tipo', 'status'], 'required'],
            [['contratto_id'], 'integer'],
            [['importo_lordo', 'ritenute', 'importo_netto'], 'number'],
            [['data_pagamento', 'data_scadenza'], 'date', 'format' => 'php:Y-m-d'],
            [['tipo'], 'in', 'range' => [self::TIPO_ACCONTO, self::TIPO_SALDO, self::TIPO_RATA, self::TIPO_RIMBORSO]],
            [['status'], 'in', 'range' => [self::STATUS_PROGRAMMATO, self::STATUS_IN_ATTESA, self::STATUS_PAGATO, self::STATUS_ANNULLATO]],
            [['riferimento_fattura'], 'string', 'max' => 100],
            [['numero_mandato', 'cro_bonifico'], 'string', 'max' => 50],
            [['note', 'ore_extra_note'], 'string'],
            [['ore_extra'], 'boolean'],
            [['ore_extra_status'], 'in', 'range' => [self::ORE_EXTRA_PENDENTE, self::ORE_EXTRA_APPROVATO, self::ORE_EXTRA_RIFIUTATO]],
            [['ore_extra_approvato_da'], 'integer'],
            [['ore_extra_approvato_at'], 'safe'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contratto_id' => 'Contratto',
            'importo_lordo' => 'Importo Lordo',
            'ritenute' => 'Ritenute',
            'importo_netto' => 'Importo Netto',
            'data_pagamento' => 'Data Pagamento',
            'data_scadenza' => 'Data Scadenza',
            'tipo' => 'Tipo',
            'riferimento_fattura' => 'Riferimento Fattura',
            'numero_mandato' => 'Numero Mandato',
            'cro_bonifico' => 'CRO Bonifico',
            'status' => 'Stato',
            'note' => 'Note',
            'ore_extra' => 'Ore Extra',
            'ore_extra_status' => 'Stato Ore Extra',
            'ore_extra_approvato_da' => 'Ore Extra Approvato Da',
            'ore_extra_approvato_at' => 'Ore Extra Approvato Il',
            'ore_extra_note' => 'Note Ore Extra',
        ];
    }

    public function getContratto()
    {
        return $this->hasOne(Contratto::class, ['id' => 'contratto_id']);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Auto-creazione MovimentoSpesa quando il pagamento diventa "pagato"
        if (isset($changedAttributes['status']) && $this->status === self::STATUS_PAGATO) {
            $this->creaMovimentoSpesaAutomatico();
        }
    }

    /**
     * Crea automaticamente un MovimentoSpesa collegato a questo pagamento.
     * Non crea duplicati: verifica che non esista gia' un movimento per questo pagamento.
     */
    protected function creaMovimentoSpesaAutomatico()
    {
        try {
            // Verifica che non esista gia' un movimento per questo pagamento
            $esistente = MovimentoSpesa::find()
                ->where([
                    'riferimento_tipo' => MovimentoSpesa::RIFERIMENTO_PAGAMENTO,
                    'riferimento_id' => $this->id,
                ])
                ->exists();

            if ($esistente) {
                Yii::debug("MovimentoSpesa gia' esistente per pagamento #{$this->id}", __METHOD__);
                return;
            }

            // Risali la catena: contratto → assegnazione → modulo → avviso → progetto_id
            $contratto = $this->contratto;
            if (!$contratto) {
                Yii::warning("Pagamento #{$this->id}: contratto non trovato, impossibile creare MovimentoSpesa", __METHOD__);
                return;
            }

            $assegnazione = $contratto->assegnazione;
            if (!$assegnazione) {
                Yii::warning("Pagamento #{$this->id}: assegnazione non trovata, impossibile creare MovimentoSpesa", __METHOD__);
                return;
            }

            $modulo = $assegnazione->modulo;
            $docente = $assegnazione->docente;
            $avviso = $modulo ? $modulo->avviso : null;
            $progettoId = $avviso ? $avviso->progetto_id : null;

            // Cerca la voce di spesa appropriata
            $voceSpesaId = $this->trovaVoceSpesa();
            if (!$voceSpesaId) {
                Yii::warning("Pagamento #{$this->id}: nessuna voce di spesa trovata, impossibile creare MovimentoSpesa", __METHOD__);
                return;
            }

            // Componi la descrizione
            $nomeDocente = $docente ? $docente->getNomeCompleto() : 'N/D';
            $nomeModulo = $modulo ? $modulo->titolo : 'N/D';
            $descrizione = "Pagamento docente {$nomeDocente} - {$nomeModulo}";

            // Crea il movimento
            $movimento = new MovimentoSpesa();
            $movimento->progetto_id = $progettoId;
            $movimento->voce_spesa_id = $voceSpesaId;
            $movimento->descrizione = $descrizione;
            $movimento->importo = (float) $this->importo_lordo;
            $movimento->data_movimento = $this->data_pagamento ?: date('Y-m-d');
            $movimento->tipo = MovimentoSpesa::TIPO_SPESA;
            $movimento->riferimento_tipo = MovimentoSpesa::RIFERIMENTO_PAGAMENTO;
            $movimento->riferimento_id = $this->id;
            $movimento->fornitore = $nomeDocente;

            if (!$movimento->save()) {
                Yii::error("Pagamento #{$this->id}: errore creazione MovimentoSpesa: " . implode(', ', $movimento->getFirstErrors()), __METHOD__);
            } else {
                Yii::info("MovimentoSpesa #{$movimento->id} creato automaticamente per pagamento #{$this->id}", __METHOD__);
            }
        } catch (\Throwable $e) {
            Yii::error("Pagamento #{$this->id}: eccezione durante creazione MovimentoSpesa: " . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * Trova la voce di spesa appropriata in base al tipo di pagamento.
     * I codici delle voci di spesa sono configurabili tramite Configurazione.
     * @return int|null ID della voce di spesa trovata
     */
    protected function trovaVoceSpesa()
    {
        $codiceRimborso = Configurazione::get('voce_spesa_rimborso_codice', 'B2.13');
        $codiceDocenza = Configurazione::get('voce_spesa_docenti_codice', 'B2.1');

        if ($this->tipo === self::TIPO_RIMBORSO) {
            // Cerca voce rimborsi
            $voce = VoceSpesa::find()
                ->where(['like', 'codice', $codiceRimborso])
                ->one();
            if ($voce) {
                return $voce->id;
            }
        }

        // Cerca voce Docenza (esatto, poi con wildcard)
        $voce = VoceSpesa::find()
            ->where(['codice' => $codiceDocenza])
            ->one();
        if ($voce) {
            return $voce->id;
        }

        // Cerca voce con codice che inizia con il codice configurato
        $voce = VoceSpesa::find()
            ->where(['like', 'codice', $codiceDocenza . '%', false])
            ->one();
        if ($voce) {
            return $voce->id;
        }

        // Fallback: prima voce disponibile
        $voce = VoceSpesa::find()
            ->orderBy(['id' => SORT_ASC])
            ->one();

        return $voce ? $voce->id : null;
    }

    public static function getTipoLabels()
    {
        return [
            self::TIPO_ACCONTO => 'Acconto',
            self::TIPO_SALDO => 'Saldo',
            self::TIPO_RATA => 'Rata',
            self::TIPO_RIMBORSO => 'Rimborso',
        ];
    }

    public function getTipoLabel()
    {
        return self::getTipoLabels()[$this->tipo] ?? $this->tipo;
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_PROGRAMMATO => 'Programmato',
            self::STATUS_IN_ATTESA => 'In Attesa',
            self::STATUS_PAGATO => 'Pagato',
            self::STATUS_ANNULLATO => 'Annullato',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_PROGRAMMATO => 'bg-default text-default-fg',
            self::STATUS_IN_ATTESA => 'bg-orange text-orange-fg',
            self::STATUS_PAGATO => 'bg-green text-green-fg',
            self::STATUS_ANNULLATO => 'bg-red text-red-fg',
            default => 'bg-default text-default-fg',
        };
    }

    /**
     * Restituisce la classe badge CSS per il tipo di pagamento.
     * @return string
     */
    public function getTipoBadgeClass()
    {
        return match ($this->tipo) {
            self::TIPO_ACCONTO => 'bg-azure text-azure-fg',
            self::TIPO_SALDO => 'bg-blue text-blue-fg',
            self::TIPO_RATA => 'bg-cyan text-cyan-fg',
            self::TIPO_RIMBORSO => 'bg-orange text-orange-fg',
            default => 'bg-default text-default-fg',
        };
    }

    // --- Ore extra ---

    public function getOreExtraApprovatoDaUser()
    {
        return $this->hasOne(User::class, ['id' => 'ore_extra_approvato_da']);
    }

    /**
     * Verifica se il pagamento riguarda ore extra
     * @return bool
     */
    public function isOreExtra()
    {
        return (bool) $this->ore_extra;
    }

    /**
     * Verifica se le ore extra sono approvate
     * @return bool
     */
    public function isOreExtraApprovato()
    {
        return $this->ore_extra_status === self::ORE_EXTRA_APPROVATO;
    }

    public static function getOreExtraStatusLabels()
    {
        return [
            self::ORE_EXTRA_PENDENTE => 'Pendente',
            self::ORE_EXTRA_APPROVATO => 'Approvato',
            self::ORE_EXTRA_RIFIUTATO => 'Rifiutato',
        ];
    }

    public function getOreExtraStatusLabel()
    {
        if (!$this->ore_extra_status) {
            return null;
        }
        return self::getOreExtraStatusLabels()[$this->ore_extra_status] ?? $this->ore_extra_status;
    }

    public function getOreExtraStatusBadgeClass()
    {
        return match ($this->ore_extra_status) {
            self::ORE_EXTRA_PENDENTE => 'bg-orange text-orange-fg',
            self::ORE_EXTRA_APPROVATO => 'bg-green text-green-fg',
            self::ORE_EXTRA_RIFIUTATO => 'bg-red text-red-fg',
            default => 'bg-default text-default-fg',
        };
    }
}
