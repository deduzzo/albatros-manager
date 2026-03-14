<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Contratto;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ContrattoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Contratti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="contratto-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-file-text me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione contratti docenti
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Contratto', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'numero_contratto',
                            'format' => 'raw',
                            'content' => function($model) {
                                return $model->numero_contratto
                                    ? '<code>' . Html::encode($model->numero_contratto) . '</code>'
                                    : '<span class="text-muted">-</span>';
                            },
                        ],
                        [
                            'attribute' => 'docente_nome',
                            'label' => 'Docente',
                            'format' => 'raw',
                            'content' => function($model) {
                                $assegnazione = $model->assegnazione;
                                if (!$assegnazione || !$assegnazione->docente) return '-';
                                return Html::a(
                                    Html::encode($assegnazione->docente->cognome . ' ' . $assegnazione->docente->nome),
                                    ['/docente/view', 'id' => $assegnazione->docente_codice_fiscale],
                                    ['class' => 'text-reset']
                                );
                            },
                        ],
                        [
                            'attribute' => 'tipo',
                            'format' => 'raw',
                            'filter' => Contratto::getTipoList(),
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . Html::encode($model->getTipoLabel()) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'data_stipula',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'compenso_totale',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'contentOptions' => ['class' => 'text-end'],
                            'content' => function($model) {
                                return Yii::$app->formatter->asCurrency($model->compenso_totale, 'EUR');
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => Contratto::getStatusLabels(),
                            'content' => function($model) {
                                return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                            },
                        ],
                        [
                            'label' => 'Periodo Lezioni',
                            'headerOptions' => ['style' => 'width: 140px;'],
                            'format' => 'raw',
                            'content' => function($model) {
                                if (!$model->data_inizio_lezioni && !$model->data_fine_lezioni) {
                                    return '<span class="text-muted">-</span>';
                                }
                                $inizio = $model->data_inizio_lezioni ? Yii::$app->formatter->asDate($model->data_inizio_lezioni, 'php:d/m/y') : '?';
                                $fine = $model->data_fine_lezioni ? Yii::$app->formatter->asDate($model->data_fine_lezioni, 'php:d/m/y') : '?';
                                return '<span class="small">' . $inizio . ' - ' . $fine . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'ore_contrattualizzate',
                            'label' => 'Ore',
                            'headerOptions' => ['style' => 'width: 70px;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . (int) $model->ore_contrattualizzate . 'h</span>';
                            },
                        ],
                        [
                            'label' => 'Saldo',
                            'headerOptions' => ['style' => 'width: 110px;'],
                            'contentOptions' => ['class' => 'text-end'],
                            'format' => 'raw',
                            'content' => function($model) {
                                $saldo = $model->getSaldoRimanente();
                                if (abs($saldo) < 0.01) {
                                    return '<span class="badge bg-green-lt"><i class="ti ti-check me-1"></i>Saldato</span>';
                                }
                                return '<span class="text-orange fw-bold">' . Yii::$app->formatter->asCurrency($saldo, 'EUR') . '</span>';
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> contratti
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
