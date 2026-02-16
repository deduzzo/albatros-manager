<?php
use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Iscrizione;

$this->title = 'Iscrizioni';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="iscrizione-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-user-plus me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Gestione iscrizioni allievi ai moduli</div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Iscrizione', ['create'], ['class' => 'btn btn-success']) ?>
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
                        [
                            'attribute' => 'allievo_nome',
                            'label' => 'Allievo',
                            'format' => 'raw',
                            'content' => fn($model) => $model->allievo
                                ? Html::a(Html::encode($model->allievo->cognome . ' ' . $model->allievo->nome), ['/allievo/view', 'id' => $model->allievo_codice_fiscale])
                                : '<span class="text-muted">-</span>',
                        ],
                        [
                            'attribute' => 'modulo_titolo',
                            'label' => 'Modulo',
                            'format' => 'raw',
                            'content' => fn($model) => $model->modulo
                                ? Html::a(Html::encode($model->modulo->titolo), ['/modulo/view', 'id' => $model->modulo_id])
                                : '<span class="text-muted">-</span>',
                        ],
                        [
                            'attribute' => 'data_iscrizione',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'percentuale_frequenza',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'content' => fn($model) => $model->percentuale_frequenza !== null
                                ? '<div class="progress progress-sm"><div class="progress-bar" style="width: ' . $model->percentuale_frequenza . '%"></div></div><small class="text-muted">' . $model->percentuale_frequenza . '%</small>'
                                : '<span class="text-muted">-</span>',
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => Iscrizione::getStatusLabels(),
                            'content' => fn($model) => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>',
                        ],
                        [
                            'attribute' => 'esito_finale',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 110px;'],
                            'filter' => Iscrizione::getEsitoLabels(),
                            'content' => fn($model) => $model->esito_finale
                                ? '<span class="badge bg-' . ($model->esito_finale === 'idoneo' ? 'success' : ($model->esito_finale === 'ritirato' ? 'warning' : 'danger')) . '">' . Html::encode($model->getEsitoLabel()) . '</span>'
                                : '<span class="text-muted">-</span>',
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> iscrizioni</p>
                </div>
            </div>
        </div>
    </div>
</div>
