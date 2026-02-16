<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Assegnazione;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AssegnazioneSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Assegnazioni';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="assegnazione-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-circle-check me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione assegnazioni docenti ai moduli
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Assegnazione', ['create'], ['class' => 'btn btn-success']) ?>
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
                                if (!$model->docente) return '-';
                                return Html::a(
                                    Html::encode($model->docente->cognome . ' ' . $model->docente->nome),
                                    ['/docente/view', 'id' => $model->docente_codice_fiscale],
                                    ['class' => 'text-reset']
                                );
                            },
                        ],
                        [
                            'attribute' => 'modulo_titolo',
                            'label' => 'Modulo',
                            'format' => 'raw',
                            'content' => function($model) {
                                if (!$model->modulo) return '-';
                                return Html::a(
                                    Html::encode($model->modulo->codice . ' - ' . $model->modulo->titolo),
                                    ['/modulo/view', 'id' => $model->modulo_id],
                                    ['class' => 'text-reset']
                                );
                            },
                        ],
                        [
                            'attribute' => 'data_assegnazione',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 130px;'],
                        ],
                        [
                            'attribute' => 'delibera_cts',
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => Assegnazione::getStatusLabels(),
                            'content' => function($model) {
                                return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> assegnazioni
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
