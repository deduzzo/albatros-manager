<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Query;
use app\models\Progetto;
use app\models\Docente;
use app\models\Allievo;
use app\models\Modulo;
use app\models\Contratto;
use app\models\Pagamento;

/**
 * AssistantApiController - API read-only per l'assistente AI.
 * Tutti gli endpoint restituiscono JSON e accettano solo GET.
 */
class AssistantApiController extends Controller
{
    /**
     * Disabilita la validazione CSRF (endpoint GET-only)
     */
    public $enableCsrfValidation = false;

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
                    'riepilogo-generale' => ['GET'],
                    'progetti' => ['GET'],
                    'moduli-senza-docente' => ['GET'],
                    'cerca-docenti' => ['GET'],
                    'valutazioni-docenti' => ['GET'],
                    'saldi-da-liquidare' => ['GET'],
                    'ore-rendicontate' => ['GET'],
                    'graduatoria' => ['GET'],
                    'studenti-per-progetto' => ['GET'],
                    'scadenze' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Imposta il formato di risposta JSON per tutte le azioni
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return true;
        }
        return false;
    }

    /**
     * Riepilogo generale: contatori progetti, docenti, studenti, moduli, finanziario.
     */
    public function actionRiepilogoGenerale()
    {
        // Progetti
        $progettiTotale = (int) (new Query())
            ->from('progetto')
            ->where(['deleted_at' => null])
            ->count();

        $progettiAttivi = (int) (new Query())
            ->from('progetto')
            ->where(['deleted_at' => null, 'status' => 'attivo'])
            ->count();

        // Docenti
        $docentiTotale = (int) (new Query())
            ->from('docente')
            ->where(['deleted_at' => null])
            ->count();

        $docentiConAssegnazione = (int) (new Query())
            ->select('COUNT(DISTINCT d.codice_fiscale)')
            ->from('docente d')
            ->innerJoin('assegnazione ass', 'ass.docente_codice_fiscale = d.codice_fiscale AND ass.deleted_at IS NULL')
            ->where(['d.deleted_at' => null])
            ->scalar();

        // Studenti
        $studentiTotale = (int) (new Query())
            ->from('allievo')
            ->where(['deleted_at' => null])
            ->count();

        $studentiFrequentanti = (int) (new Query())
            ->from('iscrizione')
            ->where(['deleted_at' => null, 'status' => 'frequentante'])
            ->count();

        // Moduli
        $moduliTotale = (int) (new Query())
            ->from('modulo')
            ->where(['deleted_at' => null])
            ->count();

        $moduliSenzaDocente = (int) (new Query())
            ->select('COUNT(m.id)')
            ->from('modulo m')
            ->leftJoin('assegnazione ass', 'ass.modulo_id = m.id AND ass.deleted_at IS NULL AND ass.status != \'revocato\'')
            ->where(['m.deleted_at' => null])
            ->andWhere(['ass.id' => null])
            ->scalar();

        $moduliCompletati = (int) (new Query())
            ->from('modulo')
            ->where(['deleted_at' => null, 'status' => 'concluso'])
            ->count();

        // Finanziario: somma budget_totale da tutti i progetti
        $budgetTotale = (float) ((new Query())
            ->from('progetto')
            ->where(['deleted_at' => null])
            ->sum('budget_totale') ?: 0);

        // Contrattualizzato, pagato, da pagare usando i metodi dei singoli progetti
        $progetti = Progetto::find()->all();
        $contrattualizzato = 0;
        $pagato = 0;
        $daPagare = 0;
        foreach ($progetti as $progetto) {
            $contrattualizzato += $progetto->getTotaleContrattualizzato();
            $pagato += $progetto->getTotalePagato();
            $daPagare += $progetto->getTotaleInAttesa();
        }

        return [
            'progetti' => [
                'totale' => $progettiTotale,
                'attivi' => $progettiAttivi,
            ],
            'docenti' => [
                'totale' => $docentiTotale,
                'con_assegnazione' => $docentiConAssegnazione,
            ],
            'studenti' => [
                'totale' => $studentiTotale,
                'frequentanti' => $studentiFrequentanti,
            ],
            'moduli' => [
                'totale' => $moduliTotale,
                'senza_docente' => $moduliSenzaDocente,
                'completati' => $moduliCompletati,
            ],
            'finanziario' => [
                'budget_totale' => round($budgetTotale, 2),
                'contrattualizzato' => round($contrattualizzato, 2),
                'pagato' => round($pagato, 2),
                'da_pagare' => round($daPagare, 2),
            ],
        ];
    }

    /**
     * Lista progetti con dati finanziari.
     */
    public function actionProgetti()
    {
        $progetti = Progetto::find()
            ->orderBy(['codice_progetto' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($progetti as $p) {
            $contrattualizzato = $p->getTotaleContrattualizzato();
            $pagato = $p->getTotalePagato();
            $daPagare = $p->getTotaleInAttesa();
            $result[] = [
                'id' => $p->id,
                'codice_progetto' => $p->codice_progetto,
                'titolo' => $p->titolo,
                'fonte' => $p->fonteFinanziamento ? $p->fonteFinanziamento->codice : null,
                'status' => $p->status,
                'budget_totale' => (float) $p->budget_totale,
                'contrattualizzato' => round($contrattualizzato, 2),
                'pagato' => round($pagato, 2),
                'da_pagare' => round($daPagare, 2),
                'residuo' => round(($p->budget_totale ?: 0) - $contrattualizzato, 2),
            ];
        }

        return $result;
    }

    /**
     * Moduli senza docente assegnato, raggruppati per progetto.
     */
    public function actionModuliSenzaDocente()
    {
        $rows = (new Query())
            ->select([
                'm.id AS modulo_id',
                'm.codice AS modulo_codice',
                'm.titolo AS modulo_titolo',
                'm.ore_totali',
                'm.status AS modulo_status',
                'p.id AS progetto_id',
                'p.codice_progetto',
                'p.titolo AS progetto_titolo',
            ])
            ->from('modulo m')
            ->leftJoin('avviso a', 'a.id = m.avviso_id AND a.deleted_at IS NULL')
            ->leftJoin('progetto p', 'p.id = a.progetto_id AND p.deleted_at IS NULL')
            ->leftJoin('assegnazione ass', 'ass.modulo_id = m.id AND ass.deleted_at IS NULL AND ass.status != \'revocato\'')
            ->where(['m.deleted_at' => null])
            ->andWhere(['ass.id' => null])
            ->orderBy(['p.codice_progetto' => SORT_ASC, 'm.codice' => SORT_ASC])
            ->all();

        // Raggruppa per progetto
        $perProgetto = [];
        foreach ($rows as $row) {
            $pid = $row['progetto_id'] ?: 'senza_progetto';
            if (!isset($perProgetto[$pid])) {
                $perProgetto[$pid] = [
                    'progetto_id' => $row['progetto_id'],
                    'codice_progetto' => $row['codice_progetto'],
                    'progetto_titolo' => $row['progetto_titolo'],
                    'moduli' => [],
                ];
            }
            $perProgetto[$pid]['moduli'][] = [
                'id' => (int) $row['modulo_id'],
                'codice' => $row['modulo_codice'],
                'titolo' => $row['modulo_titolo'],
                'ore_totali' => (int) $row['ore_totali'],
                'status' => $row['modulo_status'],
            ];
        }

        return array_values($perProgetto);
    }

    /**
     * Cerca docenti per nome, competenza (tag) e valutazione minima.
     */
    public function actionCercaDocenti($nome = null, $competenza = null, $valutazione_minima = null)
    {
        $query = (new Query())
            ->select([
                'd.codice_fiscale',
                'd.cognome',
                'd.nome',
                'd.email',
                'd.provenienza',
                'AVG(vd.punteggio_globale) AS valutazione_media',
                'COUNT(DISTINCT vd.id) AS num_valutazioni',
                'COUNT(DISTINCT ass.id) AS moduli_assegnati',
            ])
            ->from('docente d')
            ->leftJoin('valutazione_docente vd', 'vd.docente_codice_fiscale = d.codice_fiscale AND vd.deleted_at IS NULL')
            ->leftJoin('assegnazione ass', 'ass.docente_codice_fiscale = d.codice_fiscale AND ass.deleted_at IS NULL')
            ->where(['d.deleted_at' => null]);

        // Filtro per nome/cognome
        if ($nome !== null && $nome !== '') {
            $query->andWhere(['OR',
                ['LIKE', 'd.cognome', $nome],
                ['LIKE', 'd.nome', $nome],
                ['LIKE', "CONCAT(d.cognome, ' ', d.nome)", $nome],
                ['LIKE', "CONCAT(d.nome, ' ', d.cognome)", $nome],
            ]);
        }

        // Filtro per competenza (tag curriculum)
        if ($competenza !== null && $competenza !== '') {
            $query->innerJoin('docente_tag dt', 'dt.docente_codice_fiscale = d.codice_fiscale')
                ->innerJoin('tag_curriculum tc', 'tc.id = dt.tag_id AND tc.deleted_at IS NULL')
                ->andWhere(['like', 'tc.nome', $competenza]);
        }

        $query->groupBy(['d.codice_fiscale', 'd.cognome', 'd.nome', 'd.email', 'd.provenienza']);

        // Filtro per valutazione minima (HAVING sulla media)
        if ($valutazione_minima !== null && $valutazione_minima !== '') {
            $query->having(['>=', 'AVG(vd.punteggio_globale)', (float) $valutazione_minima]);
        }

        $query->orderBy(['d.cognome' => SORT_ASC, 'd.nome' => SORT_ASC]);

        $docenti = $query->all();

        // Aggiungi lista tag per ogni docente
        $result = [];
        foreach ($docenti as $doc) {
            $tags = (new Query())
                ->select(['tc.nome'])
                ->from('docente_tag dt')
                ->innerJoin('tag_curriculum tc', 'tc.id = dt.tag_id AND tc.deleted_at IS NULL')
                ->where(['dt.docente_codice_fiscale' => $doc['codice_fiscale']])
                ->column();

            $result[] = [
                'codice_fiscale' => $doc['codice_fiscale'],
                'cognome' => $doc['cognome'],
                'nome' => $doc['nome'],
                'email' => $doc['email'],
                'provenienza' => $doc['provenienza'],
                'valutazione_media' => $doc['valutazione_media'] !== null ? round((float) $doc['valutazione_media'], 2) : null,
                'num_valutazioni' => (int) $doc['num_valutazioni'],
                'moduli_assegnati' => (int) $doc['moduli_assegnati'],
                'tags' => $tags,
            ];
        }

        return $result;
    }

    /**
     * Valutazioni medie per docente, con filtro opzionale per codice fiscale.
     */
    public function actionValutazioniDocenti($codice_fiscale = null)
    {
        $query = (new Query())
            ->select([
                'd.codice_fiscale',
                'd.cognome',
                'd.nome',
                'AVG(vd.punteggio_globale) AS valutazione_media',
                'COUNT(vd.id) AS num_valutazioni',
            ])
            ->from('docente d')
            ->innerJoin('valutazione_docente vd', 'vd.docente_codice_fiscale = d.codice_fiscale AND vd.deleted_at IS NULL')
            ->where(['d.deleted_at' => null])
            ->groupBy(['d.codice_fiscale', 'd.cognome', 'd.nome'])
            ->orderBy(['valutazione_media' => SORT_DESC]);

        if ($codice_fiscale !== null && $codice_fiscale !== '') {
            $query->andWhere(['d.codice_fiscale' => strtoupper($codice_fiscale)]);
        }

        $rows = $query->all();

        return array_map(function ($row) {
            return [
                'codice_fiscale' => $row['codice_fiscale'],
                'cognome' => $row['cognome'],
                'nome' => $row['nome'],
                'valutazione_media' => round((float) $row['valutazione_media'], 2),
                'num_valutazioni' => (int) $row['num_valutazioni'],
            ];
        }, $rows);
    }

    /**
     * Contratti con saldo da liquidare (saldo > 0).
     */
    public function actionSaldiDaLiquidare()
    {
        $contratti = Contratto::find()
            ->alias('c')
            ->leftJoin('assegnazione ass', 'ass.id = c.assegnazione_id AND ass.deleted_at IS NULL')
            ->leftJoin('docente d', 'd.codice_fiscale = ass.docente_codice_fiscale AND d.deleted_at IS NULL')
            ->leftJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
            ->leftJoin('avviso a', 'a.id = m.avviso_id AND a.deleted_at IS NULL')
            ->leftJoin('progetto p', 'p.id = a.progetto_id AND p.deleted_at IS NULL')
            ->where(['c.deleted_at' => null])
            ->all();

        $result = [];
        foreach ($contratti as $contratto) {
            $saldo = $contratto->getSaldoRimanente();
            if ($saldo <= 0.01) {
                continue;
            }

            $assegnazione = $contratto->assegnazione;
            $docente = $assegnazione ? $assegnazione->docente : null;
            $modulo = $assegnazione ? $assegnazione->modulo : null;

            // Ore rendicontate approvate
            $oreApprovate = 0;
            if ($assegnazione) {
                $oreApprovate = (float) ((new Query())
                    ->from('rendicontazione_ore')
                    ->where([
                        'assegnazione_id' => $assegnazione->id,
                        'status' => 'approvato',
                        'deleted_at' => null,
                    ])
                    ->sum('ore') ?: 0);
            }

            $result[] = [
                'contratto_id' => $contratto->id,
                'numero_contratto' => $contratto->numero_contratto,
                'docente' => $docente ? $docente->cognome . ' ' . $docente->nome : null,
                'docente_codice_fiscale' => $docente ? $docente->codice_fiscale : null,
                'modulo' => $modulo ? $modulo->titolo : null,
                'progetto_codice' => $modulo && $modulo->avviso && $modulo->avviso->progetto
                    ? $modulo->avviso->progetto->codice_progetto : null,
                'compenso_totale' => (float) $contratto->compenso_totale,
                'pagato' => round($contratto->getTotalePagato(), 2),
                'saldo' => round($saldo, 2),
                'ore_contrattualizzate' => (int) $contratto->ore_contrattualizzate,
                'ore_rendicontate_approvate' => round($oreApprovate, 1),
            ];
        }

        return $result;
    }

    /**
     * Ore rendicontate per modulo: contrattualizzate vs approvate/pendenti.
     */
    public function actionOreRendicontate()
    {
        $rows = (new Query())
            ->select([
                'm.id AS modulo_id',
                'm.codice AS modulo_codice',
                'm.titolo AS modulo_titolo',
                'c.ore_contrattualizzate',
                'd.cognome',
                'd.nome',
                'p.codice_progetto',
                'COALESCE(SUM(CASE WHEN ro.status = \'approvato\' THEN ro.ore ELSE 0 END), 0) AS ore_approvate',
                'COALESCE(SUM(CASE WHEN ro.status = \'inserito\' THEN ro.ore ELSE 0 END), 0) AS ore_pendenti',
            ])
            ->from('modulo m')
            ->leftJoin('assegnazione ass', 'ass.modulo_id = m.id AND ass.deleted_at IS NULL AND ass.status != \'revocato\'')
            ->leftJoin('contratto c', 'c.assegnazione_id = ass.id AND c.deleted_at IS NULL')
            ->leftJoin('docente d', 'd.codice_fiscale = ass.docente_codice_fiscale AND d.deleted_at IS NULL')
            ->leftJoin('avviso a', 'a.id = m.avviso_id AND a.deleted_at IS NULL')
            ->leftJoin('progetto p', 'p.id = a.progetto_id AND p.deleted_at IS NULL')
            ->leftJoin('rendicontazione_ore ro', 'ro.assegnazione_id = ass.id AND ro.deleted_at IS NULL')
            ->where(['m.deleted_at' => null])
            ->groupBy([
                'm.id', 'm.codice', 'm.titolo', 'c.ore_contrattualizzate',
                'd.cognome', 'd.nome', 'p.codice_progetto',
            ])
            ->orderBy(['p.codice_progetto' => SORT_ASC, 'm.codice' => SORT_ASC])
            ->all();

        return array_map(function ($row) {
            $oreContrattualizzate = (int) $row['ore_contrattualizzate'];
            $oreApprovate = round((float) $row['ore_approvate'], 1);
            $percentuale = $oreContrattualizzate > 0
                ? round(($oreApprovate / $oreContrattualizzate) * 100, 1)
                : 0;

            return [
                'modulo_id' => (int) $row['modulo_id'],
                'modulo_codice' => $row['modulo_codice'],
                'modulo_titolo' => $row['modulo_titolo'],
                'docente' => $row['cognome'] ? $row['cognome'] . ' ' . $row['nome'] : null,
                'progetto_codice' => $row['codice_progetto'],
                'ore_contrattualizzate' => $oreContrattualizzate,
                'ore_approvate' => $oreApprovate,
                'ore_pendenti' => round((float) $row['ore_pendenti'], 1),
                'percentuale' => $percentuale,
            ];
        }, $rows);
    }

    /**
     * Graduatoria docenti per modulo (istanze con esito CTS).
     */
    public function actionGraduatoria($modulo_id = null)
    {
        $query = (new Query())
            ->select([
                'i.id AS istanza_id',
                'i.modulo_id',
                'm.codice AS modulo_codice',
                'm.titolo AS modulo_titolo',
                'i.docente_codice_fiscale',
                'd.cognome',
                'd.nome',
                'i.punteggio_totale',
                'i.esito_cts',
            ])
            ->from('istanza i')
            ->leftJoin('docente d', 'd.codice_fiscale = i.docente_codice_fiscale AND d.deleted_at IS NULL')
            ->leftJoin('modulo m', 'm.id = i.modulo_id AND m.deleted_at IS NULL')
            ->where(['i.deleted_at' => null])
            ->andWhere(['is not', 'i.esito_cts', null])
            ->orderBy(['i.modulo_id' => SORT_ASC, 'i.punteggio_totale' => SORT_DESC]);

        if ($modulo_id !== null && $modulo_id !== '') {
            $query->andWhere(['i.modulo_id' => (int) $modulo_id]);
        }

        $rows = $query->all();

        // Verifico assegnazione attiva per ogni istanza
        // Pre-carica assegnazioni attive per i moduli coinvolti
        $moduloIds = array_unique(array_column($rows, 'modulo_id'));
        $assegnazioni = [];
        if (!empty($moduloIds)) {
            $assRows = (new Query())
                ->select(['modulo_id', 'docente_codice_fiscale'])
                ->from('assegnazione')
                ->where(['modulo_id' => $moduloIds])
                ->andWhere(['deleted_at' => null])
                ->andWhere(['!=', 'status', 'revocato'])
                ->all();
            foreach ($assRows as $ar) {
                $key = $ar['modulo_id'] . '_' . $ar['docente_codice_fiscale'];
                $assegnazioni[$key] = true;
            }
        }

        return array_map(function ($row) use ($assegnazioni) {
            $key = $row['modulo_id'] . '_' . $row['docente_codice_fiscale'];
            return [
                'istanza_id' => (int) $row['istanza_id'],
                'modulo_id' => (int) $row['modulo_id'],
                'modulo_codice' => $row['modulo_codice'],
                'modulo_titolo' => $row['modulo_titolo'],
                'docente_codice_fiscale' => $row['docente_codice_fiscale'],
                'docente' => $row['cognome'] . ' ' . $row['nome'],
                'punteggio' => $row['punteggio_totale'] !== null ? (float) $row['punteggio_totale'] : null,
                'esito_cts' => $row['esito_cts'],
                'ha_assegnazione' => isset($assegnazioni[$key]),
            ];
        }, $rows);
    }

    /**
     * Studenti per progetto: conteggio iscrizioni per status e posti disponibili.
     */
    public function actionStudentiPerProgetto($progetto_id = null)
    {
        $queryProgetti = (new Query())
            ->select(['p.id', 'p.codice_progetto', 'p.titolo', 'p.numero_studenti'])
            ->from('progetto p')
            ->where(['p.deleted_at' => null])
            ->orderBy(['p.codice_progetto' => SORT_ASC]);

        if ($progetto_id !== null && $progetto_id !== '') {
            $queryProgetti->andWhere(['p.id' => (int) $progetto_id]);
        }

        $progetti = $queryProgetti->all();

        $result = [];
        foreach ($progetti as $prog) {
            // Conta iscrizioni per status, passando attraverso avviso
            $iscrizioni = (new Query())
                ->select(['i.status', 'COUNT(*) AS conteggio'])
                ->from('iscrizione i')
                ->leftJoin('avviso a', 'a.id = i.avviso_id AND a.deleted_at IS NULL')
                ->where(['a.progetto_id' => $prog['id']])
                ->andWhere(['i.deleted_at' => null])
                ->groupBy(['i.status'])
                ->all();

            $statusMap = [];
            $frequentanti = 0;
            $ammessi = 0;
            foreach ($iscrizioni as $isc) {
                $statusMap[$isc['status']] = (int) $isc['conteggio'];
                if ($isc['status'] === 'frequentante') {
                    $frequentanti = (int) $isc['conteggio'];
                }
                if (in_array($isc['status'], ['ammesso', 'ammesso_con_riserva'])) {
                    $ammessi += (int) $isc['conteggio'];
                }
            }

            $numeroStudenti = (int) ($prog['numero_studenti'] ?: 0);
            $postiDisponibili = max(0, $numeroStudenti - $frequentanti - $ammessi);

            $result[] = [
                'progetto_id' => (int) $prog['id'],
                'codice_progetto' => $prog['codice_progetto'],
                'titolo' => $prog['titolo'],
                'numero_studenti' => $numeroStudenti,
                'iscrizioni_per_status' => $statusMap,
                'posti_disponibili' => $postiDisponibili,
            ];
        }

        return $result;
    }

    /**
     * Scadenze: contratti in scadenza, bandi aperti, pagamenti programmati.
     */
    public function actionScadenze()
    {
        $oggi = date('Y-m-d');
        $fra30giorni = date('Y-m-d', strtotime('+30 days'));

        // Contratti in scadenza (data_fine_lezioni tra oggi e +30 giorni)
        $contrattiInScadenza = (new Query())
            ->select([
                'c.id AS contratto_id',
                'c.numero_contratto',
                'c.data_fine_lezioni',
                'd.cognome',
                'd.nome',
                'm.titolo AS modulo_titolo',
                'p.codice_progetto',
            ])
            ->from('contratto c')
            ->leftJoin('assegnazione ass', 'ass.id = c.assegnazione_id AND ass.deleted_at IS NULL')
            ->leftJoin('docente d', 'd.codice_fiscale = ass.docente_codice_fiscale AND d.deleted_at IS NULL')
            ->leftJoin('modulo m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
            ->leftJoin('avviso a', 'a.id = m.avviso_id AND a.deleted_at IS NULL')
            ->leftJoin('progetto p', 'p.id = a.progetto_id AND p.deleted_at IS NULL')
            ->where(['c.deleted_at' => null])
            ->andWhere(['between', 'c.data_fine_lezioni', $oggi, $fra30giorni])
            ->orderBy(['c.data_fine_lezioni' => SORT_ASC])
            ->all();

        $contrattiResult = array_map(function ($row) {
            return [
                'contratto_id' => (int) $row['contratto_id'],
                'numero_contratto' => $row['numero_contratto'],
                'data_fine_lezioni' => $row['data_fine_lezioni'],
                'docente' => $row['cognome'] ? $row['cognome'] . ' ' . $row['nome'] : null,
                'modulo' => $row['modulo_titolo'],
                'progetto_codice' => $row['codice_progetto'],
            ];
        }, $contrattiInScadenza);

        // Bandi aperti (data_scadenza >= oggi)
        $bandiAperti = (new Query())
            ->select([
                'a.id AS avviso_id',
                'a.codice_avviso',
                'a.titolo',
                'a.data_scadenza',
                'p.codice_progetto',
            ])
            ->from('avviso a')
            ->leftJoin('progetto p', 'p.id = a.progetto_id AND p.deleted_at IS NULL')
            ->where(['a.deleted_at' => null])
            ->andWhere(['>=', 'a.data_scadenza', $oggi])
            ->orderBy(['a.data_scadenza' => SORT_ASC])
            ->all();

        $bandiResult = array_map(function ($row) {
            return [
                'avviso_id' => (int) $row['avviso_id'],
                'codice_avviso' => $row['codice_avviso'],
                'titolo' => $row['titolo'],
                'data_scadenza' => $row['data_scadenza'],
                'progetto_codice' => $row['codice_progetto'],
            ];
        }, $bandiAperti);

        // Pagamenti programmati/in attesa (limit 20)
        $pagamentiProgrammati = (new Query())
            ->select([
                'pag.id AS pagamento_id',
                'pag.importo_netto',
                'pag.data_scadenza',
                'pag.status',
                'c.numero_contratto',
                'd.cognome',
                'd.nome',
            ])
            ->from('pagamento pag')
            ->leftJoin('contratto c', 'c.id = pag.contratto_id AND c.deleted_at IS NULL')
            ->leftJoin('assegnazione ass', 'ass.id = c.assegnazione_id AND ass.deleted_at IS NULL')
            ->leftJoin('docente d', 'd.codice_fiscale = ass.docente_codice_fiscale AND d.deleted_at IS NULL')
            ->where(['pag.deleted_at' => null])
            ->andWhere(['pag.status' => ['programmato', 'in_attesa']])
            ->orderBy(['pag.data_scadenza' => SORT_ASC])
            ->limit(20)
            ->all();

        $pagamentiResult = array_map(function ($row) {
            return [
                'pagamento_id' => (int) $row['pagamento_id'],
                'importo_netto' => (float) $row['importo_netto'],
                'data_scadenza' => $row['data_scadenza'],
                'status' => $row['status'],
                'numero_contratto' => $row['numero_contratto'],
                'docente' => $row['cognome'] ? $row['cognome'] . ' ' . $row['nome'] : null,
            ];
        }, $pagamentiProgrammati);

        return [
            'contratti_in_scadenza' => $contrattiResult,
            'bandi_aperti' => $bandiResult,
            'pagamenti_programmati' => $pagamentiResult,
        ];
    }
}
