<?php

use yii\helpers\Html;
use app\components\widgets\Alert;

$this->title = 'Import Dati';
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
                        Importa dati da file Excel nel sistema
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= Alert::widget() ?>

            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a href="#tab-completo" class="nav-link active" data-bs-toggle="tab" role="tab" aria-selected="true">
                                <i class="ti ti-database-import me-1"></i>Import Completo
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-assegnazioni" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-user-check me-1"></i>Import Assegnazioni
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-calendari" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-calendar-plus me-1"></i>Import Calendari
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        <!-- TAB 1: Import Completo -->
                        <div class="tab-pane active show" id="tab-completo" role="tabpanel">
                            <!-- Istruzioni -->
                            <div class="card bg-azure-lt mb-3">
                                <div class="card-body">
                                    <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Istruzioni - Import Completo</h4>
                                    <ol class="mb-0">
                                        <li class="mb-1">Scarica il template Excel cliccando il pulsante qui sotto</li>
                                        <li class="mb-1">Compila tutti i fogli (INFO PROGETTO, MODULI, DOCENTI &amp; ESITI)</li>
                                        <li>Carica il file per importare un progetto completo con tutti i dati</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="mb-3">
                                <?= Html::a(
                                    '<i class="ti ti-download me-1"></i> Scarica Template',
                                    ['download-template'],
                                    ['class' => 'btn btn-outline-primary']
                                ) ?>
                            </div>

                            <!-- Form upload -->
                            <div class="mb-3">
                                <label class="form-label required">File Excel (.xlsx)</label>
                                <input type="file" id="file-completo" class="form-control" accept=".xlsx">
                                <div class="form-hint">Dimensione massima: 10 MB</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" id="dry-run-completo" checked>
                                    <span class="form-check-label">
                                        <strong>Modalita test</strong> - Valida i dati senza salvare nel database
                                    </span>
                                </label>
                            </div>

                            <div class="mb-3">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" id="overwrite-completo">
                                    <span class="form-check-label text-red">
                                        <strong>Sovrascrivi progetto esistente</strong> - Elimina tutti i dati del progetto se gia presente
                                    </span>
                                </label>
                                <small class="text-muted d-block mt-1">
                                    ATTENZIONE: Questa opzione eliminera completamente il progetto, avvisi, moduli, istanze, assegnazioni e contratti esistenti per poi ricrearli dall'import
                                </small>
                            </div>

                            <button type="button" class="btn btn-primary" id="btn-upload-completo">
                                <i class="ti ti-upload me-1"></i>Avvia Import
                            </button>

                            <!-- Risultati -->
                            <div id="results-completo" class="mt-3" style="display:none;">
                                <div id="progress-completo" style="display:none;">
                                    <div class="progress mb-3">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-completo" style="width:0%">
                                            <span id="progresstext-completo">0%</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="status-completo"></div>
                                <div id="log-completo"></div>
                                <div id="errors-completo"></div>
                                <div id="warnings-completo"></div>
                                <div id="stats-completo" class="mt-3"></div>
                                <div id="footer-completo" class="mt-3" style="display:none;">
                                    <button type="button" class="btn bg-green text-green-fg" id="btn-confirm-completo" style="display:none;">
                                        <i class="ti ti-check me-1"></i>Conferma e Salva nel Database
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                                        <i class="ti ti-refresh me-1"></i>Nuova Importazione
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: Import Assegnazioni -->
                        <div class="tab-pane" id="tab-assegnazioni" role="tabpanel">
                            <!-- Istruzioni -->
                            <div class="card bg-azure-lt mb-3">
                                <div class="card-body">
                                    <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Istruzioni - Import Assegnazioni</h4>
                                    <ol class="mb-0">
                                        <li class="mb-1">Scarica il template per le assegnazioni</li>
                                        <li class="mb-1">Compila con CODICE_MODULO (deve gia esistere nel sistema), COGNOME, NOME, CF, etc.</li>
                                        <li class="mb-1">I docenti non presenti verranno creati automaticamente</li>
                                        <li>Le assegnazioni gia esistenti verranno saltate</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="mb-3">
                                <?= Html::a(
                                    '<i class="ti ti-download me-1"></i> Scarica Template Assegnazioni',
                                    ['download-template-assegnazioni'],
                                    ['class' => 'btn btn-outline-primary']
                                ) ?>
                            </div>

                            <!-- Form upload -->
                            <div class="mb-3">
                                <label class="form-label required">File Excel (.xlsx)</label>
                                <input type="file" id="file-assegnazioni" class="form-control" accept=".xlsx">
                                <div class="form-hint">Dimensione massima: 10 MB</div>
                            </div>

                            <button type="button" class="btn btn-primary" id="btn-upload-assegnazioni">
                                <i class="ti ti-search me-1"></i>Valida File
                            </button>

                            <!-- Risultati -->
                            <div id="results-assegnazioni" class="mt-3" style="display:none;">
                                <div id="progress-assegnazioni" style="display:none;">
                                    <div class="progress mb-3">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-assegnazioni" style="width:0%">
                                            <span id="progresstext-assegnazioni">0%</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="status-assegnazioni"></div>
                                <div id="preview-assegnazioni"></div>
                                <div id="errors-assegnazioni"></div>
                                <div id="warnings-assegnazioni"></div>
                                <div id="stats-assegnazioni" class="mt-3"></div>
                                <div id="footer-assegnazioni" class="mt-3" style="display:none;">
                                    <button type="button" class="btn bg-green text-green-fg" id="btn-confirm-assegnazioni" style="display:none;">
                                        <i class="ti ti-check me-1"></i>Conferma Import
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                                        <i class="ti ti-refresh me-1"></i>Nuova Importazione
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: Import Calendari -->
                        <div class="tab-pane" id="tab-calendari" role="tabpanel">
                            <!-- Istruzioni -->
                            <div class="card bg-azure-lt mb-3">
                                <div class="card-body">
                                    <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Istruzioni - Import Calendari</h4>
                                    <ol class="mb-0">
                                        <li class="mb-1">Scarica il template per il calendario</li>
                                        <li class="mb-1">Compila con CODICE_MODULO, CF_DOCENTE (devono gia esistere nel sistema)</li>
                                        <li>Le lezioni duplicate (stessa data/modulo/docente/ora) verranno saltate</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="mb-3">
                                <?= Html::a(
                                    '<i class="ti ti-download me-1"></i> Scarica Template Calendari',
                                    ['download-template-calendari'],
                                    ['class' => 'btn btn-outline-primary']
                                ) ?>
                            </div>

                            <!-- Form upload -->
                            <div class="mb-3">
                                <label class="form-label required">File Excel (.xlsx)</label>
                                <input type="file" id="file-calendari" class="form-control" accept=".xlsx">
                                <div class="form-hint">Dimensione massima: 10 MB</div>
                            </div>

                            <button type="button" class="btn btn-primary" id="btn-upload-calendari">
                                <i class="ti ti-search me-1"></i>Valida File
                            </button>

                            <!-- Risultati -->
                            <div id="results-calendari" class="mt-3" style="display:none;">
                                <div id="progress-calendari" style="display:none;">
                                    <div class="progress mb-3">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-calendari" style="width:0%">
                                            <span id="progresstext-calendari">0%</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="status-calendari"></div>
                                <div id="preview-calendari"></div>
                                <div id="errors-calendari"></div>
                                <div id="warnings-calendari"></div>
                                <div id="stats-calendari" class="mt-3"></div>
                                <div id="footer-calendari" class="mt-3" style="display:none;">
                                    <button type="button" class="btn bg-green text-green-fg" id="btn-confirm-calendari" style="display:none;">
                                        <i class="ti ti-check me-1"></i>Conferma Import
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                                        <i class="ti ti-refresh me-1"></i>Nuova Importazione
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
$urlCompleto = \yii\helpers\Url::to(['import/upload']);
$urlAssegnazioni = \yii\helpers\Url::to(['import/upload-assegnazioni']);
$urlCalendari = \yii\helpers\Url::to(['import/upload-calendari']);

