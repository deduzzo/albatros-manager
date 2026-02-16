<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Contratto */

$assegnazione = $model->assegnazione;
$docente = $assegnazione ? $assegnazione->docente : null;
$modulo = $assegnazione ? $assegnazione->modulo : null;
$docenteNome = $docente ? $docente->cognome . ' ' . $docente->nome : 'N/D';

$this->title = 'Contratto #' . $model->id . ($model->numero_contratto ? ' (' . $model->numero_contratto . ')' : '');
$this->params['breadcrumbs'][] = ['label' => 'Contratti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="contratto-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-file-text me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($docenteNome) ?>
                        <?php if ($modulo): ?>
                            &mdash; <?= Html::encode($modulo->titolo) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo contratto?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <!-- Riepilogo rapido -->
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Stato</div>
                            <div class="h1 mb-0 mt-2">
                                <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Compenso Totale</div>
                            <div class="h1 mb-0 mt-2">
                                <?= Yii::$app->formatter->asCurrency($model->compenso_totale, 'EUR') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Ore</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->ore_contrattualizzate ?>
                            </div>
                            <div class="text-muted small mt-1">
                                a <?= Yii::$app->formatter->asCurrency($model->compenso_orario, 'EUR') ?>/ora
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Lezioni</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->getGiorniRimanentiBadge() ?>
                            </div>
                            <div class="text-muted small mt-1">
                                giorni rimanenti
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dettagli contratto -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-info-circle me-2"></i>
                                Dettagli Contratto
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Numero Contratto</div>
                                    <div class="datagrid-content">
                                        <?= $model->numero_contratto ? '<code>' . Html::encode($model->numero_contratto) . '</code>' : '-' ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Tipo</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-blue-lt"><?= Html::encode($model->getTipoLabel()) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Stipula</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asDate($model->data_stipula, 'long') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Periodo Contratto</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asDate($model->data_inizio, 'medium') ?>
                                        &rarr;
                                        <?= Yii::$app->formatter->asDate($model->data_fine, 'medium') ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Periodo Lezioni</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->data_inizio_lezioni): ?>
                                            <?= Yii::$app->formatter->asDate($model->data_inizio_lezioni, 'medium') ?>
                                            &rarr;
                                            <?= $model->data_fine_lezioni ? Yii::$app->formatter->asDate($model->data_fine_lezioni, 'medium') : 'N/D' ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Creato il</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                                </div>
                            </div>
                            <?php if ($model->note): ?>
                                <hr>
                                <div class="mb-2"><strong>Note:</strong></div>
                                <p class="text-muted"><?= nl2br(Html::encode($model->note)) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Dati economici e riferimenti -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-currency-euro me-2"></i>
                                Dati Economici
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Compenso Orario</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asCurrency($model->compenso_orario, 'EUR') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ore Contrattualizzate</div>
                                    <div class="datagrid-content"><?= $model->ore_contrattualizzate ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Compenso Totale</div>
                                    <div class="datagrid-content"><strong><?= Yii::$app->formatter->asCurrency($model->compenso_totale, 'EUR') ?></strong></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ritenute</div>
                                    <div class="datagrid-content"><?= $model->ritenute !== null ? Yii::$app->formatter->asCurrency($model->ritenute, 'EUR') : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Netto Docente</div>
                                    <div class="datagrid-content"><?= $model->netto_docente !== null ? Yii::$app->formatter->asCurrency($model->netto_docente, 'EUR') : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Totale Pagato</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asCurrency($model->getTotalePagato(), 'EUR') ?>
                                        <?php if ($model->isSaldato()): ?>
                                            <span class="badge bg-success ms-1">Saldato</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Saldo Rimanente</div>
                                    <div class="datagrid-content">
                                        <?php $saldo = $model->getSaldoRimanente(); ?>
                                        <span class="<?= $saldo > 0 ? 'text-danger' : 'text-success' ?>">
                                            <?= Yii::$app->formatter->asCurrency($saldo, 'EUR') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riferimenti -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-link me-2"></i>
                                Riferimenti
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Assegnazione</div>
                                    <div class="datagrid-content">
                                        <?php if ($assegnazione): ?>
                                            <?= Html::a('Assegnazione #' . $assegnazione->id, ['/assegnazione/view', 'id' => $assegnazione->id]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Docente</div>
                                    <div class="datagrid-content">
                                        <?php if ($docente): ?>
                                            <?= Html::a(Html::encode($docente->cognome . ' ' . $docente->nome), ['/docente/view', 'id' => $docente->codice_fiscale]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Modulo</div>
                                    <div class="datagrid-content">
                                        <?php if ($modulo): ?>
                                            <?= Html::a(Html::encode($modulo->codice . ' - ' . $modulo->titolo), ['/modulo/view', 'id' => $modulo->id]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagamenti -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-currency-euro me-2"></i>
                                Pagamenti
                            </h3>
                            <div class="card-actions">
                                <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Pagamento', ['/pagamento/create', 'contratto_id' => $model->id], ['class' => 'btn btn-sm btn-success']) ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php $pagamenti = $model->pagamenti; ?>
                            <?php if (empty($pagamenti)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="ti ti-currency-euro" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Nessun pagamento registrato per questo contratto.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tipo</th>
                                                <th>Importo Lordo</th>
                                                <th>Importo Netto</th>
                                                <th>Data Pagamento</th>
                                                <th>Stato</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pagamenti as $pagamento): ?>
                                                <tr>
                                                    <td><?= $pagamento->id ?></td>
                                                    <td><span class="badge bg-blue-lt"><?= Html::encode($pagamento->getTipoLabel()) ?></span></td>
                                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($pagamento->importo_lordo, 'EUR') ?></td>
                                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($pagamento->importo_netto, 'EUR') ?></td>
                                                    <td><?= $pagamento->data_pagamento ? Yii::$app->formatter->asDate($pagamento->data_pagamento) : '-' ?></td>
                                                    <td><span class="badge <?= $pagamento->getStatusBadgeClass() ?>"><?= Html::encode($pagamento->getStatusLabel()) ?></span></td>
                                                    <td>
                                                        <?= Html::a('<i class="ti ti-eye"></i>', ['/pagamento/view', 'id' => $pagamento->id], [
                                                            'class' => 'btn btn-info btn-sm',
                                                            'title' => 'Visualizza',
                                                        ]) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer con azioni -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
