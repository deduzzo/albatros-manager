<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\TagCategoria;

/* @var $this yii\web\View */
/* @var $model app\models\TagCurriculum */
/* @var $form app\components\widgets\AppActiveForm */

// Supporto preselect categoria da query string
if ($model->isNewRecord && !$model->categoria_id && Yii::$app->request->get('categoria_id')) {
    $model->categoria_id = Yii::$app->request->get('categoria_id');
}
?>

<div class="tag-curriculum-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-tag me-2"></i>
                Dati Tag Curriculum
            </h3>
        </div>

        <?php $form = AppActiveForm::begin(); ?>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'categoria_id')->dropDownList(
                        TagCategoria::getList(),
                        [
                            'class' => 'form-select',
                            'prompt' => '-- Seleziona categoria --',
                        ]
                    ) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'codice')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'es: comp-informatica',
                    ]) ?>
                    <small class="form-hint">Solo lettere minuscole, numeri e trattini</small>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'nome')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Es: Competenze Informatiche',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <?= $form->field($model, 'descrizione')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Descrizione del tag (opzionale)',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'keywords')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'parola1, parola2, parola3',
                    ]) ?>
                    <small class="form-hint">Keywords separate da virgola, utili per la ricerca</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'ordine')->textInput([
                        'type' => 'number',
                        'min' => 0,
                        'value' => $model->ordine ?: 0,
                        'placeholder' => '0',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->fieldSwitch($model, 'attivo') ?>
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
