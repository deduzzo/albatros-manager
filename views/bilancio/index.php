<?php

use yii\helpers\Html;
use yii\helpers\Json;
use app\components\assets\ApexChartsAsset;

/* @var $this yii\web\View */
/* @var $riepilogo array */
/* @var $totaleEntrate float */
/* @var $totaleUscite float */
/* @var $totaleBudget float */

ApexChartsAsset::register($this);

$this->title = 'Bilancio';
$this->params['breadcrumbs'][] = $this->title;

$saldo = $totaleEntrate - $totaleUscite;
$budgetResiduo = $totaleBudget - $totaleUscite;

// Preparare dati per i grafici come stringhe JSON
$chartLabels = [];
$chartEntrate = [];
$chartUscite = [];
$chartTorta = [];
foreach ($riepilogo as $r) {
    $chartLabels[] = $r['progetto']->codice_progetto;
    $chartEntrate[] = round($r['entrate'], 2);
    $chartUscite[] = round($r['uscite'], 2);
    if ($r['uscite'] > 0) {
        $chartTorta[] = ['label' => $r['progetto']->codice_progetto, 'value' => round($r['uscite'], 2)];
    }
}

$chartLabelsJson = Json::encode($chartLabels);
$chartEntrateJson = Json::encode($chartEntrate);
$chartUsciteJson = Json::encode($chartUscite);
$chartTortaLabelsJson = Json::encode(array_column($chartTorta, 'label'));
$chartTortaValuesJson = Json::encode(array_column($chartTorta, 'value'));
?>

