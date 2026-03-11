<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Verifica Firma Digitale';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="firma-digitale-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-certificate me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Verifica la validita' di firme digitali P7M (CAdES) e PDF firmati (PAdES)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-upload me-2"></i>
                                Carica file da verificare
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="alert alert-info">
                                    <div class="d-flex">
                                        <i class="ti ti-info-circle me-2" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <h4 class="alert-title">Formati supportati</h4>
                                            <ul class="mb-0">
                                                <li><strong>.p7m</strong> - Firma CAdES (busta crittografica PKCS#7)</li>
                                                <li><strong>.pdf</strong> - Firma PAdES (PDF con firma digitale integrata)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?= Html::beginForm(['verifica'], 'post', ['enctype' => 'multipart/form-data', 'id' => 'form-verifica']) ?>
                                <div class="mb-4">
                                    <label class="form-label" for="file_firma">Seleziona il file da verificare</label>
                                    <div class="upload-area border border-2 border-dashed rounded-3 p-5 text-center" id="upload-area" style="cursor: pointer; transition: all 0.3s ease;">
                                        <i class="ti ti-cloud-upload" style="font-size: 3rem;" id="upload-icon"></i>
                                        <p class="mt-2 mb-1" id="upload-text">Trascina qui il file oppure clicca per selezionarlo</p>
                                        <p class="text-muted small" id="upload-hint">Formati accettati: .p7m, .pdf</p>
                                        <input type="file" name="file_firma" id="file_firma" class="d-none" accept=".p7m,.pdf" required>
                                    </div>
                                    <div id="file-info" class="mt-2 d-none">
                                        <span class="badge bg-azure-lt" id="file-name"></span>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg" id="btn-verifica" disabled>
                                        <i class="ti ti-shield-check me-1"></i>
                                        Verifica Firma
                                    </button>
                                </div>
                            <?= Html::endForm() ?>
                        </div>
                    </div>

                    <!-- Informazioni -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-info-circle me-2"></i>
                                Informazioni sulla verifica
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Firma CAdES (P7M)</h4>
                                    <p class="text-muted">
                                        La firma CAdES crea un file .p7m che contiene il documento originale
                                        e la firma digitale in un'unica busta crittografica. La verifica
                                        estrae il certificato, controlla la validita' della firma e
                                        restituisce il documento originale.
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h4>Firma PAdES (PDF)</h4>
                                    <p class="text-muted">
                                        La firma PAdES integra la firma digitale direttamente nel documento
                                        PDF. Il file mantiene il formato .pdf e puo' essere visualizzato
                                        normalmente. La verifica richiede gli strumenti poppler-utils
                                        per un'analisi completa.
                                    </p>
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
$js = <<<JS
// Drag & drop e selezione file
var uploadArea = document.getElementById('upload-area');
var fileInput = document.getElementById('file_firma');
var btnVerifica = document.getElementById('btn-verifica');
var fileInfo = document.getElementById('file-info');
var fileName = document.getElementById('file-name');
var uploadText = document.getElementById('upload-text');
var uploadIcon = document.getElementById('upload-icon');

uploadArea.addEventListener('click', function() {
    fileInput.click();
});

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('border-primary');
    uploadArea.style.backgroundColor = 'rgba(32, 107, 196, 0.05)';
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-primary');
    uploadArea.style.backgroundColor = '';
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-primary');
    uploadArea.style.backgroundColor = '';
    if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        updateFileInfo(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', function() {
    if (fileInput.files.length > 0) {
        updateFileInfo(fileInput.files[0]);
    }
});

function updateFileInfo(file) {
    var size = (file.size / 1024).toFixed(1);
    if (size > 1024) {
        size = (size / 1024).toFixed(1) + ' MB';
    } else {
        size = size + ' KB';
    }
    fileName.textContent = file.name + ' (' + size + ')';
    fileInfo.classList.remove('d-none');
    btnVerifica.disabled = false;
    uploadText.textContent = 'File selezionato:';
    uploadIcon.className = 'ti ti-file-check';
    uploadArea.style.borderColor = '#2fb344';
}
JS;
$this->registerJs($js);
?>
