<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppActiveForm;
use app\models\Pagamento;

/* @var $this yii\web\View */
/* @var $model app\models\Pagamento */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $contratti array */
/* @var $infoOre array|null */
?>

<div class="pagamento-form">
    <?php $form = AppActiveForm::begin([
        'id' => 'pagamento-form',
        'options' => ['class' => 'card'],
    ]); ?>

    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-currency-euro me-2"></i>
            Dati Pagamento
        </h3>
    </div>

    <div class="card-body">
        <!-- Info Card Contratto (nascosta se nessun contratto selezionato) -->
        <div id="contratto-info-card" class="alert alert-info mb-4" style="display: none;">
            <div class="d-flex align-items-start">
                <span class="avatar bg-blue-lt me-3"><i class="ti ti-file-invoice"></i></span>
                <div class="flex-fill">
                    <h4 class="alert-title mb-1" id="ci-docente">-</h4>
                    <div class="text-muted small mb-2" id="ci-modulo">-</div>
                    <div class="row g-2">
                        <div class="col-auto">
                            <span class="text-muted">Contrattualizzato:</span>
                            <strong id="ci-totale">-</strong>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Pagato:</span>
                            <strong class="text-green" id="ci-pagato">-</strong>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Saldo rimanente:</span>
                            <strong class="text-orange" id="ci-saldo">-</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Ore non completamente rendicontate -->
        <div id="ore-alert" class="alert alert-warning mb-4" style="display: none;">
            <div class="d-flex align-items-center">
                <i class="ti ti-alert-triangle me-2" style="font-size: 1.5rem;"></i>
                <div class="flex-fill">
                    <h4 class="alert-title mb-1">Ore non completamente rendicontate</h4>
                    <div id="ore-alert-text">-</div>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" id="btn-paga-ore-rendicontate" class="btn btn-sm btn-green">
                    <i class="ti ti-clock-dollar me-1"></i> Paga solo ore rendicontate come acconto
                </button>
                <button type="button" id="btn-popola-saldo" class="btn btn-sm btn-outline-blue ms-2">
                    <i class="ti ti-cash me-1"></i> Popola saldo rimanente
                </button>
            </div>
        </div>

        <!-- Alert Ore Complete -->
        <div id="ore-ok-alert" class="alert alert-success mb-4" style="display: none;">
            <div class="d-flex align-items-center">
                <i class="ti ti-circle-check me-2" style="font-size: 1.5rem;"></i>
                <div class="flex-fill">
                    <h4 class="alert-title mb-0">Ore completamente rendicontate</h4>
                    <div id="ore-ok-text">-</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'contratto_id')->dropDownList($contratti, [
                    'prompt' => 'Seleziona un contratto...',
                    'class' => 'form-select',
                    'id' => 'pagamento-contratto_id',
                ])->label('Contratto <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'tipo')->dropDownList(Pagamento::getTipoLabels(), [
                    'prompt' => '-- Seleziona tipo --',
                    'class' => 'form-select',
                    'id' => 'pagamento-tipo',
                ])->label('Tipo <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Pagamento::getStatusLabels(), [
                    'class' => 'form-select',
                ])->label('Stato <span class="text-red">*</span>') ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-cash me-2"></i>
            Importi
        </h4>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'importo_lordo')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '0.00',
                    'id' => 'pagamento-importo_lordo',
                ])->label('Importo Lordo (&euro;) <span class="text-red">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'ritenute')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '0.00',
                    'id' => 'pagamento-ritenute',
                ])->label('Ritenute (&euro;)') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'importo_netto')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => '0.00',
                    'id' => 'pagamento-importo_netto',
                ])->label('Importo Netto (&euro;) <span class="text-red">*</span>') ?>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">
            <i class="ti ti-calendar me-2"></i>
            Date e Riferimenti
        </h4>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'data_pagamento')->textInput(['type' => 'date']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'data_scadenza')->textInput(['type' => 'date']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'riferimento_fattura')->textInput(['maxlength' => true, 'placeholder' => 'Es: FT-2026/001']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'numero_mandato')->textInput(['maxlength' => true, 'placeholder' => 'Es: M-001/2026']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'cro_bonifico')->textInput(['maxlength' => true, 'placeholder' => 'Es: 1234567890']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'note')->textarea(['rows' => 3, 'placeholder' => 'Note aggiuntive...']) ?>
            </div>
        </div>
    </div>

    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-green']) ?>
    </div>

    <?php AppActiveForm::end(); ?>
</div>

