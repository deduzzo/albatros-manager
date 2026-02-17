<?php

use yii\helpers\Html;
use app\models\RendicontazioneOre;

/* @var $this yii\web\View */
/* @var $model app\models\RendicontazioneOre */

$assegnazione = $model->assegnazione;
$docente = $model->docente;
$modulo = $model->modulo;

$this->title = 'Rendicontazione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Rendicontazione Ore', 'url' => ['dashboard']];
if ($assegnazione) {
    $this->params['breadcrumbs'][] = ['label' => 'Assegnazione #' . $assegnazione->id, 'url' => ['index', 'assegnazione_id' => $model->assegnazione_id]];
}
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rendicontazione-ore-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-clock-hour-4 me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                        <?php if ($docente): ?>
                            <span class="ms-2">
                                <i class="ti ti-user me-1"></i>
                                <?= Html::a(Html::encode($docente->cognome . ' ' . $docente->nome), ['/docente/view', 'id' => $docente->codice_fiscale]) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <?php if ($model->status === RendicontazioneOre::STATUS_INSERITO): ?>
                        <?= Html::a('<i class="ti ti-check me-1"></i> Approva', ['approva', 'id' => $model->id], [
                            'class' => 'btn btn-success me-1',
                            'data' => [
                                'confirm' => 'Approvare la rendicontazione del ' . Yii::$app->formatter->asDate($model->data) . ' (' . number_format($model->ore, 1, ',', '.') . ' ore)?',
                                'method' => 'post',
                            ],
                        ]) ?>
                        <?= Html::button('<i class="ti ti-x me-1"></i> Rifiuta', [
                            'class' => 'btn btn-danger me-1',
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#modal-rifiuta',
                        ]) ?>
                        <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-1']) ?>
                        <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-outline-danger',
                            'data' => [
                                'confirm' => 'Sei sicuro di voler eliminare questa rendicontazione?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php else: ?>
                        <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index', 'assegnazione_id' => $model->assegnazione_id], ['class' => 'btn btn-secondary']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <!-- Colonna principale -->
                <div class="col-lg-8">
                    <!-- Stat Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-blue-lt me-3"><i class="ti ti-calendar"></i></span>
                                        <div>
                                            <div class="text-muted small">Data</div>
                                            <div class="fw-bold"><?= Yii::$app->formatter->asDate($model->data, 'long') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-green-lt me-3"><i class="ti ti-clock"></i></span>
                                        <div>
                                            <div class="text-muted small">Ore</div>
                                            <div class="fw-bold h3 mb-0"><?= number_format($model->ore, 1, ',', '.') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar <?= $model->firma_digitale ? 'bg-green-lt' : 'bg-default' ?> me-3">
                                            <i class="ti ti-<?= $model->firma_digitale ? 'certificate' : 'certificate-off' ?>"></i>
                                        </span>
                                        <div>
                                            <div class="text-muted small">Firma Digitale</div>
                                            <div class="fw-bold"><?= $model->firma_digitale ? 'Si' : 'No' ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dettagli principali -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Dettagli Rendicontazione</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">ID</div>
                                    <div class="datagrid-content">#<?= $model->id ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asDate($model->data, 'long') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ore</div>
                                    <div class="datagrid-content"><strong><?= number_format($model->ore, 1, ',', '.') ?></strong></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Orario</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->ora_inizio && $model->ora_fine): ?>
                                            <?= substr($model->ora_inizio, 0, 5) ?> - <?= substr($model->ora_fine, 0, 5) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non specificato</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Stato</div>
                                    <div class="datagrid-content">
                                        <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Firma Digitale</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->firma_digitale): ?>
                                            <i class="ti ti-certificate text-success me-1"></i> Firmato
                                        <?php else: ?>
                                            <i class="ti ti-certificate-off text-muted me-1"></i> Non firmato
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Creato il</div>
                                    <div class="datagrid-content"><?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Aggiornato il</div>
                                    <div class="datagrid-content"><?= $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at) : '-' ?></div>
                                </div>
                            </div>

                            <?php if ($model->note): ?>
                                <hr>
                                <div class="mb-2"><strong><i class="ti ti-notes me-1"></i>Note:</strong></div>
                                <p class="text-muted mb-0"><?= nl2br(Html::encode($model->note)) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Approvazione / Rifiuto -->
                    <?php if ($model->status === RendicontazioneOre::STATUS_APPROVATO || $model->status === RendicontazioneOre::STATUS_RIFIUTATO): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <?php if ($model->status === RendicontazioneOre::STATUS_APPROVATO): ?>
                                    <i class="ti ti-circle-check me-2 text-success"></i>Approvazione
                                <?php else: ?>
                                    <i class="ti ti-circle-x me-2 text-danger"></i>Rifiuto
                                <?php endif; ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title"><?= $model->status === RendicontazioneOre::STATUS_APPROVATO ? 'Approvato da' : 'Rifiutato da' ?></div>
                                    <div class="datagrid-content">
                                        <?php if ($model->approvatoDaUser): ?>
                                            <i class="ti ti-user me-1"></i><?= Html::encode($model->approvatoDaUser->username) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/D</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data</div>
                                    <div class="datagrid-content">
                                        <?= $model->approvato_at ? Yii::$app->formatter->asDatetime($model->approvato_at) : '-' ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($model->motivo_rifiuto): ?>
                                <hr>
                                <div class="alert alert-danger mb-0">
                                    <div class="d-flex">
                                        <div><i class="ti ti-alert-circle me-2"></i></div>
                                        <div>
                                            <h4 class="alert-title">Motivo del Rifiuto</h4>
                                            <div><?= nl2br(Html::encode($model->motivo_rifiuto)) ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Colonna laterale -->
                <div class="col-lg-4">
                    <!-- Info Docente -->
                    <?php if ($docente): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-user me-2"></i>Docente</h3>
                        </div>
                        <div class="card-body">
                            <h4 class="mb-1"><?= Html::a(Html::encode($docente->cognome . ' ' . $docente->nome), ['/docente/view', 'id' => $docente->codice_fiscale]) ?></h4>
                            <p class="text-muted mb-2"><code><?= Html::encode($docente->codice_fiscale) ?></code></p>
                            <?php if ($docente->email): ?>
                                <p class="mb-1"><i class="ti ti-mail me-2"></i><?= Html::encode($docente->email) ?></p>
                            <?php endif; ?>
                            <?php if ($docente->telefono): ?>
                                <p class="mb-1"><i class="ti ti-phone me-2"></i><?= Html::encode($docente->telefono) ?></p>
                            <?php endif; ?>
                            <?php if ($docente->provenienza): ?>
                                <p class="mb-0">
                                    <span class="badge <?= $docente->provenienza === 'TP' ? 'bg-blue-lt' : 'bg-purple-lt' ?>">
                                        <?= $docente->provenienza === 'TP' ? 'Professionista' : 'Ricercatore Universitario' ?>
                                    </span>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Info Assegnazione / Modulo -->
                    <?php if ($assegnazione): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-book me-2"></i>Assegnazione / Modulo</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($modulo): ?>
                                <div class="mb-2">
                                    <span class="badge bg-blue-lt me-1"><?= Html::encode($modulo->codice) ?></span>
                                    <?= Html::a(Html::encode($modulo->titolo), ['/modulo/view', 'id' => $modulo->id]) ?>
                                </div>
                            <?php endif; ?>
                            <div class="mb-2">
                                <?= Html::a('Assegnazione #' . $assegnazione->id, ['/assegnazione/view', 'id' => $assegnazione->id], ['class' => 'text-reset']) ?>
                            </div>
                            <?php
                            $contratto = $assegnazione->contratto;
                            if ($contratto):
                                $oreContrattualizzate = $contratto->ore_contrattualizzate;
                                $oreApprovate = RendicontazioneOre::getTotaleOreApprovate($assegnazione->id);
                                $percOre = $oreContrattualizzate > 0 ? round(($oreApprovate / $oreContrattualizzate) * 100) : 0;
                            ?>
                                <hr class="my-2">
                                <div class="mb-2">
                                    <small class="text-muted">Contratto:</small>
                                    <?= Html::a($contratto->numero_contratto ?: '#' . $contratto->id, ['/contratto/view', 'id' => $contratto->id], ['class' => 'text-reset']) ?>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Ore approvate</small>
                                    <small class="fw-bold"><?= number_format($oreApprovate, 1, ',', '.') ?> / <?= $oreContrattualizzate ?></small>
                                </div>
                                <div class="progress mb-2" style="height: 6px;">
                                    <div class="progress-bar <?= $percOre > 100 ? 'bg-red' : ($percOre >= 100 ? 'bg-green' : 'bg-blue') ?>"
                                         style="width: <?= min($percOre, 100) ?>%"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Navigazione -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?= Html::a('<i class="ti ti-list me-1"></i> Tutte le rendicontazioni', ['index', 'assegnazione_id' => $model->assegnazione_id], ['class' => 'btn btn-secondary']) ?>
                                <?= Html::a('<i class="ti ti-chart-bar me-1"></i> Riepilogo mensile', ['riepilogo', 'assegnazione_id' => $model->assegnazione_id], ['class' => 'btn btn-outline-info']) ?>
                                <?= Html::a('<i class="ti ti-dashboard me-1"></i> Dashboard Ore', ['dashboard'], ['class' => 'btn btn-outline-primary']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($model->status === RendicontazioneOre::STATUS_INSERITO): ?>
<!-- Modal Rifiuto -->
<div class="modal modal-blur fade" id="modal-rifiuta" tabindex="-1" role="dialog" aria-hidden="true">
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
