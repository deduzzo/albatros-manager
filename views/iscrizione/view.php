<?php
use yii\helpers\Html;

$this->title = 'Iscrizione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Iscrizioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="iscrizione-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti ti-user-plus me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => ['confirm' => 'Sei sicuro di voler eliminare questa iscrizione?', 'method' => 'post'],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <!-- Stat Cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Stato</div>
                            <div class="mt-2"><span class="badge <?= $model->getStatusBadgeClass() ?> fs-5"><?= $model->getStatusLabel() ?></span></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Esito Finale</div>
                            <div class="mt-2">
                                <?php if ($model->esito_finale): ?>
                                    <span class="badge bg-<?= $model->esito_finale === 'idoneo' ? 'success' : ($model->esito_finale === 'ritirato' ? 'warning' : 'danger') ?> fs-5"><?= $model->getEsitoLabel() ?></span>
                                <?php else: ?>
                                    <span class="text-muted fs-5">In corso</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Frequenza</div>
                            <?php if ($model->percentuale_frequenza !== null): ?>
                                <div class="h1 mt-2"><?= $model->percentuale_frequenza ?>%</div>
                                <div class="progress progress-sm"><div class="progress-bar" style="width: <?= $model->percentuale_frequenza ?>%"></div></div>
                            <?php else: ?>
                                <div class="h1 mt-2 text-muted">-</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Data Iscrizione</div>
                            <div class="h1 mt-2"><?= Yii::$app->formatter->asDate($model->data_iscrizione) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dettagli -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Dettagli Iscrizione</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ID</div>
                            <div class="datagrid-content"><?= $model->id ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Allievo</div>
                            <div class="datagrid-content">
                                <?= $model->allievo
                                    ? Html::a('<i class="ti ti-school me-1"></i>' . Html::encode($model->allievo->cognome . ' ' . $model->allievo->nome), ['/allievo/view', 'id' => $model->allievo_codice_fiscale])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Modulo</div>
                            <div class="datagrid-content">
                                <?= $model->modulo
                                    ? Html::a('<i class="ti ti-book me-1"></i>' . Html::encode($model->modulo->titolo), ['/modulo/view', 'id' => $model->modulo_id])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Data Iscrizione</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDate($model->data_iscrizione) ?></div>
                        </div>
                        <?php if ($model->data_ritiro): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Data Ritiro</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDate($model->data_ritiro) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->motivo_ritiro): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Motivo Ritiro</div>
                            <div class="datagrid-content"><?= Html::encode($model->motivo_ritiro) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Creato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->note): ?>
            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-notes me-2"></i>Note</h3></div>
                <div class="card-body"><?= nl2br(Html::encode($model->note)) ?></div>
            </div>
            <?php endif; ?>

            <div class="card mt-3"><div class="card-footer"><?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?></div></div>
        </div>
    </div>
</div>
