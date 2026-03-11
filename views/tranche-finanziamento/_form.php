<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\TrancheFinanziamento;

/* @var $this yii\web\View */
/* @var $model app\models\TrancheFinanziamento */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $progetti array */
?>

<div class="tranche-finanziamento-form">
    <?php $form = AppActiveForm::begin([
        'id' => 'tranche-form',
        'options' => ['class' => 'card'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-coins me-2"></i>
            Dati Tranche
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'progetto_id')->dropDownList($progetti, [
                    'prompt' => 'Seleziona un progetto...',
                    'class' => 'form-select',
                ])->label('Progetto <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'numero_tranche')->textInput([
                    'type' => 'number',
                    'min' => '1',
                    'placeholder' => 'Es: 1',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(TrancheFinanziamento::getStatusLabels(), [
                    'class' => 'form-select',
                ])->label('Stato <span class="text-red">*</span>') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'importo')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0.01',
                    'placeholder' => '0.00',
                ])->label('Importo (&euro;) <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'data_prevista')->textInput([
                    'type' => 'date',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'data_accredito')->textInput([
                    'type' => 'date',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'riferimento')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Es: Anticipo, SAL 1, Saldo',
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
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-green']) ?>
    </div>

    <?php AppActiveForm::end(); ?>
</div>
