<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\widgets\Alert;

$this->title = 'Import Dati da Excel';
$this->params['breadcrumbs'][] = ['label' => 'Gestione', 'url' => ['/site/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="import-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-file-upload me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Importa progetti, moduli e docenti da file Excel standardizzato
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a(
                        '<i class="ti ti-download me-1"></i> Scarica Template',
                        ['download-template'],
                        ['class' => 'btn btn-primary']
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= Alert::widget() ?>

            <!-- Card istruzioni -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Istruzioni</h3>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Scarica il template Excel</strong> usando il pulsante in alto a destra
                        </li>
                        <li class="mb-2">
                            <strong>Compila tutti i fogli</strong> seguendo le istruzioni nel foglio "📖 ISTRUZIONI"
                        </li>
                        <li class="mb-2">
                            <strong>Campi obbligatori</strong> sono evidenziati in <span class="badge bg-blue text-blue-fg">BLU</span>
                        </li>
                        <li class="mb-2">
                            <strong>Salva il file Excel</strong> senza modificare i nomi dei fogli
                        </li>
                        <li>
                            <strong>Carica il file</strong> usando il form qui sotto
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Card upload -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-upload me-2"></i>Carica File Excel</h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'import-form',
                        'options' => [
                            'enctype' => 'multipart/form-data',
                            'class' => 'needs-validation'
                        ],
                    ]); ?>

                    <div class="mb-3">
                        <label class="form-label required">File Excel (.xlsx)</label>
                        <input type="file"
                               name="excel_file"
                               id="excel-file"
                               class="form-control"
                               accept=".xlsx"
                               required>
                        <div class="form-hint">
                            Carica il file Excel compilato seguendo il template standard. Dimensione massima: 10 MB
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="dry_run" id="dry-run" checked>
                            <span class="form-check-label">
                                <strong>Modalità test</strong> - Valida i dati senza salvare nel database
                            </span>
                        </label>
                        <small class="text-muted d-block mt-1">
                            Consigliato: esegui prima un test per verificare la correttezza dei dati
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="overwrite" id="overwrite">
                            <span class="form-check-label text-danger">
                                <strong>Sovrascrivi progetto esistente</strong> - Elimina tutti i dati del progetto se già presente
                            </span>
                        </label>
                        <small class="text-muted d-block mt-1">
                            ⚠️ ATTENZIONE: Questa opzione eliminerà completamente il progetto, avvisi, moduli, istanze, assegnazioni e contratti esistenti per poi ricrearli dall'import
                        </small>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="ti ti-upload me-1"></i>
                            Avvia Import
                        </button>
                        <button type="button" class="btn btn-link" id="reset-btn" onclick="location.reload()">
                            Annulla
                        </button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <!-- Card risultati (nascosta inizialmente) -->
            <div class="card mt-3" id="results-card" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <span id="results-icon"></span>
                        <span id="results-title"></span>
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Progress bar -->
                    <div id="progress-container" style="display: none;">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar"
                                 id="progress-bar"
                                 style="width: 0%">
                                <span id="progress-text">0%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Messaggi log -->
                    <div id="log-container"></div>

                    <!-- Errori -->
                    <div id="errors-container"></div>

                    <!-- Warnings -->
                    <div id="warnings-container"></div>

                    <!-- Statistiche -->
                    <div id="stats-container" class="mt-3"></div>
                </div>
                <div class="card-footer" id="results-footer" style="display: none;">
                    <button type="button" class="btn btn-success" id="confirm-import-btn" style="display: none;">
                        <i class="ti ti-check me-1"></i>
                        Conferma e Salva nel Database
                    </button>
                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                        <i class="ti ti-refresh me-1"></i>
                        Nuova Importazione
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$uploadUrl = \yii\helpers\Url::to(['import/upload']);
$this->registerJs(<<<JS
let lastValidationData = null;

$('#import-form').on('submit', function(e) {
    e.preventDefault();

    const fileInput = document.getElementById('excel-file');
    const file = fileInput.files[0];

    if (!file) {
        alert('Seleziona un file Excel');
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        alert('Il file è troppo grande (max 10 MB)');
        return;
    }

    const isDryRun = $('#dry-run').is(':checked');

    // Mostra progress
    $('#results-card').show();
    $('#progress-container').show();
    $('#results-icon').html('<i class="ti ti-loader-2 me-2 spinner-border spinner-border-sm"></i>');
    $('#results-title').text(isDryRun ? 'Validazione in corso...' : 'Import in corso...');
    $('#log-container, #errors-container, #warnings-container, #stats-container').html('');
    $('#results-footer').hide();
    $('#submit-btn').prop('disabled', true);

    // Simula progress (real progress richiede WebSocket o polling)
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 5;
        if (progress >= 90) {
            clearInterval(progressInterval);
        }
        updateProgress(progress);
    }, 200);

    // Upload via AJAX
    const formData = new FormData();
    formData.append('excel_file', file);
    formData.append('dry_run', isDryRun ? '1' : '0');
    formData.append('overwrite', $('#overwrite').is(':checked') ? '1' : '0');

    fetch('$uploadUrl', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        updateProgress(100);

        setTimeout(() => {
            $('#progress-container').hide();
            handleResponse(data, isDryRun);
        }, 500);
    })
    .catch(error => {
        clearInterval(progressInterval);
        $('#progress-container').hide();
        $('#results-icon').html('<i class="ti ti-alert-circle me-2 text-danger"></i>');
        $('#results-title').text('Errore durante l\'upload');

        console.error('Errore fetch:', error);

        $('#errors-container').html(
            '<div class="alert alert-danger">' +
            '<i class="ti ti-alert-circle me-2"></i>' +
            '<strong>Errore di connessione:</strong> ' + error.message + '<br>' +
            '<small>Controlla la console del browser (F12) per dettagli tecnici.</small>' +
            '</div>'
        );
        $('#results-footer').show();
        $('#submit-btn').prop('disabled', false);
    });
});

