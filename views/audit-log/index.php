<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppGridView;
use app\models\AuditLogSearch;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AuditLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Registro Attivita';
$this->params['breadcrumbs'][] = 'Audit Log';
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto">
                <h2 class="page-title">
                    <i class="ti ti-activity me-2"></i><?= Html::encode($this->title) ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <!-- Filtri date -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-filter me-2"></i>Filtri Data</h3>
            </div>
            <div class="card-body">
                <?php $form = \yii\widgets\ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'options' => ['class' => 'row g-3 align-items-end'],
                ]); ?>
                    <div class="col-sm-4 col-lg-3">
                        <label class="form-label">Data Da</label>
                        <input type="date" class="form-control" name="AuditLogSearch[date_from]" value="<?= Html::encode($searchModel->date_from) ?>">
                    </div>
                    <div class="col-sm-4 col-lg-3">
                        <label class="form-label">Data A</label>
                        <input type="date" class="form-control" name="AuditLogSearch[date_to]" value="<?= Html::encode($searchModel->date_to) ?>">
                    </div>
                    <div class="col-auto">
                        <?= Html::submitButton('<i class="ti ti-search me-1"></i> Filtra', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('<i class="ti ti-x me-1"></i> Reset', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>

        <!-- Tabella -->
        <div class="card">
            <div class="card-body">
                <?= AppGridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'headerOptions' => ['style' => 'width: 70px;'],
                        ],
                        [
                            'attribute' => 'table_name',
                            'filter' => array_combine(
                                AuditLogSearch::getTableNameList(),
                                AuditLogSearch::getTableNameList()
                            ),
                            'headerOptions' => ['style' => 'width: 140px;'],
                        ],
                        [
                            'attribute' => 'action',
                            'format' => 'raw',
                            'filter' => AuditLogSearch::getActionList(),
                            'content' => function ($model) {
                                return $model->getActionBadge();
                            },
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'record_id',
                            'headerOptions' => ['style' => 'width: 100px;'],
                        ],
                        [
                            'attribute' => 'username',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'ip_address',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'created_at',
                            'format' => ['datetime', 'php:d/m/Y H:i:s'],
                            'filter' => false,
                            'headerOptions' => ['style' => 'width: 160px;'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a('<i class="ti ti-eye"></i>', $url, [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'title' => 'Dettaglio',
                                    ]);
                                },
                            ],
                            'headerOptions' => ['style' => 'width: 60px;'],
                        ],
                    ],
                ]) ?>
            </div>
        </div>

    </div>
</div>
