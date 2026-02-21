<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Avviso;
use app\models\Allievo;
use app\models\Iscrizione;

/**
 * Controller per l'area pubblica studenti.
 * Le iscrizioni sono per BANDO (avviso), non per singolo corso (modulo).
 * Tutte le action sono accessibili senza login (guest).
 */
class StudentiController extends Controller
{
    public $layout = 'studenti-demo';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    ['allow' => true], // Accesso guest a tutte le action
                ],
            ],
        ];
    }

    /**
     * Lista bandi aperti con moduli disponibili.
     */
    public function actionIndex()
    {
        $bandiAperti = Avviso::find()
            ->where(['status' => Avviso::STATUS_PUBBLICATO])
            ->andWhere(['>=', 'data_scadenza', date('Y-m-d')])
            ->orderBy(['data_scadenza' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'bandiAperti' => $bandiAperti,
        ]);
    }

    /**
     * Dettaglio bando con moduli e posti disponibili.
     */
    public function actionBando($id)
    {
        $bando = Avviso::find()
            ->where(['id' => $id, 'status' => Avviso::STATUS_PUBBLICATO])
            ->one();

        if (!$bando) {
            return $this->redirect(['index']);
        }

        return $this->render('bando', [
            'bando' => $bando,
        ]);
    }

    /**
     * Form iscrizione a un BANDO (avviso) con salvataggio reale nel DB.
     * Lo studente si iscrive al bando, non al singolo modulo.
     */
    public function actionIscrizione($id)
    {
        $bando = Avviso::find()
            ->where(['id' => $id])
            ->andWhere(['status' => Avviso::STATUS_PUBBLICATO])
            ->one();

        if (!$bando || !$bando->isAperto()) {
            Yii::$app->session->setFlash('warning', 'Il bando selezionato non è disponibile per le iscrizioni.');
            return $this->redirect(['index']);
        }

        $allievo = new Allievo();
        $iscrizione = new Iscrizione();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            $allievo->load($post);
            $allievo->codice_fiscale = strtoupper($allievo->codice_fiscale ?? '');
            // Pulisci campi ENUM vuoti (MySQL non accetta stringa vuota per ENUM)
            if (empty($allievo->titolo_studio)) $allievo->titolo_studio = null;
            if (empty($allievo->stato_occupazionale)) $allievo->stato_occupazionale = null;

            $iscrizione->load($post);
            $iscrizione->avviso_id = $bando->id;
            $iscrizione->data_iscrizione = date('Y-m-d');
            $iscrizione->status = Iscrizione::STATUS_ISCRITTO;

            // Verifica se allievo esiste gia con stesso CF
            $existingAllievo = Allievo::findOne($allievo->codice_fiscale);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($existingAllievo) {
                    // Aggiorna dati allievo esistente
                    $existingAllievo->attributes = $allievo->attributes;
                    $existingAllievo->codice_fiscale = $allievo->codice_fiscale;
                    if (!$existingAllievo->save()) {
                        throw new \Exception('Errore salvataggio dati: ' . implode(', ', $existingAllievo->getFirstErrors()));
                    }
                    $allievo = $existingAllievo;
                } else {
                    if (!$allievo->save()) {
                        throw new \Exception('Errore salvataggio dati: ' . implode(', ', $allievo->getFirstErrors()));
                    }
                }

                $iscrizione->allievo_codice_fiscale = $allievo->codice_fiscale;

                // Verifica iscrizione duplicata per lo stesso bando
                $iscrizioneEsistente = Iscrizione::find()
                    ->where([
                        'avviso_id' => $bando->id,
                        'allievo_codice_fiscale' => $allievo->codice_fiscale,
                    ])
                    ->andWhere(['not in', 'status', [Iscrizione::STATUS_RITIRATO, Iscrizione::STATUS_NON_AMMESSO]])
                    ->one();

                if ($iscrizioneEsistente) {
                    throw new \Exception('Sei gia iscritto a questo bando.');
                }

                if (!$iscrizione->save()) {
                    throw new \Exception('Errore salvataggio iscrizione: ' . implode(', ', $iscrizione->getFirstErrors()));
                }

                $transaction->commit();

                Yii::$app->session->set('ultima_iscrizione_id', $iscrizione->id);
                return $this->redirect(['conferma']);

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('danger', $e->getMessage());
            }
        }

        return $this->render('iscrizione', [
            'bando' => $bando,
            'allievo' => $allievo,
            'iscrizione' => $iscrizione,
        ]);
    }

    /**
     * Pagina conferma post-iscrizione con dati reali.
     */
    public function actionConferma()
    {
        $iscrizioneId = Yii::$app->session->get('ultima_iscrizione_id');
        $iscrizione = $iscrizioneId ? Iscrizione::findOne($iscrizioneId) : null;

        return $this->render('conferma', [
            'iscrizione' => $iscrizione,
        ]);
    }

    /**
     * Redirect al login area studenti autenticata.
     */
    public function actionLogin()
    {
        return $this->redirect(['/studente-area/login']);
    }

    /**
     * Redirect alla registrazione area studenti.
     */
    public function actionRegistrazione()
    {
        return $this->redirect(['/studente-area/registrazione']);
    }
}
