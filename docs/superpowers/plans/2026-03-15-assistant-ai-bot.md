# Assistente AI Bot — Piano di Implementazione

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Integrare un assistente AI conversazionale nel portale admin basato su WebLLM (inferenza nel browser via WebGPU) con 10 endpoint API read-only per interrogare i dati del sistema.

**Architecture:** WebLLM gira in un Web Worker nel browser dell'admin. L'utente fa domande in linguaggio naturale, il modello usa function calling per chiamare endpoint API server (Yii2/PHP), riceve dati JSON, e formula risposte in italiano. Fallback deterministico con keyword routing se il function calling fallisce.

**Tech Stack:** PHP 8.4, Yii2, MySQL, Tabler v1.4.0, `@mlc-ai/web-llm` via CDN, WebGPU, Web Workers.

**Spec:** `docs/superpowers/specs/2026-03-15-assistant-ai-bot-design.md`

---

## Ordine di implementazione

| # | Chunk | Contenuto | Dipende da |
|---|-------|-----------|------------|
| 1 | Server API | AssistantApiController + 10 endpoint + route | - |
| 2 | Asset Bundle + CSS | WebLlmAsset + stili chat | - |
| 3 | Chat UI | Widget flottante + pagina full-screen | #2 |
| 4 | WebLLM Engine | Web Worker + engine + tools + fallback | #2, #3 |
| 5 | Integrazione | Collegamento layout + test manuale | #1, #3, #4 |

---

## Chunk 1: Server API

### Task 1.1: AssistantApiController — struttura base + riepilogo-generale

**Files:**
- Create: `controllers/AssistantApiController.php`

- [ ] **Step 1: Creare il controller con behaviors e primo endpoint**

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Progetto;
use app\models\Docente;
use app\models\Allievo;
use app\models\Modulo;
use app\models\Contratto;
use app\models\Pagamento;

