<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $assegnazioni app\models\Assegnazione[] */
/* @var $totaleAssegnazioni int */
/* @var $totaleOreContrattualizzate float */
/* @var $totaleOreApprovate float */
/* @var $totaleOreDaValidare float */

$this->title = 'Le Mie Assegnazioni';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-clock me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
            <div class="page-subtitle text-secondary">
                Gestisci la rendicontazione delle ore per le tue assegnazioni
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Assegnazioni attive</div>
                        </div>
                        <div class="h1 mb-0"><?= $totaleAssegnazioni ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Ore contrattualizzate</div>
                        </div>
                        <div class="h1 mb-0"><?= number_format($totaleOreContrattualizzate, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Ore approvate</div>
                        </div>
                        <div class="h1 mb-0 text-success"><?= number_format($totaleOreApprovate, 1, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Ore da validare</div>
                        </div>
                        <div class="h1 mb-0 text-warning"><?= number_format($totaleOreDaValidare, 1, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($assegnazioni)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <span class="avatar avatar-xl bg-secondary-lt">
                            <i class="ti ti-clipboard-off"></i>
                        </span>
                    </div>
                    <h3>Nessuna assegnazione</h3>
                    <p class="text-secondary">Non hai ancora ricevuto nessuna assegnazione attiva.</p>
                    <?= Html::a('<i class="ti ti-arrow-left me-2"></i> Torna alla Dashboard', ['index'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Modulo</th>
                                <th>Avviso</th>
                                <th class="text-center">Ore contratto</th>
                                <th style="min-width: 200px;">Progresso ore</th>
                                <th class="text-center">Stato</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assegnazioni as $assegnazione): ?>
                                <?php
                                    $modulo = $assegnazione->modulo;
                                    $contratto = $assegnazione->contratto;
                                    $oreContratto = $contratto->ore_contrattualizzate ?? ($modulo->ore_totali ?? 0);
                                    $oreApprovate = $assegnazione->getTotaleOreApprovate();
                                    $oreInserite = \app\models\RendicontazioneOre::getTotaleOreInserite($assegnazione->id);
                                    $oreRimanenti = max(0, $oreContratto - $oreApprovate - $oreInserite);

                                    $percApprovate = $oreContratto > 0 ? min(100, ($oreApprovate / $oreContratto) * 100) : 0;
                                    $percInserite = $oreContratto > 0 ? min(100 - $percApprovate, ($oreInserite / $oreContratto) * 100) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-azure-lt"><?= Html::encode($modulo->codice ?? '-') ?></span>
                                        <div class="small text-muted mt-1"><?= Html::encode($modulo->titolo ?? '') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt"><?= Html::encode($modulo->avviso->codice_avviso ?? '-') ?></span>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= $oreContratto ?></strong> ore
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="flex-fill">
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-green" style="width: <?= $percApprovate ?>%" title="Approvate: <?= number_format($oreApprovate, 1, ',', '.') ?> ore"></div>
                                                    <div class="progress-bar bg-orange" style="width: <?= $percInserite ?>%" title="In attesa: <?= number_format($oreInserite, 1, ',', '.') ?> ore"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex small text-muted">
                                            <span class="me-2"><span class="badge bg-green text-green-fg badge-sm">&nbsp;</span> <?= number_format($oreApprovate, 1, ',', '.') ?> appr.</span>
                                            <span class="me-2"><span class="badge bg-orange text-orange-fg badge-sm">&nbsp;</span> <?= number_format($oreInserite, 1, ',', '.') ?> att.</span>
                                            <span><span class="badge bg-default text-default-fg badge-sm">&nbsp;</span> <?= number_format($oreRimanenti, 1, ',', '.') ?> rim.</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $assegnazione->getStatusBadgeClass() ?>">
                                            <?= $assegnazione->getStatusLabel() ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-list justify-content-end">
                                            <?= Html::a('<i class="ti ti-plus me-1"></i> Inserisci Ore', ['inserisci-ore', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-primary btn-sm']) ?>
                                            <?= Html::a('<i class="ti ti-list me-1"></i> Le mie ore', ['le-mie-ore', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <p class="text-muted small">
                    <i class="ti ti-info-circle me-1"></i>
                    <span class="badge bg-green text-green-fg badge-sm">&nbsp;</span> Ore approvate |
                    <span class="badge bg-orange text-orange-fg badge-sm">&nbsp;</span> Ore in attesa di validazione |
                    <span class="badge bg-default text-default-fg badge-sm">&nbsp;</span> Ore rimanenti
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
