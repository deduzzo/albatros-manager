<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="conto-corrente-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'card']]); ?>
    <div class="card-header"><h3 class="card-title"><i class="ti ti-building-bank me-2"></i>Dati Conto Corrente</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'banca')->textInput(['maxlength' => true, 'placeholder' => 'Nome banca'])->label('Banca <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'iban')->textInput(['maxlength' => 34, 'style' => 'text-transform: uppercase;', 'placeholder' => 'IT60X0542811101000000123456'])->label('IBAN <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'intestatario')->textInput(['maxlength' => true, 'placeholder' => 'Intestatario conto'])->label('Intestatario <span class="text-danger">*</span>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'note')->textarea(['rows' => 3, 'placeholder' => 'Note aggiuntive...']) ?>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
