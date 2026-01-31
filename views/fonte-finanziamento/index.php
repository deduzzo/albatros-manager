<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\FonteFinanziamentoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Fonti di Finanziamento';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="fonte-finanziamento-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Fonte', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'codice',
                            'headerOptions' => ['style' => 'width: 150px;'],
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . Html::encode($model->codice) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'descrizione',
                        ],
                        [
                            'attribute' => 'note',
                            'format' => 'ntext',
                            'value' => function($model) {
                                return $model->note ? mb_substr($model->note, 0, 50) . (mb_strlen($model->note) > 50 ? '...' : '') : '-';
                            },
                        ],
                        [
                            'attribute' => 'created_at',
                            'format' => ['date', 'php:d/m/Y'],
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => false, // Date non filtrabili via testo
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
