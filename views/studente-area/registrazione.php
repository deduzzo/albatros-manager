<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\AllievoRegistrazioneForm */

$this->title = 'Registrazione Studente';
?>

<div class="container py-4" style="max-width: 600px;">
    <div class="text-center mb-4">
        <a href="<?= Url::to(['/site/index']) ?>" class="navbar-brand navbar-brand-autodark">
            <h1 class="h2" style="color: #ae3ec9;"><i class="ti ti-school me-2"></i>Area Studenti</h1>
        </a>
    </div>

    <div class="card card-md" style="border-top: 3px solid #ae3ec9;">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Crea il tuo account</h2>

            <?php $form = AppActiveForm::begin([
                'id' => 'registrazione-form',
            ]); ?>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'codice_fiscale')->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Es: RSSMRA80A01F205X',
                        'maxlength' => 16,
                        'style' => 'text-transform: uppercase;',
                    ])->hint('Il codice fiscale sara il tuo username') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'cognome')->textInput([
                        'placeholder' => 'Rossi',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'nome')->textInput([
                        'placeholder' => 'Mario',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'email')->textInput([
                        'type' => 'email',
                        'placeholder' => 'mario.rossi@email.it',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'telefono')->textInput([
                        'placeholder' => '+39 333 1234567',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'data_nascita')->textInput([
                        'type' => 'date',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'luogo_nascita')->textInput([
                        'placeholder' => 'Milano',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'indirizzo')->textInput([
                        'placeholder' => 'Via Roma 1',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'cap')->textInput([
                        'placeholder' => '20100',
                        'maxlength' => 5,
                    ]) ?>
                </div>
                <div class="col-md-5">
                    <?= $form->field($model, 'citta')->textInput([
                        'placeholder' => 'Milano',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'provincia')->textInput([
                        'placeholder' => 'MI',
                        'maxlength' => 2,
                        'style' => 'text-transform: uppercase;',
                    ]) ?>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'password')->passwordInput([
                        'placeholder' => 'Minimo 6 caratteri',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'password_confirm')->passwordInput([
                        'placeholder' => 'Ripeti la password',
                    ]) ?>
                </div>
            </div>

            <div class="mb-3">
                <?= $form->field($model, 'privacy_policy')->checkbox([
                    'class' => 'form-check-input',
                ])->label('Accetto la <a href="#" target="_blank">Privacy Policy</a> e i <a href="#" target="_blank">Termini di Servizio</a>') ?>
            </div>

            <div class="form-footer">
                <?= Html::submitButton('<i class="ti ti-user-plus me-2"></i> Registrati', [
                    'class' => 'btn w-100',
                    'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;',
                ]) ?>
            </div>

            <?php AppActiveForm::end(); ?>
        </div>
    </div>

    <div class="text-center text-secondary mt-3">
        Hai gia un account?
        <a href="<?= Url::to(['login']) ?>" tabindex="-1">Accedi</a>
    </div>
</div>
