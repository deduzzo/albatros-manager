<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Allievo */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="allievo-form">
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'card'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-school me-2"></i>
            Dati Anagrafici
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'codice_fiscale', [
                    'inputOptions' => [
                        'class' => 'form-control',
                        'style' => 'text-transform: uppercase;',
                        'maxlength' => 16,
                        'placeholder' => 'RSSMRA80A01F205X',
                    ],
                ])->label('Codice Fiscale <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'cognome')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Rossi',
                ])->label('Cognome <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'nome')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Mario',
                ])->label('Nome <span class="text-danger">*</span>') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'data_nascita')->textInput([
                    'type' => 'date',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'luogo_nascita')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Roma',
                ]) ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-map-pin me-2"></i>
            Indirizzo
        </h4>

        <div class="row">
            <div class="col-md-5">
                <?= $form->field($model, 'indirizzo')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Via Roma, 123',
                ]) ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'cap')->textInput([
                    'maxlength' => 5,
                    'placeholder' => '00100',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'citta')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Roma',
                ]) ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'provincia')->textInput([
                    'maxlength' => 2,
                    'style' => 'text-transform: uppercase;',
                    'placeholder' => 'RM',
                ]) ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-address-book me-2"></i>
            Contatti
        </h4>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'telefono')->textInput([
                    'maxlength' => true,
                    'placeholder' => '+39 06 12345678',
                ]) ?>
            </div>
            <div class="col-md-8">
                <?= $form->field($model, 'email')->textInput([
                    'type' => 'email',
                    'maxlength' => true,
                    'placeholder' => 'mario.rossi@example.com',
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
