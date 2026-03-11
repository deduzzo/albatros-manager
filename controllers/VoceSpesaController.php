<?php

namespace app\controllers;

use Yii;
use app\models\VoceSpesaCategoria;
use app\models\VoceSpesa;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * VoceSpesaController - CRUD per voci di spesa e categorie.
 */
class VoceSpesaController extends Controller
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
                    'delete-categoria' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Index - Vista accordion sezione > categorie > voci.
     * @return string
     */
    public function actionIndex()
    {
        $categorie = VoceSpesaCategoria::find()
            ->with('vociSpesa')
            ->orderBy(['ordinamento' => SORT_ASC])
            ->all();

        $bySezione = [];
        foreach ($categorie as $cat) {
            $bySezione[$cat->sezione][] = $cat;
        }

        return $this->render('index', [
            'bySezione' => $bySezione,
        ]);
    }

    /**
     * Crea una nuova voce di spesa.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new VoceSpesa();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Voce di spesa creata con successo.');
            return $this->redirect(['index']);
        }

        return $this->render('_form', [
            'model' => $model,
            'categorie' => VoceSpesaCategoria::getList(),
            'title' => 'Nuova Voce di Spesa',
        ]);
    }

    /**
     * Modifica una voce di spesa.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Voce di spesa aggiornata con successo.');
            return $this->redirect(['index']);
        }

        return $this->render('_form', [
            'model' => $model,
            'categorie' => VoceSpesaCategoria::getList(),
            'title' => 'Modifica Voce di Spesa: ' . $model->nome,
        ]);
    }

    /**
     * Elimina una voce di spesa (soft delete).
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Voce di spesa eliminata con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Crea una nuova categoria.
     * @return string|\yii\web\Response
     */
    public function actionCreateCategoria()
    {
        $model = new VoceSpesaCategoria();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Categoria creata con successo.');
            return $this->redirect(['index']);
        }

        return $this->render('_form_categoria', [
            'model' => $model,
        ]);
    }

    /**
     * Modifica una categoria.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateCategoria($id)
    {
        $model = VoceSpesaCategoria::findOne(['id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('La categoria richiesta non esiste.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Categoria aggiornata con successo.');
            return $this->redirect(['index']);
        }

        return $this->render('_form_categoria', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina una categoria (soft delete).
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteCategoria($id)
    {
        $model = VoceSpesaCategoria::findOne(['id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('La categoria richiesta non esiste.');
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Categoria eliminata con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Trova il model VoceSpesa.
     * @param int $id
     * @return VoceSpesa
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = VoceSpesa::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La voce di spesa richiesta non esiste.');
    }
}
