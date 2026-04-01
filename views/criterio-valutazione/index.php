<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CriterioValutazioneStudenteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Criteri di Valutazione';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="criterio-valutazione-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-star me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">Criteri per la valutazione delle candidature studenti</div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Criterio', ['create'], ['class' => 'btn btn-success']) ?>
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
                        'nome',
                        [
                            'attribute' => 'avviso_id',
                            'label' => 'Corso',
                            'format' => 'raw',
                            'content' => function ($model) {
                                if ($model->isGlobale()) {
                                    return '<span class="badge bg-azure-lt"><i class="ti ti-world me-1"></i>Globale</span>';
                                }
                                return $model->avviso
                                    ? Html::a(Html::encode($model->avviso->titolo), ['/avviso/view', 'id' => $model->avviso_id])
                                    : '<span class="text-muted">Corso #' . $model->avviso_id . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'peso',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'format' => ['decimal', 2],
                        ],
                        [
                            'label' => 'Range Punteggio',
                            'headerOptions' => ['style' => 'width: 140px;'],
                            'format' => 'raw',
                            'content' => function ($model) {
                                return '<span class="badge bg-blue-lt">' . $model->punteggio_min . ' - ' . $model->punteggio_max . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'ordine',
                            'headerOptions' => ['style' => 'width: 80px;'],
                        ],
                        [
                            'attribute' => 'attivo',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'filter' => [0 => 'No', 1 => 'Si'],
                            'content' => function ($model) {
                                return $model->attivo
                                    ? '<span class="badge bg-green text-green-fg">Si</span>'
                                    : '<span class="badge bg-red text-red-fg">No</span>';
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> criteri</p>
                </div>
            </div>
        </div>
    </div>
</div>
