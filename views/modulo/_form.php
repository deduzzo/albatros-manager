<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\Modulo;

/* @var $this yii\web\View */
/* @var $model app\models\Modulo */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $avvisiList array */
/* @var $sediList array */
?>

<div class="modulo-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dati Modulo/Insegnamento</h3>
        </div>

        <?php $form = AppActiveForm::begin(); ?>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'codice')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Es: MOD-PYTHON',
                    ]) ?>
                </div>
                <div class="col-md-5">
                    <?= $form->field($model, 'avviso_id')->dropDownList($avvisiList, [
                        'prompt' => '-- Seleziona corso --',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'sede_id')->dropDownList($sediList, [
                        'prompt' => '-- Seleziona sede --',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'titolo')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Titolo del modulo/insegnamento',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'ore_totali')->textInput([
                        'type' => 'number',
                        'min' => 1,
                        'placeholder' => '40',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'compenso_orario')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'placeholder' => '70.00',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'max_allievi')->textInput([
                        'type' => 'number',
                        'min' => 1,
                        'placeholder' => '25',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'status')->dropDownList(Modulo::getStatusLabels()) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'data_inizio')->textInput([
                        'type' => 'date',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'data_fine')->textInput([
                        'type' => 'date',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'descrizione')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Descrizione del corso, argomenti trattati...',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'requisiti_docente')->textarea([
                        'rows' => 2,
                        'placeholder' => 'Requisiti richiesti al docente (titoli, esperienza, certificazioni...)',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'note')->textarea([
                        'rows' => 2,
                        'placeholder' => 'Note aggiuntive (opzionale)',
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success ms-2']) ?>
        </div>

        <?php AppActiveForm::end(); ?>
    </div>
</div>
