<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\assets\ApexChartsAsset;

/* @var $this yii\web\View */
/* @var $stats array */
/* @var $progettiRecenti app\models\Progetto[] */
/* @var $distribuzioneFonti array */
/* @var $etichetteFonti array */
/* @var $totaleBudgetFonti float */
/* @var $liquidazioniMensili array */
/* @var $mesi array */

$this->title = 'Dashboard';

// Registra ApexCharts per i grafici
ApexChartsAsset::register($this);

// Contatori aggiuntivi
$fontiCount = \app\models\FonteFinanziamento::find()->count();
$sediCount = \app\models\Sede::find()->count();
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
                                <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($stats['liquidato'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: <?= $stats['percentuale_liquidato'] ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted"><?= $stats['percentuale_liquidato'] ?>% del budget totale</small>
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
                                <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($stats['da_liquidare'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning me-2"><?= $stats['pratiche_da_liquidare'] ?> pratiche</span>
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
                                <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($stats['budget_totale'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-azure me-2"><?= $stats['progetti_attivi'] ?> progetti</span>
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
                                <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($stats['in_attesa'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-purple me-2"><?= $stats['progetti_da_rendicontare'] ?> progetti</span>
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
                        <div class="h1 m-0 text-primary"><?= $stats['progetti_attivi'] ?></div>
                        <div class="text-muted mb-2">Progetti Attivi</div>
                        <div class="hr-text hr-text-center my-2">su <?= $stats['total_progetti'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0 text-info"><?= $stats['docenti'] ?></div>
                        <div class="text-muted mb-2">Docenti</div>
                        <div class="hr-text hr-text-center my-2">registrati</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0 text-success"><?= $stats['allievi'] ?></div>
                        <div class="text-muted mb-2">Allievi</div>
                        <div class="hr-text hr-text-center my-2">iscritti</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0 text-warning"><?= number_format($stats['ore_erogate'], 0, ',', '.') ?></div>
                        <div class="text-muted mb-2">Ore Erogate</div>
                        <div class="hr-text hr-text-center my-2"><?= $stats['percentuale_ore'] ?>%</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0"><?= $fontiCount ?></div>
                        <div class="text-muted mb-2">Fonti Finanz.</div>
                        <?= Html::a('Gestisci <i class="ti ti-arrow-right"></i>', ['/fonte-finanziamento/index'], ['class' => 'small']) ?>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="h1 m-0"><?= $sediCount ?></div>
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
                        <?php if (empty($progettiRecenti)): ?>
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="ti ti-folder-off mb-2" style="font-size: 2rem;"></i>
                                <div>Nessun progetto presente</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($progettiRecenti as $progetto): ?>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-<?= $progetto->status === 'attivo' ? 'green' : ($progetto->status === 'concluso' ? 'secondary' : 'blue') ?>-lt me-3">
                                            <?= strtoupper(substr($progetto->codice_progetto, 0, 3)) ?>
                                        </span>
                                        <div class="flex-fill">
                                            <div class="fw-medium"><?= Html::encode($progetto->titolo) ?></div>
                                            <small class="text-muted"><?= Html::encode($progetto->fonteFinanziamento->descrizione ?? 'N/D') ?></small>
                                        </div>
                                        <span class="badge bg-<?= $progetto->status === 'attivo' ? 'success' : ($progetto->status === 'concluso' ? 'secondary' : 'warning') ?>">
                                            <?= ucfirst($progetto->status) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
// Preparare i dati JSON come stringhe prima del heredoc
$liquidazioniJson = json_encode(array_values($liquidazioniMensili ?? []));
$mesiJson = json_encode($mesi ?? ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic']);
$distribuzioneFontiJson = json_encode(array_values($distribuzioneFonti ?? []));
$etichetteFontiJson = json_encode(array_values($etichetteFonti ?? []));
$totaleBudgetFontiVal = $totaleBudgetFonti ?? 0;

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
        data: $liquidazioniJson
    }],
    xaxis: {
        categories: $mesiJson
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
    series: $distribuzioneFontiJson,
    labels: $etichetteFontiJson,
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
                            const total = $totaleBudgetFontiVal;
                            return '€ ' + (total / 1000000).toFixed(2) + 'M';
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
