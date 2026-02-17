<?php
use yii\helpers\Html;

$this->title = 'Presenza #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Presenze Allievi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="presenza-allievo-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti ti-checkbox me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => ['confirm' => 'Sei sicuro di voler eliminare questa presenza?', 'method' => 'post'],
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
                            <div class="subheader">Presenza</div>
                            <div class="mt-2"><span class="badge <?= $model->getPresenzaBadgeClass() ?> fs-5"><?= $model->getPresenzaLabel() ?></span></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Data Lezione</div>
                            <div class="h1 mt-2"><?= $model->calendario ? Yii::$app->formatter->asDate($model->calendario->data) : '-' ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Ore Presenza</div>
                            <div class="h1 mt-2"><?= $model->ore_presenza ? $model->ore_presenza . 'h' : '-' ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Giustificato</div>
                            <div class="mt-2">
                                <?= $model->giustificato
                                    ? '<span class="badge bg-orange text-orange-fg fs-5">Si</span>'
                                    : '<span class="badge bg-default text-default-fg fs-5">No</span>' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dettagli -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Dettagli Presenza</h3></div>
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
                            <div class="datagrid-title">Lezione</div>
                            <div class="datagrid-content">
                                <?= $model->calendario
                                    ? Html::a('<i class="ti ti-calendar me-1"></i>' . Yii::$app->formatter->asDate($model->calendario->data) . ' (' . substr($model->calendario->ora_inizio, 0, 5) . '-' . substr($model->calendario->ora_fine, 0, 5) . ')', ['/calendario/view', 'id' => $model->calendario_id])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Modulo</div>
                            <div class="datagrid-content">
                                <?= $model->calendario && $model->calendario->modulo
                                    ? Html::a(Html::encode($model->calendario->modulo->titolo), ['/modulo/view', 'id' => $model->calendario->modulo_id])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Ora Ingresso</div>
                            <div class="datagrid-content"><?= $model->ora_ingresso ? substr($model->ora_ingresso, 0, 5) : '-' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Ora Uscita</div>
                            <div class="datagrid-content"><?= $model->ora_uscita ? substr($model->ora_uscita, 0, 5) : '-' ?></div>
                        </div>
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
