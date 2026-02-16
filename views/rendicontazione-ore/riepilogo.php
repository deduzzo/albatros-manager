<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $assegnazione app\models\Assegnazione */
/* @var $oreContrattualizzate int */
/* @var $oreApprovate float */
/* @var $oreInserite float */
/* @var $riepilogoMensile array */

$docente = $assegnazione->docente;
$modulo = $assegnazione->modulo;
$contratto = $assegnazione->contratto;

$this->title = 'Riepilogo Ore';
$this->params['breadcrumbs'][] = ['label' => 'Moduli/Corsi', 'url' => ['/modulo/index']];
if ($modulo) {
    $this->params['breadcrumbs'][] = ['label' => $modulo->codice, 'url' => ['/modulo/view', 'id' => $modulo->id]];
}
$this->params['breadcrumbs'][] = ['label' => 'Rendicontazione Ore', 'url' => ['index', 'assegnazione_id' => $assegnazione->id]];
$this->params['breadcrumbs'][] = $this->title;

$percApprovate = ($oreContrattualizzate > 0) ? round(($oreApprovate / $oreContrattualizzate) * 100) : 0;
$oreExtra = max(0, $oreApprovate - $oreContrattualizzate);
$compensoOrario = $contratto ? $contratto->compenso_orario : ($modulo ? $modulo->compenso_orario : 0);

// Nomi mesi italiani
$mesiItaliani = [
    '01' => 'Gennaio', '02' => 'Febbraio', '03' => 'Marzo', '04' => 'Aprile',
    '05' => 'Maggio', '06' => 'Giugno', '07' => 'Luglio', '08' => 'Agosto',
    '09' => 'Settembre', '10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre',
];
?>

