<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\RichiestaFatturazione;

/* @var $this yii\web\View */
/* @var $model app\models\RichiestaFatturazione */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $contratti array */
?>

<div class="richiesta-fatturazione-form">
    <?php $form = AppActiveForm::begin([
        'id' => 'richiesta-fatturazione-form',
        'options' => ['class' => 'card'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-file-invoice me-2"></i>
            Dati Richiesta
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'contratto_id')->dropDownList($contratti, [
                    'prompt' => 'Seleziona un contratto...',
                    'class' => 'form-select',
                ])->label('Contratto <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'tipo')->dropDownList(RichiestaFatturazione::getTipoList(), [
                    'prompt' => '-- Seleziona tipo --',
                    'class' => 'form-select',
                ])->label('Tipo <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'importo')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '0.00',
                ])->label('Importo (&euro;) <span class="text-red">*</span>') ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-mail me-2"></i>
            Destinatario
        </h4>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'destinatario_email')->textInput([
                    'type' => 'email',
                    'placeholder' => 'email@esempio.it',
                ])->label('Email Destinatario <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'destinatario_nome')->textInput([
                    'placeholder' => 'Nome e cognome',
                ]) ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-calendar me-2"></i>
            Date e Riferimenti
        </h4>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'data_richiesta')->textInput([
                    'type' => 'date',
                ])->label('Data Richiesta <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'data_scadenza')->textInput([
                    'type' => 'date',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'periodo_riferimento')->textInput([
                    'placeholder' => 'Es: Gennaio 2026',
                ]) ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-message me-2"></i>
            Contenuto Email (opzionale)
        </h4>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'oggetto_email')->textInput([
                    'placeholder' => 'Oggetto dell\'email (auto-generato se vuoto)',
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'corpo_email')->textarea([
                    'rows' => 6,
                    'placeholder' => 'Corpo dell\'email (auto-generato se vuoto)',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'note')->textarea([
                    'rows' => 3,
                    'placeholder' => 'Note interne...',
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
