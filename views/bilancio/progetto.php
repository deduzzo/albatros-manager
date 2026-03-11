<?php

use yii\helpers\Html;
use yii\helpers\Json;
use app\components\assets\ApexChartsAsset;

/* @var $this yii\web\View */
/* @var $progetto app\models\Progetto */
/* @var $budget float */
/* @var $totaleSpese float */
/* @var $totaleEntrate float */
/* @var $trancheAccreditate float */
/* @var $tranchePreviste float */
/* @var $movimentiPerCategoria array */
/* @var $tranche app\models\TrancheFinanziamento[] */

ApexChartsAsset::register($this);

$this->title = 'Bilancio: ' . $progetto->codice_progetto;
$this->params['breadcrumbs'][] = ['label' => 'Bilancio', 'url' => ['index']];
$this->params['breadcrumbs'][] = $progetto->codice_progetto;

$disponibilita = $trancheAccreditate - $totaleSpese;
$percSpeso = $budget > 0 ? round(($totaleSpese / $budget) * 100, 1) : 0;
$percSpesoCapped = min($percSpeso, 100);

// Dati per grafico a ciambella categorie
$chartCatLabels = [];
$chartCatValues = [];
foreach ($movimentiPerCategoria as $catLabel => $movimenti) {
    $subtotale = 0;
    foreach ($movimenti as $mov) {
        $subtotale += (float) $mov->importo;
    }
    $chartCatLabels[] = $catLabel;
    $chartCatValues[] = round($subtotale, 2);
}
$chartCatLabelsJson = Json::encode($chartCatLabels);
$chartCatValuesJson = Json::encode($chartCatValues);
?>

