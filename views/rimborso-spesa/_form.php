<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\RimborsoSpesa;

/* @var $this yii\web\View */
/* @var $model app\models\RimborsoSpesa */
/* @var $form app\components\widgets\AppActiveForm */
/* @var $assegnazioni array */
/* @var $vociSpesa array */

$dettagli = !$model->isNewRecord ? $model->dettagli : [];
?>

<div class="rimborso-spesa-form">
    <?php $form = AppActiveForm::begin([
        'id' => 'rimborso-spesa-form',
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Dati principali -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Dati Rimborso</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?= $form->field($model, 'assegnazione_id')->dropDownList($assegnazioni, [
                                'prompt' => '-- Seleziona assegnazione --',
                                'class' => 'form-select',
                            ])->label('Assegnazione (Docente - Modulo) <span class="text-red">*</span>') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'data_spesa')->textInput([
                                'type' => 'date',
                            ])->label('Data spesa <span class="text-red">*</span>') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'descrizione')->textarea([
                                'rows' => 3,
                                'placeholder' => 'Descrizione generale della spesa...',
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

            <!-- Dettagli voci di spesa -->
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
                                        <label class="form-label">Voce di spesa</label>
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
                                               value="<?= Html::encode($dettaglio->descrizione) ?>"
                                               placeholder="Descrizione voce...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label required">Importo (&euro;)</label>
                                        <input type="number" name="Dettagli[<?= $i ?>][importo]"
                                               class="form-control importo-input" required
                                               step="0.01" min="0"
                                               value="<?= $dettaglio->importo ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Note</label>
                                        <input type="text" name="Dettagli[<?= $i ?>][note]"
                                               class="form-control"
                                               value="<?= Html::encode($dettaglio->note) ?>"
                                               placeholder="Note...">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-rimuovi-voce" title="Rimuovi">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Riga default vuota -->
                            <div class="dettaglio-row row mb-3 align-items-end" data-index="0">
                                <div class="col-md-3">
                                    <label class="form-label">Voce di spesa</label>
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
                                           class="form-control" required
                                           placeholder="Descrizione voce...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label required">Importo (&euro;)</label>
                                    <input type="number" name="Dettagli[0][importo]"
                                           class="form-control importo-input" required
                                           step="0.01" min="0"
                                           placeholder="0.00">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Note</label>
                                    <input type="text" name="Dettagli[0][note]"
                                           class="form-control"
                                           placeholder="Note...">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-rimuovi-voce" title="Rimuovi">
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

            <!-- Allegato -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-paperclip me-2"></i> Allegato</h3>
                </div>
                <div class="card-body">
                    <?php if (!$model->isNewRecord && $model->allegato_path): ?>
                        <div class="alert alert-info mb-3">
                            <i class="ti ti-file me-1"></i>
                            Allegato attuale: <strong><?= Html::encode(basename($model->allegato_path)) ?></strong>
                            <?= Html::a('Visualizza', Yii::getAlias('@web/' . $model->allegato_path), ['target' => '_blank', 'class' => 'ms-2']) ?>
                            <br><small class="text-muted">Caricando un nuovo file, quello attuale verra' sostituito.</small>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Ricevuta/Scontrino</label>
                        <input type="file" name="allegato" class="form-control"
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
                    <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Stato</h3>
                </div>
                <div class="card-body">
                    <?php if (!$model->isNewRecord): ?>
                        <dl class="row mb-0">
                            <dt class="col-5">Stato:</dt>
                            <dd class="col-7"><?= $model->getStatusBadge() ?></dd>
                            <dt class="col-5">Creato il:</dt>
                            <dd class="col-7"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></dd>
                        </dl>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-1"></i>
                            Il rimborso verra' creato in stato <strong>Richiesto</strong>.
                            Potrai approvarlo successivamente.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Actions -->
    <div class="card">
        <div class="card-footer text-end">
            <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
            <?= Html::submitButton(
                '<i class="ti ti-check me-1"></i> ' . ($model->isNewRecord ? 'Crea Rimborso' : 'Salva Modifiche'),
                ['class' => 'btn btn-green']
            ) ?>
        </div>
    </div>

    <?php AppActiveForm::end(); ?>
</div>

<?php
// Template riga dettaglio per JavaScript (con voci di spesa grouped)
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

// Aggiungi nuova riga
document.getElementById('btn-aggiungi-voce').addEventListener('click', function() {
    var container = document.getElementById('dettagli-container');
    var row = document.createElement('div');
    row.className = 'dettaglio-row row mb-3 align-items-end';
    row.setAttribute('data-index', dettaglioIndex);
    row.innerHTML = '<div class="col-md-3">' +
        '<label class="form-label">Voce di spesa</label>' +
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
        '<button type="button" class="btn btn-sm btn-outline-danger btn-rimuovi-voce" title="Rimuovi"><i class="ti ti-trash"></i></button>' +
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

// Rimuovi riga
function aggiornaListeners() {
    document.querySelectorAll('.btn-rimuovi-voce').forEach(function(btn) {
        btn.onclick = function() {
            var rows = document.querySelectorAll('.dettaglio-row');
            if (rows.length > 1) {
                // Distruggi Tom Select prima di rimuovere il DOM
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

// Calcola totale
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