class AssistantApiController extends Controller
{
    public $enableCsrfValidation = false;

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
                    'riepilogo-generale' => ['GET'],
                    'progetti' => ['GET'],
                    'moduli-senza-docente' => ['GET'],
                    'cerca-docenti' => ['GET'],
                    'saldi-da-liquidare' => ['GET'],
                    'ore-rendicontate' => ['GET'],
                    'graduatoria' => ['GET'],
                    'studenti-per-progetto' => ['GET'],
                    'valutazioni-docenti' => ['GET'],
                    'scadenze' => ['GET'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * GET /assistant-api/riepilogo-generale
     * Contatori globali del sistema
     */
    public function actionRiepilogoGenerale()
    {
        $progetti = Progetto::find();
        $progettiTotale = (clone $progetti)->count();
        $progettiAttivi = (clone $progetti)->andWhere(['status' => 'attivo'])->count();

        $docentiTotale = Docente::find()->count();
        $docentiConAssegnazione = (int) (new \yii\db\Query())
            ->select('COUNT(DISTINCT docente_codice_fiscale)')
            ->from('{{%assegnazione}}')
            ->where(['AND',
                ['deleted_at' => null],
                ['!=', 'status', 'revocato'],
            ])
            ->scalar();

        $studentiTotale = Allievo::find()->count();
        $studentiFrequentanti = (int) (new \yii\db\Query())
            ->select('COUNT(DISTINCT allievo_codice_fiscale)')
            ->from('{{%iscrizione}}')
            ->where(['AND',
                ['deleted_at' => null],
                ['status' => 'frequentante'],
            ])
            ->scalar();

        $moduliTotale = Modulo::find()->count();
        $moduliSenzaDocente = $this->countModuliSenzaDocente();
        $moduliCompletati = Modulo::find()->andWhere(['status' => 'concluso'])->count();

        $budgetTotale = (float) Progetto::find()->sum('budget_totale') ?: 0;
        $contrattualizzato = 0;
        $pagato = 0;
        $daPagare = 0;

        foreach (Progetto::find()->all() as $progetto) {
            $contrattualizzato += $progetto->getTotaleContrattualizzato();
            $pagato += $progetto->getTotalePagato();
            $daPagare += $progetto->getTotaleInAttesa();
        }

        return [
            'progetti' => [
                'totale' => (int) $progettiTotale,
                'attivi' => (int) $progettiAttivi,
            ],
            'docenti' => [
                'totale' => (int) $docentiTotale,
                'con_assegnazione' => $docentiConAssegnazione,
            ],
            'studenti' => [
                'totale' => (int) $studentiTotale,
                'frequentanti' => (int) $studentiFrequentanti,
            ],
            'moduli' => [
                'totale' => (int) $moduliTotale,
                'senza_docente' => $moduliSenzaDocente,
                'completati' => (int) $moduliCompletati,
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
     * Helper: conta moduli senza assegnazione attiva
     */
    private function countModuliSenzaDocente()
    {
        return (int) (new \yii\db\Query())
            ->from('{{%modulo}} m')
            ->leftJoin('{{%assegnazione}} a', 'a.modulo_id = m.id AND a.deleted_at IS NULL AND a.status != :revocato', [':revocato' => 'revocato'])
            ->where(['m.deleted_at' => null])
            ->andWhere(['a.id' => null])
            ->count();
    }
}
```

- [ ] **Step 2: Verificare che il controller risponda**

```bash
# Nel browser o con curl dopo login:
# http://localhost:8000/assistant-api/riepilogo-generale
# Deve restituire JSON con le statistiche
```

- [ ] **Step 3: Commit**

```bash
git add controllers/AssistantApiController.php
git commit -m "feat: add AssistantApiController with riepilogo-generale endpoint"
```

---

### Task 1.2: Endpoint progetti + moduli-senza-docente

**Files:**
- Modify: `controllers/AssistantApiController.php`

- [ ] **Step 1: Aggiungere actionProgetti**

```php
/**
 * GET /assistant-api/progetti
 * Lista progetti con stato finanziario
 */
public function actionProgetti()
{
    $progetti = Progetto::find()->all();
    $risultati = [];

    foreach ($progetti as $p) {
        $risultati[] = [
            'id' => $p->id,
            'codice' => $p->codice_progetto,
            'titolo' => $p->titolo,
            'fonte' => $p->fonteFinanziamento ? $p->fonteFinanziamento->nome : null,
            'status' => $p->status,
            'budget_totale' => round((float) $p->budget_totale, 2),
            'contrattualizzato' => round($p->getTotaleContrattualizzato(), 2),
            'pagato' => round($p->getTotalePagato(), 2),
            'da_pagare' => round($p->getTotaleInAttesa(), 2),
            'residuo' => round((float) $p->budget_totale - $p->getTotaleContrattualizzato(), 2),
        ];
    }

    return [
        'progetti' => $risultati,
        'totale' => count($risultati),
    ];
}
```

- [ ] **Step 2: Aggiungere actionModuliSenzaDocente**

```php
/**
 * GET /assistant-api/moduli-senza-docente
 * Moduli senza assegnazione attiva, raggruppati per progetto
 */
public function actionModuliSenzaDocente()
{
    $moduli = (new \yii\db\Query())
        ->select([
            'm.id AS modulo_id',
            'm.titolo AS modulo_titolo',
            'm.ore_totali',
            'm.compenso_orario',
            'a.titolo AS avviso_titolo',
            'p.id AS progetto_id',
            'p.codice_progetto',
            'p.titolo AS progetto_titolo',
        ])
        ->from('{{%modulo}} m')
        ->leftJoin('{{%assegnazione}} ass', 'ass.modulo_id = m.id AND ass.deleted_at IS NULL AND ass.status != :revocato', [':revocato' => 'revocato'])
        ->innerJoin('{{%avviso}} a', 'a.id = m.avviso_id AND a.deleted_at IS NULL')
        ->innerJoin('{{%progetto}} p', 'p.id = a.progetto_id AND p.deleted_at IS NULL')
        ->where(['m.deleted_at' => null])
        ->andWhere(['ass.id' => null])
        ->orderBy(['p.codice_progetto' => SORT_ASC, 'm.titolo' => SORT_ASC])
        ->all();

    // Raggruppamento per progetto
    $perProgetto = [];
    foreach ($moduli as $m) {
        $key = $m['progetto_id'];
        if (!isset($perProgetto[$key])) {
            $perProgetto[$key] = [
                'progetto_id' => (int) $m['progetto_id'],
                'codice_progetto' => $m['codice_progetto'],
                'progetto_titolo' => $m['progetto_titolo'],
                'moduli' => [],
            ];
        }
        $perProgetto[$key]['moduli'][] = [
            'modulo_id' => (int) $m['modulo_id'],
            'titolo' => $m['modulo_titolo'],
            'ore_totali' => (int) $m['ore_totali'],
            'compenso_orario' => (float) $m['compenso_orario'],
            'avviso' => $m['avviso_titolo'],
        ];
    }

    return [
        'per_progetto' => array_values($perProgetto),
        'totale_moduli_scoperti' => count($moduli),
    ];
}
```

- [ ] **Step 3: Verificare nel browser**

```
http://localhost:8000/assistant-api/progetti
http://localhost:8000/assistant-api/moduli-senza-docente
```

- [ ] **Step 4: Commit**

```bash
git add controllers/AssistantApiController.php
git commit -m "feat: add progetti and moduli-senza-docente API endpoints"
```

---

### Task 1.3: Endpoint cerca-docenti + valutazioni-docenti

**Files:**
- Modify: `controllers/AssistantApiController.php`

- [ ] **Step 1: Aggiungere actionCercaDocenti**

```php
/**
 * GET /assistant-api/cerca-docenti
 * Cerca docenti per competenze/tag e valutazione minima
 * Params: competenza (string), valutazione_minima (float)
 */
public function actionCercaDocenti($competenza = null, $valutazione_minima = null)
{
    $query = (new \yii\db\Query())
        ->select([
            'd.codice_fiscale',
            'd.cognome',
            'd.nome',
            'd.provenienza',
            'd.email',
            'd.specializzazioni',
        ])
        ->from('{{%docente}} d')
        ->where(['d.deleted_at' => null])
        ->groupBy('d.codice_fiscale');

    if ($competenza) {
        $query->innerJoin('{{%docente_tag}} dt', 'dt.docente_codice_fiscale = d.codice_fiscale')
            ->innerJoin('{{%tag_curriculum}} tc', 'tc.id = dt.tag_id AND tc.deleted_at IS NULL')
            ->andWhere(['LIKE', 'tc.nome', $competenza]);
    }

    $docenti = $query->all();
    $risultati = [];

    foreach ($docenti as $doc) {
        // Calcola media valutazioni
        $valMedia = (new \yii\db\Query())
            ->from('{{%valutazione_docente}}')
            ->where([
                'docente_codice_fiscale' => $doc['codice_fiscale'],
                'deleted_at' => null,
            ])
            ->average('punteggio_globale');

        $valMedia = $valMedia ? round((float) $valMedia, 1) : null;

        if ($valutazione_minima !== null && ($valMedia === null || $valMedia < (float) $valutazione_minima)) {
            continue;
        }

        // Tags del docente
        $tags = (new \yii\db\Query())
            ->select('tc.nome')
            ->from('{{%docente_tag}} dt')
            ->innerJoin('{{%tag_curriculum}} tc', 'tc.id = dt.tag_id AND tc.deleted_at IS NULL')
            ->where(['dt.docente_codice_fiscale' => $doc['codice_fiscale']])
            ->column();

        // Conteggio moduli assegnati
        $moduliAssegnati = (int) (new \yii\db\Query())
            ->from('{{%assegnazione}}')
            ->where([
                'docente_codice_fiscale' => $doc['codice_fiscale'],
                'deleted_at' => null,
            ])
            ->andWhere(['!=', 'status', 'revocato'])
            ->count();

        $risultati[] = [
            'codice_fiscale' => $doc['codice_fiscale'],
            'cognome' => $doc['cognome'],
            'nome' => $doc['nome'],
            'provenienza' => $doc['provenienza'],
            'tags' => $tags,
            'valutazione_media' => $valMedia,
            'num_valutazioni' => (int) (new \yii\db\Query())
                ->from('{{%valutazione_docente}}')
                ->where([
                    'docente_codice_fiscale' => $doc['codice_fiscale'],
                    'deleted_at' => null,
                ])
                ->count(),
            'moduli_assegnati' => $moduliAssegnati,
        ];
    }

    // Ordina per valutazione decrescente
    usort($risultati, function ($a, $b) {
        return ($b['valutazione_media'] ?? 0) <=> ($a['valutazione_media'] ?? 0);
    });

    return [
        'risultati' => $risultati,
        'totale' => count($risultati),
    ];
}

/**
 * GET /assistant-api/valutazioni-docenti
 * Media valutazioni per docente con dettaglio criteri
 * Params: codice_fiscale (string, opzionale — se omesso restituisce tutti)
 */
public function actionValutazioniDocenti($codice_fiscale = null)
{
    $query = (new \yii\db\Query())
        ->select([
            'vd.docente_codice_fiscale',
            'd.cognome',
            'd.nome',
        ])
        ->from('{{%valutazione_docente}} vd')
        ->innerJoin('{{%docente}} d', 'd.codice_fiscale = vd.docente_codice_fiscale AND d.deleted_at IS NULL')
        ->where(['vd.deleted_at' => null])
        ->groupBy(['vd.docente_codice_fiscale', 'd.cognome', 'd.nome']);

    if ($codice_fiscale) {
        $query->andWhere(['vd.docente_codice_fiscale' => $codice_fiscale]);
    }

    $docenti = $query->all();
    $risultati = [];

    foreach ($docenti as $doc) {
        $valutazioni = (new \yii\db\Query())
            ->from('{{%valutazione_docente}}')
            ->where([
                'docente_codice_fiscale' => $doc['docente_codice_fiscale'],
                'deleted_at' => null,
            ])
            ->all();

        $media = 0;
        $count = count($valutazioni);
        if ($count > 0) {
            $somma = array_sum(array_column($valutazioni, 'punteggio_globale'));
            $media = round($somma / $count, 1);
        }

        $risultati[] = [
            'codice_fiscale' => $doc['docente_codice_fiscale'],
            'cognome' => $doc['cognome'],
            'nome' => $doc['nome'],
            'valutazione_media' => $media,
            'num_valutazioni' => $count,
        ];
    }

    usort($risultati, function ($a, $b) {
        return $b['valutazione_media'] <=> $a['valutazione_media'];
    });

    return [
        'risultati' => $risultati,
        'totale' => count($risultati),
    ];
}
```

- [ ] **Step 2: Verificare nel browser**

```
http://localhost:8000/assistant-api/cerca-docenti?competenza=informatica
http://localhost:8000/assistant-api/cerca-docenti?competenza=informatica&valutazione_minima=4
http://localhost:8000/assistant-api/valutazioni-docenti
```

- [ ] **Step 3: Commit**

```bash
git add controllers/AssistantApiController.php
git commit -m "feat: add cerca-docenti and valutazioni-docenti API endpoints"
```

---

### Task 1.4: Endpoint saldi-da-liquidare + ore-rendicontate

**Files:**
- Modify: `controllers/AssistantApiController.php`

- [ ] **Step 1: Aggiungere actionSaldiDaLiquidare**

```php
/**
 * GET /assistant-api/saldi-da-liquidare
 * Contratti con saldo > 0
 */
public function actionSaldiDaLiquidare()
{
    $contratti = Contratto::find()
        ->alias('c')
        ->all();

    $risultati = [];
    $totaleDaLiquidare = 0;

    foreach ($contratti as $c) {
        $saldo = $c->getSaldoRimanente();
        if ($saldo <= 0) {
            continue;
        }

        $assegnazione = $c->assegnazione;
        $docente = $assegnazione ? $assegnazione->docente : null;
        $modulo = $assegnazione ? $assegnazione->modulo : null;
        $avviso = $modulo ? $modulo->avviso : null;
        $progetto = $avviso ? $avviso->progetto : null;

        // Ore rendicontate approvate
        $oreApprovate = 0;
        if ($assegnazione) {
            $oreApprovate = (float) (new \yii\db\Query())
                ->from('{{%rendicontazione_ore}}')
                ->where([
                    'assegnazione_id' => $assegnazione->id,
                    'status' => 'approvato',
                    'deleted_at' => null,
                ])
                ->sum('ore') ?: 0;
        }

        $risultati[] = [
            'contratto_id' => $c->id,
            'docente' => $docente ? $docente->cognome . ' ' . $docente->nome : 'N/D',
            'modulo' => $modulo ? $modulo->titolo : 'N/D',
            'progetto' => $progetto ? $progetto->codice_progetto : 'N/D',
            'compenso_totale' => round((float) $c->compenso_totale, 2),
            'pagato' => round($c->getTotalePagato(), 2),
            'saldo' => round($saldo, 2),
            'ore_contrattualizzate' => (int) $c->ore_contrattualizzate,
            'ore_rendicontate_approvate' => round($oreApprovate, 1),
        ];

        $totaleDaLiquidare += $saldo;
    }

    usort($risultati, function ($a, $b) {
        return $b['saldo'] <=> $a['saldo'];
    });

    return [
        'contratti' => $risultati,
        'totale_da_liquidare' => round($totaleDaLiquidare, 2),
        'num_contratti' => count($risultati),
    ];
}

/**
 * GET /assistant-api/ore-rendicontate
 * Ore approvate vs contrattualizzate per modulo
 */
public function actionOreRendicontate()
{
    $moduli = (new \yii\db\Query())
        ->select([
            'm.id AS modulo_id',
            'm.titolo',
            'm.ore_totali',
            'd.cognome',
            'd.nome',
            'c.ore_contrattualizzate',
            'ass.id AS assegnazione_id',
            'p.codice_progetto',
        ])
        ->from('{{%modulo}} m')
        ->innerJoin('{{%avviso}} av', 'av.id = m.avviso_id AND av.deleted_at IS NULL')
        ->innerJoin('{{%progetto}} p', 'p.id = av.progetto_id AND p.deleted_at IS NULL')
        ->leftJoin('{{%assegnazione}} ass', 'ass.modulo_id = m.id AND ass.deleted_at IS NULL AND ass.status != :rev', [':rev' => 'revocato'])
        ->leftJoin('{{%docente}} d', 'd.codice_fiscale = ass.docente_codice_fiscale AND d.deleted_at IS NULL')
        ->leftJoin('{{%contratto}} c', 'c.assegnazione_id = ass.id AND c.deleted_at IS NULL')
        ->where(['m.deleted_at' => null])
        ->orderBy(['p.codice_progetto' => SORT_ASC, 'm.titolo' => SORT_ASC])
        ->all();

    $risultati = [];
    foreach ($moduli as $m) {
        $oreApprovate = 0;
        $orePendenti = 0;

        if ($m['assegnazione_id']) {
            $oreApprovate = (float) (new \yii\db\Query())
                ->from('{{%rendicontazione_ore}}')
                ->where([
                    'assegnazione_id' => $m['assegnazione_id'],
                    'status' => 'approvato',
                    'deleted_at' => null,
                ])
                ->sum('ore') ?: 0;

            $orePendenti = (float) (new \yii\db\Query())
                ->from('{{%rendicontazione_ore}}')
                ->where([
                    'assegnazione_id' => $m['assegnazione_id'],
                    'status' => 'inserito',
                    'deleted_at' => null,
                ])
                ->sum('ore') ?: 0;
        }

        $oreContrattualizzate = (int) ($m['ore_contrattualizzate'] ?: $m['ore_totali']);

        $risultati[] = [
            'modulo' => $m['titolo'],
            'progetto' => $m['codice_progetto'],
            'docente' => $m['cognome'] ? $m['cognome'] . ' ' . $m['nome'] : 'Non assegnato',
            'ore_contrattualizzate' => $oreContrattualizzate,
            'ore_approvate' => round($oreApprovate, 1),
            'ore_pendenti' => round($orePendenti, 1),
            'ore_mancanti' => round($oreContrattualizzate - $oreApprovate, 1),
            'percentuale' => $oreContrattualizzate > 0 ? round(($oreApprovate / $oreContrattualizzate) * 100, 1) : 0,
        ];
    }

    return [
        'moduli' => $risultati,
        'totale' => count($risultati),
    ];
}
```

- [ ] **Step 2: Verificare nel browser**

```
http://localhost:8000/assistant-api/saldi-da-liquidare
http://localhost:8000/assistant-api/ore-rendicontate
```

- [ ] **Step 3: Commit**

```bash
git add controllers/AssistantApiController.php
git commit -m "feat: add saldi-da-liquidare and ore-rendicontate API endpoints"
```

---

### Task 1.5: Endpoint graduatoria + studenti-per-progetto + scadenze

**Files:**
- Modify: `controllers/AssistantApiController.php`

- [ ] **Step 1: Aggiungere i tre endpoint rimanenti**

```php
/**
 * GET /assistant-api/graduatoria
 * Graduatoria docenti per modulo
 * Params: modulo_id (int, opzionale)
 */
public function actionGraduatoria($modulo_id = null)
{
    $query = (new \yii\db\Query())
        ->select([
            'i.id AS istanza_id',
            'i.docente_codice_fiscale',
            'd.cognome',
            'd.nome',
            'm.id AS modulo_id',
            'm.titolo AS modulo_titolo',
            'i.punteggio_totale',
            'i.posizione_graduatoria',
            'i.esito_cts',
            'i.status',
            'p.codice_progetto',
        ])
        ->from('{{%istanza}} i')
        ->innerJoin('{{%docente}} d', 'd.codice_fiscale = i.docente_codice_fiscale AND d.deleted_at IS NULL')
        ->innerJoin('{{%modulo}} m', 'm.id = i.modulo_id AND m.deleted_at IS NULL')
        ->innerJoin('{{%avviso}} av', 'av.id = m.avviso_id AND av.deleted_at IS NULL')
        ->innerJoin('{{%progetto}} p', 'p.id = av.progetto_id AND p.deleted_at IS NULL')
        ->where(['i.deleted_at' => null])
        ->andWhere(['IS NOT', 'i.esito_cts', null])
        ->orderBy(['m.id' => SORT_ASC, 'i.punteggio_totale' => SORT_DESC]);

    if ($modulo_id) {
        $query->andWhere(['i.modulo_id' => (int) $modulo_id]);
    }

    $istanze = $query->all();

    // Verifica se ha assegnazione attiva
    $risultati = [];
    foreach ($istanze as $ist) {
        $haAssegnazione = (new \yii\db\Query())
            ->from('{{%assegnazione}}')
            ->where([
                'istanza_id' => $ist['istanza_id'],
                'deleted_at' => null,
            ])
            ->andWhere(['!=', 'status', 'revocato'])
            ->exists();

        $risultati[] = [
            'docente' => $ist['cognome'] . ' ' . $ist['nome'],
            'codice_fiscale' => $ist['docente_codice_fiscale'],
            'modulo' => $ist['modulo_titolo'],
            'progetto' => $ist['codice_progetto'],
            'punteggio' => (float) $ist['punteggio_totale'],
            'esito_cts' => $ist['esito_cts'],
            'ha_assegnazione' => $haAssegnazione,
        ];
    }

    return [
        'graduatoria' => $risultati,
        'totale' => count($risultati),
    ];
}

/**
 * GET /assistant-api/studenti-per-progetto
 * Iscrizioni studenti con status e posti disponibili
 * Params: progetto_id (int, opzionale)
 */
public function actionStudentiPerProgetto($progetto_id = null)
{
    $query = Progetto::find();
    if ($progetto_id) {
        $query->andWhere(['id' => (int) $progetto_id]);
    }

    $progetti = $query->all();
    $risultati = [];

    foreach ($progetti as $p) {
        $iscrizioni = (new \yii\db\Query())
            ->select([
                'isc.status',
                'COUNT(*) AS conteggio',
            ])
            ->from('{{%iscrizione}} isc')
            ->innerJoin('{{%avviso}} av', 'av.id = isc.avviso_id AND av.deleted_at IS NULL')
            ->where([
                'av.progetto_id' => $p->id,
                'isc.deleted_at' => null,
            ])
            ->groupBy('isc.status')
            ->all();

        $statusMap = [];
        $totaleIscritti = 0;
        foreach ($iscrizioni as $isc) {
            $statusMap[$isc['status']] = (int) $isc['conteggio'];
            $totaleIscritti += (int) $isc['conteggio'];
        }

        $postiTotali = (int) ($p->numero_studenti ?: 20);

        $risultati[] = [
            'progetto_id' => $p->id,
            'codice' => $p->codice_progetto,
            'titolo' => $p->titolo,
            'posti_totali' => $postiTotali,
            'totale_iscritti' => $totaleIscritti,
            'posti_disponibili' => max(0, $postiTotali - ($statusMap['frequentante'] ?? 0) - ($statusMap['ammesso'] ?? 0)),
            'per_status' => $statusMap,
        ];
    }

    return [
        'progetti' => $risultati,
        'totale' => count($risultati),
    ];
}

/**
 * GET /assistant-api/scadenze
 * Contratti in scadenza, bandi aperti/chiusi, pagamenti programmati
 */
public function actionScadenze()
{
    $oggi = date('Y-m-d');
    $tra30giorni = date('Y-m-d', strtotime('+30 days'));

    // Contratti in scadenza (data_fine_lezioni nei prossimi 30 giorni)
    $contrattiScadenza = (new \yii\db\Query())
        ->select([
            'c.id',
            'c.data_fine_lezioni',
            'd.cognome',
            'd.nome',
            'm.titolo AS modulo',
        ])
        ->from('{{%contratto}} c')
        ->innerJoin('{{%assegnazione}} ass', 'ass.id = c.assegnazione_id AND ass.deleted_at IS NULL')
        ->innerJoin('{{%docente}} d', 'd.codice_fiscale = ass.docente_codice_fiscale')
        ->innerJoin('{{%modulo}} m', 'm.id = ass.modulo_id AND m.deleted_at IS NULL')
        ->where(['c.deleted_at' => null])
        ->andWhere(['BETWEEN', 'c.data_fine_lezioni', $oggi, $tra30giorni])
        ->orderBy(['c.data_fine_lezioni' => SORT_ASC])
        ->all();

    // Avvisi/bandi aperti
    $bandiAperti = (new \yii\db\Query())
        ->select(['a.id', 'a.titolo', 'a.data_scadenza', 'p.codice_progetto'])
        ->from('{{%avviso}} a')
        ->innerJoin('{{%progetto}} p', 'p.id = a.progetto_id AND p.deleted_at IS NULL')
        ->where(['a.deleted_at' => null])
        ->andWhere(['>=', 'a.data_scadenza', $oggi])
        ->orderBy(['a.data_scadenza' => SORT_ASC])
        ->all();

    // Pagamenti programmati (non ancora pagati)
    $pagamentiProgrammati = (new \yii\db\Query())
        ->select([
            'pag.id',
            'pag.importo_netto',
            'pag.data_pagamento',
            'pag.status',
            'd.cognome',
            'd.nome',
        ])
        ->from('{{%pagamento}} pag')
        ->innerJoin('{{%contratto}} c', 'c.id = pag.contratto_id AND c.deleted_at IS NULL')
        ->innerJoin('{{%assegnazione}} ass', 'ass.id = c.assegnazione_id AND ass.deleted_at IS NULL')
        ->innerJoin('{{%docente}} d', 'd.codice_fiscale = ass.docente_codice_fiscale')
        ->where(['pag.deleted_at' => null])
        ->andWhere(['IN', 'pag.status', ['programmato', 'in_attesa']])
        ->orderBy(['pag.data_pagamento' => SORT_ASC])
        ->limit(20)
        ->all();

    return [
        'contratti_in_scadenza' => array_map(function ($c) {
            return [
                'id' => (int) $c['id'],
                'docente' => $c['cognome'] . ' ' . $c['nome'],
                'modulo' => $c['modulo'],
                'data_fine' => $c['data_fine_lezioni'],
            ];
        }, $contrattiScadenza),
        'bandi_aperti' => array_map(function ($b) {
            return [
                'id' => (int) $b['id'],
                'titolo' => $b['titolo'],
                'scadenza' => $b['data_scadenza'],
                'progetto' => $b['codice_progetto'],
            ];
        }, $bandiAperti),
        'pagamenti_programmati' => array_map(function ($p) {
            return [
                'id' => (int) $p['id'],
                'docente' => $p['cognome'] . ' ' . $p['nome'],
                'importo' => round((float) $p['importo_netto'], 2),
                'data' => $p['data_pagamento'],
                'status' => $p['status'],
            ];
        }, $pagamentiProgrammati),
        'num_contratti_scadenza' => count($contrattiScadenza),
        'num_bandi_aperti' => count($bandiAperti),
        'num_pagamenti_programmati' => count($pagamentiProgrammati),
    ];
}
```

- [ ] **Step 2: Verificare tutti gli endpoint nel browser**

```
http://localhost:8000/assistant-api/graduatoria
http://localhost:8000/assistant-api/studenti-per-progetto
http://localhost:8000/assistant-api/scadenze
```

- [ ] **Step 3: Commit**

```bash
git add controllers/AssistantApiController.php
git commit -m "feat: add graduatoria, studenti-per-progetto, scadenze API endpoints"
```

---

## Chunk 2: Asset Bundle + CSS

### Task 2.1: WebLlmAsset bundle

**Files:**
- Create: `components/assets/WebLlmAsset.php`

- [ ] **Step 1: Creare l'asset bundle**

```php
<?php

namespace app\components\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Asset bundle per l'assistente AI basato su WebLLM.
 * Registra solo il CSS. I file JS sono caricati come ES modules
 * direttamente nel widget HTML per preservare i path relativi degli import.
 */
class WebLlmAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/assistant.css',
    ];

    // I JS NON sono gestiti dall'asset bundle perché sono ES modules
    // con import relativi tra loro. Yii2 pubblica gli asset in directory
    // con hash che romperebbero i path. I <script type="module"> sono
    // inseriti direttamente nel widget partial _assistant-widget.php.
    public $js = [];

    public $depends = [
        'app\components\assets\TablerAsset',
    ];
}
```

**NOTA IMPORTANTE**: I file JS sono ES modules con import relativi tra loro (`./assistant-chat.js`, `./assistant-tools.js`). Il sistema di asset Yii2 pubblica i file in directory con hash (`/assets/abc123/...`) che romperebbero questi import. Per questo i JS vengono caricati direttamente come `<script type="module" src="/js/assistant/assistant-engine.js">` nel widget partial.

- [ ] **Step 2: Commit**

```bash
git add components/assets/WebLlmAsset.php
git commit -m "feat: add WebLlmAsset bundle for AI assistant"
```

---

### Task 2.2: CSS del widget chat

**Files:**
- Create: `web/css/assistant.css`

- [ ] **Step 1: Creare il foglio di stile**

```css
/* ============================================
   Assistente AI - Widget Chat + Full Screen
   ============================================ */

/* --- Variabili --- */
:root {
    --assistant-primary: #206bc4;
    --assistant-bg: #1e293b;
    --assistant-bg-light: #334155;
    --assistant-border: #475569;
    --assistant-text: #e2e8f0;
    --assistant-text-muted: #94a3b8;
    --assistant-user-bg: #206bc4;
    --assistant-bot-bg: #334155;
    --assistant-radius: 12px;
    --assistant-widget-size: 56px;
    --assistant-panel-width: 400px;
    --assistant-panel-height: 520px;
}

/* --- Pulsante flottante --- */
.assistant-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: var(--assistant-widget-size);
    height: var(--assistant-widget-size);
    border-radius: 50%;
    background: var(--assistant-primary);
    color: #fff;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1040;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.assistant-fab:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
}

.assistant-fab i {
    font-size: 24px;
}

.assistant-fab .assistant-fab-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #1e293b;
}

