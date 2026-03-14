<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\components\widgets\AppActiveForm;
use app\models\Notifica;
use app\models\User;
use app\models\Docente;
use app\models\Allievo;

/* @var $this yii\web\View */
/* @var $model app\models\Notifica */
/* @var $form app\components\widgets\AppActiveForm */

// Prepara elenchi destinatari singoli
$adminUsers = ArrayHelper::map(
    User::find()->andWhere(['status' => User::STATUS_ACTIVE])->orderBy('nominativo')->all(),
    'id',
    function($u) { return ($u->nominativo ?: $u->username) . ' (ID: ' . $u->id . ')'; }
);

$docenti = ArrayHelper::map(
    Docente::find()->orderBy('cognome, nome')->all(),
    'codice_fiscale',
    function($d) { return $d->cognome . ' ' . $d->nome . ' - ' . $d->codice_fiscale; }
);

$allievi = ArrayHelper::map(
    Allievo::find()->orderBy('cognome, nome')->all(),
    'codice_fiscale',
    function($a) { return $a->cognome . ' ' . $a->nome . ' - ' . $a->codice_fiscale; }
);
?>

<div class="notifica-form">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-bell-plus me-2"></i>
                Dati notifica
            </h3>
        </div>
        <div class="card-body">
            <?php $form = AppActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <?= $form->field($model, 'titolo')->textInput(['maxlength' => 255, 'autofocus' => true, 'placeholder' => 'Titolo della notifica']) ?>
                </div>
                <div class="col-md-4 mb-3">
                    <?= $form->field($model, 'link')->textInput(['maxlength' => 500, 'placeholder' => 'es. /contratto/view/5']) ?>
                </div>
            </div>

            <div class="mb-3">
                <?= $form->field($model, 'contenuto')->textarea(['rows' => 6, 'id' => 'contenuto-editor', 'placeholder' => 'Contenuto della notifica...']) ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'destinatario_tipo')->dropDownList(
                        Notifica::getDestinatarioTipoOptions(),
                        [
                            'class' => 'form-select',
                            'id' => 'destinatario-tipo',
                            'prompt' => '-- Seleziona destinatari --',
                        ]
                    ) ?>
                </div>
                <div class="col-md-6 mb-3">
                    <!-- Campo nascosto per destinatario_id -->
                    <?= Html::activeHiddenInput($model, 'destinatario_id', ['id' => 'destinatario-id-hidden']) ?>

                    <div id="select-admin" style="display: none;">
                        <label class="form-label">Seleziona Amministratore</label>
                        <?= Html::dropDownList('destinatario_admin', $model->destinatario_id, $adminUsers, [
                            'class' => 'form-select',
                            'id' => 'destinatario-admin',
                            'prompt' => '-- Seleziona --',
                        ]) ?>
                    </div>

                    <div id="select-docente" style="display: none;">
                        <label class="form-label">Seleziona Docente</label>
                        <?= Html::dropDownList('destinatario_docente', $model->destinatario_id, $docenti, [
                            'class' => 'form-select',
                            'id' => 'destinatario-docente',
                            'prompt' => '-- Seleziona --',
                        ]) ?>
                    </div>

                    <div id="select-studente" style="display: none;">
                        <label class="form-label">Seleziona Studente</label>
                        <?= Html::dropDownList('destinatario_studente', $model->destinatario_id, $allievi, [
                            'class' => 'form-select',
                            'id' => 'destinatario-studente',
                            'prompt' => '-- Seleziona --',
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <?= $form->field($model, 'invia_email')->checkbox() ?>
            </div>

            <?= AppActiveForm::buttons('Invia Notifica', ['index']) ?>

            <?php AppActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$js = <<<JS
// Mostra/nasconde il select giusto in base al tipo destinatario
var tipoSelect = document.getElementById('destinatario-tipo');
var selectAdmin = document.getElementById('select-admin');
var selectDocente = document.getElementById('select-docente');
var selectStudente = document.getElementById('select-studente');
var hiddenField = document.getElementById('destinatario-id-hidden');

function aggiornaDestinatario() {
    var tipo = tipoSelect.value;
    selectAdmin.style.display = 'none';
    selectDocente.style.display = 'none';
    selectStudente.style.display = 'none';
    hiddenField.value = '';

    if (tipo === 'singolo_admin') {
        selectAdmin.style.display = 'block';
        hiddenField.value = document.getElementById('destinatario-admin').value;
    } else if (tipo === 'singolo_docente') {
        selectDocente.style.display = 'block';
        hiddenField.value = document.getElementById('destinatario-docente').value;
    } else if (tipo === 'singolo_studente') {
        selectStudente.style.display = 'block';
        hiddenField.value = document.getElementById('destinatario-studente').value;
    }
}

tipoSelect.addEventListener('change', aggiornaDestinatario);
document.getElementById('destinatario-admin').addEventListener('change', function() {
    hiddenField.value = this.value;
});
document.getElementById('destinatario-docente').addEventListener('change', function() {
    hiddenField.value = this.value;
});
document.getElementById('destinatario-studente').addEventListener('change', function() {
    hiddenField.value = this.value;
});

// Inizializza al caricamento
aggiornaDestinatario();
JS;
$this->registerJs($js);
?>
