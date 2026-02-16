<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Pagamento;

/* @var $this yii\web\View */
/* @var $model app\models\Pagamento */
/* @var $form yii\widgets\ActiveForm */
/* @var $contratti array */
?>

<div class="pagamento-form">
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'card'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-currency-euro me-2"></i>
            Dati Pagamento
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'contratto_id')->dropDownList($contratti, [
                    'prompt' => 'Seleziona un contratto...',
                    'class' => 'form-select',
                ])->label('Contratto <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'tipo')->dropDownList(Pagamento::getTipoLabels(), [
                    'class' => 'form-select',
                ])->label('Tipo <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Pagamento::getStatusLabels(), [
                    'class' => 'form-select',
                ])->label('Stato <span class="text-danger">*</span>') ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-cash me-2"></i>
            Importi
        </h4>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'importo_lordo')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '1000.00',
                ])->label('Importo Lordo (&euro;) <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'ritenute')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '200.00',
                ])->label('Ritenute (&euro;)') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'importo_netto')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '800.00',
                ])->label('Importo Netto (&euro;) <span class="text-danger">*</span>') ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-calendar me-2"></i>
            Date e Riferimenti
        </h4>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'data_pagamento')->textInput([
                    'type' => 'date',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'data_scadenza')->textInput([
                    'type' => 'date',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'riferimento_fattura')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Es: FT-2026/001',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'numero_mandato')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Es: M-001/2026',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'cro_bonifico')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Es: 1234567890',
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
