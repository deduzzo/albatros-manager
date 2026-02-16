<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Assegnazione */

$docenteNome = $model->docente ? $model->docente->cognome . ' ' . $model->docente->nome : 'N/D';
$moduloTitolo = $model->modulo ? $model->modulo->titolo : 'N/D';

$this->title = 'Assegnazione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Assegnazioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="assegnazione-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-circle-check me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($docenteNome) ?> &rarr; <?= Html::encode($moduloTitolo) ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questa assegnazione?',
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
                <!-- Dati Assegnazione -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-info-circle me-2"></i>
                                Dettagli Assegnazione
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">ID</div>
                                    <div class="datagrid-content"><?= $model->id ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Stato</div>
                                    <div class="datagrid-content">
                                        <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Assegnazione</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asDate($model->data_assegnazione, 'long') ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Delibera CTS</div>
                                    <div class="datagrid-content"><?= Html::encode($model->delibera_cts) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Delibera</div>
                                    <div class="datagrid-content">
                                        <?= $model->data_delibera ? Yii::$app->formatter->asDate($model->data_delibera, 'long') : '-' ?>
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
                                    <div class="datagrid-title">Docente</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->docente): ?>
                                            <?= Html::a(
                                                Html::encode($model->docente->cognome . ' ' . $model->docente->nome),
                                                ['/docente/view', 'id' => $model->docente_codice_fiscale]
                                            ) ?>
                                            <br><small class="text-muted"><code><?= Html::encode($model->docente_codice_fiscale) ?></code></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Modulo</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->modulo): ?>
                                            <?= Html::a(
                                                Html::encode($model->modulo->codice . ' - ' . $model->modulo->titolo),
                                                ['/modulo/view', 'id' => $model->modulo_id]
                                            ) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Istanza</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->istanza): ?>
                                            <?= Html::a(
                                                'Istanza #' . $model->istanza_id,
                                                ['/istanza/view', 'id' => $model->istanza_id]
                                            ) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Contratto</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->contratto): ?>
                                            <?= Html::a(
                                                'Contratto #' . $model->contratto->id . ($model->contratto->numero_contratto ? ' (' . $model->contratto->numero_contratto . ')' : ''),
                                                ['/contratto/view', 'id' => $model->contratto->id]
                                            ) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Nessun contratto associato</span>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
