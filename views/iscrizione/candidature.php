<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppGridView;
use app\models\Iscrizione;

/* @var $this yii\web\View */
/* @var $avviso app\models\Avviso|null */
/* @var $avvisi app\models\Avviso[]|null */
/* @var $dataProvider yii\data\ActiveDataProvider|null */

$this->title = 'Candidature Studenti';
$this->params['breadcrumbs'][] = ['label' => 'Iscrizioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Candidature';
if ($avviso) {
    $this->params['breadcrumbs'][] = $avviso->titolo;
}
?>

<div class="iscrizione-candidature">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-users me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                    <?php if ($avviso): ?>
                        <div class="text-muted mt-1">
                            Bando: <strong><?= Html::encode($avviso->titolo) ?></strong>
                            <span class="badge <?= $avviso->getStatusBadgeClass() ?> ms-2"><?= Html::encode($avviso->getStatusLabel()) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="text-muted mt-1">Seleziona un bando per visualizzare le candidature</div>
                    <?php endif; ?>
                </div>
                <?php if ($avviso): ?>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-list-numbers me-1"></i> Graduatoria', ['graduatoria-studenti', 'avviso_id' => $avviso->id], ['class' => 'btn btn-primary']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?php if (!$avviso && isset($avvisi)): ?>
                <!-- Lista bandi disponibili -->
                <div class="row row-deck row-cards">
                    <?php foreach ($avvisi as $a): ?>
                        <?php
                            $numCandidature = Iscrizione::find()->where(['avviso_id' => $a->id])->count();
                        ?>
                        <div class="col-sm-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge <?= $a->getStatusBadgeClass() ?> me-2"><?= Html::encode($a->getStatusLabel()) ?></span>
                                        <span class="text-muted small"><?= Html::encode($a->codice_avviso) ?></span>
                                    </div>
                                    <h3 class="card-title mb-1"><?= Html::encode($a->titolo) ?></h3>
                                    <div class="text-muted mb-3">
                                        <?= $numCandidature ?> candidatura/e
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <?php if ($numCandidature > 0): ?>
                                        <?= Html::a('<i class="ti ti-eye me-1"></i> Vedi Candidature', ['candidature', 'avviso_id' => $a->id], ['class' => 'btn btn-primary btn-sm']) ?>
                                        <?= Html::a('<i class="ti ti-list-numbers me-1"></i> Graduatoria', ['graduatoria-studenti', 'avviso_id' => $a->id], ['class' => 'btn btn-outline-blue btn-sm']) ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Nessuna candidatura</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($avvisi)): ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="empty">
                                        <div class="empty-icon"><i class="ti ti-file-off" style="font-size: 3rem;"></i></div>
                                        <p class="empty-title">Nessun bando trovato</p>
                                        <p class="empty-subtitle text-muted">Non ci sono bandi nel sistema.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($avviso && $dataProvider): ?>
                <!-- Lista candidature per bando specifico -->
                <div class="card">
                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            [
                                'label' => '#',
                                'headerOptions' => ['style' => 'width: 50px;'],
                                'content' => function ($model, $key, $index) {
                                    return $index + 1;
                                },
                            ],
                            [
                                'label' => 'Allievo',
                                'format' => 'raw',
                                'content' => function ($model) {
                                    if (!$model->allievo) return '<span class="text-muted">-</span>';
                                    return Html::a(
                                        Html::encode($model->allievo->cognome . ' ' . $model->allievo->nome),
                                        ['/allievo/view', 'id' => $model->allievo_codice_fiscale]
                                    ) . '<br><small class="text-muted">' . Html::encode($model->allievo_codice_fiscale) . '</small>';
                                },
                            ],
                            [
                                'attribute' => 'data_iscrizione',
                                'format' => 'date',
                                'headerOptions' => ['style' => 'width: 120px;'],
                            ],
                            [
                                'attribute' => 'punteggio_totale',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 120px;'],
                                'content' => function ($model) {
                                    if ($model->punteggio_totale === null) {
                                        return '<span class="text-muted">Da valutare</span>';
                                    }
                                    return '<strong>' . Yii::$app->formatter->asDecimal($model->punteggio_totale, 2) . '</strong>';
                                },
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 130px;'],
                                'content' => function ($model) {
                                    return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{valuta} {view} {storico}',
                                'headerOptions' => ['style' => 'width: 130px;'],
                                'buttons' => [
                                    'valuta' => function ($url, $model) {
                                        return Html::a('<i class="ti ti-star"></i>', ['valuta', 'id' => $model->id], [
                                            'class' => 'btn btn-orange btn-sm',
                                            'title' => 'Valuta',
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'view' => function ($url, $model) {
                                        return Html::a('<i class="ti ti-eye"></i>', ['view', 'id' => $model->id], [
                                            'class' => 'btn btn-info btn-sm',
                                            'title' => 'Dettaglio',
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'storico' => function ($url, $model) {
                                        return Html::a('<i class="ti ti-history"></i>', ['storico', 'id' => $model->id], [
                                            'class' => 'btn btn-secondary btn-sm',
                                            'title' => 'Storico',
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                ],
                            ],
                        ],
                    ]); ?>

                    <div class="card-footer d-flex align-items-center">
                        <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> candidature</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
