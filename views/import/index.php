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
                        <li class="nav-item" role="presentation">
                            <a href="#tab-studenti" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-school me-1"></i>Studenti
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-presenze" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-clipboard-check me-1"></i>Presenze
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-borse" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-award me-1"></i>Borse Studio
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-rendicontazione" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-clock-hour-4 me-1"></i>Ore Docenti
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-movimenti" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-receipt me-1"></i>Movimenti Spesa
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-tranche" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-cash me-1"></i>Tranche
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-pagamenti" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-credit-card me-1"></i>Pagamenti
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

                        <!-- TAB 4: Import Studenti -->
                        <div class="tab-pane" id="tab-studenti" role="tabpanel">
                            <div class="card bg-azure-lt mb-3"><div class="card-body">
                                <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Import Studenti + Iscrizioni</h4>
                                <ol class="mb-0">
                                    <li class="mb-1">Scarica il template e compila il foglio STUDENTI</li>
                                    <li class="mb-1">CF_STUDENTE (16 car.), COGNOME e NOME sono obbligatori</li>
                                    <li class="mb-1">Se CODICE_MODULO è fornito, verrà creata un'iscrizione</li>
                                    <li>Studenti esistenti (stesso CF) verranno aggiornati</li>
                                </ol>
                            </div></div>
                            <div class="mb-3"><?= Html::a('<i class="ti ti-download me-1"></i> Scarica Template Studenti', ['download-template-studenti'], ['class' => 'btn btn-outline-primary']) ?></div>
                            <div class="mb-3"><label class="form-label required">File Excel (.xlsx)</label><input type="file" id="file-studenti" class="form-control" accept=".xlsx"><div class="form-hint">Dimensione massima: 10 MB</div></div>
                            <button type="button" class="btn btn-primary" id="btn-upload-studenti"><i class="ti ti-search me-1"></i>Valida File</button>
                            <div id="results-studenti" class="mt-3" style="display:none;">
                                <div id="progress-studenti" style="display:none;"><div class="progress mb-3"><div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-studenti" style="width:0%"><span id="progresstext-studenti">0%</span></div></div></div>
                                <div id="status-studenti"></div><div id="preview-studenti"></div><div id="errors-studenti"></div><div id="warnings-studenti"></div><div id="stats-studenti" class="mt-3"></div>
                                <div id="footer-studenti" class="mt-3" style="display:none;"><button type="button" class="btn bg-green text-green-fg" id="btn-confirm-studenti" style="display:none;"><i class="ti ti-check me-1"></i>Conferma Import</button> <button type="button" class="btn btn-primary" onclick="location.reload()"><i class="ti ti-refresh me-1"></i>Nuova Importazione</button></div>
                            </div>
                        </div>

                        <!-- TAB 5: Import Presenze -->
                        <div class="tab-pane" id="tab-presenze" role="tabpanel">
                            <div class="card bg-azure-lt mb-3"><div class="card-body">
                                <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Import Presenze Allievi</h4>
                                <ol class="mb-0">
                                    <li class="mb-1">CODICE_MODULO + DATA_LEZIONE devono corrispondere a una lezione nel calendario</li>
                                    <li class="mb-1">CF_STUDENTE deve essere uno studente esistente</li>
                                    <li>Presenze duplicate (stesso studente + lezione) verranno saltate</li>
                                </ol>
                            </div></div>
                            <div class="mb-3"><?= Html::a('<i class="ti ti-download me-1"></i> Scarica Template Presenze', ['download-template-presenze'], ['class' => 'btn btn-outline-primary']) ?></div>
                            <div class="mb-3"><label class="form-label required">File Excel (.xlsx)</label><input type="file" id="file-presenze" class="form-control" accept=".xlsx"><div class="form-hint">Dimensione massima: 10 MB</div></div>
                            <button type="button" class="btn btn-primary" id="btn-upload-presenze"><i class="ti ti-search me-1"></i>Valida File</button>
                            <div id="results-presenze" class="mt-3" style="display:none;">
                                <div id="progress-presenze" style="display:none;"><div class="progress mb-3"><div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-presenze" style="width:0%"><span id="progresstext-presenze">0%</span></div></div></div>
                                <div id="status-presenze"></div><div id="preview-presenze"></div><div id="errors-presenze"></div><div id="warnings-presenze"></div><div id="stats-presenze" class="mt-3"></div>
                                <div id="footer-presenze" class="mt-3" style="display:none;"><button type="button" class="btn bg-green text-green-fg" id="btn-confirm-presenze" style="display:none;"><i class="ti ti-check me-1"></i>Conferma Import</button> <button type="button" class="btn btn-primary" onclick="location.reload()"><i class="ti ti-refresh me-1"></i>Nuova Importazione</button></div>
                            </div>
                        </div>

                        <!-- TAB 6: Import Borse di Studio -->
                        <div class="tab-pane" id="tab-borse" role="tabpanel">
                            <div class="card bg-azure-lt mb-3"><div class="card-body">
                                <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Import Borse di Studio</h4>
                                <ol class="mb-0">
                                    <li class="mb-1">CF_STUDENTE e CODICE_PROGETTO devono esistere nel sistema</li>
                                    <li class="mb-1">L'importo base è calcolato automaticamente dalla tipologia sede</li>
                                    <li>Borse duplicate (stesso studente + progetto + anno) verranno saltate</li>
                                </ol>
                            </div></div>
                            <div class="mb-3"><?= Html::a('<i class="ti ti-download me-1"></i> Scarica Template Borse', ['download-template-borse'], ['class' => 'btn btn-outline-primary']) ?></div>
                            <div class="mb-3"><label class="form-label required">File Excel (.xlsx)</label><input type="file" id="file-borse" class="form-control" accept=".xlsx"><div class="form-hint">Dimensione massima: 10 MB</div></div>
                            <button type="button" class="btn btn-primary" id="btn-upload-borse"><i class="ti ti-search me-1"></i>Valida File</button>
                            <div id="results-borse" class="mt-3" style="display:none;">
                                <div id="progress-borse" style="display:none;"><div class="progress mb-3"><div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-borse" style="width:0%"><span id="progresstext-borse">0%</span></div></div></div>
                                <div id="status-borse"></div><div id="preview-borse"></div><div id="errors-borse"></div><div id="warnings-borse"></div><div id="stats-borse" class="mt-3"></div>
                                <div id="footer-borse" class="mt-3" style="display:none;"><button type="button" class="btn bg-green text-green-fg" id="btn-confirm-borse" style="display:none;"><i class="ti ti-check me-1"></i>Conferma Import</button> <button type="button" class="btn btn-primary" onclick="location.reload()"><i class="ti ti-refresh me-1"></i>Nuova Importazione</button></div>
                            </div>
                        </div>

                        <!-- TAB 7: Import Rendicontazione Ore -->
                        <div class="tab-pane" id="tab-rendicontazione" role="tabpanel">
                            <div class="card bg-azure-lt mb-3"><div class="card-body">
                                <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Import Rendicontazione Ore</h4>
                                <ol class="mb-0">
                                    <li class="mb-1">Il docente deve avere un'assegnazione attiva al modulo</li>
                                    <li class="mb-1">ORE minimo 0.25, STATUS: inserito (default), approvato, rifiutato</li>
                                    <li>Duplicati (stessa assegnazione + data) verranno saltati</li>
                                </ol>
                            </div></div>
                            <div class="mb-3"><?= Html::a('<i class="ti ti-download me-1"></i> Scarica Template Ore', ['download-template-rendicontazione'], ['class' => 'btn btn-outline-primary']) ?></div>
                            <div class="mb-3"><label class="form-label required">File Excel (.xlsx)</label><input type="file" id="file-rendicontazione" class="form-control" accept=".xlsx"><div class="form-hint">Dimensione massima: 10 MB</div></div>
                            <button type="button" class="btn btn-primary" id="btn-upload-rendicontazione"><i class="ti ti-search me-1"></i>Valida File</button>
                            <div id="results-rendicontazione" class="mt-3" style="display:none;">
                                <div id="progress-rendicontazione" style="display:none;"><div class="progress mb-3"><div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-rendicontazione" style="width:0%"><span id="progresstext-rendicontazione">0%</span></div></div></div>
                                <div id="status-rendicontazione"></div><div id="preview-rendicontazione"></div><div id="errors-rendicontazione"></div><div id="warnings-rendicontazione"></div><div id="stats-rendicontazione" class="mt-3"></div>
                                <div id="footer-rendicontazione" class="mt-3" style="display:none;"><button type="button" class="btn bg-green text-green-fg" id="btn-confirm-rendicontazione" style="display:none;"><i class="ti ti-check me-1"></i>Conferma Import</button> <button type="button" class="btn btn-primary" onclick="location.reload()"><i class="ti ti-refresh me-1"></i>Nuova Importazione</button></div>
                            </div>
                        </div>

                        <!-- TAB 8: Import Movimenti Spesa -->
                        <div class="tab-pane" id="tab-movimenti" role="tabpanel">
                            <div class="card bg-azure-lt mb-3"><div class="card-body">
                                <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Import Movimenti Spesa</h4>
                                <ol class="mb-0">
                                    <li class="mb-1">CODICE_VOCE_SPESA deve corrispondere a una voce di spesa esistente</li>
                                    <li class="mb-1">CODICE_PROGETTO è opzionale</li>
                                    <li>TIPO: spesa (default) o entrata</li>
                                </ol>
                            </div></div>
                            <div class="mb-3"><?= Html::a('<i class="ti ti-download me-1"></i> Scarica Template Movimenti', ['download-template-movimenti'], ['class' => 'btn btn-outline-primary']) ?></div>
                            <div class="mb-3"><label class="form-label required">File Excel (.xlsx)</label><input type="file" id="file-movimenti" class="form-control" accept=".xlsx"><div class="form-hint">Dimensione massima: 10 MB</div></div>
                            <button type="button" class="btn btn-primary" id="btn-upload-movimenti"><i class="ti ti-search me-1"></i>Valida File</button>
                            <div id="results-movimenti" class="mt-3" style="display:none;">
                                <div id="progress-movimenti" style="display:none;"><div class="progress mb-3"><div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-movimenti" style="width:0%"><span id="progresstext-movimenti">0%</span></div></div></div>
                                <div id="status-movimenti"></div><div id="preview-movimenti"></div><div id="errors-movimenti"></div><div id="warnings-movimenti"></div><div id="stats-movimenti" class="mt-3"></div>
                                <div id="footer-movimenti" class="mt-3" style="display:none;"><button type="button" class="btn bg-green text-green-fg" id="btn-confirm-movimenti" style="display:none;"><i class="ti ti-check me-1"></i>Conferma Import</button> <button type="button" class="btn btn-primary" onclick="location.reload()"><i class="ti ti-refresh me-1"></i>Nuova Importazione</button></div>
                            </div>
                        </div>

                        <!-- TAB 9: Import Tranche -->
                        <div class="tab-pane" id="tab-tranche" role="tabpanel">
                            <div class="card bg-azure-lt mb-3"><div class="card-body">
                                <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Import Tranche Finanziamento</h4>
                                <ol class="mb-0">
                                    <li class="mb-1">CODICE_PROGETTO deve corrispondere a un progetto esistente</li>
                                    <li>STATUS: prevista (default), accreditata, parziale</li>
                                </ol>
                            </div></div>
                            <div class="mb-3"><?= Html::a('<i class="ti ti-download me-1"></i> Scarica Template Tranche', ['download-template-tranche'], ['class' => 'btn btn-outline-primary']) ?></div>
                            <div class="mb-3"><label class="form-label required">File Excel (.xlsx)</label><input type="file" id="file-tranche" class="form-control" accept=".xlsx"><div class="form-hint">Dimensione massima: 10 MB</div></div>
                            <button type="button" class="btn btn-primary" id="btn-upload-tranche"><i class="ti ti-search me-1"></i>Valida File</button>
                            <div id="results-tranche" class="mt-3" style="display:none;">
                                <div id="progress-tranche" style="display:none;"><div class="progress mb-3"><div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-tranche" style="width:0%"><span id="progresstext-tranche">0%</span></div></div></div>
                                <div id="status-tranche"></div><div id="preview-tranche"></div><div id="errors-tranche"></div><div id="warnings-tranche"></div><div id="stats-tranche" class="mt-3"></div>
                                <div id="footer-tranche" class="mt-3" style="display:none;"><button type="button" class="btn bg-green text-green-fg" id="btn-confirm-tranche" style="display:none;"><i class="ti ti-check me-1"></i>Conferma Import</button> <button type="button" class="btn btn-primary" onclick="location.reload()"><i class="ti ti-refresh me-1"></i>Nuova Importazione</button></div>
                            </div>
                        </div>

                        <!-- TAB 10: Import Pagamenti -->
                        <div class="tab-pane" id="tab-pagamenti" role="tabpanel">
                            <div class="card bg-azure-lt mb-3"><div class="card-body">
                                <h4 class="mb-2"><i class="ti ti-info-circle me-1"></i>Import Pagamenti Storici</h4>
                                <ol class="mb-0">
                                    <li class="mb-1">Il docente deve avere un contratto attivo per il modulo indicato</li>
                                    <li class="mb-1">TIPO: acconto, saldo (default), rata, rimborso</li>
                                    <li>STATUS: programmato, in_attesa, pagato (default), annullato</li>
                                </ol>
                            </div></div>
                            <div class="mb-3"><?= Html::a('<i class="ti ti-download me-1"></i> Scarica Template Pagamenti', ['download-template-pagamenti'], ['class' => 'btn btn-outline-primary']) ?></div>
                            <div class="mb-3"><label class="form-label required">File Excel (.xlsx)</label><input type="file" id="file-pagamenti" class="form-control" accept=".xlsx"><div class="form-hint">Dimensione massima: 10 MB</div></div>
                            <button type="button" class="btn btn-primary" id="btn-upload-pagamenti"><i class="ti ti-search me-1"></i>Valida File</button>
                            <div id="results-pagamenti" class="mt-3" style="display:none;">
                                <div id="progress-pagamenti" style="display:none;"><div class="progress mb-3"><div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar-pagamenti" style="width:0%"><span id="progresstext-pagamenti">0%</span></div></div></div>
                                <div id="status-pagamenti"></div><div id="preview-pagamenti"></div><div id="errors-pagamenti"></div><div id="warnings-pagamenti"></div><div id="stats-pagamenti" class="mt-3"></div>
                                <div id="footer-pagamenti" class="mt-3" style="display:none;"><button type="button" class="btn bg-green text-green-fg" id="btn-confirm-pagamenti" style="display:none;"><i class="ti ti-check me-1"></i>Conferma Import</button> <button type="button" class="btn btn-primary" onclick="location.reload()"><i class="ti ti-refresh me-1"></i>Nuova Importazione</button></div>
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
$urlStudenti = \yii\helpers\Url::to(['import/upload-studenti']);
$urlPresenze = \yii\helpers\Url::to(['import/upload-presenze']);
$urlBorse = \yii\helpers\Url::to(['import/upload-borse']);
$urlRendicontazione = \yii\helpers\Url::to(['import/upload-rendicontazione']);
$urlMovimenti = \yii\helpers\Url::to(['import/upload-movimenti']);
$urlTranche = \yii\helpers\Url::to(['import/upload-tranche']);
$urlPagamenti = \yii\helpers\Url::to(['import/upload-pagamenti']);

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