<?php
$contrattoInfoUrl = Url::to(['/pagamento/contratto-info']);
$this->registerJs(<<<JS
    var currentContrattoData = null;

    function formatCurrency(val) {
        return new Intl.NumberFormat('it-IT', {style: 'currency', currency: 'EUR'}).format(val);
    }

    function updateContrattoInfo(data) {
        currentContrattoData = data;
        // Info card
        $('#ci-docente').text(data.docente);
        $('#ci-modulo').text(data.modulo_codice + ' - ' + data.modulo_titolo);
        $('#ci-totale').text(formatCurrency(data.compenso_totale));
        $('#ci-pagato').text(formatCurrency(data.totale_pagato));
        $('#ci-saldo').text(formatCurrency(data.saldo_rimanente));
        $('#contratto-info-card').slideDown(200);

        // Alert ore
        var percOre = data.ore_contrattualizzate > 0
            ? Math.round((data.ore_approvate / data.ore_contrattualizzate) * 100)
            : 0;

        if (data.ore_contrattualizzate > 0 && data.ore_approvate < data.ore_contrattualizzate) {
            var importoOre = (data.ore_approvate * data.compenso_orario).toFixed(2);
            $('#ore-alert-text').html(
                'Il docente ha rendicontato <strong>' + data.ore_approvate + '/' + data.ore_contrattualizzate + ' ore</strong> (' + percOre + '%). ' +
                'Importo per ore rendicontate: <strong>' + formatCurrency(importoOre) + '</strong>'
            );
            $('#ore-alert').slideDown(200);
            $('#ore-ok-alert').slideUp(200);
        } else if (data.ore_contrattualizzate > 0) {
            $('#ore-ok-text').text(data.ore_approvate + '/' + data.ore_contrattualizzate + ' ore approvate (' + percOre + '%)');
            $('#ore-ok-alert').slideDown(200);
            $('#ore-alert').slideUp(200);
        } else {
            $('#ore-alert, #ore-ok-alert').slideUp(200);
        }
    }

    // AJAX on contratto change
    $('#pagamento-contratto_id').on('change', function() {
        var id = $(this).val();
        if (!id) {
            $('#contratto-info-card, #ore-alert, #ore-ok-alert').slideUp(200);
            currentContrattoData = null;
            return;
        }
        $.getJSON('{$contrattoInfoUrl}', {id: id}, function(data) {
            updateContrattoInfo(data);
        });
    });

    // Pulsante "Paga solo ore rendicontate come acconto"
    $('#btn-paga-ore-rendicontate').on('click', function() {
        if (!currentContrattoData) return;
        var importo = (currentContrattoData.ore_approvate * currentContrattoData.compenso_orario).toFixed(2);
        $('#pagamento-importo_lordo').val(importo);
        $('#pagamento-tipo').val('acconto');
        // Calcola ritenute se presenti
        if (currentContrattoData.ritenute_percentuale > 0) {
            var ritenute = (importo * currentContrattoData.ritenute_percentuale / 100).toFixed(2);
            $('#pagamento-ritenute').val(ritenute);
            $('#pagamento-importo_netto').val((importo - ritenute).toFixed(2));
        } else {
            $('#pagamento-ritenute').val('');
            $('#pagamento-importo_netto').val(importo);
        }
    });

    // Pulsante "Popola saldo rimanente"
    $('#btn-popola-saldo').on('click', function() {
        if (!currentContrattoData) return;
        var saldo = currentContrattoData.saldo_rimanente.toFixed(2);
        $('#pagamento-importo_lordo').val(saldo);
        if (currentContrattoData.ritenute_percentuale > 0) {
            var ritenute = (saldo * currentContrattoData.ritenute_percentuale / 100).toFixed(2);
            $('#pagamento-ritenute').val(ritenute);
            $('#pagamento-importo_netto').val((saldo - ritenute).toFixed(2));
        } else {
            $('#pagamento-ritenute').val('');
            $('#pagamento-importo_netto').val(saldo);
        }
    });

    // Auto-calcolo netto quando cambia lordo o ritenute
    $('#pagamento-importo_lordo, #pagamento-ritenute').on('input', function() {
        var lordo = parseFloat($('#pagamento-importo_lordo').val()) || 0;
        var ritenute = parseFloat($('#pagamento-ritenute').val()) || 0;
        $('#pagamento-importo_netto').val((lordo - ritenute).toFixed(2));
    });

    // Trigger iniziale se contratto gia' selezionato (pre-fill da URL)
    if ($('#pagamento-contratto_id').val()) {
        $('#pagamento-contratto_id').trigger('change');
    }
JS
);
?>
