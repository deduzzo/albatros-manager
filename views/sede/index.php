<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SedeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sedi';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="sede-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Sede', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'id',
                            'headerOptions' => ['style' => 'width: 80px;'],
                        ],
                        [
                            'attribute' => 'nome',
                            'format' => 'raw',
                            'value' => function($model) {
                                return Html::a(Html::encode($model->nome), ['view', 'id' => $model->id], ['class' => 'text-reset fw-bold']);
                            },
                        ],
                        [
                            'attribute' => 'citta',
                        ],
                        [
                            'attribute' => 'provincia',
                            'headerOptions' => ['style' => 'width: 100px;'],
                        ],
                        [
                            'attribute' => 'indirizzo',
                        ],
                        [
                            'attribute' => 'telefono',
                            'format' => 'raw',
                            'value' => function($model) {
                                return $model->telefono ? Html::a($model->telefono, 'tel:' . $model->telefono) : '-';
                            },
                        ],
                        [
                            'attribute' => 'email',
                            'format' => 'email',
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> elementi
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
