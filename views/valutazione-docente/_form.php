<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="valutazione-docente-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'card']]); ?>
    <div class="card-header"><h3 class="card-title"><i class="ti ti-star me-2"></i>Dati Valutazione</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'docente_codice_fiscale')->dropDownList($docenti, ['prompt' => '-- Seleziona Docente --'])->label('Docente <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'modulo_id')->dropDownList($moduli, ['prompt' => '-- Seleziona Modulo --'])->label('Modulo <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'data_valutazione')->input('date')->label('Data Valutazione <span class="text-danger">*</span>') ?>
            </div>
        </div>
    </div>

    <div class="card-header"><h3 class="card-title"><i class="ti ti-star-filled me-2"></i>Punteggi (1-5)</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'punteggio_globale')->dropDownList([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'], ['prompt' => '-- Seleziona --'])->label('Valutazione Globale <span class="text-danger">*</span>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'punteggio_chiarezza')->dropDownList([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'], ['prompt' => '-- Opzionale --']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'punteggio_competenza')->dropDownList([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'], ['prompt' => '-- Opzionale --']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'punteggio_disponibilita')->dropDownList([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'], ['prompt' => '-- Opzionale --']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'punteggio_materiali')->dropDownList([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'], ['prompt' => '-- Opzionale --']) ?>
            </div>
        </div>
    </div>

    <div class="card-header"><h3 class="card-title"><i class="ti ti-message me-2"></i>Commento</h3></div>
    <div class="card-body">
        <?= $form->field($model, 'commento')->textarea(['rows' => 4, 'placeholder' => 'Commento sulla valutazione (anonimo)'])->label(false) ?>
    </div>

    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