.assistant-fab-badge.status-loading {
    background: #f59e0b;
    animation: pulse-badge 1.5s infinite;
}

.assistant-fab-badge.status-ready {
    background: #22c55e;
}

.assistant-fab-badge.status-error {
    background: #ef4444;
}

@keyframes pulse-badge {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

/* --- Pannello chat (widget) --- */
.assistant-panel {
    position: fixed;
    bottom: 92px;
    right: 24px;
    width: var(--assistant-panel-width);
    height: var(--assistant-panel-height);
    background: var(--assistant-bg);
    border: 1px solid var(--assistant-border);
    border-radius: var(--assistant-radius);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    display: none;
    flex-direction: column;
    z-index: 1039;
    overflow: hidden;
}

.assistant-panel.open {
    display: flex;
    animation: slideUp 0.25s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

/* --- Header pannello --- */
.assistant-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid var(--assistant-border);
    background: var(--assistant-bg-light);
}

.assistant-header-title {
    color: var(--assistant-text);
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.assistant-header-actions {
    display: flex;
    gap: 4px;
}

.assistant-header-actions button {
    background: none;
    border: none;
    color: var(--assistant-text-muted);
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 16px;
}

.assistant-header-actions button:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--assistant-text);
}

/* --- Area messaggi --- */
.assistant-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.assistant-messages::-webkit-scrollbar {
    width: 4px;
}

