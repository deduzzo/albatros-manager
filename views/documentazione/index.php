<?php
use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Documentazione;

$this->title = 'Documentazione';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documentazione-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-files me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Gestione documenti docenti e allievi</div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Documento', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'tipo_documento_descrizione',
                            'label' => 'Tipo',
                            'format' => 'raw',
                            'content' => fn($model) => $model->tipoDocumento
                                ? '<span class="badge bg-blue-lt">' . Html::encode($model->tipoDocumento->descrizione) . '</span>'
                                : '<span class="text-muted">-</span>',
                        ],
                        'titolo',
                        [
                            'attribute' => 'docente_nome',
                            'label' => 'Docente',
                            'format' => 'raw',
                            'content' => fn($model) => $model->docente
                                ? Html::a(Html::encode($model->docente->cognome . ' ' . $model->docente->nome), ['/docente/view', 'id' => $model->docente_codice_fiscale])
                                : '<span class="text-muted">-</span>',
                        ],
                        [
                            'attribute' => 'allievo_nome',
                            'label' => 'Allievo',
                            'format' => 'raw',
                            'content' => fn($model) => $model->allievo
                                ? Html::a(Html::encode($model->allievo->cognome . ' ' . $model->allievo->nome), ['/allievo/view', 'id' => $model->allievo_codice_fiscale])
                                : '<span class="text-muted">-</span>',
                        ],
                        [
                            'attribute' => 'data_scadenza',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'filter' => Documentazione::getStatusLabels(),
                            'content' => fn($model) => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>',
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> documenti</p>
                </div>
            </div>
        </div>
    </div>
</div>
