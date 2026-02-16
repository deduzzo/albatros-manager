<?php
use yii\helpers\Html;
use app\components\widgets\AppGridView;

$this->title = 'Tipi Documento';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tipo-documento-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-file-text me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Gestione tipologie di documenti</div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Tipo', ['create'], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <?= AppGridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['attribute' => 'id', 'headerOptions' => ['style' => 'width: 70px;']],
                        [
                            'attribute' => 'codice',
                            'format' => 'raw',
                            'content' => fn($model) => '<span class="badge bg-blue-lt">' . Html::encode($model->codice) . '</span>',
                        ],
                        'descrizione',
                        [
                            'attribute' => 'richiede_scadenza',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 140px;'],
                            'filter' => [0 => 'No', 1 => 'Si'],
                            'content' => fn($model) => $model->richiede_scadenza
                                ? '<span class="badge bg-warning">Si</span>'
                                : '<span class="badge bg-secondary">No</span>',
                        ],
                        [
                            'attribute' => 'attivo',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'filter' => [0 => 'No', 1 => 'Si'],
                            'content' => fn($model) => $model->attivo
                                ? '<span class="badge bg-success">Attivo</span>'
                                : '<span class="badge bg-danger">Disattivo</span>',
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> tipi documento</p>
                </div>
            </div>
        </div>
    </div>
</div>
