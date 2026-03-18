<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RimborsoSpesa */

$assegnazione = $model->assegnazione;
$docente = $assegnazione ? $assegnazione->docente : null;
$modulo = $assegnazione ? $assegnazione->modulo : null;
$docenteNome = $docente ? $docente->cognome . ' ' . $docente->nome : 'N/D';
$dettagli = $model->dettagli;
$totale = $model->getTotaleImporto();

$this->title = 'Rimborso Spesa #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Rimborsi Spesa', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rimborso-spesa-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-receipt-2 me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($docenteNome) ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-1']) ?>
                    <?php if ($model->status === \app\models\RimborsoSpesa::STATUS_RICHIESTO): ?>
                        <?= Html::a('<i class="ti ti-check me-1"></i> Approva', ['approva', 'id' => $model->id], [
                            'class' => 'btn btn-green me-1',
                            'data' => [
                                'confirm' => 'Confermi l\'approvazione del rimborso?',
                                'method' => 'post',
                            ],
                        ]) ?>
                        <button type="button" class="btn btn-red" data-bs-toggle="modal" data-bs-target="#modal-rifiuta">
                            <i class="ti ti-x me-1"></i> Rifiuta
                        </button>
                    <?php endif; ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger ms-1',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo rimborso?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <!-- Stat Cards -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Stato</div>
                            <div class="h1 mb-0 mt-2"><?= $model->getStatusBadge() ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Totale Importo</div>
                            <div class="h1 mb-0 mt-2"><?= Yii::$app->formatter->asCurrency($totale, 'EUR') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Data Spesa</div>
                            <div class="h1 mb-0 mt-2"><?= Yii::$app->formatter->asDate($model->data_spesa) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Dettagli Rimborso -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Dettagli Rimborso</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">ID</div>
                                    <div class="datagrid-content"><?= $model->id ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Docente</div>
                                    <div class="datagrid-content">
                                        <?php if ($docente): ?>
                                            <?= Html::a(Html::encode($docenteNome), ['/docente/view', 'id' => $docente->codice_fiscale]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Modulo</div>
                                    <div class="datagrid-content">
                                        <?php if ($modulo): ?>
                                            <?= Html::a(Html::encode($modulo->codice . ' - ' . $modulo->titolo), ['/modulo/view', 'id' => $modulo->id]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Descrizione</div>
                                    <div class="datagrid-content"><?= Html::encode($model->descrizione) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Creato il</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                                </div>
                            </div>

                            <?php if ($model->note): ?>
                                <hr>
                                <div class="mb-2"><strong>Note:</strong></div>
                                <p class="text-muted"><?= nl2br(Html::encode($model->note)) ?></p>
                            <?php endif; ?>

                            <?php if ($model->motivo_rifiuto): ?>
                                <hr>
                                <div class="alert alert-danger">
                                    <h4 class="alert-title"><i class="ti ti-x me-1"></i> Motivo Rifiuto</h4>
                                    <p class="mb-0"><?= nl2br(Html::encode($model->motivo_rifiuto)) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($model->approvato_da): ?>
                                <hr>
                                <div class="datagrid">
                                    <div class="datagrid-item">
                                        <div class="datagrid-title"><?= $model->status === 'approvato' ? 'Approvato da' : 'Gestito da' ?></div>
                                        <div class="datagrid-content">
                                            <?php $user = $model->approvatoDaUser; ?>
                                            <?= $user ? Html::encode($user->username) : 'ID: ' . $model->approvato_da ?>
                                        </div>
                                    </div>
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Data</div>
                                        <div class="datagrid-content"><?= $model->approvato_at ? Yii::$app->formatter->asDatetime($model->approvato_at) : '-' ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Voci di Spesa + Allegato -->
                <div class="col-lg-6">
                    <!-- Voci di spesa dettaglio -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-list-details me-2"></i> Voci di Spesa</h3>
                        </div>
                        <?php if (!empty($dettagli)): ?>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Voce</th>
                                            <th>Descrizione</th>
                                            <th class="text-end">Importo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dettagli as $dettaglio): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($dettaglio->voceSpesa): ?>
                                                        <span class="badge bg-blue-lt"><?= Html::encode($dettaglio->voceSpesa->getLabel()) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= Html::encode($dettaglio->descrizione) ?>
                                                    <?php if ($dettaglio->note): ?>
                                                        <br><small class="text-muted"><?= Html::encode($dettaglio->note) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($dettaglio->importo, 'EUR') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="2" class="text-end">Totale</td>
                                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($totale, 'EUR') ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="card-body text-center text-muted">
                                <i class="ti ti-receipt-off" style="font-size: 2rem;"></i>
                                <p class="mt-2">Nessuna voce di spesa registrata.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Allegato -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-paperclip me-2"></i> Allegato</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($model->allegato_path): ?>
                                <?php
                                    $ext = strtolower(pathinfo($model->allegato_path, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    $isPdf = $ext === 'pdf';
                                    $fileUrl = Yii::getAlias('@web/' . $model->allegato_path);
                                ?>

                                <?php if ($isImage): ?>
                                    <div class="text-center mb-3">
                                        <img src="<?= Html::encode($fileUrl) ?>" class="img-fluid rounded" style="max-height: 400px;" alt="Allegato rimborso">
                                    </div>
                                <?php elseif ($isPdf): ?>
                                    <div class="ratio ratio-4x3 mb-3">
                                        <iframe src="<?= Html::encode($fileUrl) ?>" style="border: 1px solid #ddd; border-radius: 8px;"></iframe>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ti ti-file" style="font-size: 3rem; color: #999;"></i>
                                        <p class="text-muted mt-2"><?= Html::encode(basename($model->allegato_path)) ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="text-center">
                                    <?= Html::a(
                                        '<i class="ti ti-download me-1"></i> Scarica allegato',
                                        $fileUrl,
                                        ['class' => 'btn btn-outline-blue', 'target' => '_blank', 'download' => true]
                                    ) ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="ti ti-file-off" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Nessun allegato caricato.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rifiuto -->
<?php if ($model->status === \app\models\RimborsoSpesa::STATUS_RICHIESTO): ?>
<div class="modal modal-blur fade" id="modal-rifiuta" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <?= Html::beginForm(['rifiuta', 'id' => $model->id], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-x me-2 text-red"></i>Rifiuta Rimborso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Motivo del rifiuto</label>
                    <textarea name="motivo_rifiuto" class="form-control" rows="4" required
                              placeholder="Indica il motivo del rifiuto..."></textarea>
                    <small class="form-hint">Il motivo sara' visibile al docente.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="submit" class="btn btn-red">
                    <i class="ti ti-x me-1"></i> Conferma Rifiuto
                </button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php endif; ?>
