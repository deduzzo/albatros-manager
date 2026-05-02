<?php

namespace app\controllers;

use Yii;
use app\models\Avviso;
use app\models\Modulo;
use app\models\Assegnazione;
use app\models\Contratto;
use app\models\Iscrizione;
use app\models\Calendario;
use app\models\BorsaStudio;
use app\models\Docente;
use app\models\Istanza;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * GestioneController - Vista Semplificata per la gestione dei corsi.
 */
class GestioneController extends Controller
{
    public $layout = 'gestione';

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
                    'metti-a-bando' => ['POST'],
                    'nuova-candidatura' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * Home: griglia di tutti i corsi con statistiche.
     */
    public function actionIndex()
    {
        $this->layout = 'gestione';
        $this->view->title = 'I tuoi Corsi';

        $corsi = Avviso::findWithDeleted()
            ->alias('a')
            ->with(['progetto'])
            ->andWhere(['a.deleted_at' => null])
            ->orderBy(['a.codice_avviso' => SORT_ASC])
            ->all();

        // Calcola stats per ogni corso con 4 query aggregate (no N+1)
        $moduliTotaliMap = [];
        foreach (Modulo::findWithDeleted()
                     ->select(['avviso_id', 'cnt' => 'COUNT(*)'])
                     ->where(['deleted_at' => null])
                     ->groupBy('avviso_id')
                     ->asArray()
                     ->all() as $row) {
            $moduliTotaliMap[(int) $row['avviso_id']] = (int) $row['cnt'];
        }

        $moduliAssegnatiMap = [];
        foreach (Modulo::findWithDeleted()
                     ->alias('m')
                     ->select(['m.avviso_id', 'cnt' => 'COUNT(DISTINCT m.id)'])
                     ->innerJoin('assegnazione ass', 'ass.modulo_id = m.id AND ass.deleted_at IS NULL')
                     ->andWhere(['m.deleted_at' => null])
                     ->groupBy('m.avviso_id')
                     ->asArray()
                     ->all() as $row) {
            $moduliAssegnatiMap[(int) $row['avviso_id']] = (int) $row['cnt'];
        }

        $docentiMap = [];
        foreach (Assegnazione::findWithDeleted()
                     ->alias('ass')
                     ->select(['m.avviso_id', 'cnt' => 'COUNT(DISTINCT ass.docente_codice_fiscale)'])
                     ->innerJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
                     ->andWhere(['ass.deleted_at' => null])
                     ->groupBy('m.avviso_id')
                     ->asArray()
                     ->all() as $row) {
            $docentiMap[(int) $row['avviso_id']] = (int) $row['cnt'];
        }

        $studentiMap = [];
        foreach (Iscrizione::findWithDeleted()
                     ->alias('i')
                     ->select(['m.avviso_id', 'cnt' => 'COUNT(DISTINCT i.allievo_codice_fiscale)'])
                     ->innerJoin('modulo m', 'm.id = i.modulo_id AND m.deleted_at IS NULL')
                     ->andWhere(['i.deleted_at' => null])
                     ->groupBy('m.avviso_id')
                     ->asArray()
                     ->all() as $row) {
            $studentiMap[(int) $row['avviso_id']] = (int) $row['cnt'];
        }

        $corsiStats = [];
        foreach ($corsi as $corso) {
            $corsiStats[$corso->id] = [
                'moduliTotali'    => $moduliTotaliMap[$corso->id] ?? 0,
                'moduliAssegnati' => $moduliAssegnatiMap[$corso->id] ?? 0,
                'docenti'         => $docentiMap[$corso->id] ?? 0,
                'studenti'        => $studentiMap[$corso->id] ?? 0,
            ];
        }

        return $this->render('index', [
            'corsi'      => $corsi,
            'corsiStats' => $corsiStats,
        ]);
    }

    /**
     * Redirect a panoramica del corso.
     */
    public function actionCorso($id)
    {
        return $this->redirect(['/gestione/sezione', 'id' => $id, 'section' => 'panoramica']);
    }

    /**
     * Renderizza una sezione del corso.
     */
    public function actionSezione($id, $section = 'panoramica')
    {
        $corso = $this->findCorso($id);

        $sectionViews = [
            'panoramica'      => 'corso',
            'piano-formativo' => 'piano-formativo',
            'personale'       => 'personale',
            'studenti'        => 'studenti',
            'calendario'      => 'calendario',
            'contratti'       => 'contratti',
            'borse-studio'    => 'borse-studio',
            'import-export'   => 'import-export',
        ];

        if (!isset($sectionViews[$section])) {
            throw new NotFoundHttpException('Sezione non trovata.');
        }

        $viewName = $sectionViews[$section];
        $sectionLabels = [
            'panoramica'      => 'Panoramica',
            'piano-formativo' => 'Piano Formativo',
            'personale'       => 'Personale',
            'studenti'        => 'Studenti',
            'calendario'      => 'Calendario',
            'contratti'       => 'Contratti',
            'borse-studio'    => 'Borse di Studio',
            'import-export'   => 'Import / Export',
        ];

        $this->view->title = $corso->codice_avviso . ' - ' . ($sectionLabels[$section] ?? $section);

        $data = $this->loadSectionData($corso, $section);
        $data['corso'] = $corso;
        $data['section'] = $section;

        return $this->render($viewName, $data);
    }

