<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Istanza */

$this->title = 'Istanza #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Istanze', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$tagsNonPromossi = $model->getTagsNonPromossiCount();
?>

<div class="istanza-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= $model->getStatusLabel() ?></span>
                </div>
                <div class="col-auto ms-auto">
                    <?php if ($model->snapshot): ?>
                        <?= Html::a('<i class="ti ti-history me-1"></i> Snapshot Storico', ['snapshot', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questa istanza?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Dettagli Istanza -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Dettagli Istanza</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered'],
                                'attributes' => [
                                    'id',
                                    'data_presentazione:date',
                                    [
                                        'attribute' => 'docente_codice_fiscale',
                                        'format' => 'raw',
                                        'value' => $model->docente
                                            ? Html::a(
                                                '<strong>' . $model->docente->cognome . ' ' . $model->docente->nome . '</strong><br><small class="text-muted">' . $model->docente_codice_fiscale . '</small>',
                                                ['/docente/view', 'id' => $model->docente_codice_fiscale]
                                            )
                                            : $model->docente_codice_fiscale,
                                    ],
                                    [
                                        'attribute' => 'avviso_id',
                                        'format' => 'raw',
                                        'value' => $model->avviso
                                            ? Html::a($model->avviso->codice_avviso . ' - ' . $model->avviso->titolo, ['/avviso/view', 'id' => $model->avviso_id])
                                            : '-',
                                    ],
                                    [
                                        'attribute' => 'modulo_id',
                                        'format' => 'raw',
                                        'value' => $model->modulo
                                            ? Html::a($model->modulo->codice . ' - ' . $model->modulo->titolo, ['/modulo/view', 'id' => $model->modulo_id])
                                            : '-',
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'format' => 'raw',
                                        'value' => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>',
                                    ],
                                    'note:ntext',
                                    'created_at:datetime',
                                    'updated_at:datetime',
                                ],
                            ]) ?>
                        </div>
                    </div>

                    <!-- Valutazione -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Valutazione</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-blue text-white avatar"><i class="ti ti-school"></i></span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">Punteggio Titoli</div>
                                                    <div class="text-secondary"><?= $model->punteggio_titoli !== null ? number_format($model->punteggio_titoli, 2) : '-' ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-green text-white avatar"><i class="ti ti-briefcase"></i></span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">Punteggio Esperienza</div>
                                                    <div class="text-secondary"><?= $model->punteggio_esperienza !== null ? number_format($model->punteggio_esperienza, 2) : '-' ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-yellow text-white avatar"><i class="ti ti-calculator"></i></span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">Punteggio Totale</div>
                                                    <div class="text-secondary fw-bold"><?= $model->punteggio_totale !== null ? number_format($model->punteggio_totale, 2) : '-' ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($model->posizione_graduatoria): ?>
                                <div class="mt-3 text-center">
                                    <span class="h3">Posizione in graduatoria: <span class="badge bg-primary fs-3">#<?= $model->posizione_graduatoria ?></span></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($model->note_valutazione): ?>
                                <div class="mt-3">
                                    <label class="form-label">Note Valutazione</label>
                                    <div class="text-muted"><?= nl2br(Html::encode($model->note_valutazione)) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Documenti Caricati -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Documenti Caricati</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($model->documenti)): ?>
                                <p class="text-muted">Nessun documento caricato.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($model->documenti as $doc): ?>
                                        <div class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="avatar bg-azure-lt">
                                                        <i class="ti ti-file"></i>
                                                    </span>
                                                </div>
                                                <div class="col">
                                                    <div class="d-block text-truncate">
                                                        <strong><?= Html::encode($doc->nome_file_originale) ?></strong>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= $doc->documentoTipo ? $doc->documentoTipo->nome : 'Documento' ?>
                                                        &bull; <?= $doc->getDimensioneFormattata() ?>
                                                        &bull; <?= Yii::$app->formatter->asDatetime($doc->created_at) ?>
                                                    </small>
                                                </div>
                                                <div class="col-auto">
                                                    <?php if ($doc->fileExists()): ?>
                                                        <?= Html::a('<i class="ti ti-download"></i>', ['download-documento', 'id' => $doc->id], ['class' => 'btn btn-ghost-primary btn-icon']) ?>
                                                    <?php else: ?>
                                                        <span class="text-danger" title="File non trovato"><i class="ti ti-alert-triangle"></i></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Tag Iscrizione -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Tag Iscrizione</h3>
                            <?php if ($tagsNonPromossi > 0): ?>
                                <?= Html::a(
                                    '<i class="ti ti-arrow-up me-1"></i> Promuovi tutti',
                                    ['promuovi-tutti-tag', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-sm btn-primary',
                                        'data' => [
                                            'confirm' => "Promuovere {$tagsNonPromossi} tag al profilo docente?",
                                            'method' => 'post',
                                        ],
                                    ]
                                ) ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($model->istanzaTags)): ?>
                                <p class="text-muted">Nessun tag selezionato per questa iscrizione.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($model->istanzaTags as $istanzaTag): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-azure-lt me-2"><?= Html::encode($istanzaTag->tag->categoria->nome ?? '') ?></span>
                                                <strong><?= Html::encode($istanzaTag->tag->nome ?? 'Tag #' . $istanzaTag->tag_id) ?></strong>
                                                <?php if ($istanzaTag->livello): ?>
                                                    <small class="text-muted ms-2">Liv. <?= $istanzaTag->livello ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php if ($istanzaTag->isPromosso()): ?>
                                                    <span class="badge bg-success" title="Promosso il <?= Yii::$app->formatter->asDatetime($istanzaTag->promosso_at) ?>">
                                                        <i class="ti ti-check"></i> Promosso
                                                    </span>
                                                <?php else: ?>
                                                    <?= Html::a(
                                                        '<i class="ti ti-arrow-up"></i>',
                                                        ['promuovi-tag', 'id' => $model->id, 'tagId' => $istanzaTag->id],
                                                        [
                                                            'class' => 'btn btn-sm btn-ghost-primary',
                                                            'title' => 'Promuovi al profilo docente',
                                                            'data' => [
                                                                'confirm' => 'Promuovere questo tag al profilo docente?',
                                                                'method' => 'post',
                                                            ],
                                                        ]
                                                    ) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Info Docente -->
                    <?php if ($model->docente): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Docente</h3>
                        </div>
                        <div class="card-body">
                            <h4><?= Html::a($model->docente->cognome . ' ' . $model->docente->nome, ['/docente/view', 'id' => $model->docente_codice_fiscale]) ?></h4>
                            <p class="text-muted mb-2"><?= Html::encode($model->docente->codice_fiscale) ?></p>
                            <?php if ($model->docente->email): ?>
                                <p class="mb-1"><i class="ti ti-mail me-2"></i><?= Html::encode($model->docente->email) ?></p>
                            <?php endif; ?>
                            <?php if ($model->docente->telefono): ?>
                                <p class="mb-1"><i class="ti ti-phone me-2"></i><?= Html::encode($model->docente->telefono) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Info Bando -->
                    <?php if ($model->avviso): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bando di Riferimento</h3>
                        </div>
                        <div class="card-body">
                            <h4><?= Html::a($model->avviso->codice_avviso, ['/avviso/view', 'id' => $model->avviso_id]) ?></h4>
                            <p class="text-muted"><?= Html::encode($model->avviso->titolo) ?></p>
                            <div>
                                <span class="badge <?= $model->avviso->getStatusBadgeClass() ?>"><?= $model->avviso->getStatusLabel() ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-3">
                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>
</div>