.assistant-messages::-webkit-scrollbar-thumb {
    background: var(--assistant-border);
    border-radius: 2px;
}

/* --- Messaggi --- */
.assistant-msg {
    max-width: 85%;
    padding: 10px 14px;
    border-radius: var(--assistant-radius);
    font-size: 13px;
    line-height: 1.5;
    word-wrap: break-word;
}

.assistant-msg.user {
    align-self: flex-end;
    background: var(--assistant-user-bg);
    color: #fff;
    border-bottom-right-radius: 4px;
}

.assistant-msg.bot {
    align-self: flex-start;
    background: var(--assistant-bot-bg);
    color: var(--assistant-text);
    border-bottom-left-radius: 4px;
}

/* Markdown nelle risposte bot */
.assistant-msg.bot table {
    width: 100%;
    border-collapse: collapse;
    margin: 8px 0;
    font-size: 12px;
}

.assistant-msg.bot th,
.assistant-msg.bot td {
    border: 1px solid var(--assistant-border);
    padding: 4px 8px;
    text-align: left;
}

.assistant-msg.bot th {
    background: rgba(255, 255, 255, 0.05);
    font-weight: 600;
}

.assistant-msg.bot ul,
.assistant-msg.bot ol {
    padding-left: 20px;
    margin: 4px 0;
}

.assistant-msg.bot strong {
    color: #93c5fd;
}

.assistant-msg.bot code {
    background: rgba(0, 0, 0, 0.3);
    padding: 1px 4px;
    border-radius: 3px;
    font-size: 12px;
}

/* --- Typing indicator --- */
.assistant-typing {
    display: none;
    align-self: flex-start;
    padding: 10px 14px;
    background: var(--assistant-bot-bg);
    border-radius: var(--assistant-radius);
    border-bottom-left-radius: 4px;
}

.assistant-typing.active {
    display: flex;
    gap: 4px;
    align-items: center;
}

.assistant-typing span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--assistant-text-muted);
    animation: typingBounce 1.2s infinite;
}

.assistant-typing span:nth-child(2) { animation-delay: 0.2s; }
.assistant-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typingBounce {
    0%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-6px); }
}

/* --- Suggerimenti rapidi --- */
.assistant-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 16px;
    border-top: 1px solid var(--assistant-border);
}

.assistant-suggestion-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--assistant-border);
    color: var(--assistant-text-muted);
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}

.assistant-suggestion-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--assistant-text);
    border-color: var(--assistant-primary);
}

/* --- Input area --- */
.assistant-input-area {
    display: flex;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid var(--assistant-border);
    background: var(--assistant-bg-light);
}

.assistant-input {
    flex: 1;
    background: var(--assistant-bg);
    border: 1px solid var(--assistant-border);
    color: var(--assistant-text);
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    resize: none;
    font-family: inherit;
}

.assistant-input:focus {
    border-color: var(--assistant-primary);
}

.assistant-input::placeholder {
    color: var(--assistant-text-muted);
}