function updateProgress(percent) {
    $('#progress-bar').css('width', percent + '%');
    $('#progress-text').text(percent + '%');
}

function handleResponse(data, isDryRun) {
    lastValidationData = data;

    // Debug: mostra risposta completa
    console.log('Risposta server:', data);

    if (data.success) {
        $('#results-icon').html('<i class="ti ti-check me-2 text-success"></i>');
        $('#results-title').text(isDryRun ? 'Validazione completata con successo' : 'Import completato con successo');

        // Log operazioni
        if (data.log && data.log.length > 0) {
            let logHtml = '<div class="alert alert-success"><h4>Operazioni eseguite:</h4><ul class="mb-0">';
            data.log.forEach(msg => {
                logHtml += '<li>' + msg + '</li>';
            });
            logHtml += '</ul></div>';
            $('#log-container').html(logHtml);
        }

        // Warnings
        if (data.warnings && data.warnings.length > 0) {
            let warnHtml = '<div class="alert alert-warning"><h4>Avvisi:</h4><ul class="mb-0">';
            data.warnings.forEach(msg => {
                warnHtml += '<li>' + msg + '</li>';
            });
            warnHtml += '</ul></div>';
            $('#warnings-container').html(warnHtml);
        }

        // Statistiche
        if (data.stats) {
            let statsHtml = '<div class="row">';
            for (let key in data.stats) {
                statsHtml +=
                    '<div class="col-md-3">' +
                    '<div class="card card-sm">' +
                    '<div class="card-body">' +
                    '<div class="row align-items-center">' +
                    '<div class="col-auto">' +
                    '<span class="bg-blue text-blue-fg avatar">' + data.stats[key] + '</span>' +
                    '</div>' +
                    '<div class="col">' +
                    '<div class="font-weight-medium">' + key + '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            }
            statsHtml += '</div>';
            $('#stats-container').html(statsHtml);
        }

        // Se è dry run, mostra bottone conferma
        if (isDryRun) {
            $('#confirm-import-btn').show();
        }

        $('#results-footer').show();
        $('#submit-btn').prop('disabled', false);

    } else {
        $('#results-icon').html('<i class="ti ti-alert-circle me-2 text-danger"></i>');
        $('#results-title').text('Errori di validazione');

        // Gestione errori
        let hasErrors = false;

        if (data.errors && data.errors.length > 0) {
            let errHtml = '<div class="alert alert-danger"><h4>Errori trovati:</h4><ul class="mb-0">';
            data.errors.forEach(msg => {
                errHtml += '<li>' + msg + '</li>';
            });
            errHtml += '</ul></div>';
            $('#errors-container').html(errHtml);
            hasErrors = true;
        }

        // Errore generico se presente
        if (data.error) {
            let errHtml = '<div class="alert alert-danger">' +
                '<i class="ti ti-alert-circle me-2"></i>' +
                '<strong>Errore:</strong> ' + data.error +
                '</div>';
            $('#errors-container').html(errHtml);
            hasErrors = true;
        }

        // Se non ci sono errori specificati, mostra messaggio generico
        if (!hasErrors) {
            let errHtml = '<div class="alert alert-warning">' +
                '<i class="ti ti-alert-triangle me-2"></i>' +
                '<strong>Errore sconosciuto</strong><br>' +
                'Nessun dettaglio disponibile. Controlla la console del browser (F12) per maggiori informazioni.' +
                '</div>';
            $('#errors-container').html(errHtml);
            console.error('Risposta errore senza dettagli:', data);
        }

        // Mostra warnings se presenti
        if (data.warnings && data.warnings.length > 0) {
            let warnHtml = '<div class="alert alert-warning mt-3"><h4>Avvisi:</h4><ul class="mb-0">';
            data.warnings.forEach(msg => {
                warnHtml += '<li>' + msg + '</li>';
            });
            warnHtml += '</ul></div>';
            $('#warnings-container').html(warnHtml);
        }

        $('#results-footer').show();
        $('#submit-btn').prop('disabled', false);
    }
}

// Conferma import dopo validazione
$('#confirm-import-btn').on('click', function() {
    if (!confirm('Sei sicuro di voler procedere con l\'import definitivo?')) {
        return;
    }

    const fileInput = document.getElementById('excel-file');
    const file = fileInput.files[0];

    if (!file) {
        alert('File non più disponibile. Ricarica la pagina.');
        return;
    }

    $('#dry-run').prop('checked', false);
    $('#import-form').submit();
});
JS
);
?>
