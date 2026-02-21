<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\AllievoLoginForm */

$this->title = 'Accesso Area Studenti';
?>

<div class="container container-tight py-4">
    <div class="text-center mb-4">
        <a href="<?= Url::to(['/site/index']) ?>" class="navbar-brand navbar-brand-autodark">
            <h1 class="h2" style="color: #ae3ec9;"><i class="ti ti-school me-2"></i>Area Studenti</h1>
        </a>
    </div>

    <div class="card card-md" style="border-top: 3px solid #ae3ec9;">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Accedi al tuo account</h2>

            <?php $form = AppActiveForm::begin([
                'id' => 'login-form',
            ]); ?>

            <?= $form->field($model, 'codice_fiscale')->textInput([
                'autofocus' => true,
                'placeholder' => 'Es: RSSMRA80A01F205X',
                'maxlength' => 16,
                'style' => 'text-transform: uppercase;',
            ]) ?>

            <?= $form->field($model, 'password')->passwordInput([
                'placeholder' => YII_DEBUG ? 'Lascia vuoto per test' : 'La tua password',
            ]) ?>

            <div class="mb-2">
                <?= $form->field($model, 'rememberMe')->checkbox([
                    'class' => 'form-check-input',
                ])->label('Ricordami su questo dispositivo') ?>
            </div>

            <div class="form-footer">
                <?= Html::submitButton('<i class="ti ti-login me-2"></i> Accedi', [
                    'class' => 'btn w-100',
                    'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;',
                ]) ?>
            </div>

            <?php AppActiveForm::end(); ?>
        </div>

        <div class="hr-text">oppure</div>

        <div class="card-body">
            <div class="row">
                <div class="col">
                    <a href="<?= Url::to(['password-dimenticata']) ?>" class="btn w-100">
                        Password dimenticata?
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center text-secondary mt-3">
        Non hai un account?
        <a href="<?= Url::to(['registrazione']) ?>" tabindex="-1">Registrati</a>
    </div>
</div>
