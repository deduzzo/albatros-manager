<?php
use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Calendario;

$this->title = 'Calendario Lezioni';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="calendario-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-calendar me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Gestione lezioni programmate</div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Lezione', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'data',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'modulo_titolo',
                            'label' => 'Modulo',
                            'format' => 'raw',
                            'content' => fn($model) => $model->modulo
                                ? Html::a(Html::encode($model->modulo->titolo), ['/modulo/view', 'id' => $model->modulo_id])
                                : '<span class="text-muted">-</span>',
                        ],
                        [
                            'attribute' => 'docente_nome',
                            'label' => 'Docente',
                            'format' => 'raw',
                            'content' => fn($model) => $model->docente
                                ? Html::a(Html::encode($model->docente->cognome . ' ' . $model->docente->nome), ['/docente/view', 'id' => $model->docente_codice_fiscale])
                                : '<span class="text-muted">-</span>',
                        ],
                        [
                            'label' => 'Orario',
                            'format' => 'raw',
                            'content' => fn($model) => substr($model->ora_inizio, 0, 5) . ' - ' . substr($model->ora_fine, 0, 5),
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'ore',
                            'headerOptions' => ['style' => 'width: 70px;'],
                            'content' => fn($model) => $model->ore . 'h',
                        ],
                        'aula',
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => Calendario::getStatusLabels(),
                            'content' => fn($model) => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>',
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> lezioni</p>
                </div>
            </div>
        </div>
    </div>
</div>