.assistant-send-btn {
    background: var(--assistant-primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: opacity 0.15s;
}

.assistant-send-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.assistant-send-btn:not(:disabled):hover {
    opacity: 0.85;
}

/* --- Barra di progresso download --- */
.assistant-download-bar {
    padding: 16px;
    text-align: center;
}

.assistant-download-bar .progress {
    height: 6px;
    margin: 8px 0;
}

.assistant-download-text {
    color: var(--assistant-text-muted);
    font-size: 12px;
}

/* --- Pagina full-screen --- */
.assistant-fullscreen {
    max-width: 800px;
    margin: 0 auto;
    height: calc(100vh - 180px);
    display: flex;
    flex-direction: column;
    background: var(--assistant-bg);
    border: 1px solid var(--assistant-border);
    border-radius: var(--assistant-radius);
}

.assistant-fullscreen .assistant-messages {
    padding: 24px;
}

.assistant-fullscreen .assistant-msg {
    max-width: 70%;
    font-size: 14px;
}

.assistant-fullscreen .assistant-input-area {
    padding: 16px 24px;
}

.assistant-fullscreen .assistant-input {
    font-size: 14px;
    padding: 10px 14px;
}

/* --- Banner WebGPU non supportato --- */
.assistant-unsupported-banner {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: var(--assistant-bg);
    border: 1px solid var(--assistant-border);
    border-radius: 8px;
    padding: 12px 16px;
    color: var(--assistant-text-muted);
    font-size: 12px;
    max-width: 280px;
    z-index: 1039;
    display: none;
}

/* --- Responsive --- */
@media (max-width: 480px) {
    .assistant-panel {
        bottom: 0;
        right: 0;
        width: 100%;
        height: 100%;
        border-radius: 0;
    }

    .assistant-fab {
        bottom: 16px;
        right: 16px;
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add web/css/assistant.css
git commit -m "feat: add assistant chat CSS styles"
```

---

## Chunk 3: Chat UI

### Task 3.1: Widget HTML partial

**Files:**
- Create: `views/layouts/_assistant-widget.php`

- [ ] **Step 1: Creare il partial del widget**

```php
<?php
/**
 * Widget flottante dell'assistente AI.
 * Incluso in main.php, visibile solo se WebGPU è supportato.
 */
?>

<!-- FAB Button -->
<button class="assistant-fab" id="assistantFab" style="display:none" title="Assistente AI">
    <i class="ti ti-robot"></i>
    <span class="assistant-fab-badge status-loading" id="assistantStatusBadge"></span>
</button>

<!-- Chat Panel -->
<div class="assistant-panel" id="assistantPanel">
    <!-- Header -->
    <div class="assistant-header">
        <div class="assistant-header-title">
            <i class="ti ti-robot"></i>
            Assistente AI
            <span id="assistantStatusText" class="text-muted small"></span>
        </div>
        <div class="assistant-header-actions">
            <button id="assistantRefresh" title="Aggiorna dati">
                <i class="ti ti-refresh"></i>
            </button>
            <button id="assistantExpand" title="Espandi">
                <i class="ti ti-arrows-maximize"></i>
            </button>
            <button id="assistantSettings" title="Impostazioni">
                <i class="ti ti-settings"></i>
            </button>
            <button id="assistantClear" title="Cancella conversazione">
                <i class="ti ti-trash"></i>
            </button>
            <button id="assistantClose" title="Chiudi">
                <i class="ti ti-x"></i>
            </button>
        </div>
    </div>

    <!-- Download Progress -->
    <div class="assistant-download-bar" id="assistantDownload" style="display:none">
        <div class="assistant-download-text" id="assistantDownloadText">
            Preparazione modello AI...
        </div>
        <div class="progress">
            <div class="progress-bar" id="assistantDownloadBar" style="width: 0%"></div>
        </div>
        <div class="assistant-download-text" id="assistantDownloadSize"></div>
    </div>

    <!-- Messages -->
    <div class="assistant-messages" id="assistantMessages">
        <!-- I messaggi vengono inseriti dinamicamente -->
    </div>

    <!-- Typing indicator -->
    <div class="assistant-typing" id="assistantTyping">
        <span></span><span></span><span></span>
    </div>

    <!-- Quick suggestions -->
    <div class="assistant-suggestions" id="assistantSuggestions">
        <button class="assistant-suggestion-btn" data-query="Dammi un quadro generale">Quadro generale</button>
        <button class="assistant-suggestion-btn" data-query="Quali docenti dobbiamo ancora liquidare e quanto?">Docenti da liquidare</button>
        <button class="assistant-suggestion-btn" data-query="Ci sono moduli senza docente assegnato?">Moduli scoperti</button>
        <button class="assistant-suggestion-btn" data-query="Cosa scade nei prossimi 30 giorni?">Scadenze</button>
    </div>

    <!-- Input -->
    <div class="assistant-input-area">
        <input type="text"
               class="assistant-input"
               id="assistantInput"
               placeholder="Fai una domanda..."
               autocomplete="off"
               disabled>
        <button class="assistant-send-btn" id="assistantSend" disabled>
            <i class="ti ti-send"></i>
        </button>
    </div>
</div>

<!-- Banner WebGPU non supportato -->
<div class="assistant-unsupported-banner" id="assistantUnsupported">
    <i class="ti ti-alert-triangle me-1"></i>
    Il tuo browser non supporta l'assistente AI. Requisiti: Chrome 113+, Firefox 141+, Safari 26+.
</div>

<!-- Script ES modules (caricati direttamente, non via AssetBundle, per preservare import relativi) -->
<script type="module" src="/js/assistant/assistant-engine.js"></script>

<!-- Settings Modal -->
<div class="modal modal-blur fade" id="assistantSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="background: var(--assistant-bg); border: 1px solid var(--assistant-border);">
            <div class="modal-header" style="border-color: var(--assistant-border);">
                <h5 class="modal-title" style="color: var(--assistant-text);">Impostazioni Assistente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" style="color: var(--assistant-text);">Modello AI</label>
                    <select class="form-select" id="assistantModelSelect" style="background: var(--assistant-bg-light); color: var(--assistant-text); border-color: var(--assistant-border);">
                        <option value="Qwen2.5-3B-Instruct-q4f16_1-MLC">Qwen 3B (Standard ~2GB)</option>
                        <option value="Qwen2.5-7B-Instruct-q4f16_1-MLC">Qwen 7B (Avanzato ~5GB)</option>
                    </select>
                    <small class="text-muted">Il modello avanzato richiede più RAM e GPU.</small>
                </div>
            </div>
            <div class="modal-footer" style="border-color: var(--assistant-border);">
                <button class="btn btn-primary" id="assistantModelApply">Applica</button>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Commit**

```bash
git add views/layouts/_assistant-widget.php
git commit -m "feat: add assistant chat widget HTML partial"
```

---

### Task 3.2: Pagina full-screen

**Files:**
- Create: `views/site/assistente.php`

- [ ] **Step 1: Creare la vista full-screen**

```php
<?php

use yii\helpers\Html;
use app\components\assets\WebLlmAsset;

/** @var yii\web\View $this */

$this->title = 'Assistente AI';
$this->params['breadcrumbs'][] = $this->title;

WebLlmAsset::register($this);
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="page-pretitle">Strumenti</div>
                <h2 class="page-title">
                    <i class="ti ti-robot me-2"></i><?= Html::encode($this->title) ?>
                </h2>
            </div>
            <div class="col-auto ms-auto">
                <span class="badge" id="fullscreenStatus">
                    <span class="badge-dot"></span> Inizializzazione...
                </span>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="assistant-fullscreen" id="assistantFullscreen">
            <!-- Download Progress -->
            <div class="assistant-download-bar" id="fullscreenDownload" style="display:none">
                <div class="assistant-download-text" id="fullscreenDownloadText">
                    Preparazione modello AI...
                </div>
                <div class="progress">
                    <div class="progress-bar" id="fullscreenDownloadBar" style="width: 0%"></div>
                </div>
                <div class="assistant-download-text" id="fullscreenDownloadSize"></div>
            </div>

            <!-- Messages -->
            <div class="assistant-messages" id="fullscreenMessages">
            </div>

            <!-- Typing indicator -->
            <div class="assistant-typing" id="fullscreenTyping">
                <span></span><span></span><span></span>
            </div>

            <!-- Quick suggestions -->
            <div class="assistant-suggestions" id="fullscreenSuggestions">
                <button class="assistant-suggestion-btn" data-query="Dammi un quadro generale">Quadro generale</button>
                <button class="assistant-suggestion-btn" data-query="Quali docenti dobbiamo ancora liquidare e quanto?">Docenti da liquidare</button>
                <button class="assistant-suggestion-btn" data-query="Ci sono moduli senza docente assegnato?">Moduli scoperti</button>
                <button class="assistant-suggestion-btn" data-query="Cosa scade nei prossimi 30 giorni?">Scadenze</button>
                <button class="assistant-suggestion-btn" data-query="Quali docenti hanno le migliori valutazioni?">Top docenti</button>
                <button class="assistant-suggestion-btn" data-query="Stato rendicontazione ore">Ore rendicontate</button>
            </div>

            <!-- Input -->
            <div class="assistant-input-area">
                <input type="text"
                       class="assistant-input"
                       id="fullscreenInput"
                       placeholder="Fai una domanda..."
                       autocomplete="off"
                       disabled>
                <button class="assistant-send-btn" id="fullscreenSend" disabled>
                    <i class="ti ti-send"></i>
                </button>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Aggiungere action in SiteController**

Aprire `controllers/SiteController.php` e aggiungere:

```php
/**
 * Pagina full-screen dell'assistente AI
 */
public function actionAssistente()
{
    return $this->render('assistente');
}
```

E aggiungere `'assistente'` nella lista delle azioni permesse in `behaviors()` → `access` → `rules` con `'roles' => ['@']`.

- [ ] **Step 3: Commit**

```bash
git add views/site/assistente.php controllers/SiteController.php
git commit -m "feat: add full-screen assistant page"
```

---

## Chunk 4: WebLLM Engine

### Task 4.1: Tool definitions + keyword fallback (assistant-tools.js)

**Files:**
- Create: `web/js/assistant/assistant-tools.js`

- [ ] **Step 1: Creare il file con definizioni tools e fallback**

```javascript
/**
 * assistant-tools.js
 * Definizione tools per WebLLM (OpenAI-compatible) + fallback deterministico.
 */

// Base URL per le API
const ASSISTANT_API_BASE = '/assistant-api';

/**
 * Definizioni tools per il modello (formato OpenAI)
 */
export const toolDefinitions = [
    {
        type: "function",
        function: {
            name: "riepilogo_generale",
            description: "Ottieni un riepilogo generale del sistema con contatori di progetti, docenti, studenti, moduli e dati finanziari",
            parameters: { type: "object", properties: {} }
        }
    },
    {
        type: "function",
        function: {
            name: "lista_progetti",
            description: "Ottieni la lista di tutti i progetti con il loro stato finanziario (budget, contrattualizzato, pagato, residuo)",
            parameters: { type: "object", properties: {} }
        }
    },
    {
        type: "function",
        function: {
            name: "moduli_senza_docente",
            description: "Trova i moduli che non hanno un docente assegnato, raggruppati per progetto",
            parameters: { type: "object", properties: {} }
        }
    },
    {
        type: "function",
        function: {
            name: "cerca_docenti",
            description: "Cerca docenti per competenza/tag e valutazione minima",
            parameters: {
                type: "object",
                properties: {
                    competenza: { type: "string", description: "Competenza o tag da cercare, es: informatica, lingue, matematica" },
                    valutazione_minima: { type: "number", description: "Valutazione minima richiesta (da 1 a 10)" }
                }
            }
        }
    },
    {
        type: "function",
        function: {
            name: "saldi_da_liquidare",
            description: "Ottieni la lista dei contratti con saldo da liquidare (docenti ancora da pagare) con importi",
            parameters: { type: "object", properties: {} }
        }
    },
    {
        type: "function",
        function: {
            name: "ore_rendicontate",
            description: "Ottieni le ore rendicontate vs contrattualizzate per ogni modulo",
            parameters: { type: "object", properties: {} }
        }
    },
    {
        type: "function",
        function: {
            name: "graduatoria_docenti",
            description: "Ottieni la graduatoria dei docenti per un modulo specifico (assegnati, idonei, non idonei)",
            parameters: {
                type: "object",
                properties: {
                    modulo_id: { type: "number", description: "ID del modulo (opzionale, se omesso restituisce tutte le graduatorie)" }
                }
            }
        }
    },
    {
        type: "function",
        function: {
            name: "studenti_per_progetto",
            description: "Ottieni le iscrizioni studenti con status e posti disponibili per progetto",
            parameters: {
                type: "object",
                properties: {
                    progetto_id: { type: "number", description: "ID del progetto (opzionale, se omesso restituisce tutti)" }
                }
            }
        }
    },
    {
        type: "function",
        function: {
            name: "valutazioni_docenti",
            description: "Ottieni le valutazioni medie dei docenti con dettaglio",
            parameters: {
                type: "object",
                properties: {
                    codice_fiscale: { type: "string", description: "Codice fiscale del docente (opzionale, se omesso restituisce tutti)" }
                }
            }
        }
    },
    {
        type: "function",
        function: {
            name: "scadenze",
            description: "Ottieni le scadenze prossime: contratti in scadenza, bandi aperti, pagamenti programmati",
            parameters: { type: "object", properties: {} }
        }
    },
];

/**
 * Mappa function name → endpoint API
 */
const toolEndpointMap = {
    riepilogo_generale: '/riepilogo-generale',
    lista_progetti: '/progetti',
    moduli_senza_docente: '/moduli-senza-docente',
    cerca_docenti: '/cerca-docenti',
    saldi_da_liquidare: '/saldi-da-liquidare',
    ore_rendicontate: '/ore-rendicontate',
    graduatoria_docenti: '/graduatoria',
    studenti_per_progetto: '/studenti-per-progetto',
    valutazioni_docenti: '/valutazioni-docenti',
    scadenze: '/scadenze',
};

/**
 * Mappa parametri function → parametri query string
 */
const paramMap = {
    cerca_docenti: { competenza: 'competenza', valutazione_minima: 'valutazione_minima' },
    graduatoria_docenti: { modulo_id: 'modulo_id' },
    studenti_per_progetto: { progetto_id: 'progetto_id' },
    valutazioni_docenti: { codice_fiscale: 'codice_fiscale' },
};

/**
 * Esegue la chiamata API per una function call del modello
 */
export async function executeToolCall(functionName, args = {}) {
    const endpoint = toolEndpointMap[functionName];
    if (!endpoint) {
        return { error: `Funzione sconosciuta: ${functionName}` };
    }

    const url = new URL(ASSISTANT_API_BASE + endpoint, window.location.origin);

    // Aggiungi parametri
    const mapping = paramMap[functionName] || {};
    for (const [argName, paramName] of Object.entries(mapping)) {
        if (args[argName] !== undefined && args[argName] !== null) {
            url.searchParams.set(paramName, args[argName]);
        }
    }

    try {
        const response = await fetch(url.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            return { error: `Errore server: ${response.status}` };
        }

        return await response.json();
    } catch (err) {
        return { error: `Impossibile contattare il server: ${err.message}` };
    }
}

/**
 * Keyword routing deterministico — fallback quando il function calling fallisce.
 * Analizza la domanda dell'utente e chiama l'endpoint più probabile.
 */
const keywordRoutes = [
    { keywords: ['liquidare', 'pagare', 'saldo', 'liquidazion', 'pagament'], fn: 'saldi_da_liquidare' },
    { keywords: ['manca', 'scopert', 'senza docente', 'non assegnat'], fn: 'moduli_senza_docente' },
    { keywords: ['esperto', 'competenz', 'tag', 'specializzat', 'bravo in'], fn: 'cerca_docenti' },
    { keywords: ['valutazion', 'voto', 'stellin', 'valutato', 'giudizi'], fn: 'valutazioni_docenti' },
    { keywords: ['ore', 'rendicont', 'ore fatte', 'ore mancant'], fn: 'ore_rendicontate' },
    { keywords: ['student', 'iscritt', 'posti', 'iscrizioni', 'candidat'], fn: 'studenti_per_progetto' },
    { keywords: ['scadenz', 'prossim', 'scade', 'urgente'], fn: 'scadenze' },
    { keywords: ['progett', 'budget', 'finanzia', 'stato progett'], fn: 'lista_progetti' },
    { keywords: ['graduatoria', 'idoneo', 'idonei', 'posizione', 'ranking'], fn: 'graduatoria_docenti' },
    { keywords: ['generale', 'riepilogo', 'panoramica', 'quadro', 'situazione', 'come siamo'], fn: 'riepilogo_generale' },
];

/**
 * Tenta di risolvere la domanda con keyword matching
 * @returns {string|null} nome della function o null se nessun match
 */
export function keywordRoute(userMessage) {
    const msg = userMessage.toLowerCase();
    for (const route of keywordRoutes) {
        for (const kw of route.keywords) {
            if (msg.includes(kw)) {
                return route.fn;
            }
        }
    }
    return null;
}

/**
 * Estrai parametri dalla domanda per le function che ne richiedono
 */
export function extractParams(userMessage, functionName) {
    const msg = userMessage.toLowerCase();
    const args = {};

    if (functionName === 'cerca_docenti') {
        // Cerca pattern "esperto di X" o "competenza X"
        const match = msg.match(/(?:esperto di|competenz[ae]\s+(?:in\s+)?|bravo in|specializzat[oa]\s+in)\s+(\w+)/);
        if (match) args.competenza = match[1];

        // Cerca pattern "valutazione > N" o "voto superiore a N"
        const valMatch = msg.match(/(?:valutazion|voto|superior)[ei]?\s*(?:a|>|di|sopra)\s*(\d+)/);
        if (valMatch) args.valutazione_minima = parseInt(valMatch[1]);
    }

    return args;
}
```

- [ ] **Step 2: Commit**

```bash
git add web/js/assistant/assistant-tools.js
git commit -m "feat: add assistant tool definitions and keyword fallback routing"
```

---

### Task 4.2: Chat UI logic (assistant-chat.js)

**Files:**
- Create: `web/js/assistant/assistant-chat.js`

- [ ] **Step 1: Creare la logica UI della chat**

```javascript
/**
 * assistant-chat.js
 * Gestione UI della chat: messaggi, input, suggerimenti, localStorage.
 */

const STORAGE_KEY = 'assistant_chat_history';
const MAX_MESSAGES = 50;

/**
 * Classe per gestire l'interfaccia chat (widget o fullscreen)
 */
export class AssistantChat {
    constructor(options = {}) {
        this.messagesEl = document.getElementById(options.messagesId || 'assistantMessages');
        this.inputEl = document.getElementById(options.inputId || 'assistantInput');
        this.sendBtn = document.getElementById(options.sendBtnId || 'assistantSend');
        this.typingEl = document.getElementById(options.typingId || 'assistantTyping');
        this.suggestionsEl = document.getElementById(options.suggestionsId || 'assistantSuggestions');

        this.onSendMessage = options.onSendMessage || (() => {});
        this.messages = [];

        this._bindEvents();
        this._loadHistory();
    }

    _bindEvents() {
        // Invio messaggio
        if (this.sendBtn) {
            this.sendBtn.addEventListener('click', () => this._handleSend());
        }

        if (this.inputEl) {
            this.inputEl.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this._handleSend();
                }
            });
        }

        // Suggerimenti rapidi
        if (this.suggestionsEl) {
            this.suggestionsEl.addEventListener('click', (e) => {
                const btn = e.target.closest('.assistant-suggestion-btn');
                if (btn) {
                    const query = btn.dataset.query;
                    this.inputEl.value = query;
                    this._handleSend();
                }
            });
        }
    }

    _handleSend() {
        const text = this.inputEl.value.trim();
        if (!text) return;

        this.addMessage('user', text);
        this.inputEl.value = '';
        this.setInputEnabled(false);
        this.showTyping(true);

        this.onSendMessage(text);
    }

    /**
     * Aggiunge un messaggio alla chat
     */
    addMessage(role, content) {
        this.messages.push({ role, content, timestamp: Date.now() });

        // Limita messaggi
        if (this.messages.length > MAX_MESSAGES) {
            this.messages = this.messages.slice(-MAX_MESSAGES);
        }

        this._renderMessage(role, content);
        this._saveHistory();
        this._scrollToBottom();
    }

    /**
     * Aggiunge un messaggio bot in streaming (token per token)
     */
    startBotStream() {
        const msgEl = document.createElement('div');
        msgEl.classList.add('assistant-msg', 'bot');
        msgEl.innerHTML = '';
        this.messagesEl.appendChild(msgEl);
        this._currentStreamEl = msgEl;
        this._currentStreamText = '';
        this._scrollToBottom();
        return msgEl;
    }

    appendToStream(token) {
        if (!this._currentStreamEl) return;
        this._currentStreamText += token;
        this._currentStreamEl.innerHTML = this._renderMarkdown(this._currentStreamText);
        this._scrollToBottom();
    }

    endStream() {
        if (this._currentStreamText) {
            this.messages.push({ role: 'bot', content: this._currentStreamText, timestamp: Date.now() });
            this._saveHistory();
        }
        this._currentStreamEl = null;
        this._currentStreamText = '';
    }

    _renderMessage(role, content) {
        const msgEl = document.createElement('div');
        msgEl.classList.add('assistant-msg', role);

        if (role === 'bot') {
            msgEl.innerHTML = this._renderMarkdown(content);
        } else {
            msgEl.textContent = content;
        }

        this.messagesEl.appendChild(msgEl);
    }

    /**
     * Rendering markdown semplice per le risposte del bot
     */
    _renderMarkdown(text) {
        let html = text
            // Escape HTML
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            // Bold
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            // Italic
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            // Inline code
            .replace(/`(.*?)`/g, '<code>$1</code>')
            // Line breaks
            .replace(/\n/g, '<br>');

        // Semplice parsing tabelle markdown
        html = this._parseMarkdownTables(html);

        // Liste non ordinate
        html = html.replace(/^- (.*?)(<br>|$)/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*?<\/li>)+/g, '<ul>$&</ul>');

        // Liste numerate
        html = html.replace(/^\d+\. (.*?)(<br>|$)/gm, '<li>$1</li>');
        // Converti sequenze di <li> non dentro <ul> in <ol>
        html = html.replace(/(?<!<\/ul>)(<li>(?:(?!<\/?[uo]l>).)*<\/li>(?:\s*<li>(?:(?!<\/?[uo]l>).)*<\/li>)*)/g, '<ol>$1</ol>');

        return html;
    }

    _parseMarkdownTables(html) {
        // Pattern: linee con | che formano tabelle
        const lines = html.split('<br>');
        let inTable = false;
        let tableHtml = '';
        let result = [];

        for (const line of lines) {
            const trimmed = line.trim();
            if (trimmed.startsWith('|') && trimmed.endsWith('|')) {
                if (!inTable) {
                    inTable = true;
                    tableHtml = '<table>';
                }
                // Ignora la riga separatore (|---|---|)
                if (/^\|[\s\-:|]+\|$/.test(trimmed)) continue;

                const cells = trimmed.split('|').filter(c => c.trim() !== '');
                const isHeader = !tableHtml.includes('<tr>');
                const tag = isHeader ? 'th' : 'td';
                tableHtml += '<tr>' + cells.map(c => `<${tag}>${c.trim()}</${tag}>`).join('') + '</tr>';
            } else {
                if (inTable) {
                    tableHtml += '</table>';
                    result.push(tableHtml);
                    inTable = false;
                    tableHtml = '';
                }
                result.push(line);
            }
        }

        if (inTable) {
            tableHtml += '</table>';
            result.push(tableHtml);
        }

        return result.join('<br>');
    }

    showTyping(show) {
        if (this.typingEl) {
            this.typingEl.classList.toggle('active', show);
        }
        if (show) this._scrollToBottom();
    }

    setInputEnabled(enabled) {
        if (this.inputEl) this.inputEl.disabled = !enabled;
        if (this.sendBtn) this.sendBtn.disabled = !enabled;
        if (enabled && this.inputEl) this.inputEl.focus();
    }

    clearMessages() {
        this.messages = [];
        if (this.messagesEl) this.messagesEl.innerHTML = '';
        localStorage.removeItem(STORAGE_KEY);
    }

    _scrollToBottom() {
        if (this.messagesEl) {
            this.messagesEl.scrollTop = this.messagesEl.scrollHeight;
        }
    }

    _saveHistory() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(this.messages));
        } catch (e) {
            // Storage pieno, ignora
        }
    }

    _loadHistory() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                this.messages = JSON.parse(saved);
                for (const msg of this.messages) {
                    this._renderMessage(msg.role, msg.content);
                }
                this._scrollToBottom();
            }
        } catch (e) {
            // Dati corrotti, ignora
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add web/js/assistant/assistant-chat.js
git commit -m "feat: add assistant chat UI logic with localStorage persistence"
```

---

### Task 4.3: Web Worker per inferenza (assistant-worker.js)

**Files:**
- Create: `web/js/assistant/assistant-worker.js`

- [ ] **Step 1: Creare il Web Worker**

```javascript
/**
 * assistant-worker.js
 * Web Worker per l'inferenza WebLLM.
 * Comunicazione con il main thread via postMessage.
 */

import { CreateMLCEngine } from 'https://esm.run/@mlc-ai/web-llm';

let engine = null;

/**
 * Messaggi dal main thread:
 * - { type: 'init', model: string }
 * - { type: 'chat', messages: array, tools: array }
 * - { type: 'abort' }
 */
self.onmessage = async function (e) {
    const { type, data } = e.data;

    switch (type) {
        case 'init':
            await initEngine(data.model);
            break;
        case 'chat':
            await handleChat(data.messages, data.tools);
            break;
        case 'abort':
            if (engine) {
                engine.interruptGenerate();
            }
            break;
    }
};

async function initEngine(modelId) {
    try {
        self.postMessage({ type: 'status', data: 'loading' });

        engine = await CreateMLCEngine(modelId, {
            initProgressCallback: (progress) => {
                self.postMessage({
                    type: 'progress',
                    data: {
                        text: progress.text || '',
                        progress: progress.progress || 0,
                    }
                });
            },
        });

        self.postMessage({ type: 'status', data: 'ready' });
    } catch (err) {
        self.postMessage({ type: 'error', data: `Errore caricamento modello: ${err.message}` });
    }
}

async function handleChat(messages, tools) {
    if (!engine) {
        self.postMessage({ type: 'error', data: 'Modello non caricato' });
        return;
    }

    try {
        const request = { messages, stream: true };

        // Aggiungi tools solo se definiti
        if (tools && tools.length > 0) {
            request.tools = tools;
        }

        // Tentativo con function calling
        const chunks = await engine.chat.completions.create(request);

        let fullResponse = '';
        let toolCalls = [];

        for await (const chunk of chunks) {
            const delta = chunk.choices[0]?.delta;
            if (!delta) continue;

            // Token di testo
            if (delta.content) {
                fullResponse += delta.content;
                self.postMessage({ type: 'token', data: delta.content });
            }

            // Tool call
            if (delta.tool_calls) {
                for (const tc of delta.tool_calls) {
                    if (tc.function) {
                        toolCalls.push({
                            id: tc.id || `call_${Date.now()}`,
                            name: tc.function.name,
                            arguments: tc.function.arguments || '{}',
                        });
                    }
                }
            }
        }

        if (toolCalls.length > 0) {
            // Manda le tool calls al main thread per esecuzione
            self.postMessage({
                type: 'tool_calls',
                data: toolCalls,
            });
        } else {
            // Risposta completata senza tool calls
            self.postMessage({ type: 'done', data: fullResponse });
        }

    } catch (err) {
        self.postMessage({ type: 'error', data: `Errore generazione: ${err.message}` });
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add web/js/assistant/assistant-worker.js
git commit -m "feat: add WebLLM Web Worker for browser-side inference"
```

---

### Task 4.4: Engine principale (assistant-engine.js)

**Files:**
- Create: `web/js/assistant/assistant-engine.js`

- [ ] **Step 1: Creare l'engine che orchestra tutto**

```javascript
/**
 * assistant-engine.js
 * Orchestratore principale: collega Chat UI, Web Worker, e Tool calls.
 */

import { AssistantChat } from './assistant-chat.js';
import { toolDefinitions, executeToolCall, keywordRoute, extractParams } from './assistant-tools.js';

const DEFAULT_MODEL = 'Qwen2.5-3B-Instruct-q4f16_1-MLC';
const STORAGE_MODEL_KEY = 'assistant_selected_model';

class AssistantEngine {
    constructor() {
        this.worker = null;
        this.chat = null;
        this.fullscreenChat = null;
        this.status = 'idle'; // idle, loading, ready, error
        this.systemPrompt = '';
        this.conversationHistory = [];
        this.isFullscreen = false;
    }

    /**
     * Inizializza l'assistente
     */
    async init() {
        // Check WebGPU
        if (!navigator.gpu) {
            this._showUnsupported();
            return;
        }

        // Verifica adapter GPU reale
        try {
            const adapter = await navigator.gpu.requestAdapter();
            if (!adapter) {
                this._showUnsupported();
                return;
            }
        } catch (e) {
            this._showUnsupported();
            return;
        }

        // Determina se siamo in pagina fullscreen o widget
        this.isFullscreen = !!document.getElementById('assistantFullscreen');

        if (this.isFullscreen) {
            this._initFullscreen();
        } else {
            this._initWidget();
        }

        // Avvia il worker
        this._initWorker();
    }

    _initWidget() {
        const fab = document.getElementById('assistantFab');
        const panel = document.getElementById('assistantPanel');
        if (!fab || !panel) return;

        fab.style.display = 'flex';

        // Chat UI per il widget
        this.chat = new AssistantChat({
            messagesId: 'assistantMessages',
            inputId: 'assistantInput',
            sendBtnId: 'assistantSend',
            typingId: 'assistantTyping',
            suggestionsId: 'assistantSuggestions',
            onSendMessage: (text) => this._onUserMessage(text),
        });

        // Toggle pannello
        fab.addEventListener('click', () => {
            panel.classList.toggle('open');
            if (panel.classList.contains('open') && this.status === 'idle') {
                this._loadModel();
            }
        });

        // Chiudi
        document.getElementById('assistantClose')?.addEventListener('click', () => {
            panel.classList.remove('open');
        });

        // Espandi a fullscreen
        document.getElementById('assistantExpand')?.addEventListener('click', () => {
            window.location.href = '/site/assistente';
        });

        // Aggiorna dati
        document.getElementById('assistantRefresh')?.addEventListener('click', () => {
            this._loadRiepilogo();
        });

        // Cancella conversazione
        document.getElementById('assistantClear')?.addEventListener('click', () => {
            this.chat.clearMessages();
            this.conversationHistory = [];
        });

        // Settings
        document.getElementById('assistantSettings')?.addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('assistantSettingsModal'));
            const select = document.getElementById('assistantModelSelect');
            if (select) select.value = this._getSelectedModel();
            modal.show();
        });

        document.getElementById('assistantModelApply')?.addEventListener('click', () => {
            const select = document.getElementById('assistantModelSelect');
            if (select) {
                localStorage.setItem(STORAGE_MODEL_KEY, select.value);
                // Ricarica il modello
                this.status = 'idle';
                this._loadModel();
            }
            bootstrap.Modal.getInstance(document.getElementById('assistantSettingsModal'))?.hide();
        });
    }

    _initFullscreen() {
        this.chat = new AssistantChat({
            messagesId: 'fullscreenMessages',
            inputId: 'fullscreenInput',
            sendBtnId: 'fullscreenSend',
            typingId: 'fullscreenTyping',
            suggestionsId: 'fullscreenSuggestions',
            onSendMessage: (text) => this._onUserMessage(text),
        });

        // Carica modello subito
        this._loadModel();
    }

    _initWorker() {
        this.worker = new Worker('/js/assistant/assistant-worker.js', { type: 'module' });

        this.worker.onmessage = (e) => {
            const { type, data } = e.data;

            switch (type) {
                case 'status':
                    this._onStatusChange(data);
                    break;
                case 'progress':
                    this._onProgress(data);
                    break;
                case 'token':
                    this._onToken(data);
                    break;
                case 'tool_calls':
                    this._onToolCalls(data);
                    break;
                case 'done':
                    this._onDone(data);
                    break;
                case 'error':
                    this._onError(data);
                    break;
            }
        };
    }

    _getSelectedModel() {
        return localStorage.getItem(STORAGE_MODEL_KEY) || DEFAULT_MODEL;
    }

    _loadModel() {
        if (this.status === 'loading' || this.status === 'ready') return;
        this.status = 'loading';

        this.worker.postMessage({
            type: 'init',
            data: { model: this._getSelectedModel() }
        });
    }

    async _loadRiepilogo() {
        try {
            const data = await executeToolCall('riepilogo_generale');
            this.systemPrompt = `Sei l'assistente AI di Albatros Manager, un sistema di gestione docenti e corsi FSE/PNRR. Rispondi sempre in italiano.
Hai a disposizione delle funzioni per interrogare il database.
Usa le funzioni per rispondere con dati reali e aggiornati.
Se non riesci a trovare l'informazione, dillo chiaramente.
Formatta le risposte in modo chiaro con elenchi e tabelle quando utile.

DATI AGGIORNATI:
${JSON.stringify(data, null, 2)}`;
        } catch (e) {
            this.systemPrompt = `Sei l'assistente AI di Albatros Manager. Rispondi sempre in italiano. Non sono riuscito a caricare i dati aggiornati dal server.`;
        }
    }

    async _onUserMessage(text) {
        // Aggiungi alla history
        this.conversationHistory.push({ role: 'user', content: text });

        // Prepara messaggi per il modello
        const messages = [
            { role: 'system', content: this.systemPrompt },
            ...this.conversationHistory,
        ];

        // Invia al worker
        this._currentStreamEl = this.chat.startBotStream();
        this.chat.showTyping(true);

        this.worker.postMessage({
            type: 'chat',
            data: { messages, tools: toolDefinitions }
        });

        // Timeout 30 secondi
        this._timeout = setTimeout(() => {
            this.worker.postMessage({ type: 'abort' });
            this._onError('La risposta sta impiegando troppo tempo. Riprova.');
        }, 30000);
    }

    _onStatusChange(status) {
        this.status = status;
        const badge = document.getElementById('assistantStatusBadge');
        const statusText = document.getElementById('assistantStatusText');
        const fullscreenStatus = document.getElementById('fullscreenStatus');

        if (status === 'ready') {
            if (badge) {
                badge.classList.remove('status-loading', 'status-error');
                badge.classList.add('status-ready');
            }
            if (statusText) statusText.textContent = 'Pronto';
            if (fullscreenStatus) {
                fullscreenStatus.className = 'badge bg-green-lt';
                fullscreenStatus.innerHTML = '<span class="badge-dot badge-dot-animated"></span> Pronto';
            }

            // Nascondi progress
            this._hideDownloadBar();

            // Carica riepilogo e abilita input
            this._loadRiepilogo().then(() => {
                this.chat.setInputEnabled(true);
                if (this.conversationHistory.length === 0) {
                    this.chat.addMessage('bot', 'Ciao! Ho caricato i dati aggiornati del sistema. Chiedimi quello che vuoi sapere su progetti, docenti, studenti, pagamenti e altro.');
                }
            });
        } else if (status === 'loading') {
            if (badge) {
                badge.classList.remove('status-ready', 'status-error');
                badge.classList.add('status-loading');
            }
            if (statusText) statusText.textContent = 'Caricamento...';
            if (fullscreenStatus) {
                fullscreenStatus.className = 'badge bg-orange-lt';
                fullscreenStatus.innerHTML = '<span class="badge-dot badge-dot-animated"></span> Caricamento...';
            }
        }
    }

    _onProgress(data) {
        const downloadBar = document.getElementById(this.isFullscreen ? 'fullscreenDownload' : 'assistantDownload');
        const progressBar = document.getElementById(this.isFullscreen ? 'fullscreenDownloadBar' : 'assistantDownloadBar');
        const downloadText = document.getElementById(this.isFullscreen ? 'fullscreenDownloadText' : 'assistantDownloadText');

        if (downloadBar) downloadBar.style.display = 'block';
        if (progressBar) progressBar.style.width = `${Math.round(data.progress * 100)}%`;
        if (downloadText) downloadText.textContent = data.text || 'Scaricamento modello AI...';
    }

    _hideDownloadBar() {
        const bars = ['assistantDownload', 'fullscreenDownload'];
        for (const id of bars) {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        }
    }

    _onToken(token) {
        this.chat.showTyping(false);
        this.chat.appendToStream(token);
    }

    async _onToolCalls(toolCalls) {
        clearTimeout(this._timeout);
        this.chat.showTyping(true);

        let toolResults = [];

        for (const tc of toolCalls) {
            let args = {};
            try {
                args = JSON.parse(tc.arguments);
            } catch (e) {
                // JSON malformato, ignora args
            }

            const result = await executeToolCall(tc.name, args);
            toolResults.push({
                role: 'tool',
                tool_call_id: tc.id,
                content: JSON.stringify(result),
            });
        }

        // Aggiungi tool call e risultati alla history
        this.conversationHistory.push({
            role: 'assistant',
            tool_calls: toolCalls.map(tc => ({
                id: tc.id,
                type: 'function',
                function: { name: tc.name, arguments: tc.arguments }
            }))
        });
        this.conversationHistory.push(...toolResults);

        // Reinvia al modello con i risultati
        const messages = [
            { role: 'system', content: this.systemPrompt },
            ...this.conversationHistory,
        ];

        this._currentStreamEl = this.chat.startBotStream();

        this.worker.postMessage({
            type: 'chat',
            data: { messages, tools: [] } // No tools nel secondo round
        });

        this._timeout = setTimeout(() => {
            this.worker.postMessage({ type: 'abort' });
            this._onError('Timeout nella generazione della risposta.');
        }, 30000);
    }

    _onDone(fullText) {
        clearTimeout(this._timeout);
        this.chat.showTyping(false);
        this.chat.endStream();
        this.chat.setInputEnabled(true);

        // Aggiungi alla history
        if (fullText) {
            this.conversationHistory.push({ role: 'assistant', content: fullText });
        }
    }

    async _onError(errorMsg) {
        clearTimeout(this._timeout);
        this.chat.showTyping(false);
        this.chat.endStream();

        // Tentativo fallback deterministico
        const lastUserMsg = this.conversationHistory.filter(m => m.role === 'user').pop();
        if (lastUserMsg && !this._fallbackAttempted) {
            this._fallbackAttempted = true;
            const result = await this._fallbackKeywordRoute(lastUserMsg.content);
            if (result) {
                this._fallbackAttempted = false;
                return;
            }
        }

        this._fallbackAttempted = false;
        this.chat.addMessage('bot', `Mi dispiace, ho avuto un problema: ${errorMsg}`);
        this.chat.setInputEnabled(true);
    }

    /**
     * Fallback: usa keyword routing per ottenere dati, poi chiedi al modello di formattarli
     */
    async _fallbackKeywordRoute(userMessage) {
        const fn = keywordRoute(userMessage);
        if (!fn) return false;

        const args = extractParams(userMessage, fn);
        const data = await executeToolCall(fn, args);

        if (data.error) return false;

        // Chiedi al modello di formattare la risposta
        const fallbackMessages = [
            { role: 'system', content: 'Sei un assistente. Rispondi in italiano in modo chiaro e sintetico. Formatta con elenchi o tabelle se utile.' },
            { role: 'user', content: `L'utente ha chiesto: "${userMessage}"\n\nEcco i dati dal database:\n${JSON.stringify(data, null, 2)}\n\nRispondi alla domanda usando questi dati.` },
        ];

        this._currentStreamEl = this.chat.startBotStream();

        this.worker.postMessage({
            type: 'chat',
            data: { messages: fallbackMessages, tools: [] }
        });

        return true;
    }

    _showUnsupported() {
        // Nasconde il FAB, mostra banner opzionale
        const fab = document.getElementById('assistantFab');
        if (fab) fab.style.display = 'none';

        const banner = document.getElementById('assistantUnsupported');
        if (banner) banner.style.display = 'block';

        // Per fullscreen, mostra messaggio nella pagina
        const fullscreen = document.getElementById('assistantFullscreen');
        if (fullscreen) {
            fullscreen.innerHTML = `
                <div class="empty">
                    <div class="empty-icon"><i class="ti ti-device-desktop-off" style="font-size:3rem;color:var(--assistant-text-muted)"></i></div>
                    <p class="empty-title" style="color:var(--assistant-text)">Browser non supportato</p>
                    <p class="empty-subtitle" style="color:var(--assistant-text-muted)">
                        L'assistente AI richiede WebGPU.<br>
                        Browser supportati: Chrome 113+, Firefox 141+, Safari 26+.
                    </p>
                </div>`;
        }
    }
}