<div class="rendicontazione-ore-riepilogo">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-chart-bar me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?php if ($modulo): ?>
                            <span class="badge bg-blue-lt me-1"><?= Html::encode($modulo->codice) ?></span>
                            <?= Html::encode($modulo->titolo) ?>
                        <?php endif; ?>
                        <?php if ($docente): ?>
                            &mdash;
                            <i class="ti ti-user me-1"></i>
                            <?= Html::a(Html::encode($docente->cognome . ' ' . $docente->nome), ['/docente/view', 'id' => $docente->codice_fiscale]) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <?= Html::a('<i class="ti ti-list me-1"></i> Dettaglio Ore', ['index', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Stat Cards riepilogative -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-blue-lt me-3"><i class="ti ti-file-invoice"></i></span>
                                <div>
                                    <div class="text-muted small">Ore Contrattualizzate</div>
                                    <div class="fw-bold h3 mb-0"><?= number_format($oreContrattualizzate, 0, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3"><i class="ti ti-circle-check"></i></span>
                                <div>
                                    <div class="text-muted small">Ore Approvate</div>
                                    <div class="fw-bold h3 mb-0 text-success"><?= number_format($oreApprovate, 1, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-yellow-lt me-3"><i class="ti ti-clock"></i></span>
                                <div>
                                    <div class="text-muted small">Ore In Attesa</div>
                                    <div class="fw-bold h3 mb-0 text-warning"><?= number_format($oreInserite, 1, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-<?= $oreExtra > 0 ? 'red' : 'cyan' ?>-lt me-3"><i class="ti ti-hourglass"></i></span>
                                <div>
                                    <div class="text-muted small">Ore Rimanenti</div>
                                    <?php $oreRimanenti = max(0, $oreContrattualizzate - $oreApprovate); ?>
                                    <div class="fw-bold h3 mb-0"><?= number_format($oreRimanenti, 1, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress bar -->
            <?php if ($oreContrattualizzate > 0): ?>
            <div class="card mb-4">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Completamento: <?= number_format($oreApprovate, 1, ',', '.') ?> / <?= $oreContrattualizzate ?> ore</small>
                        <small class="fw-bold"><?= $percApprovate ?>%</small>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar <?= $percApprovate > 100 ? 'bg-danger' : ($percApprovate >= 100 ? 'bg-success' : 'bg-primary') ?>"
                             style="width: <?= min($percApprovate, 100) ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ore Extra -->
            <?php if ($oreExtra > 0): ?>
            <div class="alert alert-warning mb-4">
                <div class="d-flex">
                    <div><i class="ti ti-alert-triangle me-2"></i></div>
                    <div>
                        <h4 class="alert-title">Ore Extra: <?= number_format($oreExtra, 1, ',', '.') ?></h4>
                        <div class="text-muted">
                            Le ore approvate superano le ore contrattualizzate di <?= number_format($oreExtra, 1, ',', '.') ?> ore.
                            <?php if ($compensoOrario > 0): ?>
                                Importo extra stimato: <strong><?= Yii::$app->formatter->asCurrency($oreExtra * $compensoOrario, 'EUR') ?></strong>.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Riepilogo Mensile -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-calendar-stats me-2"></i>Riepilogo Mensile</h3>
                </div>
                <?php if ($riepilogoMensile): ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th>Mese</th>
                                <th class="text-center">Giornate</th>
                                <th class="text-end">Ore Approvate</th>
                                <th class="text-end">Ore In Attesa</th>
                                <th class="text-end">Ore Rifiutate</th>
                                <th class="text-end">Totale Ore</th>
                                <?php if ($compensoOrario > 0): ?>
                                <th class="text-end">Importo Approvato</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totaleGiornate = 0;
                            $totaleOreApprovate = 0;
                            $totaleOreInserite = 0;
                            $totaleOreRifiutate = 0;
                            ?>
                            <?php foreach ($riepilogoMensile as $riga): ?>
                            <?php
                                $parti = explode('-', $riga['mese']);
                                $nomeMese = ($mesiItaliani[$parti[1]] ?? $parti[1]) . ' ' . $parti[0];
                                $totOre = (float)$riga['ore_approvate'] + (float)$riga['ore_inserite'] + (float)$riga['ore_rifiutate'];
                                $totaleGiornate += (int)$riga['num_giornate'];
                                $totaleOreApprovate += (float)$riga['ore_approvate'];
                                $totaleOreInserite += (float)$riga['ore_inserite'];
                                $totaleOreRifiutate += (float)$riga['ore_rifiutate'];
                            ?>
                            <tr>
                                <td><strong><?= Html::encode($nomeMese) ?></strong></td>
                                <td class="text-center"><?= $riga['num_giornate'] ?></td>
                                <td class="text-end">
                                    <?php if ((float)$riga['ore_approvate'] > 0): ?>
                                        <span class="text-success fw-bold"><?= number_format((float)$riga['ore_approvate'], 1, ',', '.') ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ((float)$riga['ore_inserite'] > 0): ?>
                                        <span class="text-warning"><?= number_format((float)$riga['ore_inserite'], 1, ',', '.') ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ((float)$riga['ore_rifiutate'] > 0): ?>
                                        <span class="text-danger"><?= number_format((float)$riga['ore_rifiutate'], 1, ',', '.') ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold"><?= number_format($totOre, 1, ',', '.') ?></td>
                                <?php if ($compensoOrario > 0): ?>
                                <td class="text-end">
                                    <?php if ((float)$riga['ore_approvate'] > 0): ?>
                                        <?= Yii::$app->formatter->asCurrency((float)$riga['ore_approvate'] * $compensoOrario, 'EUR') ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold" style="border-top: 2px solid var(--tblr-border-color);">
                                <td>Totale</td>
                                <td class="text-center"><?= $totaleGiornate ?></td>
                                <td class="text-end text-success"><?= number_format($totaleOreApprovate, 1, ',', '.') ?></td>
                                <td class="text-end text-warning"><?= number_format($totaleOreInserite, 1, ',', '.') ?></td>
                                <td class="text-end text-danger"><?= number_format($totaleOreRifiutate, 1, ',', '.') ?></td>
                                <td class="text-end"><?= number_format($totaleOreApprovate + $totaleOreInserite + $totaleOreRifiutate, 1, ',', '.') ?></td>
                                <?php if ($compensoOrario > 0): ?>
                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($totaleOreApprovate * $compensoOrario, 'EUR') ?></td>
                                <?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php else: ?>
                <div class="card-body text-muted text-center">
                    <i class="ti ti-chart-bar-off mb-2" style="font-size: 2rem;"></i>
                    <p>Nessuna rendicontazione ore presente</p>
                </div>
                <?php endif; ?>

                <div class="card-footer">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
