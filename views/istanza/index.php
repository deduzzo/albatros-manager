<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Istanza;
use app\models\Avviso;
use app\models\Modulo;

/* @var $this yii\web\View */
/* @var $searchModel app\models\IstanzaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Istanze Docenti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="istanza-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-list-numbers me-1"></i> Graduatoria', ['/istanza/graduatoria'], ['class' => 'btn btn-azure']) ?>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Istanza', ['create'], ['class' => 'btn btn-primary']) ?>
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
                            'headerOptions' => ['style' => 'width: 60px;'],
                        ],
                        [
                            'attribute' => 'data_presentazione',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'docente_codice_fiscale',
                            'format' => 'raw',
                            'value' => function($model) {
                                if ($model->docente) {
                                    return Html::a(
                                        $model->docente->cognome . ' ' . $model->docente->nome,
                                        ['/docente/view', 'id' => $model->docente_codice_fiscale]
                                    );
                                }
                                return $model->docente_codice_fiscale;
                            },
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
                            'attribute' => 'modulo_id',
                            'value' => function($model) {
                                return $model->modulo ? $model->modulo->codice : '-';
                            },
                            'filter' => Modulo::getList(),
                            'headerOptions' => ['style' => 'width: 130px;'],
                        ],
                        [
                            'attribute' => 'punteggio_totale',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'contentOptions' => ['class' => 'text-end'],
                            'value' => function($model) {
                                return $model->punteggio_totale !== null ? number_format($model->punteggio_totale, 2) : '-';
                            },
                        ],
                        [
                            'attribute' => 'posizione_graduatoria',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'format' => 'raw',
                            'value' => function($model) {
                                if ($model->posizione_graduatoria) {
                                    return '<span class="badge bg-blue-lt">#' . $model->posizione_graduatoria . '</span>';
                                }
                                return '-';
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>';
                            },
                            'filter' => Istanza::getStatusLabels(),
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'esito_cts',
                            'label' => 'Esito CTS',
                            'format' => 'raw',
                            'content' => function($model) {
                                return $model->getEsitoCtsBadgeHtml();
                            },
                            'filter' => Istanza::getEsitoCtsLabels(),
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view} {update} {delete}',
                        ],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> istanze
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
