<?php

use yii\helpers\Html;
use yii\helpers\Json;
use app\components\assets\ApexChartsAsset;

/* @var $this yii\web\View */
/* @var $progettiPerFonte array */
/* @var $budgetPerProgetto array */
/* @var $pagamentiMensili array */
/* @var $docentiAttivi int */
/* @var $oreErogate float */
/* @var $totaleContrattualizzato float */
/* @var $percCompletamento float */

$this->title = 'Statistiche';
$this->params['breadcrumbs'][] = ['label' => 'Report', 'url' => ['export']];
$this->params['breadcrumbs'][] = 'Statistiche';

ApexChartsAsset::register($this);

// Preparare dati JSON PRIMA del PHP/HTML (gli array PHP non possono essere interpolati in heredoc)
$fonteLabels = Json::encode(array_column($progettiPerFonte, 'fonte'));
$fonteSeries = Json::encode(array_map('intval', array_column($progettiPerFonte, 'totale')));

$budgetLabels = Json::encode(array_column($budgetPerProgetto, 'codice_progetto'));
$budgetContrattualizzato = Json::encode(array_map('floatval', array_column($budgetPerProgetto, 'contrattualizzato')));
$budgetPagato = Json::encode(array_map('floatval', array_column($budgetPerProgetto, 'pagato')));

// Pagamenti mensili: costruire serie con labels mese/anno
$mesiLabels = [];
$mesiTotali = [];
$mesiNomi = ['', 'Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
foreach ($pagamentiMensili as $pm) {
    $mesiLabels[] = $mesiNomi[(int)$pm['mese']] . ' ' . $pm['anno'];
    $mesiTotali[] = (float)$pm['totale'];
}
$mesiLabelsJson = Json::encode($mesiLabels);
$mesiTotaliJson = Json::encode($mesiTotali);
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto">
                <h2 class="page-title">
                    <i class="ti ti-chart-pie me-2"></i><?= Html::encode($this->title) ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <!-- KPI Cards -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-primary-lt me-3"><i class="ti ti-users"></i></span>
                            <div>
                                <div class="text-muted small">Docenti Attivi</div>
                                <div class="h2 mb-0"><?= number_format($docentiAttivi, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-success-lt me-3"><i class="ti ti-clock"></i></span>
                            <div>
                                <div class="text-muted small">Ore Erogate</div>
                                <div class="h2 mb-0"><?= number_format($oreErogate, 1, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-warning-lt me-3"><i class="ti ti-currency-euro"></i></span>
                            <div>
                                <div class="text-muted small">Contrattualizzato</div>
                                <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($totaleContrattualizzato) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-info-lt me-3"><i class="ti ti-chart-pie"></i></span>
                            <div>
                                <div class="text-muted small">% Completamento</div>
                                <div class="h2 mb-0"><?= number_format($percCompletamento, 1, ',', '.') ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafici riga 1: Torta + Barre -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-chart-donut me-2"></i>Progetti per Fonte</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-fonti" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-chart-bar me-2"></i>Budget per Progetto (Top 10)</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-budget" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafico linea: Andamento pagamenti -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-trending-up me-2"></i>Andamento Pagamenti Mensili</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-pagamenti" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$js = <<<JS
// Grafico Donut - Progetti per Fonte
var fonteLabels = {$fonteLabels};
var fonteSeries = {$fonteSeries};
if (fonteLabels.length > 0) {
    new ApexCharts(document.querySelector('#chart-fonti'), {
        chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
        series: fonteSeries,
        labels: fonteLabels,
        colors: ['#206bc4', '#2fb344', '#f76707', '#d63939', '#4263eb', '#ae3ec9', '#0ca678'],
        legend: { position: 'bottom' },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%',
                    labels: {
                        show: true,
                        total: { show: true, label: 'Totale Progetti' }
                    }
                }
            }
        },
        dataLabels: { enabled: true, formatter: function(val, opts) { return opts.w.config.series[opts.seriesIndex]; } }
    }).render();
} else {
    document.querySelector('#chart-fonti').innerHTML = '<div class="text-center text-muted py-5">Nessun dato disponibile</div>';
}

// Grafico Barre Orizzontali - Budget per Progetto
var budgetLabels = {$budgetLabels};
var budgetContrattualizzato = {$budgetContrattualizzato};
var budgetPagato = {$budgetPagato};
if (budgetLabels.length > 0) {
    new ApexCharts(document.querySelector('#chart-budget'), {
        chart: { type: 'bar', height: 320, fontFamily: 'inherit', toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, barHeight: '60%' } },
        series: [
            { name: 'Contrattualizzato', data: budgetContrattualizzato },
            { name: 'Pagato', data: budgetPagato }
        ],
        colors: ['#4263eb', '#2fb344'],
        xaxis: {
            categories: budgetLabels,
            labels: { formatter: function(val) { return val >= 1000 ? (val/1000).toFixed(0) + 'k' : val; } }
        },
        yaxis: { labels: { style: { fontSize: '11px' } } },
        tooltip: { y: { formatter: function(val) { return new Intl.NumberFormat('it-IT', {style:'currency', currency:'EUR'}).format(val); } } },
        legend: { position: 'top' },
        dataLabels: { enabled: false }
    }).render();
} else {
    document.querySelector('#chart-budget').innerHTML = '<div class="text-center text-muted py-5">Nessun dato disponibile</div>';
}

// Grafico Linea - Pagamenti Mensili
var mesiLabels = {$mesiLabelsJson};
var mesiTotali = {$mesiTotaliJson};
if (mesiLabels.length > 0) {
    new ApexCharts(document.querySelector('#chart-pagamenti'), {
        chart: { type: 'area', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
        series: [{ name: 'Importo Pagato', data: mesiTotali }],
        colors: ['#206bc4'],
        xaxis: { categories: mesiLabels },
        yaxis: {
            labels: { formatter: function(val) { return new Intl.NumberFormat('it-IT', {style:'currency', currency:'EUR'}).format(val); } }
        },
        tooltip: { y: { formatter: function(val) { return new Intl.NumberFormat('it-IT', {style:'currency', currency:'EUR'}).format(val); } } },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.1 } },
        dataLabels: { enabled: false }
    }).render();
} else {
    document.querySelector('#chart-pagamenti').innerHTML = '<div class="text-center text-muted py-5">Nessun pagamento negli ultimi 12 mesi</div>';
}
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
