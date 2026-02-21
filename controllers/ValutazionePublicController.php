<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\ValutazioneCodice;
use app\models\ValutazioneDocente;

/**
 * Controller pubblico per la valutazione anonima dei docenti.
 * NON richiede autenticazione.
 */
class ValutazionePublicController extends Controller
{
    public $layout = 'login';

    public function behaviors()
    {
        return [];
    }

    /**
     * Disabilita CSRF per il submit anonimo
     */
    public function beforeAction($action)
    {
        if ($action->id === 'submit') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Mostra il form di valutazione per un codice
     */
    public function actionIndex($codice)
    {
        $valutazioneCodice = ValutazioneCodice::find()
            ->where(['codice' => strtoupper($codice)])
            ->one();

        if ($valutazioneCodice === null || $valutazioneCodice->isUsato()) {
            return $this->render('errore');
        }

        $modulo = $valutazioneCodice->modulo;
        if ($modulo === null) {
            return $this->render('errore');
        }

        // Trova il docente assegnato al modulo (prima assegnazione attiva)
        $assegnazione = $modulo->getAssegnazioni()
            ->andWhere(['not', ['status' => 'revocato']])
            ->one();

        $docente = $assegnazione ? $assegnazione->docente : null;

        return $this->render('index', [
            'valutazioneCodice' => $valutazioneCodice,
            'modulo' => $modulo,
            'docente' => $docente,
        ]);
    }

    /**
     * Gestisce il submit della valutazione
     */
    public function actionSubmit()
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            throw new NotFoundHttpException('Pagina non trovata.');
        }

        $codice = $request->post('codice');
        $valutazioneCodice = ValutazioneCodice::find()
            ->where(['codice' => $codice])
            ->one();

        if ($valutazioneCodice === null || $valutazioneCodice->isUsato()) {
            return $this->render('errore');
        }

        $modulo = $valutazioneCodice->modulo;
        $assegnazione = $modulo->getAssegnazioni()
            ->andWhere(['not', ['status' => 'revocato']])
            ->one();

        if ($assegnazione === null) {
            return $this->render('errore');
        }

        // Calcola punteggi dai criteri
        $punteggio_chiarezza = (int) $request->post('criterio_1', 0);
        $punteggio_competenza = (int) $request->post('criterio_2', 0);
        $punteggio_disponibilita = (int) $request->post('criterio_3', 0);
        $punteggio_materiali = (int) $request->post('criterio_4', 0);
        $punteggio_globale = (int) $request->post('criterio_5', 0);

        // Validazione base: tutti i criteri devono essere tra 1 e 5
        $punteggi = [$punteggio_chiarezza, $punteggio_competenza, $punteggio_disponibilita, $punteggio_materiali, $punteggio_globale];
        foreach ($punteggi as $p) {
            if ($p < 1 || $p > 5) {
                Yii::$app->session->setFlash('error', 'Seleziona un punteggio per ogni criterio.');
                return $this->redirect(['index', 'codice' => $codice]);
            }
        }

        $valutazione = new ValutazioneDocente();
        $valutazione->modulo_id = $modulo->id;
        $valutazione->docente_codice_fiscale = $assegnazione->docente_codice_fiscale;
        $valutazione->punteggio_chiarezza = $punteggio_chiarezza;
        $valutazione->punteggio_competenza = $punteggio_competenza;
        $valutazione->punteggio_disponibilita = $punteggio_disponibilita;
        $valutazione->punteggio_materiali = $punteggio_materiali;
        $valutazione->punteggio_globale = $punteggio_globale;
        $valutazione->commento = $request->post('commento', '');
        $valutazione->data_valutazione = date('Y-m-d');
        $valutazione->valutazione_codice_id = $valutazioneCodice->id;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($valutazione->save()) {
                $valutazioneCodice->invalida();
                $transaction->commit();
                return $this->redirect(['grazie']);
            } else {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Errore nel salvataggio della valutazione.');
                return $this->redirect(['index', 'codice' => $codice]);
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Si e\' verificato un errore. Riprova.');
            return $this->redirect(['index', 'codice' => $codice]);
        }
    }

    /**
     * Pagina di ringraziamento
     */
    public function actionGrazie()
    {
        return $this->render('grazie');
    }
}
