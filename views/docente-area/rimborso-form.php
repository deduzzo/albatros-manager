<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\VoceSpesa;

/* @var $this yii\web\View */
/* @var $model app\models\RimborsoSpesa */
/* @var $assegnazione app\models\Assegnazione */
/* @var $isModifica bool */

$modulo = $assegnazione->modulo;
$vociSpesa = VoceSpesa::getListGrouped();
$dettagli = $isModifica ? $model->dettagli : [];

$this->title = ($isModifica ? 'Modifica' : 'Nuovo') . ' Rimborso Spesa';
$this->params['breadcrumbs'][] = ['label' => 'I Miei Rimborsi', 'url' => ['rimborsi']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-receipt-2 me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
            <div class="page-subtitle text-secondary">
                Modulo: <span class="badge bg-azure-lt"><?= Html::encode($modulo->codice) ?></span>
                <?= Html::encode($modulo->titolo) ?>
            </div>
        </div>

        <?php $form = AppActiveForm::begin([
            'id' => 'rimborso-spesa-form',
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Dati Rimborso</h3>
                    </div>
                    <div class="card-body">
                        <?= $form->field($model, 'assegnazione_id')->hiddenInput()->label(false) ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'data_spesa')->textInput([
                                    'type' => 'date',
                                ])->label('Data spesa <span class="text-red">*</span>') ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'descrizione')->textarea([
                                    'rows' => 3,
                                    'placeholder' => 'Descrivi la spesa sostenuta...',
                                    'maxlength' => 500,
                                ])->label('Descrizione <span class="text-red">*</span>') ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'note')->textarea([
                                    'rows' => 2,
                                    'placeholder' => 'Note aggiuntive (PAX, mezzo trasporto, etc.)...',
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voci di spesa -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-list-details me-2"></i> Voci di Spesa</h3>
                        <div class="card-actions">
                            <button type="button" class="btn btn-sm btn-primary" id="btn-aggiungi-voce">
                                <i class="ti ti-plus me-1"></i> Aggiungi Voce
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="dettagli-container">
                            <?php if (!empty($dettagli)): ?>
                                <?php foreach ($dettagli as $i => $dettaglio): ?>
                                    <div class="dettaglio-row row mb-3 align-items-end" data-index="<?= $i ?>">
                                        <div class="col-md-3">
                                            <label class="form-label">Voce</label>
                                            <select name="Dettagli[<?= $i ?>][voce_spesa_id]" class="form-select voce-select">
                                                <option value="">-- Libera --</option>
                                                <?php foreach ($vociSpesa as $catLabel => $voci): ?>
                                                    <optgroup label="<?= Html::encode($catLabel) ?>">
                                                        <?php foreach ($voci as $voceId => $voceLabel): ?>
                                                            <option value="<?= $voceId ?>" <?= $dettaglio->voce_spesa_id == $voceId ? 'selected' : '' ?>>
                                                                <?= Html::encode($voceLabel) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label required">Descrizione</label>
                                            <input type="text" name="Dettagli[<?= $i ?>][descrizione]"
                                                   class="form-control" required
                                                   value="<?= Html::encode($dettaglio->descrizione) ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label required">Importo (&euro;)</label>
                                            <input type="number" name="Dettagli[<?= $i ?>][importo]"
                                                   class="form-control importo-input" required
                                                   step="0.01" min="0" value="<?= $dettaglio->importo ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Note</label>
                                            <input type="text" name="Dettagli[<?= $i ?>][note]"
                                                   class="form-control" value="<?= Html::encode($dettaglio->note) ?>">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-rimuovi-voce">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="dettaglio-row row mb-3 align-items-end" data-index="0">
                                    <div class="col-md-3">
                                        <label class="form-label">Voce</label>
                                        <select name="Dettagli[0][voce_spesa_id]" class="form-select voce-select">
                                            <option value="">-- Libera --</option>
                                            <?php foreach ($vociSpesa as $catLabel => $voci): ?>
                                                <optgroup label="<?= Html::encode($catLabel) ?>">
                                                    <?php foreach ($voci as $voceId => $voceLabel): ?>
                                                        <option value="<?= $voceId ?>"><?= Html::encode($voceLabel) ?></option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">Descrizione</label>
                                        <input type="text" name="Dettagli[0][descrizione]"
                                               class="form-control" required placeholder="Descrizione voce...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label required">Importo (&euro;)</label>
                                        <input type="number" name="Dettagli[0][importo]"
                                               class="form-control importo-input" required
                                               step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Note</label>
                                        <input type="text" name="Dettagli[0][note]" class="form-control" placeholder="Note...">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-rimuovi-voce">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <strong>Totale: &euro; <span id="totale-dettagli">0.00</span></strong>
                        </div>
                    </div>
                </div>

                <!-- Upload Allegato -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-paperclip me-2"></i> Allegato</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($isModifica && $model->allegato_path): ?>
                            <div class="alert alert-info mb-3">
                                <i class="ti ti-file me-1"></i>
                                Allegato attuale: <strong><?= Html::encode(basename($model->allegato_path)) ?></strong>
                                <?= Html::a('Visualizza', Yii::getAlias('@web/' . $model->allegato_path), ['target' => '_blank', 'class' => 'ms-2']) ?>
                                <br><small class="text-muted">Caricando un nuovo file, quello attuale verra' sostituito.</small>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label <?= !$isModifica ? 'required' : '' ?>">
                                Ricevuta/Scontrino <?= !$isModifica ? '<span class="text-red">*</span>' : '(opzionale)' ?>
                            </label>
                            <input type="file" name="allegato" class="form-control"
                                   <?= !$isModifica ? 'required' : '' ?>
                                   accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                            <small class="form-hint">Formati accettati: PDF, JPG, PNG, GIF, WEBP. Max 5MB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Informazioni</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Modulo:</dt>
                            <dd class="col-7">
                                <span class="badge bg-azure-lt"><?= Html::encode($modulo->codice) ?></span>
                            </dd>
                            <dt class="col-5">Corso:</dt>
                            <dd class="col-7">
                                <span class="badge bg-blue-lt"><?= Html::encode($modulo->avviso->codice_avviso ?? '-') ?></span>
                            </dd>
                        </dl>

                        <hr>

                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-1"></i>
                            <strong>Nota:</strong> Il rimborso sara' esaminato dall'amministrazione.
                            Ricordati di allegare sempre la ricevuta o lo scontrino della spesa.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="card">
            <div class="card-footer text-end">
                <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['rimborsi'], ['class' => 'btn btn-secondary me-2']) ?>
                <?= Html::submitButton('<i class="ti ti-check me-1"></i> ' . ($isModifica ? 'Salva Modifiche' : 'Invia Richiesta'), ['class' => 'btn btn-green']) ?>
            </div>
        </div>

        <?php AppActiveForm::end(); ?>
    </div>
</div>

<?php
// Template riga dettaglio per JavaScript
$vociOptions = '<option value="">-- Libera --</option>';
foreach ($vociSpesa as $catLabel => $voci) {
    $vociOptions .= '<optgroup label="' . Html::encode($catLabel) . '">';
    foreach ($voci as $voceId => $voceLabel) {
        $vociOptions .= '<option value="' . $voceId . '">' . Html::encode($voceLabel) . '</option>';
    }
    $vociOptions .= '</optgroup>';
}
$vociOptions = str_replace(["\n", "\r"], '', $vociOptions);

$js = <<<JS
var dettaglioIndex = document.querySelectorAll('.dettaglio-row').length;
var vociOptions = '{$vociOptions}';

document.getElementById('btn-aggiungi-voce').addEventListener('click', function() {
    var container = document.getElementById('dettagli-container');
    var row = document.createElement('div');
    row.className = 'dettaglio-row row mb-3 align-items-end';
    row.setAttribute('data-index', dettaglioIndex);
    row.innerHTML = '<div class="col-md-3">' +
        '<label class="form-label">Voce</label>' +
        '<select name="Dettagli[' + dettaglioIndex + '][voce_spesa_id]" class="form-select voce-select">' + vociOptions + '</select>' +
        '</div>' +
        '<div class="col-md-4">' +
        '<label class="form-label required">Descrizione</label>' +
        '<input type="text" name="Dettagli[' + dettaglioIndex + '][descrizione]" class="form-control" required placeholder="Descrizione voce...">' +
        '</div>' +
        '<div class="col-md-2">' +
        '<label class="form-label required">Importo (&euro;)</label>' +
        '<input type="number" name="Dettagli[' + dettaglioIndex + '][importo]" class="form-control importo-input" required step="0.01" min="0" placeholder="0.00">' +
        '</div>' +
        '<div class="col-md-2">' +
        '<label class="form-label">Note</label>' +
        '<input type="text" name="Dettagli[' + dettaglioIndex + '][note]" class="form-control" placeholder="Note...">' +
        '</div>' +
        '<div class="col-md-1">' +
        '<button type="button" class="btn btn-sm btn-outline-danger btn-rimuovi-voce"><i class="ti ti-trash"></i></button>' +
        '</div>';
    container.appendChild(row);
    dettaglioIndex++;
    aggiornaListeners();
    calcolaTotale();
    // Re-init Tom Select sui nuovi select
    if (typeof window.initTomSelectOn === 'function') {
        row.querySelectorAll('select.form-select').forEach(window.initTomSelectOn);
    }
});

function aggiornaListeners() {
    document.querySelectorAll('.btn-rimuovi-voce').forEach(function(btn) {
        btn.onclick = function() {
            if (document.querySelectorAll('.dettaglio-row').length > 1) {
                var selects = this.closest('.dettaglio-row').querySelectorAll('select');
                selects.forEach(function(s) { if (s.tomselect) s.tomselect.destroy(); });
                this.closest('.dettaglio-row').remove();
                calcolaTotale();
            }
        };
    });
    document.querySelectorAll('.importo-input').forEach(function(input) {
        input.oninput = calcolaTotale;
    });
}

function calcolaTotale() {
    var tot = 0;
    document.querySelectorAll('.importo-input').forEach(function(input) {
        tot += parseFloat(input.value) || 0;
    });
    document.getElementById('totale-dettagli').textContent = tot.toFixed(2);
}

aggiornaListeners();
calcolaTotale();
JS;

$this->registerJs($js);
?>