<div class="bilancio-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-report-analytics me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Panoramica finanziaria di tutti i progetti
                    </div>
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
                                    <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($totaleBudget, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3"><i class="ti ti-arrow-down-circle"></i></span>
                                <div>
                                    <div class="subheader">Totale Entrate</div>
                                    <div class="h2 mb-0 text-green"><?= Yii::$app->formatter->asCurrency($totaleEntrate, 'EUR') ?></div>
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
                                    <div class="subheader">Totale Uscite</div>
                                    <div class="h2 mb-0 text-red"><?= Yii::$app->formatter->asCurrency($totaleUscite, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-<?= $budgetResiduo >= 0 ? 'azure' : 'orange' ?>-lt me-3"><i class="ti ti-scale"></i></span>
                                <div>
                                    <div class="subheader">Budget Residuo</div>
                                    <div class="h2 mb-0 text-<?= $budgetResiduo >= 0 ? 'azure' : 'orange' ?>"><?= Yii::$app->formatter->asCurrency($budgetResiduo, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafici -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-chart-bar me-2"></i>Entrate vs Uscite per Progetto</h3>
                        </div>
                        <div class="card-body">
                            <div id="chart-bar" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-chart-pie me-2"></i>Ripartizione Uscite</h3>
                        </div>
                        <div class="card-body">
                            <div id="chart-pie" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabella Riepilogo -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-table me-2"></i>Riepilogo per Progetto</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr>
                                <th>Progetto</th>
                                <th class="text-end">Budget</th>
                                <th class="text-end">Entrate</th>
                                <th class="text-end">Uscite</th>
                                <th class="text-end">Saldo</th>
                                <th style="width: 200px;">% Speso</th>
                                <th style="width: 100px;" class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($riepilogo)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="ti ti-info-circle me-1"></i> Nessun progetto trovato
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($riepilogo as $r): ?>
                                <?php $p = $r['progetto']; ?>
                                <tr>
                                    <td>
                                        <div>
                                            <?= Html::a(Html::encode($p->codice_progetto), ['/progetto/view', 'id' => $p->id], ['class' => 'fw-bold text-reset']) ?>
                                        </div>
                                        <div class="text-muted small"><?= Html::encode(mb_substr($p->titolo, 0, 60)) ?></div>
                                    </td>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($r['budget'], 'EUR') ?></td>
                                    <td class="text-end text-green"><?= Yii::$app->formatter->asCurrency($r['entrate'], 'EUR') ?></td>
                                    <td class="text-end text-red"><?= Yii::$app->formatter->asCurrency($r['uscite'], 'EUR') ?></td>
                                    <td class="text-end <?= $r['saldo'] >= 0 ? 'text-blue' : 'text-orange' ?> fw-bold">
                                        <?= Yii::$app->formatter->asCurrency($r['saldo'], 'EUR') ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-fill">
                                                <div class="progress progress-sm">
                                                    <?php
                                                        $perc = min($r['percentuale_speso'], 100);
                                                        $barColor = $perc > 90 ? 'bg-red' : ($perc > 70 ? 'bg-orange' : 'bg-blue');
                                                    ?>
                                                    <div class="progress-bar <?= $barColor ?>" style="width: <?= $perc ?>%"></div>
                                                </div>
                                            </div>
                                            <span class="ms-2 small text-muted"><?= $r['percentuale_speso'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <?= Html::a('<i class="ti ti-eye"></i>', ['progetto', 'id' => $p->id], [
                                            'class' => 'btn btn-sm btn-info',
                                            'title' => 'Dettaglio',
                                            'data-bs-toggle' => 'tooltip',
                                        ]) ?>
                                        <?= Html::a('<i class="ti ti-table"></i>', ['quadro-economico', 'id' => $p->id], [
                                            'class' => 'btn btn-sm btn-primary',
                                            'title' => 'Quadro Economico',
                                            'data-bs-toggle' => 'tooltip',
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold" style="background: #f8f9fa;">
                                <td>TOTALI</td>
                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($totaleBudget, 'EUR') ?></td>
                                <td class="text-end text-green"><?= Yii::$app->formatter->asCurrency($totaleEntrate, 'EUR') ?></td>
                                <td class="text-end text-red"><?= Yii::$app->formatter->asCurrency($totaleUscite, 'EUR') ?></td>
                                <td class="text-end text-blue"><?= Yii::$app->formatter->asCurrency($saldo, 'EUR') ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
// Grafico a barre - Entrate vs Uscite
var barOptions = {
    chart: { type: 'bar', height: 300, toolbar: { show: false } },
    series: [
        { name: 'Entrate', data: {$chartEntrateJson} },
        { name: 'Uscite', data: {$chartUsciteJson} }
    ],
    xaxis: { categories: {$chartLabelsJson} },
    colors: ['#2fb344', '#d63939'],
    plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
    dataLabels: { enabled: false },
    tooltip: {
        y: { formatter: function(val) { return '\u20AC ' + val.toLocaleString('it-IT', {minimumFractionDigits: 2}); } }
    },
    legend: { position: 'top' }
};
var chartBar = new ApexCharts(document.querySelector('#chart-bar'), barOptions);
chartBar.render();

// Grafico a torta - Ripartizione uscite
var pieLabels = {$chartTortaLabelsJson};
var pieValues = {$chartTortaValuesJson};
if (pieValues.length > 0) {
    var pieOptions = {
        chart: { type: 'donut', height: 300 },
        series: pieValues,
        labels: pieLabels,
        colors: ['#206bc4', '#2fb344', '#f59f00', '#d63939', '#ae3ec9', '#0ca678', '#4263eb', '#f76707'],
        legend: { position: 'bottom', fontSize: '12px' },
        tooltip: {
            y: { formatter: function(val) { return '\u20AC ' + val.toLocaleString('it-IT', {minimumFractionDigits: 2}); } }
        },
        plotOptions: { pie: { donut: { size: '55%' } } }
    };
    var chartPie = new ApexCharts(document.querySelector('#chart-pie'), pieOptions);
    chartPie.render();
} else {
    document.querySelector('#chart-pie').innerHTML = '<div class="text-center text-muted py-5"><i class="ti ti-chart-pie-off d-block mb-2" style="font-size:2rem;"></i>Nessuna spesa registrata</div>';
}
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
