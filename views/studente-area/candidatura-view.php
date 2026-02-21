<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Iscrizione;
use app\models\Allievo;

/* @var $this yii\web\View */
/* @var $iscrizione app\models\Iscrizione */

$this->title = 'Dettaglio Candidatura';
$this->params['breadcrumbs'][] = ['label' => 'Le Mie Candidature', 'url' => ['le-mie-candidature']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-file-description me-2"></i> <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="page-subtitle text-secondary">
                        Bando: <?= Html::encode($iscrizione->avviso->codice_avviso ?? '-') ?> - <?= Html::encode($iscrizione->avviso->titolo ?? '') ?>
                    </div>
                </div>
                <div class="col-auto">
                    <span class="badge <?= $iscrizione->getStatusBadgeClass() ?> fs-5 px-3 py-2">
                        <?= $iscrizione->getStatusLabel() ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row row-cards">
            <!-- Colonna principale -->
            <div class="col-lg-8">
                <!-- Dati candidatura (snapshot) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-user me-2"></i> Dati della Candidatura</h3>
                    </div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Data Candidatura</div>
                                <div class="datagrid-content"><?= Yii::$app->formatter->asDate($iscrizione->data_iscrizione) ?></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Titolo di Studio</div>
                                <div class="datagrid-content">
                                    <?php
                                    $labels = Iscrizione::getTitoloStudioLabels();
                                    echo Html::encode($labels[$iscrizione->titolo_studio] ?? 'Non specificato');
                                    ?>
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Anno Conseguimento</div>
                                <div class="datagrid-content"><?= Html::encode($iscrizione->anno_conseguimento ?: '-') ?></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Istituto</div>
                                <div class="datagrid-content"><?= Html::encode($iscrizione->istituto ?: '-') ?></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Stato Occupazionale</div>
                                <div class="datagrid-content">
                                    <?php
                                    $labels = Iscrizione::getStatoOccupazionaleLabels();
                                    echo Html::encode($labels[$iscrizione->stato_occupazionale] ?? 'Non specificato');
                                    ?>
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Settore Attivita</div>
                                <div class="datagrid-content"><?= Html::encode($iscrizione->settore_attivita ?: '-') ?></div>
                            </div>
                        </div>
                        <?php if ($iscrizione->note): ?>
                            <div class="mt-3">
                                <strong>Note:</strong>
                                <p class="text-secondary mb-0"><?= nl2br(Html::encode($iscrizione->note)) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Punteggi per criterio (se valutata) -->
                <?php if (!empty($iscrizione->punteggi)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-chart-bar me-2"></i> Punteggi per Criterio</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Criterio</th>
                                    <th class="text-center">Punteggio</th>
                                    <th class="text-center">Peso</th>
                                    <th class="text-center">Punteggio Pesato</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($iscrizione->punteggi as $punteggio): ?>
                                    <tr>
                                        <td><?= Html::encode($punteggio->criterio ?? 'Criterio #' . $punteggio->id) ?></td>
                                        <td class="text-center"><?= number_format($punteggio->punteggio, 2, ',', '.') ?></td>
                                        <td class="text-center"><?= number_format($punteggio->peso ?? 1, 2, ',', '.') ?></td>
                                        <td class="text-center"><strong><?= number_format($punteggio->getPunteggioPesato(), 2, ',', '.') ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Punteggio Totale:</td>
                                    <td class="text-center">
                                        <span class="badge bg-purple text-purple-fg fs-5">
                                            <?= number_format($iscrizione->punteggio_totale ?? 0, 2, ',', '.') ?>
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <?php elseif ($iscrizione->punteggio_totale !== null): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-chart-bar me-2"></i> Punteggio</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="h1 text-purple"><?= number_format($iscrizione->punteggio_totale, 2, ',', '.') ?></div>
                        <div class="text-secondary">Punteggio totale assegnato</div>
                        <?php if ($iscrizione->posizione_graduatoria): ?>
                            <div class="mt-2">
                                <span class="badge bg-purple text-purple-fg fs-5">
                                    Posizione in graduatoria: #<?= $iscrizione->posizione_graduatoria ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Motivo non ammissione -->
                <?php if ($iscrizione->status === Iscrizione::STATUS_NON_AMMESSO && $iscrizione->motivo_non_ammissione): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-red"><i class="ti ti-alert-circle me-2"></i> Motivo Non Ammissione</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(Html::encode($iscrizione->motivo_non_ammissione)) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Storico cambi stato -->
                <?php if (!empty($iscrizione->storicoIscrizioni)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-history me-2"></i> Storico</h3>
                    </div>
                    <div class="card-body">
                        <ul class="timeline">
                            <?php foreach ($iscrizione->storicoIscrizioni as $storico): ?>
                                <li class="timeline-event">
                                    <div class="timeline-event-icon bg-purple-lt">
                                        <i class="ti ti-arrow-right"></i>
                                    </div>
                                    <div class="card timeline-event-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <?php if ($storico->status_precedente): ?>
                                                    <span class="badge bg-default text-default-fg me-1"><?= $storico->getStatusPrecedenteLabel() ?></span>
                                                    <i class="ti ti-arrow-right mx-1 text-muted"></i>
                                                <?php endif; ?>
                                                <?php
                                                // Badge colorato in base allo status nuovo dello storico
                                                $tmpIscrizione = new \app\models\Iscrizione(['status' => $storico->status_nuovo]);
                                                ?>
                                                <span class="badge <?= $tmpIscrizione->getStatusBadgeClass() ?>">
                                                    <?= $storico->getStatusNuovoLabel() ?>
                                                </span>
                                                <span class="ms-auto text-secondary small">
                                                    <?= Yii::$app->formatter->asDatetime($storico->created_at) ?>
                                                </span>
                                            </div>
                                            <?php if ($storico->note): ?>
                                                <p class="text-secondary mb-0 small"><?= Html::encode($storico->note) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Colonna laterale -->
            <div class="col-lg-4">
                <!-- Info Bando -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Info Bando</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Codice:</dt>
                            <dd class="col-7">
                                <span class="badge bg-purple-lt"><?= Html::encode($iscrizione->avviso->codice_avviso ?? '-') ?></span>
                            </dd>

                            <dt class="col-5">Titolo:</dt>
                            <dd class="col-7"><?= Html::encode($iscrizione->avviso->titolo ?? '-') ?></dd>

                            <?php if ($iscrizione->avviso && $iscrizione->avviso->progetto): ?>
                                <dt class="col-5">Progetto:</dt>
                                <dd class="col-7"><?= Html::encode($iscrizione->avviso->progetto->titolo) ?></dd>
                            <?php endif; ?>

                            <?php if ($iscrizione->avviso && $iscrizione->avviso->data_scadenza): ?>
                                <dt class="col-5">Scadenza:</dt>
                                <dd class="col-7"><?= Yii::$app->formatter->asDate($iscrizione->avviso->data_scadenza) ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>

                <!-- Riepilogo stato -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Stato Candidatura</h3>
                    </div>
                    <div class="card-body text-center">
                        <span class="badge <?= $iscrizione->getStatusBadgeClass() ?> fs-5 px-3 py-2 mb-2">
                            <?= $iscrizione->getStatusLabel() ?>
                        </span>
                        <?php if ($iscrizione->posizione_graduatoria): ?>
                            <div class="mt-2">
                                <div class="text-secondary small">Posizione in graduatoria</div>
                                <div class="h1 text-purple">#<?= $iscrizione->posizione_graduatoria ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Consensi -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Consensi</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <?php if ($iscrizione->accetta_privacy): ?>
                                <span class="text-green"><i class="ti ti-check me-1"></i></span> Privacy accettata
                            <?php else: ?>
                                <span class="text-muted"><i class="ti ti-x me-1"></i></span> Privacy non accettata
                            <?php endif; ?>
                        </div>
                        <div class="mb-2">
                            <?php if ($iscrizione->dichiarazione_veridicita): ?>
                                <span class="text-green"><i class="ti ti-check me-1"></i></span> Dichiarazione veridicita
                            <?php else: ?>
                                <span class="text-muted"><i class="ti ti-x me-1"></i></span> Dichiarazione veridicita
                            <?php endif; ?>
                        </div>
                        <div>
                            <?php if ($iscrizione->accetta_marketing): ?>
                                <span class="text-green"><i class="ti ti-check me-1"></i></span> Consenso marketing
                            <?php else: ?>
                                <span class="text-muted"><i class="ti ti-x me-1"></i></span> Consenso marketing
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alle candidature', ['le-mie-candidature'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
</div>