// Inizializzazione automatica al caricamento della pagina
document.addEventListener('DOMContentLoaded', () => {
    const engine = new AssistantEngine();
    engine.init();
});
```

- [ ] **Step 2: Commit**

```bash
git add web/js/assistant/assistant-engine.js
git commit -m "feat: add main assistant engine orchestrating WebLLM, chat UI, and tool calls"
```

---

## Chunk 5: Integrazione + Finalizzazione

### Task 5.1: Integrare widget nel layout principale

**Files:**
- Modify: `views/layouts/main.php`

- [ ] **Step 1: Aggiungere il widget prima della chiusura del body**

In `views/layouts/main.php`, appena prima di `<?php $this->endBody() ?>`, aggiungere:

```php
<?= $this->render('_assistant-widget') ?>
```

- [ ] **Step 2: Registrare l'asset CSS nella pagina**

Sempre in `views/layouts/main.php`, dopo la riga `TablerAsset::register($this);`, aggiungere:

```php
\app\components\assets\WebLlmAsset::register($this);
```

Questo registra solo il CSS. I file JS (ES modules) sono caricati direttamente nel widget partial con `<script type="module">`.

- [ ] **Step 3: Commit**

```bash
git add views/layouts/main.php
git commit -m "feat: integrate assistant widget into main admin layout"
```

---

### Task 5.2: Aggiungere voce menu

**Files:**
- Modify: `views/layouts/menu.php`

- [ ] **Step 1: Aggiungere link nel menu Strumenti**

Cercare la sezione "Strumenti" nel menu e aggiungere:

```php
<a class="dropdown-item" href="/site/assistente">
    <span class="nav-link-icon"><i class="ti ti-robot"></i></span>
    <span class="nav-link-title">Assistente AI</span>
