<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\RimborsoSpesa;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RimborsoSpesaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Rimborsi Spesa';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rimborso-spesa-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-receipt-2 me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione rimborsi spese docenti
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Rimborso', ['create'], ['class' => 'btn btn-primary']) ?>
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
                            'attribute' => 'docente_nome',
                            'label' => 'Docente',
                            'format' => 'raw',
                            'content' => function($model) {
                                $assegnazione = $model->assegnazione;
                                $docente = $assegnazione ? $assegnazione->docente : null;
                                if (!$docente) return '-';
                                return Html::a(
                                    Html::encode($docente->cognome . ' ' . $docente->nome),
                                    ['/docente/view', 'id' => $docente->codice_fiscale],
                                    ['class' => 'text-reset']
                                );
                            },
                        ],
                        [
                            'attribute' => 'modulo_nome',
                            'label' => 'Modulo',
                            'format' => 'raw',
                            'content' => function($model) {
                                $assegnazione = $model->assegnazione;
                                $modulo = $assegnazione ? $assegnazione->modulo : null;
                                if (!$modulo) return '-';
                                return '<span class="badge bg-azure-lt">' . Html::encode($modulo->codice) . '</span>';
                            },
                        ],
                        [
                            'label' => 'Voci',
                            'format' => 'raw',
                            'content' => function($model) {
                                return $model->getDettagliRiepilogo();
                            },
                        ],
                        [
                            'label' => 'Totale',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'text-end fw-bold'],
                            'content' => function($model) {
                                return Yii::$app->formatter->asCurrency($model->getTotaleImporto(), 'EUR');
                            },
                        ],
                        [
                            'attribute' => 'data_spesa',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => RimborsoSpesa::getStatusLabels(),
                            'content' => function($model) {
                                return $model->getStatusBadge();
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view} {update}',
                            'buttons' => [
                                'update' => function($url, $model) {
                                    return Html::a('<i class="ti ti-edit"></i>', $url, [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'title' => 'Modifica',
                                    ]);
                                },
                                'view' => function($url, $model) {
                                    return Html::a('<i class="ti ti-eye"></i>', $url, [
                                        'class' => 'btn btn-sm btn-outline-blue me-1',
                                        'title' => 'Visualizza',
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> rimborsi
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
