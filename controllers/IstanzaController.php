<?php

namespace app\controllers;

use Yii;
use app\models\Istanza;
use app\models\IstanzaSearch;
use app\models\IstanzaTag;
use app\models\IstanzaDocumento;
use app\models\IstanzaSnapshot;
use app\models\StoricoEsito;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * IstanzaController gestisce le istanze/domande dei docenti.
 */
class IstanzaController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'promuovi-tag' => ['POST'],
                    'promuovi-tutti-tag' => ['POST'],
                    'aggiungi-esito' => ['POST'],
                    'upload-documento' => ['POST'],
                    'delete-documento' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new IstanzaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Crea una nuova istanza/candidatura.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Istanza();
        $model->data_presentazione = date('Y-m-d');
        $model->status = Istanza::STATUS_PRESENTATA;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Istanza creata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Restituisce i moduli per un avviso (AJAX per dropdown dipendente).
     * @param int $avviso_id
     * @return array
     */
    public function actionModuliPerAvviso($avviso_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $moduli = \app\models\Modulo::find()
            ->where(['avviso_id' => $avviso_id])
            ->orderBy(['codice' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($moduli as $modulo) {
            $result[] = ['id' => $modulo->id, 'text' => $modulo->codice . ' - ' . $modulo->titolo];
        }
        return $result;
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Istanza aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Istanza eliminata con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Promuove un singolo tag al profilo docente
     */
    public function actionPromuoviTag($id, $tagId)
    {
        $istanzaTag = IstanzaTag::find()
            ->where(['id' => $tagId, 'istanza_id' => $id])
            ->one();

        if (!$istanzaTag) {
            throw new NotFoundHttpException('Tag non trovato.');
        }

        if ($istanzaTag->isPromosso()) {
            Yii::$app->session->setFlash('warning', 'Questo tag è già stato promosso.');
        } elseif ($istanzaTag->promuovi()) {
            Yii::$app->session->setFlash('success', 'Tag promosso al profilo docente.');
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante la promozione del tag.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Promuove tutti i tag non ancora promossi al profilo docente
     */
    public function actionPromuoviTuttiTag($id)
    {
        $model = $this->findModel($id);
        $count = $model->promuoviTuttiTags();

        if ($count > 0) {
            Yii::$app->session->setFlash('success', "{$count} tag promossi al profilo docente.");
        } else {
            Yii::$app->session->setFlash('info', 'Nessun tag da promuovere.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Aggiunge un nuovo esito storico all'istanza
     */
    public function actionAggiungiEsito($id)
    {
        $model = $this->findModel($id);
        $esito = new StoricoEsito();

        if ($esito->load(Yii::$app->request->post()) && $esito->validate()) {
            $esito->istanza_id = $model->id;
            if ($esito->save()) {
                Yii::$app->session->setFlash('success', 'Esito registrato con successo.');
            } else {
                Yii::$app->session->setFlash('error', 'Errore durante il salvataggio dell\'esito.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Dati non validi.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Visualizza lo snapshot storico dell'istanza
     */
    public function actionSnapshot($id)
    {
        $model = $this->findModel($id);

        if (!$model->snapshot) {
            Yii::$app->session->setFlash('warning', 'Snapshot non disponibile per questa istanza.');
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('snapshot', [
            'model' => $model,
        ]);
    }

    /**
     * Carica un documento per l'istanza
     */
    public function actionUploadDocumento($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $istanza = $this->findModel($id);

        $file = UploadedFile::getInstanceByName('file');
        if (!$file) {
            return ['success' => false, 'error' => 'Nessun file selezionato'];
        }

        // Validazione tipo
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
        ];
        if (!in_array($file->type, $allowedTypes)) {
            return ['success' => false, 'error' => 'Tipo file non consentito. Ammessi: PDF, DOC, DOCX, JPG, PNG'];
        }

        // Validazione dimensione (max 10MB)
        if ($file->size > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Il file supera la dimensione massima di 10MB'];
        }

        // Salva file
        $uploadDir = Yii::getAlias('@webroot/uploads/istanze/' . $istanza->id);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $savedName = uniqid() . '_' . $file->name;
        $file->saveAs($uploadDir . '/' . $savedName);

        // Crea record
        $doc = new IstanzaDocumento();
        $doc->istanza_id = $istanza->id;
        $doc->avviso_documento_tipo_id = Yii::$app->request->post('tipo_id', 0);
        $doc->nome_file_originale = $file->name;
        $doc->nome_file_salvato = $savedName;
        $doc->path = 'istanze/' . $istanza->id . '/' . $savedName;
        $doc->mime_type = $file->type;
        $doc->dimensione_bytes = $file->size;

        if ($doc->save()) {
            return ['success' => true, 'documento' => [
                'id' => $doc->id,
                'nome' => $doc->nome_file_originale,
                'dimensione' => $doc->getDimensioneFormattata(),
                'tipo' => $doc->mime_type,
                'data' => Yii::$app->formatter->asDatetime($doc->created_at),
            ]];
        }
        return ['success' => false, 'errors' => $doc->errors];
    }

    /**
     * Scarica un documento dell'istanza
     */
    public function actionDownloadDocumento($docId)
    {
        $doc = IstanzaDocumento::findOne($docId);
        if (!$doc) {
            throw new NotFoundHttpException('Documento non trovato.');
        }

        $filePath = $doc->getFullPath();
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Il file non esiste sul server.');
        }

        return Yii::$app->response->sendFile($filePath, $doc->nome_file_originale);
    }

    /**
     * Elimina un documento dell'istanza
     */
    public function actionDeleteDocumento($docId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $doc = IstanzaDocumento::findOne($docId);
        if (!$doc) {
            return ['success' => false, 'error' => 'Documento non trovato.'];
        }

        // Elimina file fisico
        $filePath = $doc->getFullPath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Elimina record DB
        if ($doc->delete()) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Errore durante l\'eliminazione del documento.'];
    }

    protected function findModel($id)
    {
        if (($model = Istanza::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Istanza non trovata.');
    }
}
