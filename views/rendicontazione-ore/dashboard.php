<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $righe array */
/* @var $totaleAssegnazioniPendenti int */
/* @var $totaleOreDaApprovare float */
/* @var $totaleOreApprovate float */

$this->title = 'Rendicontazione Ore';
$this->params['breadcrumbs'][] = ['label' => 'Pagamenti', 'url' => ['/pagamento/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rendicontazione-ore-dashboard">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-clock-hour-4 me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Panoramica ore docenti da approvare
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-orange-lt me-3"><i class="ti ti-alert-circle"></i></span>
                                <div>
                                    <div class="text-muted small">Assegnazioni con Ore Pendenti</div>
                                    <div class="fw-bold h3 mb-0"><?= $totaleAssegnazioniPendenti ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-yellow-lt me-3"><i class="ti ti-clock"></i></span>
                                <div>
                                    <div class="text-muted small">Ore da Approvare</div>
                                    <div class="fw-bold h3 mb-0 text-warning"><?= number_format($totaleOreDaApprovare, 1, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3"><i class="ti ti-circle-check"></i></span>
                                <div>
                                    <div class="text-muted small">Ore Approvate Totali</div>
                                    <div class="fw-bold h3 mb-0 text-success"><?= number_format($totaleOreApprovate, 1, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabella assegnazioni con ore pendenti -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-list me-2"></i>Assegnazioni con Ore in Attesa</h3>
                </div>
                <?php if (empty($righe)): ?>
                <div class="card-body text-center text-muted py-5">
                    <i class="ti ti-checks mb-2" style="font-size: 2.5rem;"></i>
                    <p class="mb-0">Nessuna ora in attesa di approvazione</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th>Docente</th>
                                <th>Modulo</th>
                                <th>Avviso</th>
                                <th class="text-end">Ore Contratto</th>
                                <th class="text-end">Approvate</th>
                                <th class="text-end">In Attesa</th>
                                <th class="text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($righe as $riga): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-blue-lt me-2">
                                            <?php if ($riga['docente']): ?>
                                                <?= strtoupper(mb_substr($riga['docente']->cognome, 0, 1) . mb_substr($riga['docente']->nome, 0, 1)) ?>
                                            <?php else: ?>
                                                --
                                            <?php endif; ?>
                                        </span>
                                        <div>
                                            <?php if ($riga['docente']): ?>
                                                <?= Html::a(Html::encode($riga['docente']->cognome . ' ' . $riga['docente']->nome), ['/docente/view', 'id' => $riga['docente']->codice_fiscale], ['class' => 'text-reset']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/D</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($riga['modulo']): ?>
                                        <span class="badge bg-blue-lt me-1"><?= Html::encode($riga['modulo']->codice) ?></span>
                                        <span class="d-none d-lg-inline"><?= Html::encode(mb_substr($riga['modulo']->titolo, 0, 40)) ?><?= mb_strlen($riga['modulo']->titolo) > 40 ? '...' : '' ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">N/D</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($riga['avviso']): ?>
                                        <?= Html::encode(mb_substr($riga['avviso']->titolo, 0, 30)) ?><?= mb_strlen($riga['avviso']->titolo) > 30 ? '...' : '' ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/D</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($riga['oreContratto'] > 0): ?>
                                        <?= number_format($riga['oreContratto'], 0, ',', '.') ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-green text-green-fg"><?= number_format($riga['oreApprovate'], 1, ',', '.') ?></span>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-orange text-orange-fg"><?= number_format($riga['oreInAttesa'], 1, ',', '.') ?></span>
                                </td>
                                <td class="text-center">
                                    <?= Html::a('<i class="ti ti-eye me-1"></i> Dettaglio', ['/rendicontazione-ore/index', 'assegnazione_id' => $riga['assegnazione']->id], ['class' => 'btn btn-sm btn-primary']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted small">
                    <?= count($righe) ?> assegnazion<?= count($righe) === 1 ? 'e' : 'i' ?> con ore in attesa di approvazione
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