// TAB 4: Studenti
$('#btn-upload-studenti').on('click', function() { submitImport('studenti', '$urlStudenti', true); });
$('#btn-confirm-studenti').on('click', function() { if (!confirm('Confermi l\\'import?')) return; confirmImport('studenti', '$urlStudenti'); });

// TAB 5: Presenze
$('#btn-upload-presenze').on('click', function() { submitImport('presenze', '$urlPresenze', true); });
$('#btn-confirm-presenze').on('click', function() { if (!confirm('Confermi l\\'import?')) return; confirmImport('presenze', '$urlPresenze'); });

// TAB 6: Borse di Studio
$('#btn-upload-borse').on('click', function() { submitImport('borse', '$urlBorse', true); });
$('#btn-confirm-borse').on('click', function() { if (!confirm('Confermi l\\'import?')) return; confirmImport('borse', '$urlBorse'); });

// TAB 7: Rendicontazione Ore
$('#btn-upload-rendicontazione').on('click', function() { submitImport('rendicontazione', '$urlRendicontazione', true); });
$('#btn-confirm-rendicontazione').on('click', function() { if (!confirm('Confermi l\\'import?')) return; confirmImport('rendicontazione', '$urlRendicontazione'); });

// TAB 8: Movimenti Spesa
$('#btn-upload-movimenti').on('click', function() { submitImport('movimenti', '$urlMovimenti', true); });
$('#btn-confirm-movimenti').on('click', function() { if (!confirm('Confermi l\\'import?')) return; confirmImport('movimenti', '$urlMovimenti'); });

// TAB 9: Tranche
$('#btn-upload-tranche').on('click', function() { submitImport('tranche', '$urlTranche', true); });
$('#btn-confirm-tranche').on('click', function() { if (!confirm('Confermi l\\'import?')) return; confirmImport('tranche', '$urlTranche'); });

// TAB 10: Pagamenti
$('#btn-upload-pagamenti').on('click', function() { submitImport('pagamenti', '$urlPagamenti', true); });
$('#btn-confirm-pagamenti').on('click', function() { if (!confirm('Confermi l\\'import?')) return; confirmImport('pagamenti', '$urlPagamenti'); });

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
            } else {
                // Preview generica per tutti gli altri import
                html += '<thead><tr><th>Riga</th><th>Stato</th><th>Dettagli</th></tr></thead><tbody>';
                data.preview.forEach(function(row) {
                    var stato = row.stato || row.azione || 'ok';
                    var dettagli = '';
                    if (row.azioni && row.azioni.length) {
                        dettagli = row.azioni.join('<br>');
                    } else if (row.azione) {
                        dettagli = row.azione;
                    }
                    // Mostra info chiave dalla riga
                    var info = [];
                    if (row.cf_studente) info.push('CF: ' + row.cf_studente);
                    if (row.cognome && row.nome) info.push(row.cognome + ' ' + row.nome);
                    if (row.studente_nome) info.push(row.studente_nome);
                    if (row.codice_modulo) info.push('Mod: ' + row.codice_modulo);
                    if (row.cf_docente) info.push('Doc: ' + row.cf_docente);
                    if (row.codice_progetto) info.push('Prg: ' + row.codice_progetto);
                    if (row.data_lezione) info.push(row.data_lezione);
                    if (row.data) info.push(row.data);
                    if (row.importo) info.push('€' + row.importo);
                    if (row.importo_lordo) info.push('Lordo: €' + row.importo_lordo);
                    if (row.ore) info.push(row.ore + 'h');
                    if (row.presente) info.push(row.presente === 'SI' ? 'Presente' : 'Assente');
                    var infoStr = info.length ? '<strong>' + info.join(' | ') + '</strong><br>' : '';
                    html += '<tr><td>' + (row.riga || '') + '</td><td>' + getBadge(stato) + '</td><td>' + infoStr + dettagli + '</td></tr>';
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
