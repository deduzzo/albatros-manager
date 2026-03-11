<?php

namespace app\controllers;

use Yii;
use app\components\FirmaVerificator;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\AccessControl;

/**
 * FirmaDigitaleController - Pagina demo per la verifica di firme digitali.
 */
class FirmaDigitaleController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Pagina principale con form di upload.
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Verifica la firma digitale del file caricato.
     * @return string|\yii\web\Response
     */
    public function actionVerifica()
    {
        $file = UploadedFile::getInstanceByName('file_firma');

        if (!$file) {
            Yii::$app->session->setFlash('error', 'Nessun file caricato.');
            return $this->redirect(['index']);
        }

        // Verifica estensione
        $ext = strtolower($file->extension);
        if (!in_array($ext, ['p7m', 'pdf'])) {
            Yii::$app->session->setFlash('error', 'Formato file non supportato. Caricare un file .p7m o .pdf.');
            return $this->redirect(['index']);
        }

        // Salva temporaneamente il file
        $uploadDir = Yii::getAlias('@webroot') . '/uploads/firme/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $tempFilename = 'verifica_' . uniqid() . '.' . $ext;
        $tempPath = $uploadDir . $tempFilename;

        if (!$file->saveAs($tempPath)) {
            Yii::$app->session->setFlash('error', 'Errore durante il salvataggio temporaneo del file.');
            return $this->redirect(['index']);
        }

        // Verifica la firma
        $risultato = FirmaVerificator::verifica($tempPath);
        $risultato['nome_file'] = $file->baseName . '.' . $file->extension;
        $risultato['dimensione'] = $file->size;

        // Gestisci il contenuto estratto per il download
        $contenutoPath = null;
        if (!empty($risultato['contenuto_path'])) {
            // Converti il percorso assoluto in relativo per il download
            $webroot = Yii::getAlias('@webroot');
            $contenutoPath = str_replace($webroot . '/', '', $risultato['contenuto_path']);
        }

        // Pulisci il file temporaneo (ma mantieni l'estratto)
        @unlink($tempPath);

        return $this->render('risultato', [
            'risultato' => $risultato,
            'contenutoPath' => $contenutoPath,
        ]);
    }

    /**
     * Scarica il contenuto estratto da un file P7M.
     * @param string $path Percorso relativo del file estratto
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDownloadContenuto($path)
    {
        // Sanifica il percorso per sicurezza
        $path = str_replace(['..', "\0"], '', $path);

        $filePath = Yii::getAlias('@webroot') . '/' . $path;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Il file richiesto non esiste.');
        }

        // Verifica che il file sia nella directory uploads/firme/
        $realPath = realpath($filePath);
        $allowedDir = realpath(Yii::getAlias('@webroot') . '/uploads/firme/');
        if (strpos($realPath, $allowedDir) !== 0) {
            throw new NotFoundHttpException('Accesso al file non consentito.');
        }

        return Yii::$app->response->sendFile($filePath, 'documento_estratto.' . pathinfo($filePath, PATHINFO_EXTENSION));
    }
}
