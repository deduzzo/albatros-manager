<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\VoceSpesaCategoria;
use app\models\FonteFinanziamento;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\VoceSpesaCategoria */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'Nuova Categoria' : 'Modifica Categoria: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Voci di Spesa', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="voce-spesa-categoria-form">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-category me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?php $form = AppActiveForm::begin([
                'id' => 'categoria-form',
                'options' => ['class' => 'card'],
            ]); ?>

            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-category me-2"></i>
                    Dati Categoria
                </h3>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'codice')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Es: B1, C1',
                        ])->label('Codice <span class="text-red">*</span>') ?>
                    </div>
                    <div class="col-md-5">
                        <?= $form->field($model, 'nome')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Es: Preparazione',
                        ])->label('Nome <span class="text-red">*</span>') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'sezione')->dropDownList(VoceSpesaCategoria::getSezioneList(), [
                            'prompt' => 'Seleziona sezione...',
                            'class' => 'form-select',
                        ])->label('Sezione <span class="text-red">*</span>') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'ordinamento')->textInput([
                            'type' => 'number',
                            'min' => '0',
                            'placeholder' => '0',
                        ]) ?>
                    </div>
                    <div class="col-md-9">
                        <?= $form->field($model, 'fonte_finanziamento_id')->dropDownList(
                            ArrayHelper::map(FonteFinanziamento::find()->orderBy('nome')->all(), 'id', 'nome'),
                            ['prompt' => 'Tutte le fonti (nessun filtro)', 'class' => 'form-select']
                        ) ?>
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
