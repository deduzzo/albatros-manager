<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Importa Valutazioni CSV';
$this->params['breadcrumbs'][] = ['label' => 'Valutazioni Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="valutazione-docente-import">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-upload me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Importa valutazioni docenti da file CSV</div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alle Valutazioni', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-8">
                    <form method="post" action="<?= Url::to(['import-process']) ?>" enctype="multipart/form-data" id="import-form">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="ti ti-file-spreadsheet me-2"></i>Carica file CSV</h3>
                            </div>
                            <div class="card-body">
                                <div id="drop-zone" class="border border-2 border-dashed rounded-3 p-5 text-center" style="cursor: pointer; transition: all 0.3s ease;">
                                    <div id="drop-icon">
                                        <i class="ti ti-cloud-upload" style="font-size: 3rem; color: var(--tblr-blue);"></i>
                                    </div>
                                    <h3 class="mt-3">Trascina qui il file CSV</h3>
                                    <p class="text-muted mb-3">oppure clicca per selezionare il file</p>
                                    <input type="file" name="csv_file" id="csv-file" accept=".csv,.txt" class="d-none">
                                    <div id="file-info" class="d-none">
                                        <div class="alert alert-info d-inline-flex align-items-center gap-2 mb-0">
                                            <i class="ti ti-file-check"></i>
                                            <span id="file-name"></span>
                                            <span id="file-size" class="text-muted"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
                                <button type="submit" class="btn btn-success" id="btn-import" disabled>
                                    <i class="ti ti-upload me-1"></i> Importa Valutazioni
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Formato CSV</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Il file CSV deve avere le seguenti colonne:</p>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <span class="badge bg-blue-lt me-1">Obbligatoria</span>
                                    <strong>Email</strong> - Email studente
                                </li>
                                <li class="mb-2">
                                    <span class="badge bg-blue-lt me-1">Obbligatoria</span>
                                    <strong>Nome e Cognome</strong> - Studente
                                </li>
                                <li class="mb-2">
                                    <span class="badge bg-blue-lt me-1">Obbligatoria</span>
                                    <strong>Docente</strong> - Nome docente
                                </li>
                                <li class="mb-2">
                                    <span class="badge bg-green-lt me-1">Criteri</span>
                                    15 colonne con punteggi 1-10
                                </li>
                                <li class="mb-2">
                                    <span class="badge bg-default-lt me-1">Opzionale</span>
                                    <strong>Note</strong>
                                </li>
                                <li class="mb-2">
                                    <span class="badge bg-default-lt me-1">Opzionale</span>
                                    <strong>Submission Date</strong>
                                </li>
                                <li class="mb-2">
                                    <span class="badge bg-default-lt me-1">Opzionale</span>
                                    <strong>URL</strong> - Sorgente
                                </li>
                            </ul>
                            <div class="alert alert-warning mt-3">
                                <i class="ti ti-alert-triangle me-1"></i>
                                I nomi dei docenti saranno matchati automaticamente con quelli presenti nel sistema. In caso di mancato match, potrai assegnarli manualmente.
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
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('csv-file');
const fileInfo = document.getElementById('file-info');
const fileName = document.getElementById('file-name');
const fileSize = document.getElementById('file-size');
const btnImport = document.getElementById('btn-import');

dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = 'var(--tblr-blue)';
    dropZone.style.backgroundColor = 'rgba(32, 107, 196, 0.05)';
});

dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '';
    dropZone.style.backgroundColor = '';
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '';
    dropZone.style.backgroundColor = '';
    if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        updateFileInfo(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        updateFileInfo(e.target.files[0]);
    }
});

function updateFileInfo(file) {
    fileName.textContent = file.name;
    const sizeKB = (file.size / 1024).toFixed(1);
    fileSize.textContent = '(' + sizeKB + ' KB)';
    fileInfo.classList.remove('d-none');
    btnImport.disabled = false;
}

document.getElementById('import-form').addEventListener('submit', function() {
    btnImport.disabled = true;
    btnImport.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importazione in corso...';
});
JS;
$this->registerJs($js);
?>
