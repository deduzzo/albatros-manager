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
                <div class="col-auto ms-auto d-print-none">
                    <?= Html::a('<i class="ti ti-star-half-filled me-1"></i> Gestisci Criteri', ['/criterio-valutazione-docente/index'], ['class' => 'btn btn-secondary me-2']) ?>
                    <?= Html::a('<i class="ti ti-upload me-1"></i> Importa CSV', ['import'], ['class' => 'btn btn-warning me-2']) ?>
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
                            'content' => function($model) {
                                if ($model->docente) {
                                    return Html::a(Html::encode($model->docente->cognome . ' ' . $model->docente->nome), ['/docente/view', 'id' => $model->docente_codice_fiscale]);
                                }
                                if ($model->docente_nome) {
                                    return '<span class="text-muted">' . Html::encode($model->docente_nome) . '</span>'
                                        . ' <span class="badge bg-orange-lt" title="Docente non collegato"><i class="ti ti-unlink"></i></span>';
                                }
                                return '<span class="text-muted">-</span>';
                            },
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
                            'label' => 'Media',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 160px;'],
                            'content' => function($model) {
                                $media = $model->getMediaPunteggi();
                                if ($media !== null) {
                                    return ValutazioneDocente::getProgressBarHtml(round($media));
                                }
                                // Fallback al punteggio_globale vecchio
                                if ($model->punteggio_globale) {
                                    return ValutazioneDocente::getStarsHtml($model->punteggio_globale);
                                }
                                return '<span class="text-muted">-</span>';
                            },
                        ],
                        [
                            'attribute' => 'sorgente_tipo',
                            'label' => 'Sorgente',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'filter' => [
                                'manuale' => 'Manuale',
                                'codice_monouso' => 'Codice Monouso',
                                'import_csv' => 'Import CSV',
                            ],
                            'content' => fn($model) => $model->getSorgenteTipoBadge(),
                        ],
                        [
                            'attribute' => 'studente_nome',
                            'label' => 'Studente',
                            'headerOptions' => ['style' => 'width: 150px;'],
                            'content' => function($model) {
                                return $model->studente_nome
                                    ? Html::encode($model->studente_nome)
                                    : '<span class="text-muted">-</span>';
                            },
                            'format' => 'raw',
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
