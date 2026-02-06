<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Modulo;
use app\models\Avviso;
use app\models\Sede;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ModuloSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Moduli/Corsi';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="modulo-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Modulo', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'codice',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . Html::encode($model->codice) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'titolo',
                        ],
                        [
                            'attribute' => 'avviso_id',
                            'value' => function($model) {
                                return $model->avviso ? $model->avviso->codice_avviso : '-';
                            },
                            'filter' => Avviso::getList(),
                            'headerOptions' => ['style' => 'width: 130px;'],
                        ],
                        [
                            'attribute' => 'sede_id',
                            'value' => function($model) {
                                return $model->sede ? $model->sede->nome : '-';
                            },
                            'filter' => Sede::getList(),
                            'headerOptions' => ['style' => 'width: 150px;'],
                        ],
                        [
                            'attribute' => 'ore_totali',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'contentOptions' => ['class' => 'text-end'],
                        ],
                        [
                            'attribute' => 'compenso_orario',
                            'format' => 'currency',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'contentOptions' => ['class' => 'text-end'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'content' => function($model) {
                                $badge = '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>';
                                if ($model->isApertoIscrizioni()) {
                                    $badge .= ' <i class="ti ti-check text-success" title="Aperto iscrizioni"></i>';
                                }
                                return $badge;
                            },
                            'filter' => Modulo::getStatusLabels(),
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> moduli
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
