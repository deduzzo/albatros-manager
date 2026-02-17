<?php
use yii\helpers\Html;

$this->title = 'Tipo Documento: ' . $model->descrizione;
$this->params['breadcrumbs'][] = ['label' => 'Tipi Documento', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tipo-documento-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti ti-file-text me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => ['confirm' => 'Sei sicuro di voler eliminare questo tipo documento?', 'method' => 'post'],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Dettagli</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ID</div>
                            <div class="datagrid-content"><?= $model->id ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Codice</div>
                            <div class="datagrid-content"><span class="badge bg-blue-lt"><?= Html::encode($model->codice) ?></span></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Descrizione</div>
                            <div class="datagrid-content"><?= Html::encode($model->descrizione) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Richiede Scadenza</div>
                            <div class="datagrid-content"><?= $model->richiede_scadenza ? '<span class="badge bg-orange text-orange-fg">Si</span>' : '<span class="badge bg-default text-default-fg">No</span>' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Attivo</div>
                            <div class="datagrid-content"><?= $model->attivo ? '<span class="badge bg-green text-green-fg">Attivo</span>' : '<span class="badge bg-red text-red-fg">Disattivo</span>' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Creato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3"><div class="card-footer"><?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?></div></div>
        </div>
    </div>
</div>