</a>
```

- [ ] **Step 2: Commit**

```bash
git add views/layouts/menu.php
git commit -m "feat: add Assistente AI link to admin menu"
```

---

### Task 5.3: Aggiornare CHANGELOG.md

**Files:**
- Modify: `CHANGELOG.md`

- [ ] **Step 1: Aggiungere voce al changelog**

Aggiungere in cima alla sezione più recente (o creare nuova versione):

```markdown
### Aggiunto
- **Assistente AI** {admin}: Chat intelligente basata su LLM che risponde a domande sui dati del sistema (progetti, docenti, pagamenti, studenti). Il modello AI gira interamente nel browser (WebGPU), zero costi server. Widget flottante + pagina full-screen.
```

- [ ] **Step 2: Commit**

```bash
git add CHANGELOG.md
git commit -m "docs: add AI assistant to changelog"
```

---

### Task 5.4: Test manuale

- [ ] **Step 1: Avviare Docker e verificare API**

```bash
docker-compose up -d
```

Aprire nel browser (dopo login admin):
- `http://localhost:8000/assistant-api/riepilogo-generale` → deve rispondere JSON
- `http://localhost:8000/assistant-api/progetti` → deve rispondere JSON
- `http://localhost:8000/assistant-api/saldi-da-liquidare` → deve rispondere JSON

