<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AllievoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Allievi';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="allievo-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-school me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione anagrafica allievi e studenti
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Allievo', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'codice_fiscale',
                            'headerOptions' => ['style' => 'width: 180px;'],
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<code>' . Html::encode($model->codice_fiscale) . '</code>';
                            },
                        ],
                        [
                            'attribute' => 'cognome',
                            'format' => 'raw',
                            'content' => function($model) {
                                $iniziali = strtoupper(mb_substr($model->cognome, 0, 1) . mb_substr($model->nome, 0, 1));
                                $avatar = '<span class="avatar avatar-sm bg-green-lt me-2">' . Html::encode($iniziali) . '</span>';
                                $nome = Html::a(
                                    Html::encode($model->getNomeCompleto()),
                                    ['view', 'id' => $model->codice_fiscale],
                                    ['class' => 'text-reset']
                                );
                                return '<div class="d-flex align-items-center">' . $avatar . $nome . '</div>';
                            },
                        ],
                        [
                            'attribute' => 'email',
                            'format' => 'email',
                        ],
                        [
                            'attribute' => 'telefono',
                        ],
                        [
                            'attribute' => 'citta',
                            'format' => 'raw',
                            'content' => function($model) {
                                if (!$model->citta) return '-';
                                $prov = $model->provincia ? ' (' . $model->provincia . ')' : '';
                                return Html::encode($model->citta) . $prov;
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> allievi
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
