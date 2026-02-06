<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Avviso;
use app\models\Progetto;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AvvisoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Avvisi/Bandi';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="avviso-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Avviso', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'codice_avviso',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . Html::encode($model->codice_avviso) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'titolo',
                        ],
                        [
                            'attribute' => 'progetto_id',
                            'value' => function($model) {
                                return $model->progetto ? $model->progetto->codice_progetto : '-';
                            },
                            'filter' => Progetto::getList(),
                            'headerOptions' => ['style' => 'width: 140px;'],
                        ],
                        [
                            'attribute' => 'data_scadenza',
                            'format' => ['date', 'php:d/m/Y'],
                            'headerOptions' => ['style' => 'width: 110px;'],
                            'filter' => false,
                            'content' => function($model) {
                                $date = Yii::$app->formatter->asDate($model->data_scadenza, 'php:d/m/Y');
                                if ($model->isAperto()) {
                                    return '<span class="text-success">' . $date . '</span>';
                                } elseif ($model->data_scadenza && strtotime($model->data_scadenza) < strtotime('today')) {
                                    return '<span class="text-muted">' . $date . '</span>';
                                }
                                return $date;
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>';
                            },
                            'filter' => Avviso::getStatusLabels(),
                            'headerOptions' => ['style' => 'width: 110px;'],
                        ],
                        [
                            'label' => 'Moduli',
                            'headerOptions' => ['style' => 'width: 70px;'],
                            'content' => function($model) {
                                return '<span class="badge bg-secondary">' . $model->getModuliCount() . '</span>';
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> avvisi
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
