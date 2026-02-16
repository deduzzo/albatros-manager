<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Documentazione;
?>
<div class="documentazione-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'card']]); ?>
    <div class="card-header"><h3 class="card-title"><i class="ti ti-file-text me-2"></i>Dati Documento</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'titolo')->textInput(['maxlength' => true, 'placeholder' => 'Titolo del documento'])->label('Titolo <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'tipo_documento_id')->dropDownList($tipiDocumento, ['prompt' => '-- Seleziona --'])->label('Tipo Documento <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Documentazione::getStatusLabels()) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'docente_codice_fiscale')->dropDownList($docenti, ['prompt' => '-- Nessun Docente --']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'allievo_codice_fiscale')->dropDownList($allievi, ['prompt' => '-- Nessun Allievo --']) ?>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-1"></i> Specificare almeno un docente o un allievo come intestatario del documento.
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'descrizione')->textarea(['rows' => 3, 'placeholder' => 'Descrizione opzionale']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'data_documento')->input('date') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'data_scadenza')->input('date') ?>
            </div>
        </div>
    </div>

    <div class="card-header"><h3 class="card-title"><i class="ti ti-paperclip me-2"></i>Informazioni File</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'file_path')->textInput(['maxlength' => true, 'placeholder' => 'Percorso del file'])->label('Percorso File <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'file_nome_originale')->textInput(['maxlength' => true, 'placeholder' => 'Nome file originale']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'file_size')->textInput(['type' => 'number', 'placeholder' => 'Dimensione in bytes']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'file_mime_type')->textInput(['maxlength' => true, 'placeholder' => 'Es: application/pdf']) ?>
            </div>
        </div>
    </div>

    <div class="card-header"><h3 class="card-title"><i class="ti ti-notes me-2"></i>Note</h3></div>
    <div class="card-body">
        <?= $form->field($model, 'note')->textarea(['rows' => 3, 'placeholder' => 'Note aggiuntive'])->label(false) ?>
    </div>

    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
