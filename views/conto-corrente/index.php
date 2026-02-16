<?php
use yii\helpers\Html;
use app\components\widgets\AppGridView;

$this->title = 'Conti Correnti';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conto-corrente-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-building-bank me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Gestione conti correnti dei progetti</div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Conto', ['create'], ['class' => 'btn btn-success']) ?>
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
                        ['attribute' => 'id', 'headerOptions' => ['style' => 'width: 70px;']],
                        'banca',
                        [
                            'attribute' => 'iban',
                            'format' => 'raw',
                            'content' => fn($model) => '<code>' . Html::encode($model->iban) . '</code>',
                        ],
                        'intestatario',
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> conti correnti</p>
                </div>
            </div>
        </div>
    </div>
</div>
