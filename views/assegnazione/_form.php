<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Assegnazione;

/* @var $this yii\web\View */
/* @var $model app\models\Assegnazione */
/* @var $form yii\widgets\ActiveForm */
/* @var $moduli array */
/* @var $docenti array */
/* @var $istanze array */
?>

<div class="assegnazione-form">
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'card'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-circle-check me-2"></i>
            Dati Assegnazione
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'modulo_id')->dropDownList($moduli, [
                    'prompt' => 'Seleziona un modulo...',
                    'class' => 'form-select',
                ])->label('Modulo <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'docente_codice_fiscale')->dropDownList($docenti, [
                    'prompt' => 'Seleziona un docente...',
                    'class' => 'form-select',
                ])->label('Docente <span class="text-danger">*</span>') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'data_assegnazione')->textInput([
                    'type' => 'date',
                ])->label('Data Assegnazione <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'istanza_id')->dropDownList($istanze, [
                    'prompt' => 'Nessuna istanza collegata',
                    'class' => 'form-select',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'status')->dropDownList(Assegnazione::getStatusLabels(), [
                    'class' => 'form-select',
                ]) ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-gavel me-2"></i>
            Delibera CTS
        </h4>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'delibera_cts')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Numero/riferimento delibera',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'data_delibera')->textInput([
                    'type' => 'date',
                ]) ?>
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
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
