<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Notifica;

/* @var $this yii\web\View */
/* @var $searchModel app\models\NotificaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Notifiche';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="notifica-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-bell me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione notifiche per utenti del sistema
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Notifica', ['create'], ['class' => 'btn btn-primary']) ?>
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
                            'attribute' => 'titolo',
                            'format' => 'raw',
                            'content' => function($model) {
                                return Html::a(
                                    '<i class="ti ti-bell me-1"></i> ' . Html::encode($model->titolo),
                                    ['view', 'id' => $model->id],
                                    ['class' => 'text-reset']
                                );
                            },
                        ],
                        [
                            'attribute' => 'destinatario_tipo',
                            'label' => 'Destinatari',
                            'format' => 'raw',
                            'content' => function($model) {
                                $colors = [
                                    Notifica::TIPO_TUTTI => 'bg-blue-lt',
                                    Notifica::TIPO_ADMIN => 'bg-red-lt',
                                    Notifica::TIPO_DOCENTE => 'bg-green-lt',
                                    Notifica::TIPO_STUDENTE => 'bg-purple-lt',
                                    Notifica::TIPO_SINGOLO_ADMIN => 'bg-red-lt',
                                    Notifica::TIPO_SINGOLO_DOCENTE => 'bg-green-lt',
                                    Notifica::TIPO_SINGOLO_STUDENTE => 'bg-purple-lt',
                                ];
                                $class = $colors[$model->destinatario_tipo] ?? 'bg-default-lt';
                                return '<span class="badge ' . $class . '">' . Html::encode($model->getDestinatariLabel()) . '</span>';
                            },
                            'filter' => Notifica::getDestinatarioTipoOptions(),
                        ],
                        [
                            'attribute' => 'created_at',
                            'label' => 'Data',
                            'format' => 'raw',
                            'content' => function($model) {
                                return $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '-';
                            },
                            'headerOptions' => ['style' => 'width: 160px;'],
                        ],
                        [
                            'attribute' => 'invia_email',
                            'label' => 'Email',
                            'format' => 'raw',
                            'content' => function($model) {
                                if (!$model->invia_email) {
                                    return '<i class="ti ti-minus text-muted" title="Email non richiesta" data-bs-toggle="tooltip"></i>';
                                }
                                if ($model->email_inviata) {
                                    return '<i class="ti ti-check text-green" title="Email inviata" data-bs-toggle="tooltip"></i>';
                                }
                                return '<i class="ti ti-x text-red" title="Email non ancora inviata" data-bs-toggle="tooltip"></i>';
                            },
                            'headerOptions' => ['style' => 'width: 70px;'],
                            'contentOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view} {delete}',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'buttons' => [
                                'view' => function($url, $model) {
                                    return Html::a('<i class="ti ti-eye"></i>', $url, [
                                        'title' => 'Dettaglio',
                                    ]);
                                },
                                'delete' => function($url, $model) {
                                    return Html::a('<i class="ti ti-trash"></i>', $url, [
                                        'title' => 'Elimina',
                                        'data-method' => 'post',
                                        'data-confirm' => 'Sei sicuro di voler eliminare questa notifica?',
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> notifiche
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