    /**
     * Carica i dati specifici per ogni sezione.
     */
    protected function loadSectionData(Avviso $corso, string $section): array
    {
        switch ($section) {
            case 'panoramica':
                // Docenti assegnati senza contratto
                $docentiSenzaContratto = Assegnazione::findWithDeleted()
                    ->alias('ass')
                    ->with(['docente', 'modulo'])
                    ->innerJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
                    ->leftJoin('contratto c', 'c.assegnazione_id = ass.id AND c.deleted_at IS NULL')
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['ass.deleted_at' => null])
                    ->andWhere(['c.id' => null])
                    ->all();

                // Lezioni con presenze non acquisite (data passata, docente_presente IS NULL)
                $lezioniSenzaPresenze = Calendario::findWithDeleted()
                    ->alias('cal')
                    ->with(['modulo'])
                    ->innerJoin('modulo m', 'm.id = cal.modulo_id AND m.deleted_at IS NULL')
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['cal.deleted_at' => null])
                    ->andWhere(['cal.docente_presente' => null])
                    ->andWhere(['<', 'cal.data', date('Y-m-d')])
                    ->orderBy(['cal.data' => SORT_DESC])
                    ->all();

                // Contatori per stat cards
                $nModuli = Modulo::findWithDeleted()->andWhere(['avviso_id' => $corso->id, 'deleted_at' => null])->count();
                $nDocenti = Assegnazione::findWithDeleted()
                    ->alias('ass')
                    ->innerJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['ass.deleted_at' => null])
                    ->count('DISTINCT ass.docente_codice_fiscale');
                $nStudenti = Iscrizione::findWithDeleted()
                    ->alias('i')
                    ->innerJoin('modulo m', 'm.id = i.modulo_id AND m.deleted_at IS NULL')
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['i.deleted_at' => null])
                    ->count('DISTINCT i.allievo_codice_fiscale');
                $nLezioni = Calendario::findWithDeleted()
                    ->alias('cal')
                    ->innerJoin('modulo m', 'm.id = cal.modulo_id AND m.deleted_at IS NULL')
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['cal.deleted_at' => null])
                    ->count();

                return compact('docentiSenzaContratto', 'lezioniSenzaPresenze', 'nModuli', 'nDocenti', 'nStudenti', 'nLezioni');

            case 'piano-formativo':
                $moduli = Modulo::findWithDeleted()
                    ->alias('m')
                    ->with(['assegnazioni.docente'])
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['m.deleted_at' => null])
                    ->orderBy(['m.codice' => SORT_ASC])
                    ->all();
                return compact('moduli');

            case 'personale':
                $assegnazioni = Assegnazione::findWithDeleted()
                    ->alias('ass')
                    ->with(['docente', 'modulo', 'contratto'])
                    ->innerJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['ass.deleted_at' => null])
                    ->orderBy(['ass.id' => SORT_ASC])
                    ->all();
                return compact('assegnazioni');

            case 'studenti':
                $iscrizioni = Iscrizione::findWithDeleted()
                    ->alias('i')
                    ->with(['allievo'])
                    ->innerJoin('modulo m', 'm.id = i.modulo_id AND m.deleted_at IS NULL')
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['i.deleted_at' => null])
                    ->orderBy(['i.id' => SORT_ASC])
                    ->all();
                return compact('iscrizioni');

            case 'calendario':
                $lezioni = Calendario::findWithDeleted()
                    ->alias('cal')
                    ->with(['modulo', 'docente'])
                    ->innerJoin('modulo m', 'm.id = cal.modulo_id AND m.deleted_at IS NULL')
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['cal.deleted_at' => null])
                    ->orderBy(['cal.data' => SORT_DESC, 'cal.ora_inizio' => SORT_ASC])
                    ->all();
                return compact('lezioni');

            case 'contratti':
                $contratti = Contratto::findWithDeleted()
                    ->alias('c')
                    ->with(['assegnazione.docente', 'assegnazione.modulo'])
                    ->innerJoin('assegnazione ass', 'ass.id = c.assegnazione_id AND ass.deleted_at IS NULL')
                    ->innerJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
                    ->andWhere(['m.avviso_id' => $corso->id])
                    ->andWhere(['c.deleted_at' => null])
                    ->orderBy(['c.id' => SORT_ASC])
                    ->all();
                return compact('contratti');

            case 'borse-studio':
                $borse = BorsaStudio::findWithDeleted()
                    ->alias('bs')
                    ->with(['allievo'])
                    ->andWhere(['bs.progetto_id' => $corso->progetto_id])
                    ->andWhere(['bs.deleted_at' => null])
                    ->orderBy(['bs.id' => SORT_ASC])
                    ->all();
                return compact('borse');

            case 'import-export':
            default:
                return [];
        }
    }

    /**
     * Inserimento candidatura su più moduli contemporaneamente.
     */
    public function actionNuovaCandidatura($corso_id)
    {
        $corso = $this->findCorso($corso_id);

        $moduli = Modulo::find()
            ->where(['avviso_id' => $corso_id, 'deleted_at' => null])
            ->orderBy(['codice' => SORT_ASC])
            ->all();

        $docenti = Docente::find()
            ->where(['deleted_at' => null])
            ->orderBy(['cognome' => SORT_ASC, 'nome' => SORT_ASC])
            ->all();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $docenteCf = $post['docente_codice_fiscale'] ?? null;
            $moduliIds = $post['moduli_ids'] ?? [];

            if (!$docenteCf || empty($moduliIds)) {
                Yii::$app->session->setFlash('error', 'Selezionare un docente e almeno un modulo.');
                return $this->render('_candidatura-multi', compact('corso', 'moduli', 'docenti'));
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $count = 0;
                foreach ($moduliIds as $moduloId) {
                    $exists = Istanza::find()->where([
                        'docente_codice_fiscale' => $docenteCf,
                        'modulo_id' => $moduloId,
                        'deleted_at' => null,
                    ])->exists();

                    if (!$exists) {
                        $istanza = new Istanza();
                        $istanza->docente_codice_fiscale = $docenteCf;
                        $istanza->modulo_id = (int) $moduloId;
                        $istanza->avviso_id = (int) $corso_id;
                        $istanza->status = Istanza::STATUS_PRESENTATA;
                        $istanza->data_presentazione = date('Y-m-d');
                        if (!$istanza->save()) {
                            throw new \Exception('Errore salvataggio istanza: ' . implode(', ', $istanza->getFirstErrors()));
                        }
                        $count++;
                    }
                }
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Candidatura inserita per {$count} modulo/i.");
                return $this->redirect(['/gestione/sezione', 'id' => $corso_id, 'section' => 'piano-formativo']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('_candidatura-multi', compact('corso', 'moduli', 'docenti'));
    }

    /**
     * Mette a bando i moduli scoperti selezionati per il corso indicato.
     * Setta status modulo = APERTO e, se il corso è in BOZZA, lo porta a PUBBLICATO.
     */
    public function actionMettiABando($corso_id)
    {
        $corso = $this->findCorso($corso_id);
        $moduliIds = Yii::$app->request->post('moduli', []);

        if (!is_array($moduliIds) || empty($moduliIds)) {
            Yii::$app->session->setFlash('error', 'Selezionare almeno un modulo da mettere a bando.');
            return $this->redirect(['/gestione/sezione', 'id' => $corso_id, 'section' => 'piano-formativo']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $count = 0;
            foreach ($moduliIds as $moduloId) {
                $modulo = Modulo::findOne(['id' => (int) $moduloId, 'deleted_at' => null]);
                if ($modulo === null || (int) $modulo->avviso_id !== (int) $corso_id) {
                    continue;
                }
                $modulo->status = Modulo::STATUS_APERTO;
                if (!$modulo->withAuditNote('Messo a bando da Gestione Semplificata')->save()) {
                    throw new \Exception('Errore salvataggio modulo ' . $modulo->id . ': ' . implode(', ', $modulo->getFirstErrors()));
                }
                $count++;
            }

            if ($corso->status === Avviso::STATUS_BOZZA) {
                $corso->status = Avviso::STATUS_PUBBLICATO;
                if (!$corso->withAuditNote('Messo a bando da Gestione Semplificata')->save()) {
                    throw new \Exception('Errore salvataggio corso: ' . implode(', ', $corso->getFirstErrors()));
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', "Messi a bando {$count} modulo/i.");
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Errore durante l\'operazione: ' . $e->getMessage());
        }

        return $this->redirect(['/gestione/sezione', 'id' => $corso_id, 'section' => 'piano-formativo']);
    }

    protected function findCorso($id): Avviso
    {
        $model = Avviso::findOne(['id' => $id, 'deleted_at' => null]);
        if ($model === null) {
            throw new NotFoundHttpException('Corso non trovato.');
        }
        return $model;
    }
}
