<?php
use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\ValutazioneDocente;

$this->title = 'Valutazioni Docenti';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="valutazione-docente-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-star me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Valutazioni anonime dei docenti</div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Valutazione', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'docente_nome',
                            'label' => 'Docente',
                            'format' => 'raw',
                            'content' => fn($model) => $model->docente
                                ? Html::a(Html::encode($model->docente->cognome . ' ' . $model->docente->nome), ['/docente/view', 'id' => $model->docente_codice_fiscale])
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
                            'attribute' => 'punteggio_globale',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 160px;'],
                            'content' => fn($model) => ValutazioneDocente::getStarsHtml($model->punteggio_globale),
                        ],
                        [
                            'attribute' => 'punteggio_chiarezza',
                            'label' => 'Chiarezza',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 50px;'],
                            'content' => fn($model) => $model->punteggio_chiarezza ?: '-',
                        ],
                        [
                            'attribute' => 'punteggio_competenza',
                            'label' => 'Compet.',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 50px;'],
                            'content' => fn($model) => $model->punteggio_competenza ?: '-',
                        ],
                        [
                            'attribute' => 'data_valutazione',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> valutazioni</p>
                </div>
            </div>
        </div>
    </div>
</div>
