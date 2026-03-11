<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\MovimentoSpesa;

/* @var $this yii\web\View */
/* @var $model app\models\MovimentoSpesa */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $progetti array */
/* @var $vociSpesa array */
?>

<div class="movimento-spesa-form">
    <?php $form = AppActiveForm::begin([
        'id' => 'movimento-spesa-form',
        'options' => ['class' => 'card', 'enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-arrows-exchange me-2"></i>
            Dati Movimento
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'progetto_id')->dropDownList($progetti, [
                    'prompt' => '-- Nessun progetto (spesa generica) --',
                    'class' => 'form-select',
                ])->label('Progetto') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'voce_spesa_id')->dropDownList($vociSpesa, [
                    'prompt' => 'Seleziona una voce...',
                    'class' => 'form-select',
                ])->label('Voce di Spesa <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'tipo')->dropDownList(MovimentoSpesa::getTipoList(), [
                    'class' => 'form-select',
                ])->label('Tipo <span class="text-red">*</span>') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'descrizione')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Descrizione del movimento...',
                ])->label('Descrizione <span class="text-red">*</span>') ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-cash me-2"></i>
            Importo e Date
        </h4>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'importo')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0.01',
                    'placeholder' => '0.00',
                ])->label('Importo (&euro;) <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'data_movimento')->textInput([
                    'type' => 'date',
                ])->label('Data Movimento <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'data_documento')->textInput([
                    'type' => 'date',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'numero_documento')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Es: FT-001/2026',
                ]) ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-building me-2"></i>
            Fornitore e Allegato
        </h4>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'fornitore')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Nome fornitore...',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'allegato_path')->fileInput([
                    'class' => 'form-control',
                    'accept' => '.pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx',
                ])->label('Allegato (fattura, scontrino, etc.)') ?>
                <?php if (!$model->isNewRecord && $model->allegato_path): ?>
                    <div class="mt-1">
                        <span class="text-muted small">
                            <i class="ti ti-paperclip me-1"></i>
                            Allegato attuale: <?= Html::a(basename($model->allegato_path), Yii::getAlias('@web') . '/' . $model->allegato_path, ['target' => '_blank']) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'note')->textarea([
                    'rows' => 3,
                    'placeholder' => 'Note aggiuntive...',
                ]) ?>
            </div>
        </div>
    </div>

    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-green']) ?>
    </div>

    <?php AppActiveForm::end(); ?>
</div>
