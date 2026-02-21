<?php

namespace app\controllers;

use Yii;
use app\models\Configurazione;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * ConfigurazioneController - Gestione dati fondazione e impostazioni sistema.
 */
class ConfigurazioneController extends Controller
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
        ];
    }

    /**
     * Mostra il form configurazione raggruppato per gruppo.
     * @return string
     */
    public function actionIndex()
    {
        $gruppi = Configurazione::getByGruppo();

        return $this->render('index', [
            'gruppi' => $gruppi,
        ]);
    }

    /**
     * Salva le impostazioni di configurazione.
     * @return \yii\web\Response
     */
    public function actionSalva()
    {
        $post = Yii::$app->request->post('Configurazione', []);

        if (!empty($post)) {
            $salvati = 0;
            foreach ($post as $chiave => $valore) {
                if (Configurazione::set($chiave, $valore)) {
                    $salvati++;
                }
            }
            Yii::$app->session->setFlash('success', "Impostazioni salvate con successo ({$salvati} campi aggiornati).");
        }

        return $this->redirect(['index']);
    }
}
