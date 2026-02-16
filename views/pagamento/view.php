<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Pagamento */

$contratto = $model->contratto;
$assegnazione = $contratto ? $contratto->assegnazione : null;
$docente = $assegnazione ? $assegnazione->docente : null;
$docenteNome = $docente ? $docente->cognome . ' ' . $docente->nome : 'N/D';

$this->title = 'Pagamento #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Pagamenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="pagamento-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-currency-euro me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($docenteNome) ?>
                        <?php if ($contratto && $contratto->numero_contratto): ?>
                            &mdash; Contratto: <code><?= Html::encode($contratto->numero_contratto) ?></code>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo pagamento?',
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
                            <div class="subheader">Importo Lordo</div>
                            <div class="h1 mb-0 mt-2">
                                <?= Yii::$app->formatter->asCurrency($model->importo_lordo, 'EUR') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Ritenute</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->ritenute !== null ? Yii::$app->formatter->asCurrency($model->ritenute, 'EUR') : '-' ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Importo Netto</div>
                            <div class="h1 mb-0 mt-2">
                                <strong><?= Yii::$app->formatter->asCurrency($model->importo_netto, 'EUR') ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dettagli pagamento -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-info-circle me-2"></i>
                                Dettagli Pagamento
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">ID</div>
                                    <div class="datagrid-content"><?= $model->id ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Tipo</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-blue-lt"><?= Html::encode($model->getTipoLabel()) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Pagamento</div>
                                    <div class="datagrid-content">
                                        <?= $model->data_pagamento ? Yii::$app->formatter->asDate($model->data_pagamento, 'long') : '-' ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Scadenza</div>
                                    <div class="datagrid-content">
                                        <?= $model->data_scadenza ? Yii::$app->formatter->asDate($model->data_scadenza, 'long') : '-' ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Riferimento Fattura</div>
                                    <div class="datagrid-content"><?= Html::encode($model->riferimento_fattura) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Numero Mandato</div>
                                    <div class="datagrid-content"><?= Html::encode($model->numero_mandato) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">CRO Bonifico</div>
                                    <div class="datagrid-content">
                                        <?= $model->cro_bonifico ? '<code>' . Html::encode($model->cro_bonifico) . '</code>' : '-' ?>
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
                                    <div class="datagrid-title">Contratto</div>
                                    <div class="datagrid-content">
                                        <?php if ($contratto): ?>
                                            <?= Html::a(
                                                'Contratto #' . $contratto->id . ($contratto->numero_contratto ? ' (' . $contratto->numero_contratto . ')' : ''),
                                                ['/contratto/view', 'id' => $contratto->id]
                                            ) ?>
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
                                    <div class="datagrid-title">Assegnazione</div>
                                    <div class="datagrid-content">
                                        <?php if ($assegnazione): ?>
                                            <?= Html::a('Assegnazione #' . $assegnazione->id, ['/assegnazione/view', 'id' => $assegnazione->id]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer con azioni -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                            <?php if ($contratto): ?>
                                <?= Html::a('<i class="ti ti-file-text me-1"></i> Vai al Contratto', ['/contratto/view', 'id' => $contratto->id], ['class' => 'btn btn-info ms-2']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
