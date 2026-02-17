# Sistema Liquidazione Pagamenti Docenti - Piano di Implementazione

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Permettere la liquidazione facile (singola e massiva) dei pagamenti docenti, con importi pre-popolati, alert ore rendicontate, e pulsante "paga solo ore rendicontate come acconto".

**Architecture:** Si estende il PagamentoController con endpoint AJAX per info contratto e liquidazione massiva. Le viste modulo e avviso vengono potenziate con alert ore, pulsanti pre-compilati e checkbox per selezione massiva. Nessuna nuova tabella DB.

**Tech Stack:** Yii2, jQuery (gia' incluso), Tabler UI, PHP 8.4

---

### Task 1: Endpoint AJAX `actionContrattoInfo` in PagamentoController

**Files:**
- Modify: `controllers/PagamentoController.php:77-96`

**Step 1: Aggiungere `actionContrattoInfo` dopo `actionDelete`**

Aggiungere dopo riga 131 di `controllers/PagamentoController.php`:

```php
/**
 * Restituisce info contratto in JSON per AJAX auto-fill form pagamento.
 * @param int $id Contratto ID
 * @return array
 */
public function actionContrattoInfo($id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $contratto = Contratto::findOne($id);
    if (!$contratto) {
        throw new NotFoundHttpException('Contratto non trovato.');
    }

    $assegnazione = $contratto->assegnazione;
    $docente = $assegnazione ? $assegnazione->docente : null;
    $modulo = $assegnazione ? $assegnazione->modulo : null;

    // Ore rendicontate
    $oreApprovate = $assegnazione ? RendicontazioneOre::getTotaleOreApprovate($assegnazione->id) : 0;
    $oreInserite = $assegnazione ? RendicontazioneOre::getTotaleOreInserite($assegnazione->id) : 0;

    // Calcolo ritenute percentuale dal contratto
    $ritenutePerc = ($contratto->compenso_totale > 0 && $contratto->ritenute)
        ? round(($contratto->ritenute / $contratto->compenso_totale) * 100, 2)
        : 0;

    return [
        'compenso_totale' => (float) $contratto->compenso_totale,
        'compenso_orario' => (float) $contratto->compenso_orario,
        'totale_pagato' => $contratto->getTotalePagato(),
        'saldo_rimanente' => $contratto->getSaldoRimanente(),
        'ritenute_percentuale' => $ritenutePerc,
        'netto_docente' => (float) ($contratto->netto_docente ?: 0),
        'ore_contrattualizzate' => (int) $contratto->ore_contrattualizzate,
        'ore_approvate' => (float) $oreApprovate,
        'ore_inserite' => (float) $oreInserite,
        'docente' => $docente ? $docente->cognome . ' ' . $docente->nome : 'N/D',
        'modulo_codice' => $modulo ? $modulo->codice : 'N/D',
        'modulo_titolo' => $modulo ? $modulo->titolo : 'N/D',
    ];
}
```

**Step 2: Aggiungere import `RendicontazioneOre` in testa al controller**

Aggiungere dopo riga 8 (`use app\models\Contratto;`):

```php
use app\models\RendicontazioneOre;
use app\models\Assegnazione;
```

**Step 3: Verificare che il server risponda**

```bash
curl -s "http://localhost:8000/pagamento/contratto-info?id=1" -b "cookie.txt" | head -20
```

Expected: JSON con dati contratto o 404 se id=1 non esiste.

**Step 4: Commit**

```bash
git add controllers/PagamentoController.php
git commit -m "feat: add AJAX contratto-info endpoint for payment auto-fill"
```

---

### Task 2: Estendere `actionCreate` per parametri pre-compilazione URL

**Files:**
- Modify: `controllers/PagamentoController.php:77-96`

**Step 1: Modificare `actionCreate` per accettare parametri extra**

Sostituire l'attuale `actionCreate` (righe 77-96):

```php
/**
 * Crea un nuovo pagamento.
 * @param int|null $contratto_id Pre-seleziona il contratto
 * @param string|null $tipo Pre-seleziona tipo (acconto, saldo, rata, rimborso)
 * @param float|null $importo Pre-popola importo lordo
 * @param float|null $importo_netto Pre-popola importo netto
 * @return string|\yii\web\Response
 */
public function actionCreate($contratto_id = null, $tipo = null, $importo = null, $importo_netto = null)
{
    $model = new Pagamento();
    $model->status = Pagamento::STATUS_PROGRAMMATO;

    if ($contratto_id) {
        $model->contratto_id = $contratto_id;

        // Auto-popola saldo rimanente se nessun importo specificato via URL
        $contratto = Contratto::findOne($contratto_id);
        if ($contratto && $importo === null) {
            $saldo = $contratto->getSaldoRimanente();
            $model->importo_lordo = $saldo;
            // Calcola ritenute proporzionali se il contratto le ha
            if ($contratto->ritenute && $contratto->compenso_totale > 0) {
                $percRitenute = $contratto->ritenute / $contratto->compenso_totale;
                $model->ritenute = round($saldo * $percRitenute, 2);
                $model->importo_netto = round($saldo - $model->ritenute, 2);
            } else {
                $model->importo_netto = $saldo;
            }
        }
    }

    // Parametri URL sovrascrivono i default
    if ($tipo !== null) {
        $model->tipo = $tipo;
    }
    if ($importo !== null) {
        $model->importo_lordo = (float) $importo;
    }
    if ($importo_netto !== null) {
        $model->importo_netto = (float) $importo_netto;
    }

    // Dati ore per alert nel form
    $infoOre = null;
    if ($model->contratto_id) {
        $contratto = $contratto ?? Contratto::findOne($model->contratto_id);
        if ($contratto && $contratto->assegnazione) {
            $ass = $contratto->assegnazione;
            $infoOre = [
                'ore_contrattualizzate' => (int) $contratto->ore_contrattualizzate,
                'ore_approvate' => RendicontazioneOre::getTotaleOreApprovate($ass->id),
                'ore_inserite' => RendicontazioneOre::getTotaleOreInserite($ass->id),
                'compenso_orario' => (float) $contratto->compenso_orario,
            ];
        }
    }

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        Yii::$app->session->setFlash('success', 'Pagamento creato con successo.');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    return $this->render('create', [
        'model' => $model,
        'contratti' => $this->getContrattiList(),
        'infoOre' => $infoOre,
    ]);
}
```

**Step 2: Commit**

```bash
git add controllers/PagamentoController.php
git commit -m "feat: smart payment creation with URL pre-fill and hours info"
```

---

### Task 3: Form Pagamento smart con AJAX e info card

**Files:**
- Modify: `views/pagamento/_form.php` (intero file)
- Modify: `views/pagamento/create.php:33` (passare `infoOre`)

**Step 1: Aggiornare `views/pagamento/create.php` per passare `infoOre`**

Sostituire il render della _form (riga 33-35):

```php
<?= $this->render('_form', [
    'model' => $model,
    'contratti' => $contratti,
    'infoOre' => $infoOre ?? null,
]) ?>
```

**Step 2: Riscrivere `views/pagamento/_form.php` con AJAX e info card**

Il nuovo form include:
- Info card contratto (visibile quando contratto selezionato)
- Alert ore rendicontate con pulsante "Popola saldo rimanente"
- AJAX on change della dropdown contratto che chiama `contratto-info`
- Auto-calcolo ritenute/netto dal lordo

```php
<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppActiveForm;
use app\models\Pagamento;

/* @var $this yii\web\View */
/* @var $model app\models\Pagamento */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $contratti array */
/* @var $infoOre array|null */
?>

<div class="pagamento-form">
    <?php $form = AppActiveForm::begin([
        'id' => 'pagamento-form',
        'options' => ['class' => 'card'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-currency-euro me-2"></i>
            Dati Pagamento
        </h3>
    </div>

    <div class="card-body">
        <!-- Info Card Contratto (nascosta se nessun contratto selezionato) -->
        <div id="contratto-info-card" class="alert alert-info mb-4" style="display: none;">
            <div class="d-flex align-items-start">
                <span class="avatar bg-blue-lt me-3"><i class="ti ti-file-invoice"></i></span>
                <div class="flex-fill">
                    <h4 class="alert-title mb-1" id="ci-docente">-</h4>
                    <div class="text-muted small mb-2" id="ci-modulo">-</div>
                    <div class="row g-2">
                        <div class="col-auto">
                            <span class="text-muted">Contrattualizzato:</span>
                            <strong id="ci-totale">-</strong>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Pagato:</span>
                            <strong class="text-green" id="ci-pagato">-</strong>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Saldo rimanente:</span>
                            <strong class="text-orange" id="ci-saldo">-</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Ore Rendicontate -->
        <div id="ore-alert" class="alert alert-warning mb-4" style="display: none;">
            <div class="d-flex align-items-center">
                <i class="ti ti-alert-triangle me-2" style="font-size: 1.5rem;"></i>
                <div class="flex-fill">
                    <h4 class="alert-title mb-1">Ore non completamente rendicontate</h4>
                    <div id="ore-alert-text">-</div>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" id="btn-paga-ore-rendicontate" class="btn btn-sm btn-green">
                    <i class="ti ti-clock-dollar me-1"></i> Paga solo ore rendicontate come acconto
                </button>
                <button type="button" id="btn-popola-saldo" class="btn btn-sm btn-outline-blue ms-2">
                    <i class="ti ti-cash me-1"></i> Popola saldo rimanente
                </button>
            </div>
        </div>

        <!-- Alert Ore Complete -->
        <div id="ore-ok-alert" class="alert alert-success mb-4" style="display: none;">
            <div class="d-flex align-items-center">
                <i class="ti ti-circle-check me-2" style="font-size: 1.5rem;"></i>
                <div class="flex-fill">
                    <h4 class="alert-title mb-0">Ore completamente rendicontate</h4>
                    <div id="ore-ok-text">-</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'contratto_id')->dropDownList($contratti, [
                    'prompt' => 'Seleziona un contratto...',
                    'class' => 'form-select',
                    'id' => 'pagamento-contratto_id',
                ])->label('Contratto <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'tipo')->dropDownList(Pagamento::getTipoLabels(), [
                    'prompt' => '-- Seleziona tipo --',
                    'class' => 'form-select',
                    'id' => 'pagamento-tipo',
                ])->label('Tipo <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Pagamento::getStatusLabels(), [
                    'class' => 'form-select',
                ])->label('Stato <span class="text-red">*</span>') ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-cash me-2"></i>
            Importi
        </h4>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'importo_lordo')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '0.00',
                    'id' => 'pagamento-importo_lordo',
                ])->label('Importo Lordo (&euro;) <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'ritenute')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '0.00',
                    'id' => 'pagamento-ritenute',
                ])->label('Ritenute (&euro;)') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'importo_netto')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '0.00',
                    'id' => 'pagamento-importo_netto',
                ])->label('Importo Netto (&euro;) <span class="text-red">*</span>') ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-calendar me-2"></i>
            Date e Riferimenti
        </h4>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'data_pagamento')->textInput(['type' => 'date']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'data_scadenza')->textInput(['type' => 'date']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'riferimento_fattura')->textInput(['maxlength' => true, 'placeholder' => 'Es: FT-2026/001']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'numero_mandato')->textInput(['maxlength' => true, 'placeholder' => 'Es: M-001/2026']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'cro_bonifico')->textInput(['maxlength' => true, 'placeholder' => 'Es: 1234567890']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'note')->textarea(['rows' => 3, 'placeholder' => 'Note aggiuntive...']) ?>
            </div>
        </div>
    </div>

    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-green']) ?>
    </div>

    <?php AppActiveForm::end(); ?>
</div>

<?php
$contrattoInfoUrl = Url::to(['/pagamento/contratto-info']);
$this->registerJs(<<<JS
    var currentContrattoData = null;

    function formatCurrency(val) {
        return new Intl.NumberFormat('it-IT', {style: 'currency', currency: 'EUR'}).format(val);
    }

    function updateContrattoInfo(data) {
        currentContrattoData = data;
        // Info card
        $('#ci-docente').text(data.docente);
        $('#ci-modulo').text(data.modulo_codice + ' - ' + data.modulo_titolo);
        $('#ci-totale').text(formatCurrency(data.compenso_totale));
        $('#ci-pagato').text(formatCurrency(data.totale_pagato));
        $('#ci-saldo').text(formatCurrency(data.saldo_rimanente));
        $('#contratto-info-card').slideDown(200);

        // Alert ore
        var percOre = data.ore_contrattualizzate > 0
            ? Math.round((data.ore_approvate / data.ore_contrattualizzate) * 100)
            : 0;

        if (data.ore_contrattualizzate > 0 && data.ore_approvate < data.ore_contrattualizzate) {
            var importoOre = (data.ore_approvate * data.compenso_orario).toFixed(2);
            $('#ore-alert-text').html(
                'Il docente ha rendicontato <strong>' + data.ore_approvate + '/' + data.ore_contrattualizzate + ' ore</strong> (' + percOre + '%). ' +
                'Importo per ore rendicontate: <strong>' + formatCurrency(importoOre) + '</strong>'
            );
            $('#ore-alert').slideDown(200);
            $('#ore-ok-alert').slideUp(200);
        } else if (data.ore_contrattualizzate > 0) {
            $('#ore-ok-text').text(data.ore_approvate + '/' + data.ore_contrattualizzate + ' ore approvate (' + percOre + '%)');
            $('#ore-ok-alert').slideDown(200);
            $('#ore-alert').slideUp(200);
        } else {
            $('#ore-alert, #ore-ok-alert').slideUp(200);
        }
    }

    // AJAX on contratto change
    $('#pagamento-contratto_id').on('change', function() {
        var id = $(this).val();
        if (!id) {
            $('#contratto-info-card, #ore-alert, #ore-ok-alert').slideUp(200);
            currentContrattoData = null;
            return;
        }
        $.getJSON('{$contrattoInfoUrl}', {id: id}, function(data) {
            updateContrattoInfo(data);
        });
    });

    // Pulsante "Paga solo ore rendicontate come acconto"
    $('#btn-paga-ore-rendicontate').on('click', function() {
        if (!currentContrattoData) return;
        var importo = (currentContrattoData.ore_approvate * currentContrattoData.compenso_orario).toFixed(2);
        $('#pagamento-importo_lordo').val(importo);
        $('#pagamento-tipo').val('acconto');
        // Calcola ritenute se presenti
        if (currentContrattoData.ritenute_percentuale > 0) {
            var ritenute = (importo * currentContrattoData.ritenute_percentuale / 100).toFixed(2);
            $('#pagamento-ritenute').val(ritenute);
            $('#pagamento-importo_netto').val((importo - ritenute).toFixed(2));
        } else {
            $('#pagamento-ritenute').val('');
            $('#pagamento-importo_netto').val(importo);
        }
    });

    // Pulsante "Popola saldo rimanente"
    $('#btn-popola-saldo').on('click', function() {
        if (!currentContrattoData) return;
        var saldo = currentContrattoData.saldo_rimanente.toFixed(2);
        $('#pagamento-importo_lordo').val(saldo);
        if (currentContrattoData.ritenute_percentuale > 0) {
            var ritenute = (saldo * currentContrattoData.ritenute_percentuale / 100).toFixed(2);
            $('#pagamento-ritenute').val(ritenute);
            $('#pagamento-importo_netto').val((saldo - ritenute).toFixed(2));
        } else {
            $('#pagamento-ritenute').val('');
            $('#pagamento-importo_netto').val(saldo);
        }
    });

    // Auto-calcolo netto quando cambia lordo o ritenute
    $('#pagamento-importo_lordo, #pagamento-ritenute').on('input', function() {
        var lordo = parseFloat($('#pagamento-importo_lordo').val()) || 0;
        var ritenute = parseFloat($('#pagamento-ritenute').val()) || 0;
        $('#pagamento-importo_netto').val((lordo - ritenute).toFixed(2));
    });

    // Trigger iniziale se contratto gia' selezionato (pre-fill da URL)
    if ($('#pagamento-contratto_id').val()) {
        $('#pagamento-contratto_id').trigger('change');
    }
JS
);
?>
```

**Step 3: Commit**

```bash
git add views/pagamento/_form.php views/pagamento/create.php
git commit -m "feat: smart payment form with AJAX contract info and hours alert"
```

---

### Task 4: Vista Modulo - Alert ore e pulsante "Paga ore rendicontate"

**Files:**
- Modify: `views/modulo/view.php:237-356` (sezione Pagamenti)

**Step 1: Potenziare la sezione Pagamenti**

Sostituire la sezione Pagamenti (righe 237-357) con versione che include:
- Alert ore non rendicontate con pulsante "Paga solo ore rendicontate come acconto"
- Colonna Azioni nella tabella pagamenti
- Link diretto a pagamento view/edit

Nel blocco `<?php if ($contrattoAttivo): ?>` (riga 253), dopo le stat cards e prima della tabella pagamenti, aggiungere l'alert ore:

```php
<!-- Alert Ore Rendicontate -->
<?php
    $assOre = $model->getAssegnazioni()->one();
    $oreContrattoVal = $contrattoAttivo->ore_contrattualizzate;
    $oreApprVal = $assOre ? \app\models\RendicontazioneOre::getTotaleOreApprovate($assOre->id) : 0;
    $percOreVal = ($oreContrattoVal > 0) ? round(($oreApprVal / $oreContrattoVal) * 100) : 0;
    $importoOreRendicontate = $oreApprVal * $contrattoAttivo->compenso_orario;
?>
<?php if ($oreContrattoVal > 0 && $oreApprVal < $oreContrattoVal): ?>
<div class="alert alert-warning mb-3">
    <div class="d-flex align-items-center">
        <i class="ti ti-alert-triangle me-2" style="font-size: 1.5rem;"></i>
        <div class="flex-fill">
            <strong>Ore non completamente rendicontate:</strong>
            <?= number_format($oreApprVal, 1, ',', '.') ?>/<?= $oreContrattoVal ?> ore (<?= $percOreVal ?>%)
        </div>
    </div>
    <div class="mt-2">
        <?= Html::a(
            '<i class="ti ti-clock-dollar me-1"></i> Paga solo ore rendicontate come acconto',
            ['/pagamento/create',
                'contratto_id' => $contrattoAttivo->id,
                'tipo' => 'acconto',
                'importo' => $importoOreRendicontate,
            ],
            ['class' => 'btn btn-sm btn-green']
        ) ?>
    </div>
</div>
<?php elseif ($oreContrattoVal > 0): ?>
<div class="alert alert-success mb-3">
    <i class="ti ti-circle-check me-2"></i>
    <strong>Ore completamente rendicontate:</strong>
    <?= number_format($oreApprVal, 1, ',', '.') ?>/<?= $oreContrattoVal ?> ore
</div>
<?php endif; ?>
```

Nella tabella pagamenti, aggiungere header `<th>Azioni</th>` e colonna con link:

```php
<td class="text-end">
    <?= Html::a('<i class="ti ti-eye"></i>', ['/pagamento/view', 'id' => $pagamento->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Dettaglio']) ?>
    <?= Html::a('<i class="ti ti-edit"></i>', ['/pagamento/update', 'id' => $pagamento->id], ['class' => 'btn btn-sm btn-outline-blue', 'title' => 'Modifica']) ?>
</td>
```

**Step 2: Commit**

```bash
git add views/modulo/view.php
git commit -m "feat: modulo view payment section with hours alert and quick pay button"
```

---

### Task 5: Endpoint liquidazione massiva `actionLiquidaMassiva`

**Files:**
- Modify: `controllers/PagamentoController.php`

**Step 1: Aggiungere `actionLiquidaMassiva` al controller**

Aggiungere al VerbFilter nelle behaviors (riga 38):
```php
'liquida-massiva' => ['POST'],
```

Aggiungere l'action:

```php
/**
 * Liquidazione massiva: crea pagamenti per piu' contratti.
 * POST JSON: { contratti_ids: [1,2,3], tipo: "saldo", status: "programmato" }
 * @return array
 */
public function actionLiquidaMassiva()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $request = Yii::$app->request;
    $contrattiIds = $request->post('contratti_ids', []);
    $tipo = $request->post('tipo');
    $status = $request->post('status', Pagamento::STATUS_PROGRAMMATO);

    if (empty($contrattiIds) || empty($tipo)) {
        return ['success' => false, 'message' => 'Parametri mancanti: contratti_ids e tipo sono obbligatori.'];
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
        $count = 0;
        $totale = 0;
        $errori = [];

        foreach ($contrattiIds as $contrattoId) {
            $contratto = Contratto::findOne($contrattoId);
            if (!$contratto) {
                $errori[] = "Contratto #{$contrattoId} non trovato.";
                continue;
            }

            $saldo = $contratto->getSaldoRimanente();
            if ($saldo <= 0.01) {
                $errori[] = "Contratto #{$contrattoId}: saldo zero o negativo.";
                continue;
            }

            $pagamento = new Pagamento();
            $pagamento->contratto_id = $contrattoId;
            $pagamento->tipo = $tipo;
            $pagamento->status = $status;
            $pagamento->importo_lordo = $saldo;

            // Calcola ritenute proporzionali
            if ($contratto->ritenute && $contratto->compenso_totale > 0) {
                $percRitenute = $contratto->ritenute / $contratto->compenso_totale;
                $pagamento->ritenute = round($saldo * $percRitenute, 2);
                $pagamento->importo_netto = round($saldo - $pagamento->ritenute, 2);
            } else {
                $pagamento->importo_netto = $saldo;
            }

            if ($pagamento->save()) {
                $count++;
                $totale += $pagamento->importo_netto;
            } else {
                $errori[] = "Contratto #{$contrattoId}: " . implode(', ', $pagamento->getFirstErrors());
            }
        }

        $transaction->commit();

        return [
            'success' => true,
            'count' => $count,
            'totale' => round($totale, 2),
            'errori' => $errori,
        ];
    } catch (\Throwable $e) {
        $transaction->rollBack();
        return ['success' => false, 'message' => 'Errore: ' . $e->getMessage()];
    }
}
```

**Step 2: Commit**

```bash
git add controllers/PagamentoController.php
git commit -m "feat: bulk payment liquidation endpoint"
```

---

### Task 6: Vista Avviso - Ore rendicontate e liquidazione massiva

**Files:**
- Modify: `controllers/AvvisoController.php:58-66` (aggiungere ore al rendiconto)
- Modify: `views/avviso/view.php:270-446` (tabella rendicontazione)

**Step 1: Estendere `getRendicontoModuli` in `Avviso.php` con dati ore**

In `models/Avviso.php`, metodo `getRendicontoModuli()` (riga 243-266), aggiungere dati ore per ogni modulo. Modificare il blocco `$result[]`:

```php
// Ore rendicontate
$oreContrattualizzate = $contratto ? (int) $contratto->ore_contrattualizzate : 0;
$oreApprovate = $assegnazione ? RendicontazioneOre::getTotaleOreApprovate($assegnazione->id) : 0;

$result[] = [
    'modulo' => $modulo,
    'docente' => $docente,
    'assegnazione' => $assegnazione,
    'contratto' => $contratto,
    'contrattualizzato' => $contrattualizzato,
    'pagato' => $pagato,
    'percentuale' => $modulo->getPercentualePagamento(),
    'ore_contrattualizzate' => $oreContrattualizzate,
    'ore_approvate' => $oreApprovate,
    'saldo_rimanente' => $contratto ? $contratto->getSaldoRimanente() : 0,
];
```

Aggiungere import in testa ad Avviso.php se non presente:
```php
use app\models\RendicontazioneOre;
```

**Step 2: Potenziare la tabella rendicontazione in `views/avviso/view.php`**

Nella tabella moduli (righe 360-442), aggiungere:
- Colonna **Ore Rend.** con mini progress bar
- Colonna **Checkbox** per selezione massiva
- Toolbar liquidazione massiva sopra la tabella
- Azione rapida pagamento per ogni riga

Nell'header della tabella aggiungere:
```php
<th><input type="checkbox" id="select-all-moduli" title="Seleziona tutti"></th>
```
e colonna:
```php
<th>Ore Rend.</th>
```

Per ogni riga modulo con contratto e saldo > 0:
```php
<td>
    <?php if ($rm['contratto'] && $rm['saldo_rimanente'] > 0.01): ?>
        <input type="checkbox" class="modulo-checkbox" value="<?= $rm['contratto']->id ?>"
               data-saldo="<?= $rm['saldo_rimanente'] ?>"
               data-docente="<?= Html::encode($rm['docente'] ? $rm['docente']->cognome . ' ' . $rm['docente']->nome : '-') ?>"
               data-modulo="<?= Html::encode($m->codice) ?>">
    <?php endif; ?>
</td>
```

Colonna ore:
```php
<td>
    <?php if ($rm['ore_contrattualizzate'] > 0): ?>
        <?php $percOreM = round(($rm['ore_approvate'] / $rm['ore_contrattualizzate']) * 100); ?>
        <div class="d-flex align-items-center gap-1">
            <div class="progress flex-grow-1" style="height: 4px; width: 50px;">
                <div class="progress-bar <?= $percOreM >= 100 ? 'bg-green' : ($percOreM > 0 ? 'bg-orange' : '') ?>"
                     style="width: <?= min($percOreM, 100) ?>%"></div>
            </div>
            <small class="text-nowrap"><?= $rm['ore_approvate'] ?>/<?= $rm['ore_contrattualizzate'] ?></small>
        </div>
    <?php else: ?>
        <span class="text-muted">-</span>
    <?php endif; ?>
</td>
```

**Step 3: Aggiungere toolbar e modale liquidazione massiva**

Sopra la tabella rendicontazione, dentro il card-body:

```php
<!-- Toolbar Liquidazione Massiva -->
<div id="bulk-toolbar" class="alert alert-blue mb-3" style="display: none;">
    <div class="d-flex align-items-center justify-content-between">
        <span>
            <strong id="bulk-count">0</strong> moduli selezionati -
            Totale: <strong id="bulk-totale">€ 0,00</strong>
        </span>
        <button type="button" class="btn btn-green btn-sm" data-bs-toggle="modal" data-bs-target="#modal-liquidazione">
            <i class="ti ti-cash me-1"></i> Liquida selezionati
        </button>
    </div>
</div>
```

Dopo la card di rendicontazione, aggiungere la modale:

```php
<!-- Modale Conferma Liquidazione Massiva -->
<div class="modal modal-blur fade" id="modal-liquidazione" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-cash me-2"></i>Conferma Liquidazione Massiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-lista-docenti"></div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label required">Tipo Pagamento</label>
                        <select id="bulk-tipo" class="form-select" required>
                            <option value="">-- Seleziona --</option>
                            <option value="acconto">Acconto</option>
                            <option value="saldo">Saldo</option>
                            <option value="rata">Rata</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stato</label>
                        <select id="bulk-status" class="form-select">
                            <option value="programmato">Programmato</option>
                            <option value="in_attesa">In Attesa</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" id="btn-conferma-liquidazione" class="btn btn-green">
                    <i class="ti ti-check me-1"></i> Conferma Liquidazione
                </button>
            </div>
        </div>
    </div>
</div>
```

**Step 4: JavaScript per checkbox e liquidazione massiva**

```javascript
// Checkbox select all
$('#select-all-moduli').on('change', function() {
    $('.modulo-checkbox').prop('checked', $(this).is(':checked'));
    updateBulkToolbar();
});

$('.modulo-checkbox').on('change', updateBulkToolbar);

function updateBulkToolbar() {
    var checked = $('.modulo-checkbox:checked');
    if (checked.length > 0) {
        var totale = 0;
        checked.each(function() { totale += parseFloat($(this).data('saldo')); });
        $('#bulk-count').text(checked.length);
        $('#bulk-totale').text(new Intl.NumberFormat('it-IT', {style:'currency',currency:'EUR'}).format(totale));
        $('#bulk-toolbar').slideDown(200);
    } else {
        $('#bulk-toolbar').slideUp(200);
    }
}

// Popola modale
$('#modal-liquidazione').on('show.bs.modal', function() {
    var html = '<table class="table table-sm"><thead><tr><th>Docente</th><th>Modulo</th><th class="text-end">Importo</th></tr></thead><tbody>';
    $('.modulo-checkbox:checked').each(function() {
        html += '<tr><td>' + $(this).data('docente') + '</td><td>' + $(this).data('modulo') + '</td><td class="text-end">' +
            new Intl.NumberFormat('it-IT',{style:'currency',currency:'EUR'}).format($(this).data('saldo')) + '</td></tr>';
    });
    html += '</tbody></table>';
    $('#modal-lista-docenti').html(html);
});

// Conferma liquidazione
$('#btn-conferma-liquidazione').on('click', function() {
    var tipo = $('#bulk-tipo').val();
    if (!tipo) { alert('Seleziona il tipo di pagamento.'); return; }

    var ids = [];
    $('.modulo-checkbox:checked').each(function() { ids.push(parseInt($(this).val())); });

    var btn = $(this);
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Elaborazione...');

    $.post('LIQUIDA_MASSIVA_URL', {
        contratti_ids: ids,
        tipo: tipo,
        status: $('#bulk-status').val(),
        _csrf: yii.getCsrfToken()
    }, function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore: ' + (data.message || 'Errore sconosciuto'));
            btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i> Conferma Liquidazione');
        }
    }, 'json');
});
```

(L'URL `LIQUIDA_MASSIVA_URL` sara' generato con `Url::to(['/pagamento/liquida-massiva'])` nel PHP)

**Step 5: Commit**

```bash
git add models/Avviso.php views/avviso/view.php controllers/AvvisoController.php
git commit -m "feat: avviso view with hours tracking and bulk payment liquidation"
```

---

### Task 7: Fix `bg-secondary` in tutti i model

**Files:**
- Modify: `models/Pagamento.php:143,147,162,214`
- Modify: `models/Contratto.php:152,158,209`
- Modify: `models/Avviso.php:118,121`
- Modify: `models/Modulo.php:152`
- Modify: `models/Assegnazione.php:116`
- Modify: `models/Documentazione.php` (default)
- Modify: `models/StoricoEsito.php` (default)
- Modify: `models/Docente.php` (default)
- Modify: `models/DocenteAuth.php` (default)
- Modify: `models/PresenzaAllievo.php` (default)
- Modify: `models/Istanza.php` (default)
- Modify: `models/Iscrizione.php` (default)
- Modify: `models/RendicontazioneOre.php:142`
- Modify: `models/Calendario.php:118`
- Modify: `models/Progetto.php:119,122`

**Step 1: Replace `bg-secondary` con `bg-muted` in tutti i model**

In ogni file, sostituire tutte le occorrenze:
- `'bg-secondary'` -> `'bg-muted'`

Per i badge con testo (come in Contratto.php riga 209 e Docente.php):
- `'badge bg-secondary'` -> `'badge bg-muted'`

Anche in `views/docente/index.php:170`:
- `'badge bg-secondary'` -> `'badge bg-muted'`

**Step 2: Commit**

```bash
git add models/*.php views/docente/index.php
git commit -m "fix: replace bg-secondary with bg-muted across all models"
```

---

### Task 8: Fix link menu Rendicontazione Ore (href="#")

**Files:**
- Modify: `views/layouts/menu.php:121`

**Step 1: Collegare il menu Rendicontazione Ore**

Creare una nuova action index senza parametro obbligatorio in RendicontazioneOreController (o reindirizzare a una lista globale). La soluzione piu' semplice: puntare il menu a un riepilogo globale.

In `views/layouts/menu.php:121` sostituire `href="#"` con:
```php
href="<?= Url::to(['/rendicontazione-ore/dashboard']) ?>"
```

Aggiungere `actionDashboard` a `RendicontazioneOreController` che mostra un riepilogo globale di tutte le assegnazioni con ore da approvare.

**Step 2: Commit**

```bash
git add views/layouts/menu.php controllers/RendicontazioneOreController.php views/rendicontazione-ore/dashboard.php
git commit -m "feat: rendicontazione ore admin dashboard with global overview"
```

---

### Ordine di esecuzione

1. **Task 1**: Endpoint `actionContrattoInfo` (base per tutto il resto)
2. **Task 2**: Estensione `actionCreate` con parametri URL
3. **Task 3**: Form pagamento smart con AJAX
4. **Task 4**: Vista modulo con alert ore e pulsante
5. **Task 5**: Endpoint liquidazione massiva
6. **Task 6**: Vista avviso con ore e checkbox
7. **Task 7**: Fix `bg-secondary` (indipendente)
8. **Task 8**: Fix menu rendicontazione ore (indipendente)

Task 7 e 8 sono indipendenti e possono essere eseguiti in parallelo con qualsiasi altro task.
