<?php

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Accedi';

// Usa il layout login senza menu
$this->context->layout = 'login';
?>

<div class="container container-tight py-4">
    <div class="text-center mb-4">
        <a href="." class="navbar-brand navbar-brand-autodark">
            <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-badge-small.png" height="48" alt="Albatros">
        </a>
        <h2 class="mt-3">Albatros Manager</h2>
        <p class="text-muted">Sistema di gestione docenti FSE/PNRR</p>
    </div>

    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Accedi al portale</h2>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'fieldConfig' => [
                    'template' => "{input}\n{error}",
                    'errorOptions' => ['class' => 'invalid-feedback d-block'],
                ],
            ]); ?>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <?= $form->field($model, 'username')->textInput([
                    'class' => 'form-control',
                    'placeholder' => 'Inserisci username',
                    'autofocus' => true,
                ]) ?>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Password
                    <span class="form-label-description">
                        <a href="#">Password dimenticata?</a>
                    </span>
                </label>
                <?= $form->field($model, 'password')->passwordInput([
                    'class' => 'form-control',
                    'placeholder' => 'Inserisci password',
                ]) ?>
            </div>

            <div class="mb-3">
                <label class="form-check">
                    <?= Html::activeCheckbox($model, 'rememberMe', [
                        'class' => 'form-check-input',
                        'label' => false,
                    ]) ?>
                    <span class="form-check-label">Ricordami su questo dispositivo</span>
                </label>
            </div>

            <div class="form-footer">
                <?= Html::submitButton('Accedi', ['class' => 'btn btn-primary w-100']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="text-center text-muted mt-3">
        <small>&copy; <?= date('Y') ?> Fondazione ITS Albatros Academy</small>
    </div>
</div>
