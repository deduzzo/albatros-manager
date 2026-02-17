<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TagCategoriaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Categorie Tag';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tag-categoria-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-category me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Categoria', ['create'], ['class' => 'btn btn-success']) ?>
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
                        ],
                        [
                            'attribute' => 'colore',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'content' => function($model) {
                                return '<span class="badge" style="background-color: ' . Html::encode($model->colore) . '; color: #fff;">' . Html::encode($model->colore) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'icona',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'content' => function($model) {
                                if ($model->icona) {
                                    return '<i class="' . Html::encode($model->icona) . ' me-1"></i> <small class="text-muted">' . Html::encode($model->icona) . '</small>';
                                }
                                return '<span class="text-muted">-</span>';
                            },
                        ],
                        [
                            'attribute' => 'ordine',
                            'headerOptions' => ['style' => 'width: 100px;'],
                        ],
                        [
                            'label' => 'Tag',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'content' => function($model) {
                                $count = $model->getTagCount();
                                return '<span class="badge bg-blue-lt">' . $count . '</span>';
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> categorie
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
