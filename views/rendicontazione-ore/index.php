<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppGridView;
use app\models\RendicontazioneOre;

/* @var $this yii\web\View */
/* @var $assegnazione app\models\Assegnazione */
/* @var $searchModel app\models\RendicontazioneOreSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $oreApprovate float */
/* @var $oreInserite float */
/* @var $oreContrattualizzate int */

$docente = $assegnazione->docente;
$modulo = $assegnazione->modulo;

$this->title = 'Rendicontazione Ore';
$this->params['breadcrumbs'][] = ['label' => 'Moduli/Corsi', 'url' => ['/modulo/index']];
if ($modulo) {
    $this->params['breadcrumbs'][] = ['label' => $modulo->codice, 'url' => ['/modulo/view', 'id' => $modulo->id]];
}
$this->params['breadcrumbs'][] = $this->title;

$percApprovate = ($oreContrattualizzate > 0) ? round(($oreApprovate / $oreContrattualizzate) * 100) : 0;
$oreExtra = max(0, $oreApprovate - $oreContrattualizzate);
?>

<div class="rendicontazione-ore-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-clock-hour-4 me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?php if ($modulo): ?>
                            <span class="badge bg-blue-lt me-1"><?= Html::encode($modulo->codice) ?></span>
                            <?= Html::encode($modulo->titolo) ?>
                        <?php endif; ?>
                        <?php if ($docente): ?>
                            &mdash;
                            <i class="ti ti-user me-1"></i>
                            <?= Html::a(Html::encode($docente->cognome . ' ' . $docente->nome), ['/docente/view', 'id' => $docente->codice_fiscale]) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <?= Html::a('<i class="ti ti-chart-bar me-1"></i> Riepilogo', ['riepilogo', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-info me-2']) ?>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Rendicontazione', ['create', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-blue-lt me-3"><i class="ti ti-file-invoice"></i></span>
                                <div>
                                    <div class="text-muted small">Ore Contrattualizzate</div>
                                    <div class="fw-bold h3 mb-0"><?= number_format($oreContrattualizzate, 0, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3"><i class="ti ti-circle-check"></i></span>
                                <div>
                                    <div class="text-muted small">Ore Approvate</div>
                                    <div class="fw-bold h3 mb-0 text-success"><?= number_format($oreApprovate, 1, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-yellow-lt me-3"><i class="ti ti-clock"></i></span>
                                <div>
                                    <div class="text-muted small">Ore In Attesa</div>
                                    <div class="fw-bold h3 mb-0 text-warning"><?= number_format($oreInserite, 1, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress bar -->
            <?php if ($oreContrattualizzate > 0): ?>
            <div class="card mb-4">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Avanzamento ore</small>
                        <small class="fw-bold"><?= $percApprovate ?>%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?= $percApprovate > 100 ? 'bg-red' : ($percApprovate >= 100 ? 'bg-green' : 'bg-blue') ?>"
                             style="width: <?= min($percApprovate, 100) ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Alert ore superiori -->
            <?php if ($oreApprovate > $oreContrattualizzate && $oreContrattualizzate > 0): ?>
            <div class="alert alert-warning mb-4">
                <div class="d-flex">
                    <div><i class="ti ti-alert-triangle me-2"></i></div>
                    <div>
                        <h4 class="alert-title">Ore Superiori al Contratto</h4>
                        <div class="text-muted">
                            Le ore approvate (<strong><?= number_format($oreApprovate, 1, ',', '.') ?></strong>) superano
                            le ore contrattualizzate (<strong><?= $oreContrattualizzate ?></strong>) di
                            <span class="badge bg-red text-red-fg"><?= number_format($oreExtra, 1, ',', '.') ?> ore extra</span>.
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabella rendicontazioni -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-list me-2"></i>Dettaglio Ore Rendicontate</h3>
                    <?php if ($oreInserite > 0): ?>
                    <div class="card-actions">
                        <?= Html::a('<i class="ti ti-checks me-1"></i> Approva Tutte (' . number_format($oreInserite, 1, ',', '.') . 'h)', ['approva-multi', 'assegnazione_id' => $assegnazione->id], [
                            'class' => 'btn btn-sm btn-success',
                            'data' => [
                                'confirm' => 'Sei sicuro di voler approvare tutte le ' . number_format($oreInserite, 1, ',', '.') . ' ore in attesa?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?= AppGridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        [
                            'attribute' => 'data',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => Html::activeTextInput($searchModel, 'data', [
                                'class' => 'form-control form-control-sm',
                                'type' => 'date',
                            ]),
                        ],
                        [
                            'attribute' => 'ore',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'contentOptions' => ['class' => 'text-center fw-bold'],
                            'content' => function ($model) {
                                return number_format($model->ore, 1, ',', '.');
                            },
                        ],
                        [
                            'label' => 'Orario',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'content' => function ($model) {
                                if ($model->ora_inizio && $model->ora_fine) {
                                    return '<small>' . substr($model->ora_inizio, 0, 5) . ' - ' . substr($model->ora_fine, 0, 5) . '</small>';
                                }
                                return '<small class="text-muted">-</small>';
                            },
                        ],
                        [
                            'attribute' => 'firma_digitale',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'content' => function ($model) {
                                return $model->firma_digitale
                                    ? '<i class="ti ti-certificate text-success" title="Firmato"></i>'
                                    : '<i class="ti ti-certificate-off text-muted" title="Non firmato"></i>';
                            },
                            'filter' => [0 => 'No', 1 => 'Si'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 110px;'],
                            'filter' => RendicontazioneOre::getStatusLabels(),
                            'content' => function ($model) {
                                return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'note',
                            'format' => 'raw',
                            'content' => function ($model) {
                                $html = '';
                                if ($model->note) {
                                    $html .= '<small>' . Html::encode(mb_substr($model->note, 0, 50)) . (mb_strlen($model->note) > 50 ? '...' : '') . '</small>';
                                }
                                if ($model->motivo_rifiuto) {
                                    $html .= '<br><small class="text-danger"><i class="ti ti-alert-circle me-1"></i>' . Html::encode($model->motivo_rifiuto) . '</small>';
                                }
                                return $html ?: '-';
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{approva} {rifiuta} {update} {delete}',
                            'headerOptions' => ['style' => 'width: 180px;'],
                            'visibleButtons' => [
                                'approva' => function ($model) {
                                    return $model->status === RendicontazioneOre::STATUS_INSERITO;
                                },
                                'rifiuta' => function ($model) {
                                    return $model->status === RendicontazioneOre::STATUS_INSERITO;
                                },
                                'update' => function ($model) {
                                    return $model->status === RendicontazioneOre::STATUS_INSERITO;
                                },
                                'delete' => function ($model) {
                                    return $model->status === RendicontazioneOre::STATUS_INSERITO;
                                },
                            ],
                            'buttons' => [
                                'approva' => function ($url, $model) {
                                    return Html::a('<i class="ti ti-check"></i>', ['approva', 'id' => $model->id], [
                                        'class' => 'btn btn-success btn-sm me-1',
                                        'title' => 'Approva',
                                        'data-bs-toggle' => 'tooltip',
                                        'data' => [
                                            'confirm' => 'Approvare la rendicontazione del ' . Yii::$app->formatter->asDate($model->data) . ' (' . number_format($model->ore, 1, ',', '.') . ' ore)?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                },
                                'rifiuta' => function ($url, $model) {
                                    return Html::button('<i class="ti ti-x"></i>', [
                                        'class' => 'btn btn-danger btn-sm me-1',
                                        'title' => 'Rifiuta',
                                        'data-bs-toggle' => 'modal',
                                        'data-bs-target' => '#modal-rifiuta-' . $model->id,
                                    ]);
                                },
                                'update' => function ($url, $model) {
                                    return Html::a('<i class="ti ti-edit"></i>', ['update', 'id' => $model->id], [
                                        'class' => 'btn btn-primary btn-sm me-1',
                                        'title' => 'Modifica',
                                        'data-bs-toggle' => 'tooltip',
                                    ]);
                                },
                                'delete' => function ($url, $model) {
                                    return Html::a('<i class="ti ti-trash"></i>', ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-danger btn-sm',
                                        'title' => 'Elimina',
                                        'data-bs-toggle' => 'tooltip',
                                        'data' => [
                                            'confirm' => 'Sei sicuro di voler eliminare questa rendicontazione?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> rendicontazioni
                    </p>
                    <div class="ms-auto">
                        <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna al Modulo', ['/modulo/view', 'id' => $modulo ? $modulo->id : null], ['class' => 'btn btn-secondary btn-sm']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal rifiuto per ogni rendicontazione in attesa -->
<?php foreach ($dataProvider->getModels() as $model): ?>
<?php if ($model->status === RendicontazioneOre::STATUS_INSERITO): ?>
<div class="modal modal-blur fade" id="modal-rifiuta-<?= $model->id ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <?= Html::beginForm(['rifiuta', 'id' => $model->id], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">Rifiuta Rendicontazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <p>Stai rifiutando la rendicontazione del <strong><?= Yii::$app->formatter->asDate($model->data) ?></strong>
                    (<?= number_format($model->ore, 1, ',', '.') ?> ore).</p>
                <div class="mb-3">
                    <label class="form-label required">Motivo del Rifiuto</label>
                    <textarea name="motivo_rifiuto" class="form-control" rows="3" required
                              placeholder="Inserisci il motivo del rifiuto..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Annulla
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="ti ti-x me-1"></i> Rifiuta
                </button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>
