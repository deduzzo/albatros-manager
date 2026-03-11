<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\VoceSpesa */
/* @var $categorie array */
/* @var $title string */

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'Voci di Spesa', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="voce-spesa-form">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-list-tree me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?php $form = AppActiveForm::begin([
                'id' => 'voce-spesa-form',
                'options' => ['class' => 'card'],
            ]); ?>

            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-file-invoice me-2"></i>
                    Dati Voce di Spesa
                </h3>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'categoria_id')->dropDownList($categorie, [
                            'prompt' => 'Seleziona una categoria...',
                            'class' => 'form-select',
                        ])->label('Categoria <span class="text-red">*</span>') ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'codice')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Es: B2.1',
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'ordinamento')->textInput([
                            'type' => 'number',
                            'min' => '0',
                            'placeholder' => '0',
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'nome')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Es: Docenza',
                        ])->label('Nome <span class="text-red">*</span>') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'descrizione')->textarea([
                            'rows' => 3,
                            'placeholder' => 'Descrizione opzionale della voce...',
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
    </div>
</div>