<div class="bilancio-progetto">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-report-analytics me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($progetto->titolo) ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-table me-1"></i> Quadro Economico', ['quadro-economico', 'id' => $progetto->id], ['class' => 'btn btn-primary me-2']) ?>
                    <?= Html::a('<i class="ti ti-download me-1"></i> Export Excel', ['export', 'id' => $progetto->id], ['class' => 'btn btn-warning']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Stat Cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-blue-lt me-3"><i class="ti ti-wallet"></i></span>
                                <div>
                                    <div class="subheader">Budget Totale</div>
                                    <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($budget, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3"><i class="ti ti-coins"></i></span>
                                <div>
                                    <div class="subheader">Tranche Ricevute</div>
                                    <div class="h2 mb-0 text-green"><?= Yii::$app->formatter->asCurrency($trancheAccreditate, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-red-lt me-3"><i class="ti ti-arrow-up-circle"></i></span>
                                <div>
                                    <div class="subheader">Spese Totali</div>
                                    <div class="h2 mb-0 text-red"><?= Yii::$app->formatter->asCurrency($totaleSpese, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-<?= $disponibilita >= 0 ? 'azure' : 'orange' ?>-lt me-3"><i class="ti ti-scale"></i></span>
                                <div>
                                    <div class="subheader">Disponibilita' Residua</div>
                                    <div class="h2 mb-0 text-<?= $disponibilita >= 0 ? 'azure' : 'orange' ?>"><?= Yii::$app->formatter->asCurrency($disponibilita, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Avanzamento Spesa</span>
                        <span class="text-muted"><?= $percSpeso ?>% del budget</span>
                    </div>
                    <?php
                        $barColor = $percSpeso > 90 ? 'bg-red' : ($percSpeso > 70 ? 'bg-orange' : 'bg-blue');
                    ?>
                    <div class="progress progress-lg">
                        <div class="progress-bar <?= $barColor ?>" style="width: <?= $percSpesoCapped ?>%">
                            <?= $percSpeso ?>%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 text-muted small">
                        <span>Speso: <?= Yii::$app->formatter->asCurrency($totaleSpese, 'EUR') ?></span>
                        <span>Budget: <?= Yii::$app->formatter->asCurrency($budget, 'EUR') ?></span>
                    </div>
                </div>
            </div>

            <div class="row row-deck row-cards mb-3">
                <!-- Movimenti per Categoria -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-list me-2"></i>Spese per Categoria</h3>
                        </div>
                        <?php if (empty($movimentiPerCategoria)): ?>
                            <div class="card-body">
                                <div class="text-center text-muted py-4">
                                    <i class="ti ti-info-circle me-1"></i> Nessuna spesa registrata per questo progetto.
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card-body p-0">
                                <div class="accordion" id="accordion-categorie">
                                    <?php $i = 0; foreach ($movimentiPerCategoria as $catLabel => $movimenti): ?>
                                        <?php
                                            $subtotale = 0;
                                            foreach ($movimenti as $mov) {
                                                $subtotale += (float) $mov->importo;
                                            }
                                        ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#cat-<?= $i ?>">
                                                    <span class="badge bg-blue-lt me-2"><?= Html::encode($catLabel) ?></span>
                                                    <span class="ms-auto me-3 text-red fw-bold"><?= Yii::$app->formatter->asCurrency($subtotale, 'EUR') ?></span>
                                                    <span class="badge bg-default"><?= count($movimenti) ?> movimenti</span>
                                                </button>
                                            </h2>
                                            <div id="cat-<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#accordion-categorie">
                                                <div class="accordion-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-vcenter table-sm mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Data</th>
                                                                    <th>Descrizione</th>
                                                                    <th>Fornitore</th>
                                                                    <th class="text-end">Importo</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($movimenti as $mov): ?>
                                                                    <tr>
                                                                        <td class="text-muted"><?= Yii::$app->formatter->asDate($mov->data_movimento) ?></td>
                                                                        <td>
                                                                            <?= Html::a(Html::encode(mb_substr($mov->descrizione, 0, 50)), ['/movimento-spesa/view', 'id' => $mov->id], ['class' => 'text-reset']) ?>
                                                                        </td>
                                                                        <td class="text-muted"><?= Html::encode($mov->fornitore) ?: '-' ?></td>
                                                                        <td class="text-end text-red fw-bold"><?= Yii::$app->formatter->asCurrency($mov->importo, 'EUR') ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="fw-bold" style="background: #fdf2f2;">
                                                                    <td colspan="3" class="text-end">Subtotale:</td>
                                                                    <td class="text-end text-red"><?= Yii::$app->formatter->asCurrency($subtotale, 'EUR') ?></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php $i++; endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Grafico + Tranche -->
                <div class="col-lg-4">
                    <!-- Grafico categorie -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-chart-pie me-2"></i>Per Categoria</h3>
                        </div>
                        <div class="card-body">
                            <div id="chart-cat" style="height: 250px;"></div>
                        </div>
                    </div>

                    <!-- Tranche -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-coins me-2"></i>Tranche</h3>
                            <div class="card-actions">
                                <?= Html::a('<i class="ti ti-plus"></i>', ['/tranche-finanziamento/create', 'progetto_id' => $progetto->id], [
                                    'class' => 'btn btn-sm btn-success',
                                    'title' => 'Nuova Tranche',
                                    'data-bs-toggle' => 'tooltip',
                                ]) ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($tranche)): ?>
                                <div class="p-3 text-center text-muted">
                                    Nessuna tranche registrata.
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($tranche as $t): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold"><?= Html::encode($t->getLabel()) ?></div>
                                                    <div class="text-muted small">
                                                        <?php if ($t->data_accredito): ?>
                                                            <i class="ti ti-calendar-check me-1"></i><?= Yii::$app->formatter->asDate($t->data_accredito) ?>
                                                        <?php elseif ($t->data_prevista): ?>
                                                            <i class="ti ti-calendar me-1"></i><?= Yii::$app->formatter->asDate($t->data_prevista) ?> (prevista)
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold"><?= Yii::$app->formatter->asCurrency($t->importo, 'EUR') ?></div>
                                                    <?= $t->getStatusBadge() ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="card-footer text-end">
                                    <span class="text-muted">Totale previsto:</span>
                                    <strong><?= Yii::$app->formatter->asCurrency($tranchePreviste, 'EUR') ?></strong>
                                    <span class="mx-2">|</span>
                                    <span class="text-muted">Accreditato:</span>
                                    <strong class="text-green"><?= Yii::$app->formatter->asCurrency($trancheAccreditate, 'EUR') ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="card">
                <div class="card-footer">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna al Bilancio', ['index'], ['class' => 'btn btn-secondary']) ?>
                    <?= Html::a('<i class="ti ti-folder me-1"></i> Progetto', ['/progetto/view', 'id' => $progetto->id], ['class' => 'btn btn-info ms-2']) ?>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Movimento', ['/movimento-spesa/create'], ['class' => 'btn btn-success ms-2']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
var catLabels = {$chartCatLabelsJson};
var catValues = {$chartCatValuesJson};
if (catValues.length > 0) {
    var catOptions = {
        chart: { type: 'donut', height: 250 },
        series: catValues,
        labels: catLabels,
        colors: ['#206bc4', '#2fb344', '#f59f00', '#d63939', '#ae3ec9', '#0ca678', '#4263eb', '#f76707'],
        legend: { position: 'bottom', fontSize: '11px' },
        tooltip: {
            y: { formatter: function(val) { return '\u20AC ' + val.toLocaleString('it-IT', {minimumFractionDigits: 2}); } }
        },
        plotOptions: { pie: { donut: { size: '55%' } } }
    };
    new ApexCharts(document.querySelector('#chart-cat'), catOptions).render();
} else {
    document.querySelector('#chart-cat').innerHTML = '<div class="text-center text-muted py-4"><i class="ti ti-chart-pie-off d-block mb-2" style="font-size:2rem;"></i>Nessuna spesa</div>';
}
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
