<?php
use yii\helpers\Html;
use app\components\widgets\AppGridView;

$this->title = 'Presenze Allievi';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="presenza-allievo-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-checkbox me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Registro presenze per lezione</div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Registra Presenza', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'calendario_data',
                            'label' => 'Data Lezione',
                            'format' => 'raw',
                            'content' => fn($model) => $model->calendario
                                ? Html::a(Yii::$app->formatter->asDate($model->calendario->data), ['/calendario/view', 'id' => $model->calendario_id])
                                : '-',
                        ],
                        [
                            'attribute' => 'modulo_titolo',
                            'label' => 'Modulo',
                            'format' => 'raw',
                            'content' => fn($model) => $model->calendario && $model->calendario->modulo
                                ? Html::encode($model->calendario->modulo->titolo)
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
                            'attribute' => 'presente',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => [0 => 'Assente', 1 => 'Presente'],
                            'content' => fn($model) => '<span class="badge ' . $model->getPresenzaBadgeClass() . '">' . $model->getPresenzaLabel() . '</span>',
                        ],
                        [
                            'label' => 'Orario',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'content' => fn($model) => ($model->ora_ingresso ? substr($model->ora_ingresso, 0, 5) : '-')
                                . ' - ' . ($model->ora_uscita ? substr($model->ora_uscita, 0, 5) : '-'),
                        ],
                        [
                            'attribute' => 'ore_presenza',
                            'headerOptions' => ['style' => 'width: 70px;'],
                            'content' => fn($model) => $model->ore_presenza ? $model->ore_presenza . 'h' : '-',
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> presenze</p>
                </div>
            </div>
        </div>
    </div>
</div>
