<?php

namespace app\models;

use Yii;
use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella richiesta_fatturazione.
 *
 * @property int $id
 * @property int $contratto_id
 * @property string $tipo
 * @property float $importo
 * @property string|null $periodo_riferimento
 * @property string $data_richiesta
 * @property string|null $data_scadenza
 * @property string $destinatario_email
 * @property string|null $destinatario_nome
 * @property string|null $oggetto_email
 * @property string|null $corpo_email
 * @property string|null $documento_ricevuto_path
 * @property string|null $data_ricezione
 * @property string $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Contratto $contratto
 */
class RichiestaFatturazione extends BaseActiveRecord
{
    // Costanti tipo
    const TIPO_FATTURA = 'fattura';
    const TIPO_CEDOLINO = 'cedolino';

    // Costanti status
    const STATUS_BOZZA = 'bozza';
    const STATUS_INVIATA = 'inviata';
    const STATUS_RICEVUTA = 'ricevuta';
    const STATUS_VERIFICATA = 'verificata';

    public static function tableName()
    {
        return '{{%richiesta_fatturazione}}';
    }

    public function rules()
    {
        return [
            [['contratto_id', 'tipo', 'importo', 'data_richiesta', 'destinatario_email'], 'required'],
            [['contratto_id'], 'integer'],
            [['importo'], 'number', 'min' => 0.01],
            [['data_richiesta', 'data_scadenza', 'data_ricezione'], 'date', 'format' => 'php:Y-m-d'],
            [['periodo_riferimento'], 'string', 'max' => 100],
            [['destinatario_email'], 'string', 'max' => 255],
            [['destinatario_email'], 'email'],
            [['destinatario_nome'], 'string', 'max' => 255],
            [['oggetto_email'], 'string', 'max' => 500],
            [['corpo_email', 'note'], 'string'],
            [['documento_ricevuto_path'], 'string', 'max' => 500],
            [['tipo'], 'in', 'range' => [self::TIPO_FATTURA, self::TIPO_CEDOLINO]],
            [['status'], 'in', 'range' => [self::STATUS_BOZZA, self::STATUS_INVIATA, self::STATUS_RICEVUTA, self::STATUS_VERIFICATA]],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contratto_id' => 'Contratto',
            'tipo' => 'Tipo',
            'importo' => 'Importo (€)',
            'periodo_riferimento' => 'Periodo Riferimento',
            'data_richiesta' => 'Data Richiesta',
            'data_scadenza' => 'Data Scadenza',
            'destinatario_email' => 'Email Destinatario',
            'destinatario_nome' => 'Nome Destinatario',
            'oggetto_email' => 'Oggetto Email',
            'corpo_email' => 'Corpo Email',
            'documento_ricevuto_path' => 'Documento Ricevuto',
            'data_ricezione' => 'Data Ricezione',
            'status' => 'Stato',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContratto()
    {
        return $this->hasOne(Contratto::class, ['id' => 'contratto_id']);
    }

    // ---- Labels e badge ----

    /**
     * Restituisce le opzioni per il dropdown tipo
     * @return array
     */
    public static function getTipoList()
    {
        return [
            self::TIPO_FATTURA => 'Fattura (P.IVA)',
            self::TIPO_CEDOLINO => 'Cedolino (Co.Co.Co)',
        ];
    }

    /**
     * Restituisce la label del tipo
     * @return string
     */
    public function getTipoLabel()
    {
        return self::getTipoList()[$this->tipo] ?? $this->tipo;
    }

    /**
     * Restituisce il badge HTML del tipo
     * @return string
     */
    public function getTipoBadge()
    {
        $class = match ($this->tipo) {
            self::TIPO_FATTURA => 'bg-blue text-blue-fg',
            self::TIPO_CEDOLINO => 'bg-purple text-purple-fg',
            default => 'bg-default text-default-fg',
        };
        return '<span class="badge ' . $class . '">' . $this->getTipoLabel() . '</span>';
    }

    /**
     * Restituisce le opzioni per il dropdown status
     * @return array
     */
    public static function getStatusLabels()
    {
        return [
            self::STATUS_BOZZA => 'Bozza',
            self::STATUS_INVIATA => 'Inviata',
            self::STATUS_RICEVUTA => 'Ricevuta',
            self::STATUS_VERIFICATA => 'Verificata',
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
            self::STATUS_BOZZA => 'bg-default text-default-fg',
            self::STATUS_INVIATA => 'bg-azure text-azure-fg',
            self::STATUS_RICEVUTA => 'bg-orange text-orange-fg',
            self::STATUS_VERIFICATA => 'bg-green text-green-fg',
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

    // ---- Azioni ----

    /**
     * Invia la richiesta (cambia status e invia email)
     * @return bool
     */
    public function invia()
    {
        if ($this->status !== self::STATUS_BOZZA) {
            return false;
        }

        // Genera corpo email se non presente
        if (empty($this->corpo_email)) {
            $this->corpo_email = $this->generaCorpoEmail();
        }

        $this->status = self::STATUS_INVIATA;
        return $this->save(false);
    }

    /**
     * Genera il corpo dell'email in base al tipo e ai dati del contratto
     * @return string
     */
    public function generaCorpoEmail()
    {
        $contratto = $this->contratto;
        if (!$contratto) {
            return '';
        }

        $assegnazione = $contratto->assegnazione;
        $docente = $assegnazione ? $assegnazione->docente : null;
        $modulo = $assegnazione ? $assegnazione->modulo : null;

        $fondazione = Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
        $cfEnte = Configurazione::get('codice_fiscale_ente') ?: '';

        if ($this->tipo === self::TIPO_FATTURA) {
            return "Gentile {$this->destinatario_nome},\n\n"
                . "si prega di emettere fattura per la seguente prestazione:\n\n"
                . "Ente committente: {$fondazione}\n"
                . "C.F. Ente: {$cfEnte}\n"
                . ($modulo ? "Modulo: {$modulo->titolo}\n" : '')
                . "Importo: € " . number_format($this->importo, 2, ',', '.') . "\n"
                . ($this->periodo_riferimento ? "Periodo: {$this->periodo_riferimento}\n" : '')
                . "N. Contratto: {$contratto->numero_contratto}\n\n"
                . ($this->data_scadenza ? "Scadenza presentazione: {$this->data_scadenza}\n\n" : '')
                . "Cordiali saluti";
        }

        // Cedolino
        return "Spett.le Consulente,\n\n"
            . "si prega di elaborare il cedolino per il seguente collaboratore:\n\n"
            . ($docente ? "Collaboratore: {$docente->getNomeCompleto()}\n" : '')
            . ($docente ? "C.F.: {$docente->codice_fiscale}\n" : '')
            . "Importo lordo: € " . number_format($this->importo, 2, ',', '.') . "\n"
            . ($this->periodo_riferimento ? "Periodo: {$this->periodo_riferimento}\n" : '')
            . "N. Contratto: {$contratto->numero_contratto}\n\n"
            . ($this->data_scadenza ? "Scadenza elaborazione: {$this->data_scadenza}\n\n" : '')
            . "Cordiali saluti";
    }

    /**
     * Verifica se la richiesta e' modificabile
     * @return bool
     */
    public function isModificabile()
    {
        return $this->status === self::STATUS_BOZZA;
    }

    /**
     * Verifica se la richiesta ha il documento ricevuto
     * @return bool
     */
    public function hasDocumentoRicevuto()
    {
        return !empty($this->documento_ricevuto_path);
    }

    /**
     * Invia notifica email al docente quando il cedolino/fattura e' stato caricato.
     * Non blocca il flusso in caso di errore.
     * @return bool true se l'email e' stata inviata con successo
     */
    public function notificaDocumentoCaricato()
    {
        try {
            $contratto = $this->contratto;
            if (!$contratto) {
                Yii::warning("RichiestaFatturazione #{$this->id}: contratto non trovato, notifica non inviata", __METHOD__);
                return false;
            }

            $assegnazione = $contratto->assegnazione;
            $docente = $assegnazione ? $assegnazione->docente : null;
            if (!$docente || empty($docente->email)) {
                Yii::warning("RichiestaFatturazione #{$this->id}: docente o email non trovata, notifica non inviata", __METHOD__);
                return false;
            }

            $modulo = $assegnazione->modulo;
            $fondazione = Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
            $tipoDoc = $this->tipo === self::TIPO_FATTURA ? 'fattura' : 'cedolino';

            $oggetto = ucfirst($tipoDoc) . " disponibile - {$fondazione}";

            $sent = Yii::$app->mailer->compose('notifica-documento-html', [
                    'model' => $this,
                    'docente' => $docente,
                    'modulo' => $modulo,
                    'fondazione' => $fondazione,
                    'tipoDocumento' => $tipoDoc,
                ])
                ->setTo($docente->email)
                ->setSubject($oggetto)
                ->send();

            if ($sent) {
                Yii::info("Notifica documento caricato inviata al docente {$docente->email} per richiesta #{$this->id}", __METHOD__);
            }

            return (bool) $sent;
        } catch (\Throwable $e) {
            Yii::error("RichiestaFatturazione #{$this->id}: errore invio notifica documento: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
