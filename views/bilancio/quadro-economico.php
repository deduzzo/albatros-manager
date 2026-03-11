<?php

use yii\helpers\Html;
use app\models\VoceSpesaCategoria;

/* @var $this yii\web\View */
/* @var $progetto app\models\Progetto */
/* @var $categorie app\models\VoceSpesaCategoria[] */
/* @var $totaliVoce array */
/* @var $totaliCategoria array */
/* @var $totaliSezione array */
/* @var $totaleGenerale float */
/* @var $budget float */

$this->title = 'Quadro Economico: ' . $progetto->codice_progetto;
$this->params['breadcrumbs'][] = ['label' => 'Bilancio', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $progetto->codice_progetto, 'url' => ['progetto', 'id' => $progetto->id]];
$this->params['breadcrumbs'][] = 'Quadro Economico';

$sezioneLabels = VoceSpesaCategoria::getSezioneList();
$sezioneColors = [
    'costi_diretti' => '#206bc4',
    'costi_indiretti' => '#ae3ec9',
    'attivita_accessorie' => '#0ca678',
];

$differenza = $budget - $totaleGenerale;
?>

<div class="quadro-economico">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-table me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($progetto->titolo) ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-download me-1"></i> Export Excel', ['export', 'id' => $progetto->id], ['class' => 'btn btn-warning me-2']) ?>
                    <?= Html::a('<i class="ti ti-printer me-1"></i> Stampa', '#', ['class' => 'btn btn-secondary', 'onclick' => 'window.print(); return false;']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Riepilogo -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-blue-lt me-3"><i class="ti ti-wallet"></i></span>
                                <div>
                                    <div class="subheader">Budget Preventivo</div>
                                    <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($budget, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-red-lt me-3"><i class="ti ti-receipt"></i></span>
                                <div>
                                    <div class="subheader">Totale Consuntivo</div>
                                    <div class="h2 mb-0 text-red"><?= Yii::$app->formatter->asCurrency($totaleGenerale, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-<?= $differenza >= 0 ? 'green' : 'orange' ?>-lt me-3"><i class="ti ti-scale"></i></span>
                                <div>
                                    <div class="subheader">Differenza</div>
                                    <div class="h2 mb-0 text-<?= $differenza >= 0 ? 'green' : 'orange' ?>"><?= Yii::$app->formatter->asCurrency($differenza, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quadro Economico Tabella -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-table me-2"></i>
                        Quadro Economico Dettagliato
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter table-bordered mb-0">
                        <thead>
                            <tr style="background: #206bc4; color: white;">
                                <th style="width: 100px;">Codice</th>
                                <th>Voce di Spesa</th>
                                <th class="text-end" style="width: 160px;">Consuntivo (&euro;)</th>
                                <th class="text-end" style="width: 120px;">% su Budget</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $currentSezione = null;
                            foreach ($categorie as $cat):
                                // Intestazione sezione
                                if ($cat->sezione !== $currentSezione):
                                    $currentSezione = $cat->sezione;
                                    $sezTotale = $totaliSezione[$currentSezione] ?? 0;
                                    $sezPerc = $budget > 0 ? round(($sezTotale / $budget) * 100, 1) : 0;
                                    $sezColor = $sezioneColors[$currentSezione] ?? '#666';
                            ?>
                                <tr style="background: <?= $sezColor ?>15;">
                                    <td colspan="2" class="fw-bold" style="color: <?= $sezColor ?>;">
                                        <i class="ti ti-folder me-1"></i>
                                        <?= strtoupper($sezioneLabels[$currentSezione] ?? $currentSezione) ?>
                                    </td>
                                    <td class="text-end fw-bold" style="color: <?= $sezColor ?>;">
                                        <?= Yii::$app->formatter->asCurrency($sezTotale, 'EUR') ?>
                                    </td>
                                    <td class="text-end fw-bold" style="color: <?= $sezColor ?>;">
                                        <?= $sezPerc ?>%
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <!-- Categoria -->
                            <?php
                                $catTotale = $totaliCategoria[$cat->id] ?? 0;
                                $catPerc = $budget > 0 ? round(($catTotale / $budget) * 100, 1) : 0;
                            ?>
                            <tr style="background: #f8f9fa;">
                                <td class="fw-bold"><code><?= Html::encode($cat->codice) ?></code></td>
                                <td class="fw-bold"><?= Html::encode($cat->nome) ?></td>
                                <td class="text-end fw-bold"><?= Yii::$app->formatter->asCurrency($catTotale, 'EUR') ?></td>
                                <td class="text-end fw-bold text-muted"><?= $catPerc ?>%</td>
                            </tr>

                            <!-- Voci -->
                            <?php foreach ($cat->vociSpesa as $voce):
                                $voceTotale = $totaliVoce[$voce->id] ?? 0;
                                $vocePerc = $budget > 0 ? round(($voceTotale / $budget) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td class="ps-4 text-muted"><code><?= Html::encode($voce->codice) ?></code></td>
                                    <td class="ps-4"><?= Html::encode($voce->nome) ?></td>
                                    <td class="text-end <?= $voceTotale > 0 ? 'text-red' : 'text-muted' ?>">
                                        <?= $voceTotale > 0 ? Yii::$app->formatter->asCurrency($voceTotale, 'EUR') : '-' ?>
                                    </td>
                                    <td class="text-end text-muted"><?= $voceTotale > 0 ? $vocePerc . '%' : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: #d4edda;">
                                <td class="fw-bold" colspan="2">
                                    <i class="ti ti-sum me-1"></i>
                                    TOTALE GENERALE
                                </td>
                                <td class="text-end fw-bold" style="font-size: 1.1em;">
                                    <?= Yii::$app->formatter->asCurrency($totaleGenerale, 'EUR') ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= $budget > 0 ? round(($totaleGenerale / $budget) * 100, 1) : 0 ?>%
                                </td>
                            </tr>
                            <tr style="background: #f0f5ff;">
                                <td class="fw-bold" colspan="2">Budget Preventivo</td>
                                <td class="text-end fw-bold"><?= Yii::$app->formatter->asCurrency($budget, 'EUR') ?></td>
                                <td class="text-end fw-bold">100%</td>
                            </tr>
                            <tr style="background: <?= $differenza >= 0 ? '#d4edda' : '#f8d7da' ?>;">
                                <td class="fw-bold" colspan="2">Differenza (Budget - Consuntivo)</td>
                                <td class="text-end fw-bold text-<?= $differenza >= 0 ? 'green' : 'red' ?>">
                                    <?= Yii::$app->formatter->asCurrency($differenza, 'EUR') ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="card mt-3 d-print-none">
                <div class="card-footer">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Bilancio Progetto', ['progetto', 'id' => $progetto->id], ['class' => 'btn btn-secondary']) ?>
                    <?= Html::a('<i class="ti ti-chart-bar me-1"></i> Dashboard Bilancio', ['index'], ['class' => 'btn btn-info ms-2']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
