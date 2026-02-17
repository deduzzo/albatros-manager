<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\TagCategoria;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TagCurriculumSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Gestione Tag';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tag-curriculum-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-tags me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Tag', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'headerOptions' => ['style' => 'width: 70px;'],
                        ],
                        [
                            'attribute' => 'categoria_id',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 160px;'],
                            'filter' => TagCategoria::getList(),
                            'content' => function($model) {
                                if ($model->categoria) {
                                    return '<span class="badge" style="background-color: ' . Html::encode($model->categoria->colore) . '; color: #fff;">'
                                        . Html::encode($model->categoria->nome) . '</span>';
                                }
                                return '<span class="text-muted">-</span>';
                            },
                        ],
                        [
                            'attribute' => 'codice',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . Html::encode($model->codice) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'nome',
                        ],
                        [
                            'attribute' => 'attivo',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 90px;'],
                            'filter' => [0 => 'No', 1 => 'Si'],
                            'content' => function($model) {
                                return $model->attivo
                                    ? '<span class="badge bg-green text-green-fg">Si</span>'
                                    : '<span class="badge bg-default text-default-fg">No</span>';
                            },
                        ],
                        [
                            'label' => 'Docenti',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 90px;'],
                            'content' => function($model) {
                                $count = $model->getDocentiCount();
                                return '<span class="badge bg-blue-lt">' . $count . '</span>';
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> tag
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
