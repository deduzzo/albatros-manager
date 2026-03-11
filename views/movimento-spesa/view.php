<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MovimentoSpesa */

$progetto = $model->progetto;
$voce = $model->voceSpesa;

$this->title = 'Movimento #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Movimenti di Spesa', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="movimento-spesa-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-arrows-exchange me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= $model->getTipoBadge() ?>
                        <?= $model->getRiferimentoBadge() ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?php if (!$model->isAutomatico()): ?>
                        <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Sei sicuro di voler eliminare questo movimento?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <!-- Importo -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Importo</div>
                            <?php $color = $model->tipo === 'spesa' ? 'text-red' : 'text-green'; ?>
                            <div class="h1 mb-0 mt-2 <?= $color ?>">
                                <?= Yii::$app->formatter->asCurrency($model->importo, 'EUR') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Data Movimento</div>
                            <div class="h1 mb-0 mt-2">
                                <?= Yii::$app->formatter->asDate($model->data_movimento, 'long') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Tipo</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->getTipoBadge() ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dettagli -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-info-circle me-2"></i>
                                Dettagli Movimento
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">ID</div>
                                    <div class="datagrid-content"><?= $model->id ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Descrizione</div>
                                    <div class="datagrid-content"><?= Html::encode($model->descrizione) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Fornitore</div>
                                    <div class="datagrid-content"><?= Html::encode($model->fornitore) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">N. Documento</div>
                                    <div class="datagrid-content"><?= Html::encode($model->numero_documento) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Documento</div>
                                    <div class="datagrid-content">
                                        <?= $model->data_documento ? Yii::$app->formatter->asDate($model->data_documento, 'long') : '-' ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Riferimento</div>
                                    <div class="datagrid-content"><?= $model->getRiferimentoBadge() ?></div>
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

                <!-- Riferimenti -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-link me-2"></i>
                                Riferimenti
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
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
                                    <div class="datagrid-title">Voce di Spesa</div>
                                    <div class="datagrid-content">
                                        <?php if ($voce): ?>
                                            <span class="badge bg-blue-lt"><?= Html::encode($voce->getLabelCompleta()) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($model->allegato_path): ?>
                                <hr>
                                <div class="mb-2"><strong>Allegato:</strong></div>
                                <?= Html::a(
                                    '<i class="ti ti-download me-1"></i> Scarica allegato',
                                    Yii::getAlias('@web') . '/' . $model->allegato_path,
                                    ['class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank']
                                ) ?>
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
