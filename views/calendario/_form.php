<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Calendario;
?>
<div class="calendario-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'card']]); ?>
    <div class="card-header"><h3 class="card-title"><i class="ti ti-calendar me-2"></i>Dati Lezione</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'modulo_id')->dropDownList($moduli, ['prompt' => '-- Seleziona Modulo --'])->label('Modulo <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'docente_codice_fiscale')->dropDownList($docenti, ['prompt' => '-- Seleziona Docente --'])->label('Docente <span class="text-danger">*</span>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'data')->input('date')->label('Data <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'ora_inizio')->input('time')->label('Ora Inizio <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'ora_fine')->input('time')->label('Ora Fine <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Calendario::getStatusLabels()) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'aula')->textInput(['maxlength' => true, 'placeholder' => 'Es: Aula 3']) ?>
            </div>
            <div class="col-md-8">
                <?= $form->field($model, 'argomento')->textInput(['maxlength' => true, 'placeholder' => 'Argomento della lezione']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'descrizione_attivita')->textarea(['rows' => 3, 'placeholder' => 'Descrizione delle attività svolte']) ?>
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
