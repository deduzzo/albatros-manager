<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\RimborsoSpesa;

/* @var $this yii\web\View */
/* @var $model app\models\RimborsoSpesa */
/* @var $assegnazione app\models\Assegnazione */
/* @var $isModifica bool */

$modulo = $assegnazione->modulo;

$this->title = ($isModifica ? 'Modifica' : 'Nuovo') . ' Rimborso Spesa';
$this->params['breadcrumbs'][] = ['label' => 'I Miei Rimborsi', 'url' => ['rimborsi']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-receipt-2 me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
            <div class="page-subtitle text-secondary">
                Modulo: <span class="badge bg-azure-lt"><?= Html::encode($modulo->codice) ?></span>
                <?= Html::encode($modulo->titolo) ?>
            </div>
        </div>

        <?php $form = AppActiveForm::begin([
            'id' => 'rimborso-spesa-form',
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Dati Rimborso</h3>
                    </div>
                    <div class="card-body">
                        <?= $form->field($model, 'assegnazione_id')->hiddenInput()->label(false) ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'tipo')->dropDownList(RimborsoSpesa::getTipoList(), [
                                    'prompt' => '-- Seleziona tipo --',
                                    'class' => 'form-select',
                                ])->label('Tipo di spesa <span class="text-red">*</span>') ?>
                            </div>
                            <div class="col-md-3">
                                <?= $form->field($model, 'importo')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0.01',
                                    'placeholder' => '0.00',
                                ])->label('Importo (&euro;) <span class="text-red">*</span>') ?>
                            </div>
                            <div class="col-md-3">
                                <?= $form->field($model, 'data_spesa')->textInput([
                                    'type' => 'date',
                                ])->label('Data spesa <span class="text-red">*</span>') ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'descrizione')->textarea([
                                    'rows' => 3,
                                    'placeholder' => 'Descrivi la spesa sostenuta...',
                                    'maxlength' => 500,
                                ])->label('Descrizione <span class="text-red">*</span>') ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'note')->textarea([
                                    'rows' => 2,
                                    'placeholder' => 'Note aggiuntive (opzionale)...',
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Allegato -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-paperclip me-2"></i> Allegato</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($isModifica && $model->allegato_path): ?>
                            <div class="alert alert-info mb-3">
                                <i class="ti ti-file me-1"></i>
                                Allegato attuale: <strong><?= Html::encode(basename($model->allegato_path)) ?></strong>
                                <?= Html::a('Visualizza', Yii::getAlias('@web/' . $model->allegato_path), ['target' => '_blank', 'class' => 'ms-2']) ?>
                                <br><small class="text-muted">Caricando un nuovo file, quello attuale verra' sostituito.</small>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label <?= !$isModifica ? 'required' : '' ?>">
                                Ricevuta/Scontrino <?= !$isModifica ? '<span class="text-red">*</span>' : '(opzionale)' ?>
                            </label>
                            <input type="file" name="allegato" class="form-control"
                                   <?= !$isModifica ? 'required' : '' ?>
                                   accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                            <small class="form-hint">Formati accettati: PDF, JPG, PNG, GIF, WEBP. Max 5MB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Informazioni</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Modulo:</dt>
                            <dd class="col-7">
                                <span class="badge bg-azure-lt"><?= Html::encode($modulo->codice) ?></span>
                            </dd>
                            <dt class="col-5">Avviso:</dt>
                            <dd class="col-7">
                                <span class="badge bg-blue-lt"><?= Html::encode($modulo->avviso->codice_avviso ?? '-') ?></span>
                            </dd>
                        </dl>

                        <hr>

                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-1"></i>
                            <strong>Nota:</strong> Il rimborso sara' esaminato dall'amministrazione.
                            Ricordati di allegare sempre la ricevuta o lo scontrino della spesa.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="card">
            <div class="card-footer text-end">
                <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['rimborsi'], ['class' => 'btn btn-secondary me-2']) ?>
                <?= Html::submitButton('<i class="ti ti-check me-1"></i> ' . ($isModifica ? 'Salva Modifiche' : 'Invia Richiesta'), ['class' => 'btn btn-green']) ?>
            </div>
        </div>

        <?php AppActiveForm::end(); ?>
    </div>
</div>
