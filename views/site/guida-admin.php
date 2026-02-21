<?php
/**
 * Guida Interattiva Amministratore - 5 Step
 * Pagina dedicata con steps Tabler navigabili
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Guida Interattiva';
?>

<div class="container-xl py-4">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="<?= Url::to(['/site/index']) ?>" class="btn btn-outline-primary">
                    <i class="ti ti-arrow-left me-1"></i> Torna alla Dashboard
                </a>
            </div>
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-compass me-2"></i> Guida Albatros Manager
                </h2>
                <div class="text-secondary">Scopri tutte le funzionalita del sistema</div>
            </div>
        </div>
    </div>

    <!-- Steps Progress -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="steps steps-blue steps-counter" id="wizard-admin-steps">
                <a href="#" class="step-item active" data-step="1">Benvenuto</a>
                <a href="#" class="step-item" data-step="2">Docenti</a>
                <a href="#" class="step-item" data-step="3">Progetti</a>
                <a href="#" class="step-item" data-step="4">Contratti</a>
                <a href="#" class="step-item" data-step="5">Report</a>
            </div>
        </div>
    </div>

    <!-- Step 1: Benvenuto -->
    <div class="wizard-admin-panel" id="step-admin-1">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="ti ti-home" style="font-size: 3rem; color: var(--tblr-blue);"></i>
                    <h2 class="mt-3 mb-2">Benvenuto in Albatros Manager!</h2>
                    <p class="text-secondary" style="max-width: 600px; margin: auto;">
                        Questo sistema ti permette di gestire docenti, progetti, contratti e pagamenti per programmi FSE, PNRR e MIM.
                    </p>
                </div>
                <div class="row g-3 justify-content-center">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card card-sm">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-users mb-2" style="font-size: 1.5rem; color: var(--tblr-azure);"></i>
                                <div class="fw-medium small">Docenti</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card card-sm">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-folder mb-2" style="font-size: 1.5rem; color: var(--tblr-purple);"></i>
                                <div class="fw-medium small">Progetti</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card card-sm">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-file-invoice mb-2" style="font-size: 1.5rem; color: var(--tblr-green);"></i>
                                <div class="fw-medium small">Contratti</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card card-sm">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-currency-euro mb-2" style="font-size: 1.5rem; color: var(--tblr-orange);"></i>
                                <div class="fw-medium small">Pagamenti</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card card-sm">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-chart-bar mb-2" style="font-size: 1.5rem; color: var(--tblr-red);"></i>
                                <div class="fw-medium small">Report</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card card-sm">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-upload mb-2" style="font-size: 1.5rem; color: var(--tblr-teal);"></i>
                                <div class="fw-medium small">Import</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Gestione Docenti -->
    <div class="wizard-admin-panel" id="step-admin-2" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="ti ti-users" style="font-size: 3rem; color: var(--tblr-azure);"></i>
                    <h2 class="mt-3 mb-2">Gestione Docenti</h2>
                    <p class="text-secondary">
                        La sezione Docenti ti permette di gestire la long list, filtrare per competenze, verificare valutazioni e monitorare gli incarichi attivi.
                    </p>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-sm bg-azure-lt me-3"><i class="ti ti-tags"></i></span>
                            <div>
                                <div class="fw-medium">Ricerca per Tag</div>
                                <div class="text-secondary small">Filtra i docenti per competenze specifiche usando il sistema di tag curriculum</div>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-sm bg-orange-lt me-3"><i class="ti ti-alert-triangle"></i></span>
                            <div>
                                <div class="fw-medium">Alert Sovra-Impegnato</div>
                                <div class="text-secondary small">Il sistema segnala automaticamente i docenti con troppi incarichi attivi</div>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-sm bg-green-lt me-3"><i class="ti ti-history"></i></span>
                            <div>
                                <div class="fw-medium">Storico Esiti CTS</div>
                                <div class="text-secondary small">Consulta l'archivio completo delle valutazioni della Commissione Tecnico Scientifica</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Progetti e Bandi -->
    <div class="wizard-admin-panel" id="step-admin-3" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="ti ti-briefcase" style="font-size: 3rem; color: var(--tblr-purple);"></i>
                    <h2 class="mt-3 mb-2">Progetti e Bandi</h2>
                    <p class="text-secondary">
                        Gestisci l'intero ciclo: crea progetti, pubblica bandi, definisci moduli e requisiti.
                    </p>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="steps steps-green steps-counter mb-0">
                            <span class="step-item">Progetto</span>
                            <span class="step-item">Avviso/Bando</span>
                            <span class="step-item">Moduli</span>
                            <span class="step-item">Assegnazione</span>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    <div class="d-flex">
                        <i class="ti ti-info-circle me-2" style="font-size: 1.25rem;"></i>
                        <div>
                            <h4 class="alert-title">Flusso operativo</h4>
                            <ul class="mb-0 ps-3 small">
                                <li>Crea un <strong>Progetto</strong> associato a una fonte di finanziamento</li>
                                <li>Pubblica un <strong>Avviso/Bando</strong> per la selezione docenti</li>
                                <li>Definisci i <strong>Moduli</strong> formativi con ore e compensi</li>
                                <li>Il CTS valuta le candidature e genera le <strong>Assegnazioni</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Contratti e Pagamenti -->
    <div class="wizard-admin-panel" id="step-admin-4" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="ti ti-file-invoice" style="font-size: 3rem; color: var(--tblr-green);"></i>
                    <h2 class="mt-3 mb-2">Contratti e Pagamenti</h2>
                    <p class="text-secondary">
                        Contrattualizza i docenti, gestisci i pagamenti singoli e la liquidazione massiva.
                    </p>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="avatar avatar-sm bg-blue-lt me-2"><i class="ti ti-file-text"></i></span>
                                    <h4 class="m-0">Tipo Contratto</h4>
                                </div>
                                <p class="text-secondary small mb-0">
                                    Supporta contratti CoCoCo e Partita IVA con calcolo automatico delle ritenute.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="avatar avatar-sm bg-green-lt me-2"><i class="ti ti-cash"></i></span>
                                    <h4 class="m-0">Liquidazione Massiva</h4>
                                </div>
                                <p class="text-secondary small mb-0">
                                    Crea pagamenti in blocco per tutti i contratti con saldo residuo in un click.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="avatar avatar-sm bg-orange-lt me-2"><i class="ti ti-calculator"></i></span>
                                    <h4 class="m-0">Ritenute Automatiche</h4>
                                </div>
                                <p class="text-secondary small mb-0">
                                    Calcolo proporzionale di ritenute d'acconto, INPS e IRAP per ogni tipo di contratto.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="avatar avatar-sm bg-purple-lt me-2"><i class="ti ti-clock"></i></span>
                                    <h4 class="m-0">Ore Extra</h4>
                                </div>
                                <p class="text-secondary small mb-0">
                                    Workflow di approvazione per ore eccedenti il contratto con doppia validazione.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 5: Report e Strumenti -->
    <div class="wizard-admin-panel" id="step-admin-5" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="ti ti-chart-bar" style="font-size: 3rem; color: var(--tblr-red);"></i>
                    <h2 class="mt-3 mb-2">Report e Strumenti</h2>
                    <p class="text-secondary">
                        Accedi a statistiche, export dati, audit log e strumenti di import.
                    </p>
                </div>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="<?= Url::to(['/report/statistiche']) ?>" class="card card-link card-link-pop text-reset">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-chart-pie mb-2" style="font-size: 2rem; color: var(--tblr-blue);"></i>
                                <div class="fw-medium">Statistiche</div>
                                <div class="text-secondary small">Panoramica completa</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= Url::to(['/report/export']) ?>" class="card card-link card-link-pop text-reset">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-download mb-2" style="font-size: 2rem; color: var(--tblr-green);"></i>
                                <div class="fw-medium">Export Dati</div>
                                <div class="text-secondary small">Scarica in Excel</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= Url::to(['/audit-log/index']) ?>" class="card card-link card-link-pop text-reset">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-activity mb-2" style="font-size: 2rem; color: var(--tblr-orange);"></i>
                                <div class="fw-medium">Audit Log</div>
                                <div class="text-secondary small">Traccia modifiche</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= Url::to(['/import/index']) ?>" class="card card-link card-link-pop text-reset">
                            <div class="card-body text-center py-3">
                                <i class="ti ti-upload mb-2" style="font-size: 2rem; color: var(--tblr-purple);"></i>
                                <div class="fw-medium">Import Excel</div>
                                <div class="text-secondary small">Importa dati</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigazione -->
    <div class="d-flex justify-content-between mt-4">
        <button type="button" class="btn" id="wizard-admin-prev" style="visibility: hidden;">
            <i class="ti ti-arrow-left me-1"></i> Indietro
        </button>
        <div>
            <button type="button" class="btn btn-primary" id="wizard-admin-next">
                Avanti <i class="ti ti-arrow-right ms-1"></i>
            </button>
            <a href="<?= Url::to(['/site/index']) ?>" class="btn bg-green text-green-fg" id="wizard-admin-finish" style="display: none;">
                <i class="ti ti-check me-1"></i> Torna alla Dashboard
            </a>
        </div>
    </div>
</div>

<style>
.wizard-admin-panel {
    animation: wizardFadeIn 0.3s ease-in-out;
}
@keyframes wizardFadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
#wizard-admin-steps .step-item {
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var currentStep = 1;
    var totalSteps = 5;

    function updateWizard() {
        for (var i = 1; i <= totalSteps; i++) {
            var panel = document.getElementById('step-admin-' + i);
            if (panel) {
                panel.style.display = i === currentStep ? 'block' : 'none';
                if (i === currentStep) {
                    panel.style.animation = 'none';
                    panel.offsetHeight;
                    panel.style.animation = 'wizardFadeIn 0.3s ease-in-out';
                }
            }
        }

        var stepItems = document.querySelectorAll('#wizard-admin-steps .step-item');
        stepItems.forEach(function(item, index) {
            item.classList.remove('active');
            if (index + 1 <= currentStep) {
                item.classList.add('active');
            }
        });

        document.getElementById('wizard-admin-prev').style.visibility = currentStep > 1 ? 'visible' : 'hidden';
        document.getElementById('wizard-admin-next').style.display = currentStep < totalSteps ? 'inline-block' : 'none';
        document.getElementById('wizard-admin-finish').style.display = currentStep === totalSteps ? 'inline-block' : 'none';
    }

    document.getElementById('wizard-admin-next').addEventListener('click', function() {
        if (currentStep < totalSteps) {
            currentStep++;
            updateWizard();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.getElementById('wizard-admin-prev').addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            updateWizard();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    var stepItems = document.querySelectorAll('#wizard-admin-steps .step-item');
    stepItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            var step = parseInt(this.getAttribute('data-step'));
            if (step && step >= 1 && step <= totalSteps) {
                currentStep = step;
                updateWizard();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
});
</script>
