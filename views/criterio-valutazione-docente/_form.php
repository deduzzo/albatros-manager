<?php
use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
?>
<div class="criterio-valutazione-docente-form">
    <?php $form = AppActiveForm::begin(['options' => ['class' => 'card']]); ?>
    <div class="card-header">
        <h3 class="card-title"><i class="ti ti-star-half-filled me-2"></i>Dati Criterio</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'codice')->textInput(['maxlength' => 50, 'placeholder' => 'es. competenza_materia'])->label('Codice <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-8">
                <?= $form->field($model, 'nome')->textInput(['maxlength' => 255, 'placeholder' => 'Nome del criterio'])->label('Nome <span class="text-danger">*</span>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'descrizione')->textarea(['rows' => 3, 'placeholder' => 'Descrizione completa del criterio (viene mostrata nel questionario)']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'scala_min')->input('number', ['min' => 0, 'max' => 100]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'scala_max')->input('number', ['min' => 1, 'max' => 100]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'ordinamento')->input('number', ['min' => 0]) ?>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Attivo</label>
                    <div class="form-check form-switch mt-2">
                        <?= Html::activeCheckbox($model, 'attivo', [
                            'class' => 'form-check-input',
                            'label' => false,
                        ]) ?>
                        <label class="form-check-label">Criterio attivo</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>
    <?php AppActiveForm::end(); ?>
</div>
