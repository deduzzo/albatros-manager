<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\RendicontazioneOre */
/* @var $assegnazione app\models\Assegnazione */
/* @var $oreApprovate float */
/* @var $oreInAttesa float */
/* @var $isModifica bool */

$modulo = $assegnazione->modulo;
$contratto = $assegnazione->contratto;
$oreContratto = $contratto->ore_contrattualizzate ?? ($modulo->ore_totali ?? 0);
$oreDisponibili = max(0, $oreContratto - $oreApprovate - $oreInAttesa);
$superaContratto = $oreDisponibili <= 0;

$this->title = ($isModifica ? 'Modifica' : 'Inserisci') . ' Ore';
$this->params['breadcrumbs'][] = ['label' => 'Le Mie Assegnazioni', 'url' => ['le-mie-assegnazioni']];
$this->params['breadcrumbs'][] = ['label' => $modulo->codice, 'url' => ['le-mie-ore', 'assegnazione_id' => $assegnazione->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-clock-plus me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
            <div class="page-subtitle text-secondary">
                Registra le ore di lezione svolte per il modulo <?= Html::encode($modulo->codice) ?>
            </div>
        </div>

        <?php $form = AppActiveForm::begin([
            'id' => 'inserisci-ore-form',
        ]); ?>

        <div class="row">
            <div class="col-lg-8">
                <!-- Info Assegnazione -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Dettagli Assegnazione</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-5">Modulo:</dt>
                                    <dd class="col-7">
                                        <span class="badge bg-azure-lt"><?= Html::encode($modulo->codice) ?></span>
                                        <div class="small text-muted"><?= Html::encode($modulo->titolo) ?></div>
                                    </dd>
                                    <dt class="col-5">Corso:</dt>
                                    <dd class="col-7">
                                        <span class="badge bg-blue-lt"><?= Html::encode($modulo->avviso->codice_avviso ?? '-') ?></span>
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <?php if ($contratto): ?>
                                        <dt class="col-5">Contratto:</dt>
                                        <dd class="col-7">
                                            <span class="badge <?= $contratto->getStatusBadgeClass() ?>"><?= $contratto->getStatusLabel() ?></span>
                                        </dd>
                                        <dt class="col-5">Tipo:</dt>
                                        <dd class="col-7"><?= Html::encode($contratto->getTipoLabel()) ?></dd>
                                    <?php else: ?>
                                        <dt class="col-5">Contratto:</dt>
                                        <dd class="col-7"><span class="text-muted">Non disponibile</span></dd>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Ore -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-clock me-2"></i> Dati Ore</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($superaContratto): ?>
                            <div class="alert alert-warning">
                                <div class="d-flex">
                                    <div><i class="ti ti-alert-triangle me-2"></i></div>
                                    <div>
                                        <h4 class="alert-title">Attenzione: ore contrattuali esaurite</h4>
                                        Le ore contrattualizzate sono state tutte utilizzate (approvate + in attesa).
                                        L'inserimento di nuove ore sara soggetto ad approvazione straordinaria.
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?= Html::activeHiddenInput($model, 'assegnazione_id') ?>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <?= $form->field($model, 'data')->input('date', [
                                    'class' => 'form-control',
                                    'value' => $model->data ?: date('Y-m-d'),
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'ore')->input('number', [
                                    'class' => 'form-control',
                                    'step' => '0.25',
                                    'min' => '0.25',
                                    'max' => '24',
                                    'placeholder' => 'Es: 4',
                                ])->hint('Minimo 0,25 - incrementi di 0,25') ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <?= $form->field($model, 'ora_inizio')->input('time', [
                                    'class' => 'form-control',
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'ora_fine')->input('time', [
                                    'class' => 'form-control',
                                ]) ?>
                            </div>
                        </div>

                        <?= $form->field($model, 'note')->textarea([
                            'rows' => 3,
                            'placeholder' => 'Argomenti trattati, attivita svolte...',
                        ]) ?>

                        <div class="mb-3">
                            <hr>
                            <label class="form-check">
                                <?= Html::activeCheckbox($model, 'firma_digitale', [
                                    'class' => 'form-check-input',
                                    'label' => false,
                                ]) ?>
                                <span class="form-check-label">
                                    <strong>Dichiaro che le ore inserite corrispondono all'attivita effettivamente svolta</strong>
                                    <span class="d-block text-secondary small">
                                        Questa dichiarazione ha valore di firma digitale ai sensi della normativa vigente.
                                    </span>
                                </span>
                            </label>
                            <?php if ($model->hasErrors('firma_digitale')): ?>
                                <div class="invalid-feedback d-block"><?= $model->getFirstError('firma_digitale') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <?= Html::a('Annulla', ['le-mie-ore', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-ghost-secondary me-2']) ?>
                        <?= Html::submitButton('<i class="ti ti-check me-2"></i> ' . ($isModifica ? 'Salva Modifiche' : 'Inserisci Ore'), [
                            'class' => 'btn btn-primary',
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Riepilogo Ore -->
                <div class="card mb-3 sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h3 class="card-title">Riepilogo Ore</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-7">Ore contrattualizzate:</dt>
                            <dd class="col-5 text-end"><strong><?= $oreContratto ?></strong></dd>

                            <dt class="col-7">Ore approvate:</dt>
                            <dd class="col-5 text-end text-success"><?= number_format($oreApprovate, 1, ',', '.') ?></dd>

                            <dt class="col-7">Ore in attesa:</dt>
                            <dd class="col-5 text-end text-warning"><?= number_format($oreInAttesa, 1, ',', '.') ?></dd>

                            <dt class="col-7 border-top pt-2">Ore disponibili:</dt>
                            <dd class="col-5 text-end border-top pt-2">
                                <?php if ($superaContratto): ?>
                                    <span class="text-danger"><strong>0</strong></span>
                                <?php else: ?>
                                    <strong><?= number_format($oreDisponibili, 1, ',', '.') ?></strong>
                                <?php endif; ?>
                            </dd>
                        </dl>

                        <!-- Progress bar -->
                        <?php
                            $percApprovate = $oreContratto > 0 ? min(100, ($oreApprovate / $oreContratto) * 100) : 0;
                            $percInAttesa = $oreContratto > 0 ? min(100 - $percApprovate, ($oreInAttesa / $oreContratto) * 100) : 0;
                        ?>
                        <div class="progress progress-sm mt-3">
                            <div class="progress-bar bg-green" style="width: <?= $percApprovate ?>%"></div>
                            <div class="progress-bar bg-orange" style="width: <?= $percInAttesa ?>%"></div>
                        </div>
                        <div class="small text-muted mt-1 text-center">
                            <?= number_format($percApprovate + $percInAttesa, 0) ?>% utilizzato
                        </div>
                    </div>

                    <?php if ($contratto): ?>
                        <div class="card-footer">
                            <dl class="row mb-0 small">
                                <dt class="col-7">Compenso orario:</dt>
                                <dd class="col-5 text-end"><?= number_format($contratto->compenso_orario, 2, ',', '.') ?> &euro;</dd>
                                <dt class="col-7">Compenso totale:</dt>
                                <dd class="col-5 text-end"><?= number_format($contratto->compenso_totale, 2, ',', '.') ?> &euro;</dd>
                            </dl>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php AppActiveForm::end(); ?>
    </div>
</div>
