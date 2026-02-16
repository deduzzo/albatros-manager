<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Iscrizione;
?>
<div class="iscrizione-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'card']]); ?>
    <div class="card-header"><h3 class="card-title"><i class="ti ti-user-plus me-2"></i>Dati Iscrizione</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'allievo_codice_fiscale')->dropDownList($allievi, ['prompt' => '-- Seleziona Allievo --'])->label('Allievo <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'modulo_id')->dropDownList($moduli, ['prompt' => '-- Seleziona Modulo --'])->label('Modulo <span class="text-danger">*</span>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'data_iscrizione')->input('date')->label('Data Iscrizione <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Iscrizione::getStatusLabels()) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'esito_finale')->dropDownList(Iscrizione::getEsitoLabels(), ['prompt' => '-- Non definito --']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'percentuale_frequenza')->textInput(['type' => 'number', 'min' => 0, 'max' => 100, 'step' => '0.01', 'placeholder' => '0-100']) ?>
            </div>
        </div>
    </div>

    <div class="card-header"><h3 class="card-title"><i class="ti ti-user-minus me-2"></i>Ritiro (opzionale)</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'data_ritiro')->input('date') ?>
            </div>
            <div class="col-md-8">
                <?= $form->field($model, 'motivo_ritiro')->textInput(['maxlength' => true, 'placeholder' => 'Motivo del ritiro']) ?>
            </div>
        </div>
    </div>

    <div class="card-header"><h3 class="card-title"><i class="ti ti-notes me-2"></i>Note</h3></div>
    <div class="card-body">
        <?= $form->field($model, 'note')->textarea(['rows' => 3, 'placeholder' => 'Note aggiuntive'])->label(false) ?>
    </div>

    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
