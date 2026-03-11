<?php
use yii\helpers\Html;
use app\components\widgets\AppGridView;

$this->title = 'Criteri Valutazione Docenti';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="criterio-valutazione-docente-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-star-half-filled me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Gestisci i criteri utilizzati nelle valutazioni dei docenti</div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Valutazioni', ['/valutazione-docente/index'], ['class' => 'btn btn-secondary me-2']) ?>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Criterio', ['create'], ['class' => 'btn btn-success']) ?>
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
                        [
                            'attribute' => 'ordinamento',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'content' => fn($model) => '<span class="badge bg-blue-lt">' . $model->ordinamento . '</span>',
                        ],
                        [
                            'attribute' => 'codice',
                            'headerOptions' => ['style' => 'width: 200px;'],
                            'format' => 'raw',
                            'content' => fn($model) => '<code>' . Html::encode($model->codice) . '</code>',
                        ],
                        'nome',
                        [
                            'attribute' => 'descrizione',
                            'format' => 'raw',
                            'content' => function ($model) {
                                $desc = $model->descrizione;
                                if (mb_strlen($desc) > 80) {
                                    $desc = mb_substr($desc, 0, 80) . '...';
                                }
                                return '<span class="text-muted small">' . Html::encode($desc) . '</span>';
                            },
                        ],
                        [
                            'label' => 'Scala',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'content' => fn($model) => $model->scala_min . ' - ' . $model->scala_max,
                        ],
                        [
                            'attribute' => 'attivo',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'filter' => [0 => 'No', 1 => 'Si'],
                            'content' => fn($model) => $model->getAttivoBadge(),
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{toggle} {update} {delete}',
                            'headerOptions' => ['style' => 'width: 140px;'],
                            'buttons' => [
                                'toggle' => function ($url, $model) {
                                    $icon = $model->attivo ? 'ti-toggle-right' : 'ti-toggle-left';
                                    $title = $model->attivo ? 'Disattiva' : 'Attiva';
                                    $class = $model->attivo ? 'btn-orange' : 'btn-green';
                                    return Html::a('<i class="ti ' . $icon . '"></i>', ['toggle-attivo', 'id' => $model->id], [
                                        'class' => 'btn ' . $class . ' btn-sm',
                                        'title' => $title,
                                        'data-bs-toggle' => 'tooltip',
                                        'data-method' => 'post',
                                    ]);
                                },
                                'update' => function ($url, $model) {
                                    return Html::a('<i class="ti ti-edit"></i>', $url, [
                                        'class' => 'btn btn-primary btn-sm',
                                        'title' => 'Modifica',
                                        'data-bs-toggle' => 'tooltip',
                                    ]);
                                },
                                'delete' => function ($url, $model) {
                                    return Html::a('<i class="ti ti-trash"></i>', $url, [
                                        'class' => 'btn btn-danger btn-sm',
                                        'title' => 'Elimina',
                                        'data-bs-toggle' => 'tooltip',
                                        'data-confirm' => 'Sei sicuro di voler eliminare questo criterio?',
                                        'data-method' => 'post',
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> criteri</p>
                </div>
            </div>
        </div>
    </div>
</div>
