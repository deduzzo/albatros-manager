<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\TrancheFinanziamento */

$progetto = $model->progetto;

$this->title = 'Tranche #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tranche di Finanziamento', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tranche-finanziamento-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-coins me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= $model->getLabel() ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questa tranche?',
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
                <!-- Stat cards -->
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Stato</div>
                            <div class="h1 mb-0 mt-2"><?= $model->getStatusBadge() ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Importo</div>
                            <div class="h1 mb-0 mt-2"><?= Yii::$app->formatter->asCurrency($model->importo, 'EUR') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Data Prevista</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->data_prevista ? Yii::$app->formatter->asDate($model->data_prevista) : '-' ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Data Accredito</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->data_accredito ? Yii::$app->formatter->asDate($model->data_accredito) : '-' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dettagli -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Dettagli</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">ID</div>
                                    <div class="datagrid-content"><?= $model->id ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">N. Tranche</div>
                                    <div class="datagrid-content"><?= $model->numero_tranche ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Riferimento</div>
                                    <div class="datagrid-content"><?= Html::encode($model->riferimento) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Progetto</div>
                                    <div class="datagrid-content">
                                        <?php if ($progetto): ?>
                                            <?= Html::a(Html::encode($progetto->codice_progetto . ' - ' . $progetto->titolo), ['/progetto/view', 'id' => $progetto->id]) ?>
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

                <!-- Footer -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                            <?php if ($progetto): ?>
                                <?= Html::a('<i class="ti ti-chart-bar me-1"></i> Bilancio Progetto', ['/bilancio/progetto', 'id' => $progetto->id], ['class' => 'btn btn-info ms-2']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
