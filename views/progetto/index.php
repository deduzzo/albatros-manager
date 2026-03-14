<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Progetto;
use app\models\FonteFinanziamento;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgettoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Progetti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="progetto-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Progetto', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'codice_progetto',
                            'headerOptions' => ['style' => 'width: 150px;'],
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . Html::encode($model->codice_progetto) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'titolo',
                        ],
                        [
                            'attribute' => 'fonte_finanziamento_id',
                            'value' => function($model) {
                                return $model->fonteFinanziamento ? $model->fonteFinanziamento->codice : '-';
                            },
                            'filter' => FonteFinanziamento::getList(),
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'budget_totale',
                            'format' => 'currency',
                            'headerOptions' => ['style' => 'width: 130px;'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>';
                            },
                            'filter' => Progetto::getStatusLabels(),
                            'headerOptions' => ['style' => 'width: 110px;'],
                        ],
                        [
                            'attribute' => 'data_fine',
                            'format' => ['date', 'php:d/m/Y'],
                            'headerOptions' => ['style' => 'width: 110px;'],
                            'filter' => false,
                        ],
                        [
                            'label' => 'Studenti',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'format' => 'raw',
                            'content' => function($model) {
                                $iscritti = $model->getNumeroStudentiIscritti();
                                $previsti = $model->numero_studenti ?: '-';
                                $color = 'default';
                                if ($model->numero_studenti && $iscritti > 0) {
                                    $perc = ($iscritti / $model->numero_studenti) * 100;
                                    $color = $perc >= 80 ? 'green' : ($perc >= 50 ? 'orange' : 'red');
                                }
                                return '<span class="badge bg-' . $color . '-lt">' . $iscritti . ' / ' . $previsti . '</span>';
                            },
                        ],
                        [
                            'label' => 'Stato Finanziario',
                            'headerOptions' => ['style' => 'width: 160px;'],
                            'format' => 'raw',
                            'content' => function($model) {
                                $contrattualizzato = $model->getTotaleContrattualizzato();
                                $pagato = $model->getTotalePagato();
                                $perc = $contrattualizzato > 0 ? round(($pagato / $contrattualizzato) * 100) : 0;
                                $colorBar = $perc >= 80 ? 'bg-green' : ($perc >= 40 ? 'bg-azure' : 'bg-orange');
                                return '<div class="small text-muted mb-1">'
                                    . Yii::$app->formatter->asCurrency($pagato, 'EUR')
                                    . ' / '
                                    . Yii::$app->formatter->asCurrency($contrattualizzato, 'EUR')
                                    . '</div>'
                                    . '<div class="progress progress-sm"><div class="progress-bar ' . $colorBar . '" style="width: ' . $perc . '%"></div></div>';
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> progetti
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
