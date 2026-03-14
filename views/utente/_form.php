<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form app\components\widgets\AppActiveForm */

$isCreate = $model->isNewRecord;
?>

<div class="utente-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-user-edit me-2"></i>
                <?= $isCreate ? 'Dati nuovo utente' : 'Modifica dati utente' ?>
            </h3>
        </div>
        <div class="card-body">
            <?php $form = AppActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'username')->textInput(['maxlength' => 50, 'autofocus' => true]) ?>
                </div>
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'nominativo')->textInput(['maxlength' => 255]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'email')->textInput(['type' => 'email', 'maxlength' => 100]) ?>
                </div>
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'ruolo')->dropDownList(User::getRuoloOptions(), [
                        'class' => 'form-select',
                        'disabled' => !$isCreate && !$model->canChangeRoleBy(Yii::$app->user->identity),
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Password
                        <?php if ($isCreate): ?>
                            <span class="text-danger">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="password" name="password" class="form-control"
                           placeholder="<?= $isCreate ? 'Inserisci la password' : 'Lascia vuoto per non modificare' ?>"
                           <?= $isCreate ? 'required' : '' ?>>
                    <?php if (!$isCreate): ?>
                        <small class="form-hint">Lascia vuoto per mantenere la password attuale</small>
                    <?php endif; ?>
                </div>
            </div>

            <?= AppActiveForm::buttons('Salva', ['index']) ?>

            <?php AppActiveForm::end(); ?>
        </div>
    </div>
</div>
