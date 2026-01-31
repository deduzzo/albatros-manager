<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\assets\ApexChartsAsset;

/* @var $this yii\web\View */

$this->title = 'Dashboard';

// Registra ApexCharts per i grafici
ApexChartsAsset::register($this);

// Contatori reali
$stats = [
    'fonti' => \app\models\FonteFinanziamento::find()->count(),
    'sedi' => \app\models\Sede::find()->count(),
];

// Dati DEMO per presentazione
$demo = [
    'budget_totale' => 2850000,
    'liquidato' => 1245680,
    'da_liquidare' => 892450,
    'in_attesa' => 711870,
    'progetti_attivi' => 12,
    'progetti_conclusi' => 8,
    'docenti' => 47,
    'allievi' => 324,
    'ore_erogate' => 4580,
    'ore_pianificate' => 7200,
];

// Percentuale avanzamento
$percentuale_liquidato = round(($demo['liquidato'] / $demo['budget_totale']) * 100);
$percentuale_ore = round(($demo['ore_erogate'] / $demo['ore_pianificate']) * 100);
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Welcome Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="avatar avatar-lg bg-primary-lt">
                            <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-badge-small.png" alt="Albatros">
                        </span>
                    </div>
                    <div class="col">
                        <h2 class="mb-1">Benvenuto in Albatros Manager</h2>
                        <p class="text-muted mb-0">Sistema di gestione docenti per progetti FSE/PNRR</p>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-blue-lt">Anno Formativo 2025/2026</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Stats -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar bg-green-lt me-3">
                                <i class="ti ti-cash"></i>
                            </span>
                            <div>
                                <div class="subheader">Totale Liquidato</div>
                                <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($demo['liquidato'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: <?= $percentuale_liquidato ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted"><?= $percentuale_liquidato ?>% del budget</small>
                            <small class="text-green">+12.5% questo mese</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar bg-yellow-lt me-3">
                                <i class="ti ti-clock-dollar"></i>
                            </span>
                            <div>
                                <div class="subheader">Da Liquidare</div>
                                <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($demo['da_liquidare'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning me-2">23 pratiche</span>
                            <small class="text-muted">in attesa di approvazione</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar bg-azure-lt me-3">
                                <i class="ti ti-wallet"></i>
                            </span>
                            <div>
                                <div class="subheader">Budget Totale</div>
                                <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($demo['budget_totale'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-azure me-2"><?= $demo['progetti_attivi'] ?> progetti</span>
                            <small class="text-muted">attivi</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar bg-purple-lt me-3">
                                <i class="ti ti-hourglass"></i>
                            </span>
                            <div>
                                <div class="subheader">In Attesa Rendiconto</div>
                                <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($demo['in_attesa'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-purple me-2">5 progetti</span>
                            <small class="text-muted">da rendicontare</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Counters Row -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0 text-primary"><?= $demo['progetti_attivi'] ?></div>
                        <div class="text-muted mb-2">Progetti Attivi</div>
                        <div class="hr-text hr-text-center my-2">su <?= $demo['progetti_attivi'] + $demo['progetti_conclusi'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0 text-info"><?= $demo['docenti'] ?></div>
                        <div class="text-muted mb-2">Docenti</div>
                        <div class="hr-text hr-text-center my-2">+3 questo mese</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0 text-success"><?= $demo['allievi'] ?></div>
                        <div class="text-muted mb-2">Allievi</div>
                        <div class="hr-text hr-text-center my-2">iscritti</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0 text-warning"><?= number_format($demo['ore_erogate'], 0, ',', '.') ?></div>
                        <div class="text-muted mb-2">Ore Erogate</div>
                        <div class="hr-text hr-text-center my-2"><?= $percentuale_ore ?>%</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0"><?= $stats['fonti'] ?></div>
                        <div class="text-muted mb-2">Fonti Finanz.</div>
                        <?= Html::a('Gestisci <i class="ti ti-arrow-right"></i>', ['/fonte-finanziamento/index'], ['class' => 'small']) ?>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0"><?= $stats['sedi'] ?></div>
                        <div class="text-muted mb-2">Sedi</div>
                        <?= Html::a('Gestisci <i class="ti ti-arrow-right"></i>', ['/sede/index'], ['class' => 'small']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-deck row-cards mb-4">
            <!-- Grafico Andamento Liquidazioni -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Andamento Liquidazioni 2025</h3>
                        <div class="card-actions">
                            <span class="badge bg-green-lt">+18.2% vs 2024</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chart-liquidazioni" style="height: 240px;"></div>
                    </div>
                </div>
            </div>

            <!-- Grafico Distribuzione Fondi -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Distribuzione per Fonte</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-fonti" style="height: 240px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-deck row-cards">
            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Azioni Rapide</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <?= Html::a('
                                    <div class="card card-sm card-link card-link-pop">
                                        <div class="card-body d-flex align-items-center">
                                            <span class="avatar bg-blue-lt me-3">
                                                <i class="ti ti-credit-card"></i>
                                            </span>
                                            <div>
                                                <div class="fw-medium">Nuova Fonte</div>
                                                <div class="text-muted small">Finanziamento</div>
                                            </div>
                                        </div>
                                    </div>
                                ', ['/fonte-finanziamento/create'], ['class' => 'text-reset']) ?>
                            </div>
                            <div class="col-6">
                                <?= Html::a('
                                    <div class="card card-sm card-link card-link-pop">
                                        <div class="card-body d-flex align-items-center">
                                            <span class="avatar bg-green-lt me-3">
                                                <i class="ti ti-map-pin"></i>
                                            </span>
                                            <div>
                                                <div class="fw-medium">Nuova Sede</div>
                                                <div class="text-muted small">Operativa</div>
                                            </div>
                                        </div>
                                    </div>
                                ', ['/sede/create'], ['class' => 'text-reset']) ?>
                            </div>
                            <div class="col-6">
                                <div class="card card-sm bg-muted-lt">
                                    <div class="card-body d-flex align-items-center">
                                        <span class="avatar bg-secondary-lt me-3">
                                            <i class="ti ti-folder"></i>
                                        </span>
                                        <div>
                                            <div class="fw-medium text-muted">Nuovo Progetto</div>
                                            <div class="text-muted small">Prossimamente</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card card-sm bg-muted-lt">
                                    <div class="card-body d-flex align-items-center">
                                        <span class="avatar bg-secondary-lt me-3">
                                            <i class="ti ti-user"></i>
                                        </span>
                                        <div>
                                            <div class="fw-medium text-muted">Nuovo Docente</div>
                                            <div class="text-muted small">Prossimamente</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ultimi Progetti -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Progetti Recenti</h3>
                    </div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3">ITS</span>
                                <div class="flex-fill">
                                    <div class="fw-medium">ITS Academy 2025</div>
                                    <small class="text-muted">PNRR - M4C1</small>
                                </div>
                                <span class="badge bg-success">Attivo</span>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-blue-lt me-3">FSE</span>
                                <div class="flex-fill">
                                    <div class="fw-medium">Formazione Continua</div>
                                    <small class="text-muted">FSE+ 2021-2027</small>
                                </div>
                                <span class="badge bg-success">Attivo</span>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-orange-lt me-3">GOL</span>
                                <div class="flex-fill">
                                    <div class="fw-medium">GOL Sicilia</div>
                                    <small class="text-muted">PNRR - M5C1</small>
                                </div>
                                <span class="badge bg-warning">In corso</span>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-purple-lt me-3">PON</span>
                                <div class="flex-fill">
                                    <div class="fw-medium">Competenze Digitali</div>
                                    <small class="text-muted">PON 2014-2020</small>
                                </div>
                                <span class="badge bg-secondary">Concluso</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attività Recenti -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Attività Recenti</h3>
                    </div>
                    <div class="list-group list-group-flush list-group-timeline">
                        <div class="list-group-item">
                            <div class="d-flex">
                                <span class="avatar avatar-sm bg-green-lt me-3">
                                    <i class="ti ti-check"></i>
                                </span>
                                <div>
                                    <div class="fw-medium">Liquidazione approvata</div>
                                    <small class="text-muted">€ 12.450 - Prof. Rossi M.</small>
                                    <div class="text-muted small">2 ore fa</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex">
                                <span class="avatar avatar-sm bg-blue-lt me-3">
                                    <i class="ti ti-user-plus"></i>
                                </span>
                                <div>
                                    <div class="fw-medium">Nuovo docente registrato</div>
                                    <small class="text-muted">Dott. Bianchi G.</small>
                                    <div class="text-muted small">5 ore fa</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex">
                                <span class="avatar avatar-sm bg-yellow-lt me-3">
                                    <i class="ti ti-file-invoice"></i>
                                </span>
                                <div>
                                    <div class="fw-medium">Rendiconto inviato</div>
                                    <small class="text-muted">ITS Academy - Q3 2025</small>
                                    <div class="text-muted small">Ieri</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex">
                                <span class="avatar avatar-sm bg-purple-lt me-3">
                                    <i class="ti ti-certificate"></i>
                                </span>
                                <div>
                                    <div class="fw-medium">Contratto firmato</div>
                                    <small class="text-muted">Prof.ssa Verdi L.</small>
                                    <div class="text-muted small">2 giorni fa</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
// ApexCharts per i grafici
$this->registerJs(<<<JS
// Grafico Andamento Liquidazioni
var optionsLiquidazioni = {
    chart: {
        type: 'area',
        height: 240,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    series: [{
        name: 'Liquidato',
        data: [85000, 125000, 98000, 156000, 189000, 145000, 167000, 198000, 145000, 178000, 0, 0]
    }, {
        name: 'Budget Mensile',
        data: [150000, 150000, 150000, 150000, 150000, 150000, 150000, 150000, 150000, 150000, 150000, 150000]
    }],
    xaxis: {
        categories: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic']
    },
    yaxis: {
        labels: {
            formatter: function(val) {
                return '€ ' + (val / 1000).toFixed(0) + 'k';
            }
        }
    },
    colors: ['#206bc4', '#e6e8e9'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.1,
        }
    },
    stroke: {
        curve: 'smooth',
        width: 2
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right'
    },
    tooltip: {
        y: {
            formatter: function(val) {
                return '€ ' + val.toLocaleString('it-IT');
            }
        }
    },
    dataLabels: { enabled: false },
    grid: {
        borderColor: '#e9ecef',
        strokeDashArray: 3
    }
};
new ApexCharts(document.querySelector("#chart-liquidazioni"), optionsLiquidazioni).render();

// Grafico Distribuzione Fonti
var optionsFonti = {
    chart: {
        type: 'donut',
        height: 240,
        fontFamily: 'inherit',
    },
    series: [1200000, 850000, 500000, 300000],
    labels: ['PNRR', 'FSE+', 'PON', 'Altri'],
    colors: ['#206bc4', '#79a6dc', '#a5c4e6', '#e6e8e9'],
    legend: {
        position: 'bottom',
        horizontalAlign: 'center'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Totale',
                        formatter: function(w) {
                            return '€ 2.85M';
                        }
                    }
                }
            }
        }
    },
    tooltip: {
        y: {
            formatter: function(val) {
                return '€ ' + val.toLocaleString('it-IT');
            }
        }
    },
    dataLabels: { enabled: false }
};
new ApexCharts(document.querySelector("#chart-fonti"), optionsFonti).render();
JS);
?>
