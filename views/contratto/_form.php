<?php

use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Contratto;

/* @var $this yii\web\View */
/* @var $model app\models\Contratto */
/* @var $form yii\widgets\ActiveForm */
/* @var $assegnazioni array */
?>

<div class="contratto-form">
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'card'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-file-text me-2"></i>
            Dati Contratto
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'assegnazione_id')->dropDownList($assegnazioni, [
                    'prompt' => 'Seleziona un\'assegnazione...',
                    'class' => 'form-select',
                ])->label('Assegnazione <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'tipo')->dropDownList(Contratto::getTipoList(), [
                    'class' => 'form-select',
                ])->label('Tipo Contratto <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'numero_contratto')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Es: CTR-2026-001',
                ]) ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-calendar me-2"></i>
            Date
        </h4>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'data_stipula')->textInput([
                    'type' => 'date',
                ])->label('Data Stipula <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'data_inizio')->textInput([
                    'type' => 'date',
                ])->label('Data Inizio Contratto <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'data_fine')->textInput([
                    'type' => 'date',
                ])->label('Data Fine Contratto <span class="text-danger">*</span>') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'data_inizio_lezioni')->textInput([
                    'type' => 'date',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'data_fine_lezioni')->textInput([
                    'type' => 'date',
                ]) ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-currency-euro me-2"></i>
            Dati Economici
        </h4>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'compenso_orario')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '60.00',
                ])->label('Compenso Orario (&euro;) <span class="text-danger">*</span>') ?>
                <?php
                if (isset($model->assegnazione) && $model->assegnazione->docente) {
                    $docente = $model->assegnazione->docente;
                    $fonte = $model->assegnazione->modulo->avviso->progetto->fonteFinanziamento->nome ?? null;
                    $suggerito = $docente->getCompensoDefault($fonte);
                    if ($suggerito) {
                        echo '<div class="text-muted small mt-1"><i class="ti ti-info-circle me-1"></i>Compenso suggerito per ' . ($docente->provenienza === 'RU' ? 'Ricercatore Universitario' : 'Professionista') . ': <strong>' . Yii::$app->formatter->asCurrency($suggerito) . '/ora</strong></div>';
                    }
                }
                ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'ore_contrattualizzate')->textInput([
                    'type' => 'number',
                    'min' => '1',
                    'placeholder' => '30',
                ])->label('Ore <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'compenso_totale')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '1800.00',
                ])->label('Compenso Totale (&euro;) <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Contratto::getStatusLabels(), [
                    'class' => 'form-select',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'ritenute')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '0.00',
                ])->label('Ritenute (&euro;)') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'netto_docente')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '0.00',
                ])->label('Netto Docente (&euro;)') ?>
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