- [ ] **Step 2: Verificare widget**

- Navigare a `http://localhost:8000` (dashboard admin)
- Verificare che il FAB (pallina robot) appaia in basso a destra
- Se WebGPU non è supportato → FAB non deve comparire
- Click sul FAB → il pannello chat si apre
- Verificare progress bar download modello

- [ ] **Step 3: Verificare pagina fullscreen**

- Navigare a `http://localhost:8000/site/assistente`
- Verificare la pagina con la chat full-screen
- Verificare breadcrumb e titolo

- [ ] **Step 4: Test conversazione (se WebGPU disponibile)**

Provare queste domande:
- "Dammi un quadro generale"
- "Ci sono moduli senza docente?"
- "Quanto dobbiamo ancora pagare ai docenti?"
- "Chi è esperto di informatica?"

- [ ] **Step 5: Commit finale**

```bash
git add -A
git commit -m "feat: complete AI assistant bot v1 integration"
```

---

## Riepilogo file

### Nuovi (10 file)
| File | Scopo |
|------|-------|
| `controllers/AssistantApiController.php` | 10 endpoint API read-only |
| `components/assets/WebLlmAsset.php` | Asset bundle |
| `views/layouts/_assistant-widget.php` | Widget HTML + settings modal |
| `views/site/assistente.php` | Pagina full-screen |
| `web/js/assistant/assistant-engine.js` | Orchestratore principale |
| `web/js/assistant/assistant-worker.js` | Web Worker WebLLM |
| `web/js/assistant/assistant-chat.js` | Logica UI chat |
| `web/js/assistant/assistant-tools.js` | Tool definitions + fallback |
| `web/css/assistant.css` | Stili widget + chat |

### Modificati (3 file)
| File | Modifica |
|------|----------|
| `views/layouts/main.php` | Include widget + registra asset |
| `views/layouts/menu.php` | Link "Assistente AI" |
| `controllers/SiteController.php` | Action `assistente` |
| `CHANGELOG.md` | Voce nuova feature |
