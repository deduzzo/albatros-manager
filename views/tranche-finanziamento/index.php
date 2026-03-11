<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\TrancheFinanziamento;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TrancheFinanziamentoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $progetti array */

$this->title = 'Tranche di Finanziamento';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tranche-finanziamento-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-coins me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione tranche e acconti di finanziamento
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Tranche', ['create'], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filtro Progetto -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Progetto</label>
                            <?= Html::dropDownList('TrancheFinanziamentoSearch[progetto_id]', $searchModel->progetto_id, $progetti, [
                                'class' => 'form-select form-select-sm',
                                'prompt' => 'Tutti i progetti',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Stato</label>
                            <?= Html::dropDownList('TrancheFinanziamentoSearch[status]', $searchModel->status, TrancheFinanziamento::getStatusLabels(), [
                                'class' => 'form-select form-select-sm',
                                'prompt' => 'Tutti',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="ti ti-search me-1"></i> Filtra
                            </button>
                            <?= Html::a('<i class="ti ti-filter-off me-1"></i> Reset', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <?= AppGridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'headerOptions' => ['style' => 'width: 60px;'],
                        ],
                        [
                            'attribute' => 'progetto_id',
                            'format' => 'raw',
                            'filter' => $progetti,
                            'content' => function ($model) {
                                $p = $model->progetto;
                                if (!$p) return '-';
                                return Html::a(Html::encode($p->codice_progetto), ['/progetto/view', 'id' => $p->id], [
                                    'class' => 'text-reset',
                                    'title' => $p->titolo,
                                    'data-bs-toggle' => 'tooltip',
                                ]);
                            },
                        ],
                        [
                            'attribute' => 'numero_tranche',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'content' => function ($model) {
                                return $model->numero_tranche ? '<span class="badge bg-blue-lt">#' . $model->numero_tranche . '</span>' : '-';
                            },
                        ],
                        [
                            'attribute' => 'riferimento',
                            'content' => function ($model) {
                                return Html::encode($model->riferimento) ?: '-';
                            },
                        ],
                        [
                            'attribute' => 'importo',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'contentOptions' => ['class' => 'text-end fw-bold'],
                            'content' => function ($model) {
                                return Yii::$app->formatter->asCurrency($model->importo, 'EUR');
                            },
                        ],
                        [
                            'attribute' => 'data_prevista',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'data_accredito',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'filter' => TrancheFinanziamento::getStatusLabels(),
                            'headerOptions' => ['style' => 'width: 110px;'],
                            'content' => function ($model) {
                                return $model->getStatusBadge();
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> tranche
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
