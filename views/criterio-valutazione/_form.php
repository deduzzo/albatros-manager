<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CriterioValutazioneStudente */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $avvisi array */
?>

<div class="criterio-valutazione-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-star me-2"></i>Dati Criterio di Valutazione</h3>
        </div>

        <?php $form = AppActiveForm::begin(); ?>

        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <?= $form->field($model, 'nome')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Es: Titolo di studio, Esperienza lavorativa',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'avviso_id')->dropDownList($avvisi, [
                        'prompt' => '-- Globale (tutti i bandi) --',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'descrizione')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Descrizione del criterio e come assegnare il punteggio',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'peso')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'placeholder' => '1.00',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'punteggio_min')->textInput([
                        'type' => 'number',
                        'step' => '0.1',
                        'placeholder' => '0',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'punteggio_max')->textInput([
                        'type' => 'number',
                        'step' => '0.1',
                        'placeholder' => '10',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'ordine')->textInput([
                        'type' => 'number',
                        'min' => '0',
                        'placeholder' => '0',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'attivo')->checkbox() ?>
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
