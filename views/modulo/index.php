<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Modulo;
use app\models\Avviso;
use app\models\Sede;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ModuloSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Moduli/Corsi';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="modulo-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Modulo', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'codice',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . Html::encode($model->codice) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'titolo',
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
                            'attribute' => 'sede_id',
                            'value' => function($model) {
                                return $model->sede ? $model->sede->nome : '-';
                            },
                            'filter' => Sede::getList(),
                            'headerOptions' => ['style' => 'width: 150px;'],
                        ],
                        [
                            'label' => 'Docente',
                            'format' => 'raw',
                            'content' => function($model) {
                                $docente = $model->getDocenteAssegnato();
                                if ($docente) {
                                    return '<i class="ti ti-user-check text-green me-1"></i>'
                                        . Html::a(Html::encode($docente->getNomeCompleto()), ['/docente/view', 'id' => $docente->codice_fiscale]);
                                }
                                return '<span class="text-muted"><i class="ti ti-user-question me-1"></i>Da assegnare</span>';
                            },
                            'headerOptions' => ['style' => 'width: 180px;'],
                        ],
                        [
                            'label' => 'Studenti',
                            'format' => 'raw',
                            'content' => function($model) {
                                $iscritti = 0;
                                foreach ($model->iscrizioni as $i) {
                                    if (!in_array($i->status, ['ritirato', 'non_ammesso'])) {
                                        $iscritti++;
                                    }
                                }
                                $max = $model->max_allievi ?: '∞';
                                $color = ($model->max_allievi && $iscritti >= $model->max_allievi) ? 'text-orange' : 'text-muted';
                                return "<span class=\"{$color}\">{$iscritti}/{$max}</span>";
                            },
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'contentOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'attribute' => 'ore_totali',
                            'label' => 'Ore',
                            'format' => 'raw',
                            'content' => function($model) {
                                $totali = $model->ore_totali ?: 0;
                                $rendicontate = $model->getOreRendicontate();
                                $liquidate = $model->getOreLiquidate();

                                // Colori in base allo stato
                                $colorRend = $rendicontate > 0 ? ($rendicontate >= $totali ? 'text-green' : 'text-azure') : 'text-muted';
                                $colorLiq = $liquidate > 0 ? ($liquidate >= $totali ? 'text-green' : 'text-blue') : 'text-muted';

                                return '<span class="d-flex flex-column lh-sm" style="font-size: 0.8rem;">'
                                    . '<span title="Ore totali"><i class="ti ti-clock me-1"></i><strong>' . $totali . '</strong></span>'
                                    . '<span class="' . $colorRend . '" title="Ore rendicontate (approvate)"><i class="ti ti-checkbox me-1"></i>' . $rendicontate . '</span>'
                                    . '<span class="' . $colorLiq . '" title="Ore liquidate"><i class="ti ti-currency-euro me-1"></i>' . $liquidate . '</span>'
                                    . '</span>';
                            },
                            'headerOptions' => ['style' => 'width: 100px;'],
                        ],
                        [
                            'attribute' => 'compenso_orario',
                            'format' => 'currency',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'contentOptions' => ['class' => 'text-end'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'content' => function($model) {
                                $badge = '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>';
                                if ($model->isApertoIscrizioni()) {
                                    $badge .= ' <i class="ti ti-check text-success" title="Aperto iscrizioni"></i>';
                                }
                                return $badge;
                            },
                            'filter' => Modulo::getStatusLabels(),
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center justify-content-between">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> moduli
                    </p>
                    <div class="d-flex gap-3 text-muted small">
                        <span><i class="ti ti-clock me-1"></i>Totali</span>
                        <span><i class="ti ti-checkbox me-1"></i>Rendicontate</span>
                        <span><i class="ti ti-currency-euro me-1"></i>Liquidate</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
