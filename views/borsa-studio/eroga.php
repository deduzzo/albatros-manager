<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\BorsaStudio $model */

$this->title = 'Eroga Trimestre - Borsa #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Borse di Studio', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Borsa #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Erogazione';

$formatter = Yii::$app->formatter;
$importoTrimestre = $model->getImportoPerTrimestre();
$residuo = $model->getResiduoDaErogare();
$preselezionaQ = Yii::$app->request->get('q', null);
?>
<div class="borsa-studio-eroga">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-cash me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">
                        Studente: <strong><?= $model->allievo ? Html::encode($model->allievo->getNomeCompleto()) : $model->allievo_codice_fiscale ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <!-- Riepilogo -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-report-money me-2"></i>Riepilogo Borsa</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Importo Totale</label>
                                <div class="h2"><?= $formatter->asCurrency($model->importo_finale, 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Gia Erogato</label>
                                <div class="h2 text-green"><?= $formatter->asCurrency($model->totale_erogato, 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Residuo</label>
                                <div class="h2 text-orange"><?= $formatter->asCurrency($residuo, 'EUR') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-green" style="width: <?= $model->getPercentualeErogazione() ?>%"></div>
                    </div>
                    <small class="text-muted"><?= $model->getPercentualeErogazione() ?>% erogato</small>
                </div>
            </div>

            <!-- Form Erogazione -->
            <form method="post" action="<?= \yii\helpers\Url::to(['eroga', 'id' => $model->id]) ?>" class="card">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                <div class="card-header"><h3 class="card-title"><i class="ti ti-cash me-2"></i>Dati Erogazione</h3></div>
                <div class="card-body">
                    <!-- Selezione Trimestre -->
                    <div class="mb-3">
                        <label class="form-label">Trimestre <span class="text-danger">*</span></label>
                        <div class="row">
                            <?php for ($q = 1; $q <= 4; $q++): ?>
                                <?php
                                $campo = 'importo_q' . $q;
                                $giaErogato = (float) $model->$campo > 0;
                                $preselect = ($preselezionaQ == $q) && !$giaErogato;
                                ?>
                                <div class="col-md-3">
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="trimestre" value="<?= $q ?>" class="form-selectgroup-input"
                                            <?= $giaErogato ? 'disabled' : '' ?>
                                            <?= $preselect ? 'checked' : '' ?>>
                                        <span class="form-selectgroup-label d-flex align-items-center p-3 <?= $giaErogato ? 'bg-green-lt' : '' ?>">
                                            <span class="me-3">
                                                <span class="form-selectgroup-check"></span>
                                            </span>
                                            <span class="form-selectgroup-label-content">
                                                <span class="form-selectgroup-title strong mb-1">Q<?= $q ?></span>
                                                <?php if ($giaErogato): ?>
                                                    <span class="d-block text-green">
                                                        <i class="ti ti-check me-1"></i>Erogato: <?= $formatter->asCurrency($model->$campo, 'EUR') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="d-block text-muted">Non erogato</span>
                                                <?php endif; ?>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Importo da erogare (€) <span class="text-danger">*</span></label>
                                <input type="number" name="importo" class="form-control" step="0.01" min="0"
                                    value="<?= $importoTrimestre ?>" required>
                                <small class="text-muted">Importo previsto per trimestre: <?= $formatter->asCurrency($importoTrimestre, 'EUR') ?></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Data Erogazione</label>
                                <input type="date" name="data_erogazione" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Note</label>
                                <input type="text" name="note_erogazione" class="form-control" placeholder="Note opzionali">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary me-2']) ?>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-cash me-1"></i> Conferma Erogazione
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
