<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use app\models\Istanza;
use app\models\IstanzaTag;
use app\models\IstanzaDocumento;
use app\models\IstanzaSnapshot;
use app\models\Modulo;
use app\models\Docente;
use app\models\AvvisoDocumentoTipo;

/**
 * Form per l'iscrizione a un modulo
 */
class IscrizioneForm extends Model
{
    public $moduloId;
    public $docenteCodiceFiscale;
    public $note;
    public $tags = [];
    public $tagsLivelli = [];
    public $documenti = [];
    public $caricaCurriculumPrecedente = false;
    public $caricaTagPrecedenti = false;

    public function rules()
    {
        return [
            [['moduloId', 'docenteCodiceFiscale'], 'required'],
            [['moduloId'], 'integer'],
            [['docenteCodiceFiscale'], 'string', 'max' => 16],
            [['note'], 'string'],
            [['tags', 'tagsLivelli'], 'safe'],
            [['caricaCurriculumPrecedente', 'caricaTagPrecedenti'], 'boolean'],
            [['documenti'], 'file', 'maxFiles' => 10, 'maxSize' => 10 * 1024 * 1024], // 10MB per file
        ];
    }

    public function attributeLabels()
    {
        return [
            'moduloId' => 'Modulo',
            'note' => 'Note aggiuntive',
            'tags' => 'Tag curriculum',
            'documenti' => 'Documenti',
            'caricaCurriculumPrecedente' => 'Carica curriculum precedente',
            'caricaTagPrecedenti' => 'Carica tag da profilo',
        ];
    }

    /**
     * Salva l'iscrizione
     */
    public function salva()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $modulo = Modulo::findOne($this->moduloId);
            if (!$modulo) {
                throw new \Exception('Modulo non trovato.');
            }

            $docente = Docente::findOne(['codice_fiscale' => $this->docenteCodiceFiscale]);
            if (!$docente) {
                throw new \Exception('Docente non trovato.');
            }

            // Crea istanza
            $istanza = new Istanza();
            $istanza->avviso_id = $modulo->avviso_id;
            $istanza->modulo_id = $this->moduloId;
            $istanza->docente_codice_fiscale = $this->docenteCodiceFiscale;
            $istanza->data_presentazione = date('Y-m-d');
            $istanza->status = Istanza::STATUS_PRESENTATA;
            $istanza->note = $this->note;

            if (!$istanza->save()) {
                throw new \Exception('Errore nel salvataggio dell\'iscrizione: ' . implode(', ', $istanza->getFirstErrors()));
            }

            // Salva tag selezionati
            $tagsIscrizione = [];
            if (!empty($this->tags)) {
                foreach ($this->tags as $tagId) {
                    $istanzaTag = new IstanzaTag();
                    $istanzaTag->istanza_id = $istanza->id;
                    $istanzaTag->tag_id = $tagId;
                    $istanzaTag->livello = $this->tagsLivelli[$tagId] ?? null;

                    if ($istanzaTag->save()) {
                        $tagsIscrizione[] = [
                            'tag_id' => $tagId,
                            'livello' => $istanzaTag->livello,
                        ];
                    }
                }
            }

            // Carica documenti
            if (!empty($this->documenti)) {
                $this->salvaDocumenti($istanza, $modulo);
            }

            // Crea snapshot
            IstanzaSnapshot::creaSnapshot($istanza, $tagsIscrizione);

            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Errore iscrizione: ' . $e->getMessage());
            $this->addError('moduloId', $e->getMessage());
            return false;
        }
    }

    /**
     * Salva i documenti caricati
     */
    protected function salvaDocumenti($istanza, $modulo)
    {
        $uploadPath = Yii::getAlias('@webroot/uploads/istanze/' . $istanza->id);

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $documentiRichiesti = $modulo->avviso->documentiRichiesti;
        $tipiDocumentoMap = [];
        foreach ($documentiRichiesti as $docTipo) {
            $tipiDocumentoMap[$docTipo->codice] = $docTipo->id;
        }

        foreach ($this->documenti as $index => $file) {
            if ($file instanceof UploadedFile) {
                $fileName = Yii::$app->security->generateRandomString(16) . '.' . $file->extension;
                $relativePath = 'istanze/' . $istanza->id . '/' . $fileName;

                if ($file->saveAs($uploadPath . '/' . $fileName)) {
                    $doc = new IstanzaDocumento();
                    $doc->istanza_id = $istanza->id;
                    // Usa il primo tipo documento disponibile se non specificato
                    $doc->avviso_documento_tipo_id = reset($tipiDocumentoMap) ?: 1;
                    $doc->nome_file_originale = $file->name;
                    $doc->nome_file_salvato = $fileName;
                    $doc->path = $relativePath;
                    $doc->mime_type = $file->type ?: 'application/octet-stream';
                    $doc->dimensione_bytes = $file->size;
                    $doc->save();
                }
            }
        }
    }
}
