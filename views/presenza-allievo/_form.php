<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="presenza-allievo-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'card']]); ?>
    <div class="card-header"><h3 class="card-title"><i class="ti ti-checkbox me-2"></i>Registra Presenza</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'calendario_id')->dropDownList($calendari, ['prompt' => '-- Seleziona Lezione --'])->label('Lezione <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'allievo_codice_fiscale')->dropDownList($allievi, ['prompt' => '-- Seleziona Allievo --'])->label('Allievo <span class="text-danger">*</span>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'presente')->checkbox() ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'giustificato')->checkbox() ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'ora_ingresso')->input('time') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'ora_uscita')->input('time') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'note')->textarea(['rows' => 2, 'placeholder' => 'Note aggiuntive']) ?>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
