<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\RendicontazioneOre */
/* @var $form yii\widgets\ActiveForm */
/* @var $assegnazione app\models\Assegnazione */
?>

<div class="rendicontazione-ore-form">
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'card'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-clock-hour-4 me-2"></i>
            Dati Rendicontazione
        </h3>
    </div>

    <div class="card-body">
        <!-- Info assegnazione -->
        <?php
        $modulo = $assegnazione->modulo;
        $docente = $assegnazione->docente;
        ?>
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-center">
                <i class="ti ti-info-circle me-2"></i>
                <div>
                    <?php if ($modulo): ?>
                        <strong>Modulo:</strong> <span class="badge bg-blue-lt"><?= Html::encode($modulo->codice) ?></span>
                        <?= Html::encode($modulo->titolo) ?>
                    <?php endif; ?>
                    <?php if ($docente): ?>
                        &mdash; <strong>Docente:</strong> <?= Html::encode($docente->cognome . ' ' . $docente->nome) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'data')->textInput([
                    'type' => 'date',
                    'max' => date('Y-m-d'),
                ])->label('Data <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'ore')->textInput([
                    'type' => 'number',
                    'step' => '0.5',
                    'min' => '0.25',
                    'max' => '12',
                    'placeholder' => 'Es: 4',
                ])->label('Ore <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'firma_digitale')->checkbox([
                    'class' => 'form-check-input',
                    'label' => 'Dichiaro di aver svolto le ore indicate',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'ora_inizio')->textInput([
                    'type' => 'time',
                ])->label('Ora Inizio (facoltativo)') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'ora_fine')->textInput([
                    'type' => 'time',
                ])->label('Ora Fine (facoltativo)') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'note')->textarea([
                    'rows' => 3,
                    'placeholder' => 'Note aggiuntive (facoltativo)...',
                ]) ?>
            </div>
        </div>
    </div>

    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index', 'assegnazione_id' => $assegnazione->id], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