$this->registerJs(<<<JS

// File storage per preview → confirm
let storedFiles = {};

// ==========================================
// TAB 1: Import Completo (senza preview)
// ==========================================
$('#btn-upload-completo').on('click', function() {
    submitImport('completo', '$urlCompleto', false);
});

$('#btn-confirm-completo').on('click', function() {
    if (!confirm('Sei sicuro di voler procedere con l\\'import definitivo?')) return;
    submitImport('completo', '$urlCompleto', false, true);
});

// ==========================================
// TAB 2: Import Assegnazioni (con preview)
// ==========================================
$('#btn-upload-assegnazioni').on('click', function() {
    submitImport('assegnazioni', '$urlAssegnazioni', true);
});

$('#btn-confirm-assegnazioni').on('click', function() {
    if (!confirm('Sei sicuro di voler procedere con l\\'import definitivo?')) return;
    confirmImport('assegnazioni', '$urlAssegnazioni');
});

// ==========================================
// TAB 3: Import Calendari (con preview)
// ==========================================
$('#btn-upload-calendari').on('click', function() {
    submitImport('calendari', '$urlCalendari', true);
});

$('#btn-confirm-calendari').on('click', function() {
    if (!confirm('Sei sicuro di voler procedere con l\\'import definitivo?')) return;
    confirmImport('calendari', '$urlCalendari');
});

// ==========================================
// Funzione generica upload
// ==========================================
function submitImport(tabId, uploadUrl, hasPreview, isConfirm) {
    isConfirm = isConfirm || false;

    var fileInput = document.getElementById('file-' + tabId);
    var file = fileInput.files[0];

    if (!file) {
        alert('Seleziona un file Excel');
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        alert('Il file e troppo grande (max 10 MB)');
        return;
    }

    // Salva file per eventuale confirm successivo
    storedFiles[tabId] = file;

    // Mostra progress
    $('#results-' + tabId).show();
    $('#progress-' + tabId).show();
    $('#status-' + tabId).html('<h4><i class="ti ti-loader-2 me-2 spinner-border spinner-border-sm"></i>' +
        (hasPreview && !isConfirm ? 'Validazione in corso...' : 'Import in corso...') + '</h4>');
    $('#preview-' + tabId + ', #log-' + tabId + ', #errors-' + tabId + ', #warnings-' + tabId + ', #stats-' + tabId).html('');
    $('#footer-' + tabId).hide();
    $('#btn-confirm-' + tabId).hide();
    $('#btn-upload-' + tabId).prop('disabled', true);

    // Progress simulata
    var progress = 0;
    var progressInterval = setInterval(function() {
        progress += 5;
        if (progress >= 90) clearInterval(progressInterval);
        updateProgress(tabId, progress);
    }, 200);

    // FormData
    var formData = new FormData();
    formData.append('excel_file', file);

    if (tabId === 'completo') {
        var isDryRun = isConfirm ? false : $('#dry-run-completo').is(':checked');
        formData.append('dry_run', isDryRun ? '1' : '0');
        formData.append('overwrite', $('#overwrite-completo').is(':checked') ? '1' : '0');
    } else if (hasPreview && !isConfirm) {
        formData.append('preview', '1');
    } else {
        formData.append('confirm', '1');
    }

    fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        clearInterval(progressInterval);
        updateProgress(tabId, 100);
        setTimeout(function() {
            $('#progress-' + tabId).hide();
            if (tabId === 'completo') {
                handleResponseCompleto(data, !isConfirm && $('#dry-run-completo').is(':checked'));
            } else if (hasPreview && !isConfirm) {
                handlePreviewResponse(tabId, data);
            } else {
                handleConfirmResponse(tabId, data);
            }
        }, 500);
    })
    .catch(function(error) {
        clearInterval(progressInterval);
        $('#progress-' + tabId).hide();
        $('#status-' + tabId).html('<h4><i class="ti ti-alert-circle me-2 text-red"></i>Errore durante l\\'upload</h4>');
        $('#errors-' + tabId).html(
            '<div class="alert alert-danger">' +
            '<i class="ti ti-alert-circle me-2"></i><strong>Errore di connessione:</strong> ' + error.message +
            '</div>'
        );
        $('#footer-' + tabId).show();
        $('#btn-upload-' + tabId).prop('disabled', false);
    });
}

// ==========================================
// Confirm import (tab con preview)
// ==========================================
function confirmImport(tabId, uploadUrl) {
    var file = storedFiles[tabId];
    if (!file) {
        alert('File non piu disponibile. Ricarica la pagina.');
        return;
    }

    // Mostra progress
    $('#progress-' + tabId).show();
    $('#status-' + tabId).html('<h4><i class="ti ti-loader-2 me-2 spinner-border spinner-border-sm"></i>Import in corso...</h4>');
    $('#preview-' + tabId + ', #errors-' + tabId + ', #warnings-' + tabId + ', #stats-' + tabId).html('');
    $('#footer-' + tabId).hide();
    $('#btn-confirm-' + tabId).hide();

    var progress = 0;
    var progressInterval = setInterval(function() {
        progress += 5;
        if (progress >= 90) clearInterval(progressInterval);
        updateProgress(tabId, progress);
    }, 200);

    var formData = new FormData();
    formData.append('excel_file', file);
    formData.append('confirm', '1');

    fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        clearInterval(progressInterval);
        updateProgress(tabId, 100);
        setTimeout(function() {
            $('#progress-' + tabId).hide();
            handleConfirmResponse(tabId, data);
        }, 500);
    })
    .catch(function(error) {
        clearInterval(progressInterval);
        $('#progress-' + tabId).hide();
        $('#status-' + tabId).html('<h4><i class="ti ti-alert-circle me-2 text-red"></i>Errore durante l\\'import</h4>');
        $('#errors-' + tabId).html(
            '<div class="alert alert-danger">' +
            '<i class="ti ti-alert-circle me-2"></i><strong>Errore di connessione:</strong> ' + error.message +
            '</div>'
        );
        $('#footer-' + tabId).show();
        $('#btn-upload-' + tabId).prop('disabled', false);
    });
}

// ==========================================
// Progress bar
// ==========================================
function updateProgress(tabId, percent) {
    $('#progressbar-' + tabId).css('width', percent + '%');
    $('#progresstext-' + tabId).text(percent + '%');
}

// ==========================================
// Response handler: Import Completo (Tab 1)
// ==========================================
function handleResponseCompleto(data, isDryRun) {
    var tabId = 'completo';
    console.log('Risposta server (completo):', data);

    if (data.success) {
        $('#status-' + tabId).html('<h4><i class="ti ti-check me-2 text-green"></i>' +
            (isDryRun ? 'Validazione completata con successo' : 'Import completato con successo') + '</h4>');

        renderLog(tabId, data.log);
        renderWarnings(tabId, data.warnings);
        renderStats(tabId, data.stats);

        if (isDryRun) {
            $('#btn-confirm-' + tabId).show();
        }
    } else {
        $('#status-' + tabId).html('<h4><i class="ti ti-alert-circle me-2 text-red"></i>Errori di validazione</h4>');
        renderErrors(tabId, data);
        renderWarnings(tabId, data.warnings);
    }

    $('#footer-' + tabId).show();
    $('#btn-upload-' + tabId).prop('disabled', false);
}

// ==========================================
// Response handler: Preview (Tab 2 & 3)
// ==========================================
function handlePreviewResponse(tabId, data) {
    console.log('Risposta server (preview ' + tabId + '):', data);

    if (data.success) {
        $('#status-' + tabId).html('<h4><i class="ti ti-check me-2 text-green"></i>Validazione completata</h4>');

        // Render preview table
        if (data.preview && data.preview.length > 0) {
            var html = '<div class="table-responsive"><table class="table table-vcenter card-table">';

            if (tabId === 'assegnazioni') {
                html += '<thead><tr>' +
                    '<th>Riga</th><th>Modulo</th><th>Docente</th><th>Stato</th><th>Azione</th>' +
                    '</tr></thead><tbody>';
                data.preview.forEach(function(row) {
                    html += '<tr>' +
                        '<td>' + (row.riga || '') + '</td>' +
                        '<td>' + (row.modulo || '') + '</td>' +
                        '<td>' + (row.docente || '') + '</td>' +
                        '<td>' + getBadge(row.stato) + '</td>' +
                        '<td>' + getActionBadge(row.azione) + '</td>' +
                        '</tr>';
                });
            } else if (tabId === 'calendari') {
                html += '<thead><tr>' +
                    '<th>Riga</th><th>Modulo</th><th>Docente</th><th>Data</th><th>Orario</th><th>Ore</th><th>Azione</th>' +
                    '</tr></thead><tbody>';
                data.preview.forEach(function(row) {
                    html += '<tr>' +
                        '<td>' + (row.riga || '') + '</td>' +
                        '<td>' + (row.modulo || '') + '</td>' +
                        '<td>' + (row.docente || '') + '</td>' +
                        '<td>' + (row.data || '') + '</td>' +
                        '<td>' + (row.orario || '') + '</td>' +
                        '<td>' + (row.ore || '') + '</td>' +
                        '<td>' + getActionBadge(row.azione) + '</td>' +
                        '</tr>';
                });
            }

            html += '</tbody></table></div>';
            $('#preview-' + tabId).html(html);
        }

        // Statistiche riepilogative
        if (data.summary) {
            var summaryHtml = '<div class="row g-2 mt-2">';
            if (data.summary.nuovi !== undefined) {
                summaryHtml += statCard('Nuovi', data.summary.nuovi, 'bg-green-lt');
            }
            if (data.summary.esistenti !== undefined) {
                summaryHtml += statCard('Esistenti', data.summary.esistenti, 'bg-azure-lt');
            }
            if (data.summary.saltati !== undefined) {
                summaryHtml += statCard('Saltati', data.summary.saltati, 'bg-orange-lt');
            }
            if (data.summary.errori !== undefined) {
                summaryHtml += statCard('Errori', data.summary.errori, 'bg-red-lt');
            }
            summaryHtml += '</div>';
            $('#stats-' + tabId).html(summaryHtml);
        }

        renderWarnings(tabId, data.warnings);

        // Mostra conferma solo se non ci sono errori bloccanti
        if (!data.summary || !data.summary.errori || data.summary.errori === 0) {
            $('#btn-confirm-' + tabId).show();
        }
    } else {
        $('#status-' + tabId).html('<h4><i class="ti ti-alert-circle me-2 text-red"></i>Errori di validazione</h4>');
        renderErrors(tabId, data);
        renderWarnings(tabId, data.warnings);
    }

    $('#footer-' + tabId).show();
    $('#btn-upload-' + tabId).prop('disabled', false);
}

// ==========================================
// Response handler: Confirm (Tab 2 & 3)
// ==========================================
function handleConfirmResponse(tabId, data) {
    console.log('Risposta server (confirm ' + tabId + '):', data);

    if (data.success) {
        $('#status-' + tabId).html('<h4><i class="ti ti-check me-2 text-green"></i>Import completato con successo</h4>');
        renderLog(tabId, data.log);
        renderWarnings(tabId, data.warnings);
        renderStats(tabId, data.stats);
    } else {
        $('#status-' + tabId).html('<h4><i class="ti ti-alert-circle me-2 text-red"></i>Errore durante l\\'import</h4>');
        renderErrors(tabId, data);
        renderWarnings(tabId, data.warnings);
    }

    $('#footer-' + tabId).show();
    $('#btn-confirm-' + tabId).hide();
    $('#btn-upload-' + tabId).prop('disabled', false);
}

// ==========================================
// Render helpers
// ==========================================
function renderLog(tabId, log) {
    if (log && log.length > 0) {
        var html = '<div class="alert alert-success"><h4>Operazioni eseguite:</h4><ul class="mb-0">';
        log.forEach(function(msg) { html += '<li>' + msg + '</li>'; });
        html += '</ul></div>';
        $('#log-' + tabId).html(html);
    }
}

function renderWarnings(tabId, warnings) {
    if (warnings && warnings.length > 0) {
        var html = '<div class="alert alert-warning"><h4>Avvisi:</h4><ul class="mb-0">';
        warnings.forEach(function(msg) { html += '<li>' + msg + '</li>'; });
        html += '</ul></div>';
        $('#warnings-' + tabId).html(html);
    }
}

function renderErrors(tabId, data) {
    var hasErrors = false;
    if (data.errors && data.errors.length > 0) {
        var html = '<div class="alert alert-danger"><h4>Errori trovati:</h4><ul class="mb-0">';
        data.errors.forEach(function(msg) { html += '<li>' + msg + '</li>'; });
        html += '</ul></div>';
        $('#errors-' + tabId).html(html);
        hasErrors = true;
    }
    if (data.error) {
        $('#errors-' + tabId).html(
            '<div class="alert alert-danger"><i class="ti ti-alert-circle me-2"></i><strong>Errore:</strong> ' + data.error + '</div>'
        );
        hasErrors = true;
    }
    if (!hasErrors) {
        $('#errors-' + tabId).html(
            '<div class="alert alert-warning"><i class="ti ti-alert-triangle me-2"></i>' +
            '<strong>Errore sconosciuto</strong><br>Nessun dettaglio disponibile. Controlla la console del browser (F12).</div>'
        );
        console.error('Risposta errore senza dettagli:', data);
    }
}

function renderStats(tabId, stats) {
    if (stats) {
        var html = '<div class="row g-2">';
        for (var key in stats) {
            html += '<div class="col-md-3"><div class="card card-sm"><div class="card-body">' +
                '<div class="row align-items-center">' +
                '<div class="col-auto"><span class="bg-blue text-blue-fg avatar">' + stats[key] + '</span></div>' +
                '<div class="col"><div class="font-weight-medium">' + key + '</div></div>' +
                '</div></div></div></div>';
        }
        html += '</div>';
        $('#stats-' + tabId).html(html);
    }
}

function statCard(label, value, cssClass) {
    return '<div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body d-flex align-items-center">' +
        '<span class="badge ' + cssClass + ' me-2" style="font-size:1.1em;padding:0.5em 0.7em;">' + value + '</span>' +
        '<span>' + label + '</span>' +
        '</div></div></div>';
}

function getBadge(stato) {
    if (!stato) return '';
    var s = stato.toLowerCase();
    if (s === 'nuovo') return '<span class="badge bg-green-lt">Nuovo</span>';
    if (s === 'esistente') return '<span class="badge bg-azure-lt">Esistente</span>';
    if (s === 'errore') return '<span class="badge bg-red-lt">Errore</span>';
    return '<span class="badge bg-default-lt">' + stato + '</span>';
}

function getActionBadge(azione) {
    if (!azione) return '';
    var a = azione.toLowerCase();
    if (a === 'crea') return '<span class="badge bg-green-lt">CREA</span>';
    if (a === 'salta') return '<span class="badge bg-orange-lt">SALTA</span>';
    if (a === 'errore') return '<span class="badge bg-red-lt">ERRORE</span>';
    return '<span class="badge bg-default-lt">' + azione + '</span>';
}

JS
);
?>
