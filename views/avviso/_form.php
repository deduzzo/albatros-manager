<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\Avviso;

/* @var $this yii\web\View */
/* @var $model app\models\Avviso */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $progettiList array */
?>

<div class="avviso-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dati Corso</h3>
        </div>

        <?php $form = AppActiveForm::begin(); ?>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'codice_avviso')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Es: AVV-001',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'progetto_id')->dropDownList($progettiList, [
                        'prompt' => '-- Seleziona progetto --',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'status')->dropDownList(Avviso::getStatusLabels()) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'titolo')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Titolo del corso',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'data_pubblicazione')->textInput([
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
                <div class="col-12">
                    <?= $form->field($model, 'descrizione')->textarea([
                        'rows' => 4,
                        'placeholder' => 'Descrizione del corso, requisiti, competenze richieste...',
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
