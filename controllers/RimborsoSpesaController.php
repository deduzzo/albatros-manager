<?php

namespace app\controllers;

use Yii;
use app\models\RimborsoSpesa;
use app\models\RimborsoSpesaSearch;
use app\models\Assegnazione;
use app\models\VoceSpesa;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * RimborsoSpesaController implementa le azioni CRUD per RimborsoSpesa (Admin).
 */
class RimborsoSpesaController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'approva' => ['POST'],
                    'rifiuta' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista tutti i rimborsi spesa.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RimborsoSpesaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza un singolo rimborso spesa.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $model->populateRelation('dettagli', $model->getDettagli()->all());

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Crea un nuovo rimborso spesa (admin).
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new RimborsoSpesa();
        $model->status = RimborsoSpesa::STATUS_RICHIESTO;
        $model->data_spesa = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            // Upload allegato
            $file = UploadedFile::getInstanceByName('allegato');
            if ($file) {
                $uploadPath = Yii::getAlias('@webroot/uploads/rimborsi');
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $fileName = 'rimborso_admin_' . time() . '.' . $file->extension;
                if ($file->saveAs($uploadPath . '/' . $fileName)) {
                    $model->allegato_path = 'uploads/rimborsi/' . $fileName;
                }
            }

            if ($model->save()) {
                // Salva dettagli
                $dettagliData = Yii::$app->request->post('Dettagli', []);
                if (!empty($dettagliData)) {
                    $model->salvaDettagli($dettagliData);
                }
                Yii::$app->session->setFlash('success', 'Rimborso spesa creato con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        // Lista assegnazioni per dropdown
        $assegnazioni = $this->getAssegnazioniList();
        $vociSpesa = VoceSpesa::getListGrouped();

        return $this->render('create', [
            'model' => $model,
            'assegnazioni' => $assegnazioni,
            'vociSpesa' => $vociSpesa,
        ]);
    }

    /**
     * Modifica un rimborso spesa esistente (admin).
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Upload allegato
            $file = UploadedFile::getInstanceByName('allegato');
            if ($file) {
                $uploadPath = Yii::getAlias('@webroot/uploads/rimborsi');
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $fileName = 'rimborso_admin_' . time() . '.' . $file->extension;
                if ($file->saveAs($uploadPath . '/' . $fileName)) {
                    $model->allegato_path = 'uploads/rimborsi/' . $fileName;
                }
            }

            if ($model->save()) {
                // Salva dettagli
                $dettagliData = Yii::$app->request->post('Dettagli', []);
                if (!empty($dettagliData)) {
                    $model->salvaDettagli($dettagliData);
                }
                Yii::$app->session->setFlash('success', 'Rimborso spesa aggiornato con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $assegnazioni = $this->getAssegnazioniList();
        $vociSpesa = VoceSpesa::getListGrouped();

        return $this->render('update', [
            'model' => $model,
            'assegnazioni' => $assegnazioni,
            'vociSpesa' => $vociSpesa,
        ]);
    }

    /**
     * Approva un rimborso spesa.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionApprova($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== RimborsoSpesa::STATUS_RICHIESTO) {
            Yii::$app->session->setFlash('error', 'Solo i rimborsi in stato "Richiesto" possono essere approvati.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $userId = Yii::$app->user->id;
        if ($model->approva($userId)) {
            Yii::$app->session->setFlash('success', 'Rimborso spesa approvato con successo.');
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante l\'approvazione del rimborso.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Rifiuta un rimborso spesa con motivo obbligatorio.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRifiuta($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== RimborsoSpesa::STATUS_RICHIESTO) {
            Yii::$app->session->setFlash('error', 'Solo i rimborsi in stato "Richiesto" possono essere rifiutati.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $motivo = Yii::$app->request->post('motivo_rifiuto');
        if (empty($motivo)) {
            Yii::$app->session->setFlash('error', 'Il motivo del rifiuto e\' obbligatorio.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $userId = Yii::$app->user->id;
        if ($model->rifiuta($motivo, $userId)) {
            Yii::$app->session->setFlash('success', 'Rimborso spesa rifiutato.');
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante il rifiuto del rimborso.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Elimina (soft delete) un rimborso spesa.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Rimborso spesa eliminato con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base all'ID.
     * @param int $id
     * @return RimborsoSpesa
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = RimborsoSpesa::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Il rimborso spesa richiesto non esiste.');
    }

    /**
     * Restituisce la lista delle assegnazioni per dropdown admin
     * @return array [id => "Docente - Modulo"]
     */
    private function getAssegnazioniList()
    {
        $assegnazioni = Assegnazione::findWithDeleted()
            ->alias('a')
            ->leftJoin('docente d', 'a.docente_codice_fiscale = d.codice_fiscale')
            ->leftJoin('modulo m', 'a.modulo_id = m.id')
            ->andWhere(['a.deleted_at' => null])
            ->select([
                'a.id',
                "CONCAT(d.cognome, ' ', d.nome, ' - ', COALESCE(m.codice, ''), ' ', COALESCE(m.titolo, '')) as label",
            ])
            ->orderBy(['d.cognome' => SORT_ASC, 'd.nome' => SORT_ASC])
            ->asArray()
            ->all();

        $result = [];
        foreach ($assegnazioni as $a) {
            $result[$a['id']] = $a['label'];
        }
        return $result;
    }
}
