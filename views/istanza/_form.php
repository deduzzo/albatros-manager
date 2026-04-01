<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\components\widgets\AppActiveForm;
use app\models\Istanza;
use app\models\Avviso;
use app\models\Modulo;
use app\models\Docente;

/* @var $this yii\web\View */
/* @var $model app\models\Istanza */

$avvisoList = ArrayHelper::map(
    Avviso::find()->orderBy(['codice_avviso' => SORT_ASC])->all(),
    'id',
    function ($avviso) {
        return $avviso->codice_avviso . ' - ' . $avviso->titolo;
    }
);

$docenteList = ArrayHelper::map(
    Docente::find()->orderBy(['cognome' => SORT_ASC, 'nome' => SORT_ASC])->all(),
    'codice_fiscale',
    function ($docente) {
        return $docente->cognome . ' ' . $docente->nome . ' (' . $docente->codice_fiscale . ')';
    }
);

// Se il modello ha gia un avviso_id, pre-carica i moduli per quel avviso
$moduloList = [];
if ($model->avviso_id) {
    $moduloList = ArrayHelper::map(
        Modulo::find()->where(['avviso_id' => $model->avviso_id])->orderBy(['codice' => SORT_ASC])->all(),
        'id',
        function ($modulo) {
            return $modulo->codice . ' - ' . $modulo->titolo;
        }
    );
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Dati Istanza</h3>
    </div>

    <?php $form = AppActiveForm::begin(); ?>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'avviso_id')->dropDownList($avvisoList, [
                    'prompt' => '-- Seleziona Corso --',
                    'id' => 'istanza-avviso_id',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'modulo_id')->dropDownList($moduloList, [
                    'prompt' => '-- Seleziona Modulo --',
                    'id' => 'istanza-modulo_id',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'docente_codice_fiscale')->dropDownList($docenteList, [
                    'prompt' => '-- Seleziona Docente --',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'data_presentazione')->textInput(['type' => 'date']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Istanza::getStatusLabels()) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'esito_cts')->dropDownList(Istanza::getEsitoCtsLabels(), [
                    'prompt' => '-- Non valutato --',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'punteggio_titoli')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => 0,
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'punteggio_esperienza')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => 0,
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'posizione_graduatoria')->textInput([
                    'type' => 'number',
                    'min' => 1,
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'note_valutazione')->textarea([
                    'rows' => 3,
                    'placeholder' => 'Note sulla valutazione della domanda...',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'note')->textarea([
                    'rows' => 3,
                    'placeholder' => 'Note generali (opzionale)',
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

<?php
$moduliUrl = \yii\helpers\Url::to(['moduli-per-avviso']);
$js = <<<JS
document.getElementById('istanza-avviso_id').addEventListener('change', function() {
    var avvisoId = this.value;
    var moduloSelect = document.getElementById('istanza-modulo_id');

    // Reset modulo dropdown
    moduloSelect.innerHTML = '<option value="">-- Seleziona Modulo --</option>';

    if (!avvisoId) return;

    fetch('{$moduliUrl}?avviso_id=' + avvisoId, {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        data.forEach(function(item) {
            var option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.text;
            moduloSelect.appendChild(option);
        });
    });
});
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
