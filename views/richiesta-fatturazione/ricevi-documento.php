<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RichiestaFatturazione */

$this->title = 'Ricevi Documento - Richiesta #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Richieste Fatturazione', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Ricevi Documento';
?>

<div class="richiesta-fatturazione-ricevi-documento">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <h2 class="page-title">
                <i class="ti ti-upload me-2"></i>
                <?= Html::encode($this->title) ?>
            </h2>
            <div class="text-muted mt-1">
                Carica il documento fattura/cedolino ricevuto dal docente
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Info Richiesta -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Riepilogo Richiesta</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tipo</div>
                            <div class="datagrid-content"><?= $model->getTipoBadge() ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Destinatario</div>
                            <div class="datagrid-content"><?= Html::encode($model->destinatario_nome) ?> (<?= Html::encode($model->destinatario_email) ?>)</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Importo</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asCurrency($model->importo, 'EUR') ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Stato attuale</div>
                            <div class="datagrid-content"><?= $model->getStatusBadge() ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <?= Html::beginForm(['ricevi-documento', 'id' => $model->id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'card']) ?>
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-file-upload me-2"></i> Carica Documento</h3>
                </div>
                <div class="card-body">
                    <?php if ($model->hasDocumentoRicevuto()): ?>
                        <div class="alert alert-info mb-3">
                            <i class="ti ti-info-circle me-1"></i>
                            Un documento e' gia' stato caricato: <strong><?= Html::encode(basename($model->documento_ricevuto_path)) ?></strong>.
                            Il nuovo caricamento sostituira' il precedente.
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label required">Documento (PDF, immagine, ecc.)</label>
                        <input type="file" name="documento" class="form-control" required
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.p7m,.xml">
                        <small class="form-hint">Formati accettati: PDF, DOC, DOCX, JPG, PNG, P7M, XML</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary me-2']) ?>
                    <?= Html::submitButton('<i class="ti ti-upload me-1"></i> Carica Documento', ['class' => 'btn btn-green']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
