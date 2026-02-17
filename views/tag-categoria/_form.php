<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\TagCategoria */
/* @var $form app\components\widgets\AppActiveForm */

$iconeDisponibili = [
    '' => '-- Nessuna icona --',
    'ti ti-category' => 'Categoria',
    'ti ti-tag' => 'Tag',
    'ti ti-tags' => 'Tags',
    'ti ti-code' => 'Codice',
    'ti ti-school' => 'Formazione',
    'ti ti-book' => 'Libro',
    'ti ti-certificate' => 'Certificato',
    'ti ti-briefcase' => 'Lavoro',
    'ti ti-building' => 'Istituzione',
    'ti ti-language' => 'Lingua',
    'ti ti-devices' => 'Tecnologia',
    'ti ti-palette' => 'Arte/Design',
    'ti ti-flask' => 'Scienza',
    'ti ti-math-function' => 'Matematica',
    'ti ti-heart' => 'Salute',
    'ti ti-plant' => 'Ambiente',
    'ti ti-tools' => 'Strumenti',
    'ti ti-world' => 'Internazionale',
    'ti ti-shield-check' => 'Sicurezza',
    'ti ti-stars' => 'Eccellenza',
    'ti ti-users' => 'Team/Gruppo',
    'ti ti-bulb' => 'Innovazione',
    'ti ti-chart-bar' => 'Analisi',
    'ti ti-atom' => 'Ricerca',
];
?>

<div class="tag-categoria-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-category me-2"></i>
                Dati Categoria Tag
            </h3>
        </div>

        <?php $form = AppActiveForm::begin(); ?>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'nome')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Es: Competenze Tecniche',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'colore')->textInput([
                        'type' => 'color',
                        'value' => $model->colore ?: '#206bc4',
                        'style' => 'height: 38px; padding: 4px;',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'ordine')->textInput([
                        'type' => 'number',
                        'min' => 0,
                        'value' => $model->ordine ?: 0,
                        'placeholder' => '0',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'descrizione')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Descrizione della categoria (opzionale)',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'icona')->dropDownList($iconeDisponibili, [
                        'class' => 'form-select',
                    ]) ?>
                    <div id="icona-preview" class="mt-2">
                        <?php if ($model->icona): ?>
                            <i class="<?= Html::encode($model->icona) ?>" style="font-size: 2rem; color: <?= Html::encode($model->colore ?: '#206bc4') ?>;"></i>
                        <?php endif; ?>
                    </div>
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

<?php
$js = <<<JS
// Aggiorna preview icona quando cambia la selezione
$('#tagcategoria-icona').on('change', function() {
    var iconClass = $(this).val();
    var colore = $('#tagcategoria-colore').val() || '#206bc4';
    if (iconClass) {
        $('#icona-preview').html('<i class="' + iconClass + '" style="font-size: 2rem; color: ' + colore + ';"></i>');
    } else {
        $('#icona-preview').html('');
    }
});
// Aggiorna colore preview quando cambia il colore
$('#tagcategoria-colore').on('input', function() {
    var colore = $(this).val();
    $('#icona-preview i').css('color', colore);
});
JS;
$this->registerJs($js);
?>
