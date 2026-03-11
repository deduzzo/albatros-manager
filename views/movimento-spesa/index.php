<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\MovimentoSpesa;

/* @var $this yii\web\View */
/* @var $searchModel app\models\MovimentoSpesaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $totaleSpese float */
/* @var $totaleEntrate float */
/* @var $progetti array */
/* @var $vociSpesa array */

$this->title = 'Movimenti di Spesa';
$this->params['breadcrumbs'][] = $this->title;

$saldo = $totaleEntrate - $totaleSpese;
?>

<div class="movimento-spesa-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-arrows-exchange me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Registrazione movimenti contabili
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Movimento', ['create'], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Stat Cards Riepilogo -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-red-lt me-3"><i class="ti ti-arrow-up"></i></span>
                                <div>
                                    <div class="subheader">Totale Spese</div>
                                    <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($totaleSpese, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3"><i class="ti ti-arrow-down"></i></span>
                                <div>
                                    <div class="subheader">Totale Entrate</div>
                                    <div class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($totaleEntrate, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-<?= $saldo >= 0 ? 'blue' : 'orange' ?>-lt me-3"><i class="ti ti-scale"></i></span>
                                <div>
                                    <div class="subheader">Saldo</div>
                                    <div class="h2 mb-0 text-<?= $saldo >= 0 ? 'blue' : 'red' ?>"><?= Yii::$app->formatter->asCurrency($saldo, 'EUR') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtri aggiuntivi -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Progetto</label>
                            <?= Html::dropDownList('MovimentoSpesaSearch[progetto_id]', $searchModel->progetto_id, $progetti, [
                                'class' => 'form-select form-select-sm',
                                'prompt' => 'Tutti i progetti',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Tipo</label>
                            <?= Html::dropDownList('MovimentoSpesaSearch[tipo]', $searchModel->tipo, MovimentoSpesa::getTipoList(), [
                                'class' => 'form-select form-select-sm',
                                'prompt' => 'Tutti',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Da</label>
                            <?= Html::textInput('MovimentoSpesaSearch[data_da]', $searchModel->data_da, [
                                'type' => 'date',
                                'class' => 'form-control form-control-sm',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">A</label>
                            <?= Html::textInput('MovimentoSpesaSearch[data_a]', $searchModel->data_a, [
                                'type' => 'date',
                                'class' => 'form-control form-control-sm',
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

            <!-- Tabella movimenti -->
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
                            'attribute' => 'data_movimento',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 110px;'],
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
                            'attribute' => 'voce_spesa_id',
                            'label' => 'Voce di Spesa',
                            'format' => 'raw',
                            'content' => function ($model) {
                                $v = $model->voceSpesa;
                                if (!$v) return '-';
                                return '<span class="badge bg-blue-lt">' . Html::encode($v->getLabel()) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'descrizione',
                            'content' => function ($model) {
                                return Html::encode(mb_substr($model->descrizione, 0, 50)) .
                                    (mb_strlen($model->descrizione) > 50 ? '...' : '');
                            },
                        ],
                        [
                            'attribute' => 'tipo',
                            'format' => 'raw',
                            'filter' => MovimentoSpesa::getTipoList(),
                            'headerOptions' => ['style' => 'width: 90px;'],
                            'content' => function ($model) {
                                return $model->getTipoBadge();
                            },
                        ],
                        [
                            'attribute' => 'importo',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'text-end fw-bold'],
                            'content' => function ($model) {
                                $color = $model->tipo === MovimentoSpesa::TIPO_SPESA ? 'text-red' : 'text-green';
                                $prefix = $model->tipo === MovimentoSpesa::TIPO_SPESA ? '-' : '+';
                                return '<span class="' . $color . '">' . $prefix . ' ' . Yii::$app->formatter->asCurrency($model->importo, 'EUR') . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'fornitore',
                            'headerOptions' => ['style' => 'width: 150px;'],
                        ],
                        [
                            'label' => 'Rif.',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'content' => function ($model) {
                                return $model->getRiferimentoBadge();
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center justify-content-between">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> movimenti
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
