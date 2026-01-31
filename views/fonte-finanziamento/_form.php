<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FonteFinanziamento */
/* @var $form app\components\widgets\AppActiveForm */
?>

<div class="fonte-finanziamento-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dati Fonte di Finanziamento</h3>
        </div>

        <?php $form = AppActiveForm::begin(); ?>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'codice')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Es: FSE, PNRR, MIM',
                    ]) ?>
                </div>
                <div class="col-md-8">
                    <?= $form->field($model, 'descrizione')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Descrizione completa',
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
