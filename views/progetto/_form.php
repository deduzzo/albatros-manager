<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\Progetto;

/* @var $this yii\web\View */
/* @var $model app\models\Progetto */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $fontiList array */
?>

<div class="progetto-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dati Progetto</h3>
        </div>

        <?php $form = AppActiveForm::begin(); ?>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'codice_progetto')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Es: FSE-2024-001',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'fonte_finanziamento_id')->dropDownList($fontiList, [
                        'prompt' => '-- Seleziona fonte --',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'status')->dropDownList(Progetto::getStatusLabels()) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'titolo')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Titolo completo del progetto',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'budget_totale')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'placeholder' => '0.00',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'data_inizio')->textInput([
                        'type' => 'date',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'data_fine')->textInput([
                        'type' => 'date',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'descrizione')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Descrizione del progetto',
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
