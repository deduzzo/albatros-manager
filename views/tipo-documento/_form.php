<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="tipo-documento-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'card']]); ?>
    <div class="card-header"><h3 class="card-title"><i class="ti ti-file-text me-2"></i>Dati Tipo Documento</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'codice')->textInput(['maxlength' => true, 'style' => 'text-transform: uppercase;', 'placeholder' => 'Es: CI, CF, CV'])->label('Codice <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-8">
                <?= $form->field($model, 'descrizione')->textInput(['maxlength' => true, 'placeholder' => 'Es: Carta d\'identita, Codice Fiscale'])->label('Descrizione <span class="text-danger">*</span>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'richiede_scadenza')->checkbox() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'attivo')->checkbox() ?>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
