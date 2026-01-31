<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Sede */
/* @var $form app\components\widgets\AppActiveForm */
?>

<div class="sede-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dati Sede</h3>
        </div>

        <?php $form = AppActiveForm::begin(); ?>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'nome')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Nome identificativo della sede',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'email')->textInput([
                        'type' => 'email',
                        'maxlength' => true,
                        'placeholder' => 'email@sede.it',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <?= $form->field($model, 'indirizzo')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Via/Piazza...',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'telefono')->textInput([
                        'maxlength' => true,
                        'placeholder' => '+39...',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'cap')->textInput([
                        'maxlength' => 5,
                        'placeholder' => '00000',
                    ]) ?>
                </div>
                <div class="col-md-8">
                    <?= $form->field($model, 'citta')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Città',
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'provincia')->textInput([
                        'maxlength' => 2,
                        'placeholder' => 'XX',
                        'style' => 'text-transform: uppercase;',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'note')->textarea([
                        'rows' => 4,
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
