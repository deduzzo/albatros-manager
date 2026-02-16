<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Pagamento;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PagamentoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Pagamenti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="pagamento-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-currency-euro me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione pagamenti docenti
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Pagamento', ['create'], ['class' => 'btn btn-success']) ?>
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
                                $contratto = $model->contratto;
                                $assegnazione = $contratto ? $contratto->assegnazione : null;
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
                            'attribute' => 'numero_contratto',
                            'label' => 'N. Contratto',
                            'format' => 'raw',
                            'content' => function($model) {
                                if (!$model->contratto) return '-';
                                $label = $model->contratto->numero_contratto ?: '#' . $model->contratto_id;
                                return Html::a(
                                    '<code>' . Html::encode($label) . '</code>',
                                    ['/contratto/view', 'id' => $model->contratto_id],
                                    ['class' => 'text-reset']
                                );
                            },
                        ],
                        [
                            'attribute' => 'tipo',
                            'format' => 'raw',
                            'filter' => Pagamento::getTipoLabels(),
                            'headerOptions' => ['style' => 'width: 110px;'],
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . Html::encode($model->getTipoLabel()) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'importo_lordo',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'text-end'],
                            'content' => function($model) {
                                return Yii::$app->formatter->asCurrency($model->importo_lordo, 'EUR');
                            },
                        ],
                        [
                            'attribute' => 'importo_netto',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'text-end'],
                            'content' => function($model) {
                                return Yii::$app->formatter->asCurrency($model->importo_netto, 'EUR');
                            },
                        ],
                        [
                            'attribute' => 'data_pagamento',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => Pagamento::getStatusLabels(),
                            'content' => function($model) {
                                return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> pagamenti
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
