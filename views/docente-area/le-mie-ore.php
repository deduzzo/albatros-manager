<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $assegnazione app\models\Assegnazione */
/* @var $rendicontazioni app\models\RendicontazioneOre[] */
/* @var $oreApprovate float */
/* @var $oreInAttesa float */
/* @var $oreRifiutate float */

$modulo = $assegnazione->modulo;
$contratto = $assegnazione->contratto;
$oreContratto = $contratto->ore_contrattualizzate ?? ($modulo->ore_totali ?? 0);

$this->title = 'Le Mie Ore - ' . $modulo->codice;
$this->params['breadcrumbs'][] = ['label' => 'Le Mie Assegnazioni', 'url' => ['le-mie-assegnazioni']];
$this->params['breadcrumbs'][] = $modulo->codice;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-clock me-2"></i> Le Mie Ore
                    </h2>
                    <div class="page-subtitle text-secondary">
                        <span class="badge bg-azure-lt me-1"><?= Html::encode($modulo->codice) ?></span>
                        <?= Html::encode($modulo->titolo) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <?= Html::a('<i class="ti ti-plus me-2"></i> Inserisci nuove ore', ['inserisci-ore', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Ore contratto</div>
                        </div>
                        <div class="h1 mb-0"><?= $oreContratto ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Ore approvate</div>
                        </div>
                        <div class="h1 mb-0 text-success"><?= number_format($oreApprovate, 1, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Ore in attesa</div>
                        </div>
                        <div class="h1 mb-0 text-warning"><?= number_format($oreInAttesa, 1, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Ore rifiutate</div>
                        </div>
                        <div class="h1 mb-0 text-danger"><?= number_format($oreRifiutate, 1, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress bar complessiva -->
        <?php
            $percApprovate = $oreContratto > 0 ? min(100, ($oreApprovate / $oreContratto) * 100) : 0;
            $percInAttesa = $oreContratto > 0 ? min(100 - $percApprovate, ($oreInAttesa / $oreContratto) * 100) : 0;
        ?>
        <div class="card mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-center mb-2">
                    <div class="flex-fill">
                        <span class="text-muted small">Progresso complessivo</span>
                    </div>
                    <div class="text-end">
                        <strong><?= number_format($oreApprovate + $oreInAttesa, 1, ',', '.') ?></strong> / <?= $oreContratto ?> ore
                    </div>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-green" style="width: <?= $percApprovate ?>%"></div>
                    <div class="progress-bar bg-orange" style="width: <?= $percInAttesa ?>%"></div>
                </div>
                <div class="d-flex small text-muted mt-1">
                    <span class="me-3"><span class="badge bg-green text-green-fg badge-sm">&nbsp;</span> Approvate</span>
                    <span class="me-3"><span class="badge bg-orange text-orange-fg badge-sm">&nbsp;</span> In attesa</span>
                    <span><span class="badge bg-default text-default-fg badge-sm">&nbsp;</span> Rimanenti</span>
                </div>
            </div>
        </div>

        <?php if (empty($rendicontazioni)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <span class="avatar avatar-xl bg-secondary-lt">
                            <i class="ti ti-clock-off"></i>
                        </span>
                    </div>
                    <h3>Nessuna ora inserita</h3>
                    <p class="text-secondary">Non hai ancora inserito ore per questa assegnazione.</p>
                    <?= Html::a('<i class="ti ti-plus me-2"></i> Inserisci le prime ore', ['inserisci-ore', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th class="text-center">Ore</th>
                                <th>Ora inizio</th>
                                <th>Ora fine</th>
                                <th>Note</th>
                                <th class="text-center">Stato</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rendicontazioni as $rend): ?>
                                <tr>
                                    <td>
                                        <strong><?= Yii::$app->formatter->asDate($rend->data, 'php:d/m/Y') ?></strong>
                                        <div class="small text-muted"><?= Yii::$app->formatter->asDate($rend->data, 'php:l') ?></div>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= number_format($rend->ore, 2, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($rend->ora_inizio): ?>
                                            <?= Yii::$app->formatter->asTime($rend->ora_inizio, 'php:H:i') ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rend->ora_fine): ?>
                                            <?= Yii::$app->formatter->asTime($rend->ora_fine, 'php:H:i') ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rend->note): ?>
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= Html::encode($rend->note) ?>">
                                                <?= Html::encode($rend->note) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $rend->getStatusBadgeClass() ?>">
                                            <?= $rend->getStatusLabel() ?>
                                        </span>
                                        <?php if ($rend->status === \app\models\RendicontazioneOre::STATUS_RIFIUTATO && $rend->motivo_rifiuto): ?>
                                            <div class="small text-danger mt-1" title="<?= Html::encode($rend->motivo_rifiuto) ?>">
                                                <i class="ti ti-alert-circle"></i>
                                                <span class="text-truncate d-inline-block" style="max-width: 150px; vertical-align: middle;">
                                                    <?= Html::encode($rend->motivo_rifiuto) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($rend->status === \app\models\RendicontazioneOre::STATUS_INSERITO): ?>
                                            <div class="btn-list justify-content-end">
                                                <?= Html::a('<i class="ti ti-edit"></i>', ['modifica-ore', 'id' => $rend->id], [
                                                    'class' => 'btn btn-outline-primary btn-sm btn-icon',
                                                    'title' => 'Modifica',
                                                ]) ?>
                                                <?= Html::a('<i class="ti ti-trash"></i>', ['elimina-ore', 'id' => $rend->id], [
                                                    'class' => 'btn btn-outline-danger btn-sm btn-icon',
                                                    'title' => 'Elimina',
                                                    'data' => [
                                                        'confirm' => 'Sei sicuro di voler eliminare questa registrazione ore?',
                                                        'method' => 'post',
                                                    ],
                                                ]) ?>
                                            </div>
                                        <?php elseif ($rend->status === \app\models\RendicontazioneOre::STATUS_APPROVATO): ?>
                                            <span class="text-success small"><i class="ti ti-check"></i></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
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
                    Puoi modificare o eliminare solo le ore con stato <span class="badge bg-orange text-orange-fg">Inserito</span>.
                    Le ore approvate o rifiutate non possono essere modificate.
                </p>
            </div>
        <?php endif; ?>

        <div class="mt-3">
            <?= Html::a('<i class="ti ti-arrow-left me-2"></i> Torna alle assegnazioni', ['le-mie-assegnazioni'], ['class' => 'btn btn-ghost-secondary']) ?>
        </div>
    </div>
</div>
