<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\BorsaStudio;

/** @var yii\web\View $this */
/** @var app\models\BorsaStudio $model */
/** @var array $progetti */

$isUpdate = !$model->isNewRecord;
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>
<div class="borsa-studio-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'card', 'id' => 'borsa-studio-form']]); ?>

    <!-- Sezione Progetto e Studente -->
    <div class="card-header"><h3 class="card-title"><i class="ti ti-school me-2"></i>Progetto e Studente</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'progetto_id')->dropDownList($progetti, [
                    'prompt' => '-- Seleziona Progetto --',
                    'id' => 'progetto-select',
                ])->label('Progetto/Corso <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'allievo_codice_fiscale')->dropDownList(
                    $isUpdate && $model->allievo ? [$model->allievo_codice_fiscale => $model->allievo->getNomeCompleto() . ' (' . $model->allievo_codice_fiscale . ')'] : [],
                    [
                        'prompt' => '-- Seleziona prima il progetto --',
                        'id' => 'studente-select',
                    ]
                )->label('Studente <span class="text-danger">*</span>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'anno_formativo')->textInput([
                    'placeholder' => 'es. 2024/2025',
                    'maxlength' => 9,
                ])->label('Anno Formativo <span class="text-danger">*</span>') ?>
            </div>
        </div>
    </div>

    <!-- Sezione Tipologia Sede -->
    <div class="card-header"><h3 class="card-title"><i class="ti ti-building me-2"></i>Tipologia Sede</h3></div>
    <div class="card-body">
        <?= $form->field($model, 'tipologia_sede')->radioList(BorsaStudio::getTipologiaSedeList(), [
            'id' => 'tipologia-sede-radios',
            'item' => function ($index, $label, $name, $checked, $value) {
                $importi = [
                    'in_sede' => '2.481,75',
                    'pendolare' => '3.598,51',
                    'fuori_sede' => '6.157,74',
                ];
                $importo = $importi[$value] ?? '';
                $checkedAttr = $checked ? ' checked' : '';
                return '<div class="form-check form-check-inline">'
                    . '<input class="form-check-input tipologia-radio" type="radio" name="' . $name . '" value="' . $value . '" id="tipologia_' . $value . '"' . $checkedAttr . ' data-importo="' . $importo . '">'
                    . '<label class="form-check-label" for="tipologia_' . $value . '">' . $label . ' <small class="text-muted">(€ ' . $importo . ')</small></label>'
                    . '</div>';
            },
        ])->label('Tipologia Sede <span class="text-danger">*</span>') ?>
    </div>

    <!-- Sezione ISEE/ISPE -->
    <div class="card-header"><h3 class="card-title"><i class="ti ti-report-money me-2"></i>Dati ISEE / ISPE</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'isee_importo')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => 0,
                    'max' => BorsaStudio::ISEE_LIMITE,
                    'id' => 'isee-importo',
                    'placeholder' => 'Max ' . number_format(BorsaStudio::ISEE_LIMITE, 2, ',', '.'),
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'ispe_importo')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => 0,
                    'max' => BorsaStudio::ISPE_LIMITE,
                    'placeholder' => 'Max ' . number_format(BorsaStudio::ISPE_LIMITE, 2, ',', '.'),
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'isee_protocollo')->textInput([
                    'maxlength' => 100,
                    'placeholder' => 'Protocollo attestazione ISEE',
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'iban_studente')->textInput([
                    'maxlength' => 34,
                    'placeholder' => 'IBAN per accredito',
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Sezione Maggiorazioni -->
    <div class="card-header"><h3 class="card-title"><i class="ti ti-percentage me-2"></i>Maggiorazioni (non cumulabili - si applica la piu alta)</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-check">
                        <input class="form-check-input maggiorazione-check" type="checkbox" name="is_isee_basso" id="check-isee-basso" data-perc="15" disabled>
                        <span class="form-check-label">ISEE ≤ <?= number_format(BorsaStudio::ISEE_LIMITE / 2, 2, ',', '.') ?> (+15%)</span>
                    </label>
                    <small class="text-muted d-block" id="isee-basso-info">Calcolato automaticamente dall'ISEE inserito</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-check">
                        <input class="form-check-input maggiorazione-check" type="checkbox" name="is_studentessa" id="check-studentessa" value="1" data-perc="20"
                            <?= $model->maggiorazione_tipo === 'studentessa' ? 'checked' : '' ?>>
                        <span class="form-check-label">Studentessa (+20%)</span>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-check">
                        <input class="form-check-input maggiorazione-check" type="checkbox" name="is_disabile" id="check-disabile" value="1" data-perc="40"
                            <?= $model->maggiorazione_tipo === 'disabilita' ? 'checked' : '' ?>>
                        <span class="form-check-label">Disabilita ≥66% (+40%)</span>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_tirocinio_estero" id="check-tirocinio" value="1">
                        <span class="form-check-label">Tirocinio estero (+600€/mese)</span>
                    </label>
                    <small class="text-muted d-block">Da gestire separatamente</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione Calcolo Importo (Live) -->
    <div class="card-header"><h3 class="card-title"><i class="ti ti-calculator me-2"></i>Calcolo Importo</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Importo Base</label>
                    <div class="h2" id="calc-importo-base"><?= $model->importo_base ? Yii::$app->formatter->asCurrency($model->importo_base, 'EUR') : '-' ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Maggiorazione Applicata</label>
                    <div class="h3" id="calc-maggiorazione">
                        <?php if ($model->maggiorazione_tipo): ?>
                            <span class="badge bg-purple-lt">+<?= $model->maggiorazione_percentuale ?>%</span>
                        <?php else: ?>
                            <span class="text-muted">Nessuna</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Importo Finale</label>
                    <div class="h1 text-blue" id="calc-importo-finale"><?= $model->importo_finale ? Yii::$app->formatter->asCurrency($model->importo_finale, 'EUR') : '-' ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Note -->
    <div class="card-header"><h3 class="card-title"><i class="ti ti-notes me-2"></i>Note</h3></div>
    <div class="card-body">
        <?= $form->field($model, 'note')->textarea(['rows' => 3, 'placeholder' => 'Note aggiuntive'])->label(false) ?>
    </div>

    <!-- Hidden fields per importi calcolati -->
    <?= Html::activeHiddenInput($model, 'importo_base', ['id' => 'hidden-importo-base']) ?>
    <?= Html::activeHiddenInput($model, 'importo_finale', ['id' => 'hidden-importo-finale']) ?>

    <div class="card-footer text-end">
        <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$importiBase = json_encode([
    'in_sede' => BorsaStudio::IMPORTO_BASE_IN_SEDE,
    'pendolare' => BorsaStudio::IMPORTO_BASE_PENDOLARE,
    'fuori_sede' => BorsaStudio::IMPORTO_BASE_FUORI_SEDE,
]);
$iseeLimite = BorsaStudio::ISEE_LIMITE;
$iseeMetaLimite = BorsaStudio::ISEE_LIMITE / 2;
$studentiUrl = \yii\helpers\Url::to(['studenti-progetto']);
$csrfParamJs = $csrfParam;
$csrfTokenJs = $csrfToken;

$js = <<<JS
(function() {
    const importiBase = {$importiBase};
    const ISEE_META_LIMITE = {$iseeMetaLimite};
    const studentiUrl = '{$studentiUrl}';

    // Cascading dropdown: progetto -> studente
    const progettoSelect = document.getElementById('progetto-select');
    const studenteSelect = document.getElementById('studente-select');

    if (progettoSelect) {
        progettoSelect.addEventListener('change', function() {
            const progettoId = this.value;
            studenteSelect.innerHTML = '<option value="">Caricamento...</option>';

            if (!progettoId) {
                studenteSelect.innerHTML = '<option value="">-- Seleziona prima il progetto --</option>';
                return;
            }

            fetch(studentiUrl + '?progetto_id=' + progettoId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                studenteSelect.innerHTML = '<option value="">-- Seleziona Studente --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.text;
                    studenteSelect.appendChild(opt);
                });
            })
            .catch(() => {
                studenteSelect.innerHTML = '<option value="">Errore caricamento studenti</option>';
            });
        });
    }

    // Calcolo live importo
    function calcolaImporto() {
        const tipologia = document.querySelector('.tipologia-radio:checked');
        if (!tipologia) {
            document.getElementById('calc-importo-base').textContent = '-';
            document.getElementById('calc-maggiorazione').innerHTML = '<span class="text-muted">Nessuna</span>';
            document.getElementById('calc-importo-finale').textContent = '-';
            document.getElementById('hidden-importo-base').value = '';
            document.getElementById('hidden-importo-finale').value = '';
            return;
        }

        const tipologiaVal = tipologia.value;
        const importoBase = importiBase[tipologiaVal] || 0;

        // Determina maggiorazione (priorita: disabilita > isee_basso > studentessa)
        const isDisabile = document.getElementById('check-disabile').checked;
        const isStudentessa = document.getElementById('check-studentessa').checked;
        const iseeInput = document.getElementById('isee-importo');
        const iseeVal = parseFloat(iseeInput.value) || 0;
        const isIseeBasso = iseeVal > 0 && iseeVal <= ISEE_META_LIMITE;

        // Aggiorna checkbox ISEE basso (auto)
        const checkIseeBasso = document.getElementById('check-isee-basso');
        checkIseeBasso.checked = isIseeBasso;

        let maggiorazionePerc = 0;
        let maggiorazioneTipo = '';

        if (isDisabile) {
            maggiorazionePerc = 40;
            maggiorazioneTipo = 'Disabilita +40%';
        } else if (isIseeBasso) {
            maggiorazionePerc = 15;
            maggiorazioneTipo = 'ISEE basso +15%';
        } else if (isStudentessa) {
            maggiorazionePerc = 20;
            maggiorazioneTipo = 'Studentessa +20%';
        }

        const importoFinale = maggiorazionePerc > 0
            ? Math.round(importoBase * (1 + maggiorazionePerc / 100) * 100) / 100
            : importoBase;

        // Aggiorna UI
        document.getElementById('calc-importo-base').textContent = formatCurrency(importoBase);
        document.getElementById('hidden-importo-base').value = importoBase;
        document.getElementById('hidden-importo-finale').value = importoFinale;

        if (maggiorazionePerc > 0) {
            document.getElementById('calc-maggiorazione').innerHTML =
                '<span class="badge bg-purple-lt">' + maggiorazioneTipo + '</span>';
        } else {
            document.getElementById('calc-maggiorazione').innerHTML = '<span class="text-muted">Nessuna</span>';
        }

        document.getElementById('calc-importo-finale').textContent = formatCurrency(importoFinale);
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(value);
    }

    // Event listeners
    document.querySelectorAll('.tipologia-radio').forEach(radio => {
        radio.addEventListener('change', calcolaImporto);
    });

    document.querySelectorAll('.maggiorazione-check').forEach(check => {
        check.addEventListener('change', calcolaImporto);
    });

    const iseeInput = document.getElementById('isee-importo');
    if (iseeInput) {
        iseeInput.addEventListener('input', calcolaImporto);
    }

    // Calcola iniziale
    calcolaImporto();
})();
JS;

$this->registerJs($js);
?>
