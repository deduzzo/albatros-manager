<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Progetto */

$this->title = 'Progetto: ' . $model->codice_progetto;
$this->params['breadcrumbs'][] = ['label' => 'Progetti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->codice_progetto;
?>

<div class="progetto-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-file-spreadsheet me-1"></i> Export Consulente', ['report/export-consulente', 'progetto_id' => $model->id], ['class' => 'btn bg-azure text-azure-fg']) ?>
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo progetto?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dettagli Progetto</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered'],
                                'attributes' => [
                                    [
                                        'attribute' => 'codice_progetto',
                                        'format' => 'raw',
                                        'value' => '<span class="badge bg-blue-lt">' . Html::encode($model->codice_progetto) . '</span>',
                                    ],
                                    'titolo',
                                    [
                                        'attribute' => 'fonte_finanziamento_id',
                                        'value' => $model->fonteFinanziamento ? $model->fonteFinanziamento->getNomeCompleto() : '-',
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'format' => 'raw',
                                        'value' => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>',
                                    ],
                                    'budget_totale:currency',
                                    'data_inizio:date',
                                    'data_fine:date',
                                    'descrizione:ntext',
                                    'note:ntext',
                                    'created_at:datetime',
                                    'updated_at:datetime',
                                ],
                            ]) ?>
                        </div>
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Riepilogo</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-2">
                                <span class="me-auto">Avvisi/Bandi</span>
                                <span class="badge bg-blue text-blue-fg"><?= count($model->avvisi) ?></span>
                            </div>
                            <div class="d-flex mb-2">
                                <span class="me-auto">Studenti previsti</span>
                                <span class="badge bg-azure text-azure-fg"><?= $model->numero_studenti ?? '-' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabella Avvisi/Bandi -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-speakerphone me-2"></i>Avvisi/Bandi del Progetto</h3>
                    <div class="card-actions">
                        <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Avviso', ['/avviso/create', 'progetto_id' => $model->id], [
                            'class' => 'btn btn-sm btn-success',
                        ]) ?>
                    </div>
                </div>
                <?php if ($model->avvisi): ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th>Codice</th>
                                <th>Titolo</th>
                                <th>Moduli</th>
                                <th>Pubblicazione</th>
                                <th>Scadenza</th>
                                <th>Stato</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($model->avvisi as $avviso): ?>
                            <tr>
                                <td>
                                    <?= Html::a('<span class="badge bg-blue-lt">' . Html::encode($avviso->codice_avviso) . '</span>', ['/avviso/view', 'id' => $avviso->id]) ?>
                                </td>
                                <td><?= Html::a(Html::encode($avviso->titolo), ['/avviso/view', 'id' => $avviso->id]) ?></td>
                                <td><span class="badge bg-cyan-lt"><?= $avviso->getModuliCount() ?></span></td>
                                <td><?= $avviso->data_pubblicazione ? Yii::$app->formatter->asDate($avviso->data_pubblicazione) : '-' ?></td>
                                <td><?= $avviso->data_scadenza ? Yii::$app->formatter->asDate($avviso->data_scadenza) : '-' ?></td>
                                <td><span class="badge <?= $avviso->getStatusBadgeClass() ?>"><?= $avviso->getStatusLabel() ?></span></td>
                                <td class="text-end">
                                    <?= Html::a('<i class="ti ti-eye"></i>', ['/avviso/view', 'id' => $avviso->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Dettaglio']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="card-body text-muted text-center">
                    <i class="ti ti-speakerphone-off mb-2" style="font-size: 2rem;"></i>
                    <p>Nessun avviso collegato a questo progetto</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Rendiconto Economico -->
            <?php
                // Raccolta dati di rendicontazione da tutti i moduli del progetto
                $moduliProgetto = [];
                $totaleContrattualizzato = 0;
                $totalePagatoProgetto = 0;

                foreach ($model->avvisi as $avviso) {
                    foreach ($avviso->moduli as $modulo) {
                        $assegnazione = $modulo->getAssegnazioni()->one();
                        $contratto = $assegnazione ? $assegnazione->contratto : null;
                        $docente = $assegnazione ? $assegnazione->docente : null;

                        $compensoContratto = $contratto ? (float) $contratto->compenso_totale : 0;
                        $pagatoContratto = $contratto ? $contratto->getTotalePagato() : 0;
                        $percModulo = ($compensoContratto > 0) ? round(($pagatoContratto / $compensoContratto) * 100) : 0;

                        $totaleContrattualizzato += $compensoContratto;
                        $totalePagatoProgetto += $pagatoContratto;

                        $moduliProgetto[] = [
                            'modulo' => $modulo,
                            'docente' => $docente,
                            'contratto' => $contratto,
                            'contrattualizzato' => $compensoContratto,
                            'pagato' => $pagatoContratto,
                            'percentuale' => $percModulo,
                        ];
                    }
                }

                $budgetTotale = (float) ($model->budget_totale ?: 0);
                $rimanenteProgetto = $budgetTotale - $totalePagatoProgetto;
                $percBudget = ($budgetTotale > 0) ? round(($totalePagatoProgetto / $budgetTotale) * 100) : 0;
                $percContrattualizzato = ($budgetTotale > 0) ? round(($totaleContrattualizzato / $budgetTotale) * 100) : 0;
            ?>
            <?php if (!empty($moduliProgetto) || $budgetTotale > 0): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-report-money me-2"></i>Rendiconto Economico</h3>
                </div>
                <div class="card-body">
                    <!-- Stat cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-blue-lt me-3"><i class="ti ti-wallet"></i></span>
                                        <div>
                                            <div class="text-muted small">Budget Totale</div>
                                            <div class="fw-bold"><?= Yii::$app->formatter->asCurrency($budgetTotale) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-purple-lt me-3"><i class="ti ti-file-invoice"></i></span>
                                        <div>
                                            <div class="text-muted small">Contrattualizzato</div>
                                            <div class="fw-bold"><?= Yii::$app->formatter->asCurrency($totaleContrattualizzato) ?></div>
                                            <?php if ($budgetTotale > 0): ?>
                                                <small class="text-muted"><?= $percContrattualizzato ?>% del budget</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-green-lt me-3"><i class="ti ti-cash"></i></span>
                                        <div>
                                            <div class="text-muted small">Pagato</div>
                                            <div class="fw-bold text-success"><?= Yii::$app->formatter->asCurrency($totalePagatoProgetto) ?></div>
                                            <?php if ($budgetTotale > 0): ?>
                                                <small class="text-muted"><?= $percBudget ?>% del budget</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-orange-lt me-3"><i class="ti ti-clock-dollar"></i></span>
                                        <div>
                                            <div class="text-muted small">Rimanente</div>
                                            <div class="fw-bold <?= $rimanenteProgetto > 0 ? 'text-warning' : 'text-success' ?>"><?= Yii::$app->formatter->asCurrency($rimanenteProgetto) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress bar complessiva -->
                    <?php if ($budgetTotale > 0): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Avanzamento budget complessivo</small>
                            <small class="fw-bold"><?= $percBudget ?>% pagato</small>
                        </div>
                        <div class="progress progress-separated" style="height: 8px;">
                            <div class="progress-bar bg-green" style="width: <?= min($percBudget, 100) ?>%" title="Pagato"></div>
                            <div class="progress-bar bg-purple-lt" style="width: <?= min(max($percContrattualizzato - $percBudget, 0), 100 - min($percBudget, 100)) ?>%" title="Contrattualizzato non ancora pagato"></div>
                        </div>
                        <div class="d-flex gap-3 mt-1">
                            <small><span class="badge bg-green text-green-fg">&nbsp;</span> Pagato</small>
                            <small><span class="badge bg-purple-lt">&nbsp;</span> Contrattualizzato</small>
                            <small><span class="badge bg-light border">&nbsp;</span> Disponibile</small>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tabella dettaglio moduli -->
                    <?php if (!empty($moduliProgetto)): ?>
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Codice Modulo</th>
                                    <th>Docente</th>
                                    <th class="text-end">Contrattualizzato</th>
                                    <th class="text-end">Pagato</th>
                                    <th style="min-width: 120px;">%</th>
                                    <th>Stato</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($moduliProgetto as $item): ?>
                                <tr>
                                    <td>
                                        <?= Html::a('<span class="badge bg-blue-lt">' . Html::encode($item['modulo']->codice) . '</span>', ['/modulo/view', 'id' => $item['modulo']->id]) ?>
                                    </td>
                                    <td>
                                        <?php if ($item['docente']): ?>
                                            <?= Html::a(Html::encode($item['docente']->cognome . ' ' . $item['docente']->nome), ['/docente/view', 'id' => $item['docente']->codice_fiscale]) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non assegnato</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($item['contratto']): ?>
                                            <?= Yii::$app->formatter->asCurrency($item['contrattualizzato']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($item['contratto']): ?>
                                            <?= Yii::$app->formatter->asCurrency($item['pagato']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['contratto']): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar <?= $item['percentuale'] >= 100 ? 'bg-green' : ($item['percentuale'] > 0 ? 'bg-orange' : 'bg-default') ?>"
                                                     style="width: <?= min($item['percentuale'], 100) ?>%"></div>
                                            </div>
                                            <small class="text-nowrap"><?= $item['percentuale'] ?>%</small>
                                        </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $item['modulo']->getStatusBadgeClass() ?>"><?= $item['modulo']->getStatusLabel() ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="2">Totale</td>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($totaleContrattualizzato) ?></td>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($totalePagatoProgetto) ?></td>
                                    <td>
                                        <?php $percTotale = ($totaleContrattualizzato > 0) ? round(($totalePagatoProgetto / $totaleContrattualizzato) * 100) : 0; ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar <?= $percTotale >= 100 ? 'bg-green' : ($percTotale > 0 ? 'bg-orange' : 'bg-default') ?>"
                                                     style="width: <?= min($percTotale, 100) ?>%"></div>
                                            </div>
                                            <small class="text-nowrap"><?= $percTotale ?>%</small>
                                        </div>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
