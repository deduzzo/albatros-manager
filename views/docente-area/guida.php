<?php
/**
 * Guida Interattiva Docente - 4 Step
 * Pagina dedicata con steps Tabler navigabili
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Guida Area Docenti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <a href="<?= Url::to(['/docente-area/index']) ?>" class="btn btn-outline-primary">
                        <i class="ti ti-arrow-left me-1"></i> Torna alla Dashboard
                    </a>
                </div>
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-compass me-2"></i> Guida Area Docenti
                    </h2>
                    <div class="text-secondary">Scopri come usare la tua area riservata</div>
                </div>
            </div>
        </div>

        <!-- Steps Progress -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <div class="steps steps-blue steps-counter" id="wizard-docente-steps">
                    <a href="#" class="step-item active" data-step="1">Benvenuto</a>
                    <a href="#" class="step-item" data-step="2">Bandi</a>
                    <a href="#" class="step-item" data-step="3">Ore</a>
                    <a href="#" class="step-item" data-step="4">Pagamenti</a>
                </div>
            </div>
        </div>

        <!-- Step 1: Benvenuto -->
        <div class="wizard-docente-panel" id="step-docente-1">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="ti ti-school" style="font-size: 3rem; color: var(--tblr-blue);"></i>
                        <h2 class="mt-3 mb-2">Benvenuto nella tua area riservata!</h2>
                        <p class="text-secondary" style="max-width: 600px; margin: auto;">
                            Qui puoi gestire il tuo profilo, consultare i bandi aperti, candidarti ai moduli formativi e rendicontare le ore di lavoro.
                        </p>
                    </div>
                    <div class="row g-3 justify-content-center">
                        <div class="col-6 col-md-3">
                            <div class="card card-sm">
                                <div class="card-body text-center py-3">
                                    <i class="ti ti-clipboard-list mb-2" style="font-size: 1.5rem; color: var(--tblr-azure);"></i>
                                    <div class="fw-medium small">Bandi Aperti</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">Candidati ai moduli</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card card-sm">
                                <div class="card-body text-center py-3">
                                    <i class="ti ti-clock mb-2" style="font-size: 1.5rem; color: var(--tblr-green);"></i>
                                    <div class="fw-medium small">Le Mie Ore</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">Rendiconta giornaliero</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card card-sm">
                                <div class="card-body text-center py-3">
                                    <i class="ti ti-coin mb-2" style="font-size: 1.5rem; color: var(--tblr-orange);"></i>
                                    <div class="fw-medium small">Pagamenti</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">Stato compensi</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card card-sm">
                                <div class="card-body text-center py-3">
                                    <i class="ti ti-user mb-2" style="font-size: 1.5rem; color: var(--tblr-purple);"></i>
                                    <div class="fw-medium small">Profilo</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">I tuoi dati</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Bandi e Iscrizioni -->
        <div class="wizard-docente-panel" id="step-docente-2" style="display: none;">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="ti ti-clipboard-list" style="font-size: 3rem; color: var(--tblr-azure);"></i>
                        <h2 class="mt-3 mb-2">Bandi e Iscrizioni</h2>
                        <p class="text-secondary">
                            Consulta i bandi aperti e candidati ai moduli formativi che corrispondono alle tue competenze.
                        </p>
                    </div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-blue text-blue-fg me-3 p-2">1</span>
                                <div>
                                    <div class="fw-medium">Consulta i Bandi</div>
                                    <div class="text-secondary small">Nella sezione "Bandi Aperti" trovi tutti gli avvisi disponibili con i moduli formativi</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-blue text-blue-fg me-3 p-2">2</span>
                                <div>
                                    <div class="fw-medium">Scegli il Modulo</div>
                                    <div class="text-secondary small">Seleziona il modulo che corrisponde alle tue competenze e verifica i requisiti</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-blue text-blue-fg me-3 p-2">3</span>
                                <div>
                                    <div class="fw-medium">Candidati</div>
                                    <div class="text-secondary small">Compila il form di iscrizione con i tuoi tag competenze e carica i documenti richiesti</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-green text-green-fg me-3 p-2"><i class="ti ti-check"></i></span>
                                <div>
                                    <div class="fw-medium">Attendi l'Esito</div>
                                    <div class="text-secondary small">Il CTS valutera la tua candidatura. Segui lo stato in "Le Mie Iscrizioni"</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Assegnazioni e Ore -->
        <div class="wizard-docente-panel" id="step-docente-3" style="display: none;">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="ti ti-clock" style="font-size: 3rem; color: var(--tblr-green);"></i>
                        <h2 class="mt-3 mb-2">Assegnazioni e Ore</h2>
                        <p class="text-secondary">
                            Visualizza i moduli assegnati e inserisci le ore giornaliere di lavoro per la rendicontazione.
                        </p>
                    </div>
                    <div class="alert alert-info">
                        <div class="d-flex">
                            <i class="ti ti-info-circle me-2" style="font-size: 1.25rem;"></i>
                            <div>
                                <h4 class="alert-title">Come funziona</h4>
                                <ol class="mb-0 ps-3 small">
                                    <li class="mb-1">Vai a "Le Mie Assegnazioni" per vedere i moduli che ti sono stati assegnati</li>
                                    <li class="mb-1">Clicca "Inserisci Ore" per registrare le ore giornaliere con data, durata e firma</li>
                                    <li class="mb-1">Le ore inserite avranno stato <span class="badge bg-orange-lt">Da validare</span></li>
                                    <li>L'amministrazione approvera le ore: <span class="badge bg-green-lt">Approvate</span> o <span class="badge bg-red-lt">Rifiutate</span></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-orange-lt me-3"><i class="ti ti-alert-triangle"></i></span>
                                <div>
                                    <div class="fw-medium">Puoi modificare le ore solo se ancora "Da validare"</div>
                                    <div class="text-secondary small">Una volta approvate o rifiutate, le ore non possono essere modificate</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Stato Pagamenti -->
        <div class="wizard-docente-panel" id="step-docente-4" style="display: none;">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="ti ti-coin" style="font-size: 3rem; color: var(--tblr-orange);"></i>
                        <h2 class="mt-3 mb-2">Stato Pagamenti</h2>
                        <p class="text-secondary">
                            Segui lo stato dei tuoi pagamenti e le ore validate dall'amministrazione.
                        </p>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center py-3">
                                    <span class="badge bg-orange-lt p-2 mb-2" style="font-size: 0.85rem;">
                                        <i class="ti ti-clock me-1"></i> Programmato
                                    </span>
                                    <div class="text-secondary small">Pagamento pianificato ma non ancora eseguito</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center py-3">
                                    <span class="badge bg-blue-lt p-2 mb-2" style="font-size: 0.85rem;">
                                        <i class="ti ti-hourglass me-1"></i> In Attesa
                                    </span>
                                    <div class="text-secondary small">Pagamento in fase di elaborazione</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center py-3">
                                    <span class="badge bg-green-lt p-2 mb-2" style="font-size: 0.85rem;">
                                        <i class="ti ti-check me-1"></i> Pagato
                                    </span>
                                    <div class="text-secondary small">Compenso accreditato</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3"><i class="ti ti-info-circle"></i></span>
                                <div>
                                    <div class="fw-medium">Riepilogo sempre disponibile</div>
                                    <div class="text-secondary small">Nella dashboard trovi il riepilogo aggiornato delle ore erogate, ore validate e compensi maturati per ogni assegnazione.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigazione -->
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn" id="wizard-docente-prev" style="visibility: hidden;">
                <i class="ti ti-arrow-left me-1"></i> Indietro
            </button>
            <div>
                <button type="button" class="btn btn-primary" id="wizard-docente-next">
                    Avanti <i class="ti ti-arrow-right ms-1"></i>
                </button>
                <a href="<?= Url::to(['/docente-area/index']) ?>" class="btn bg-green text-green-fg" id="wizard-docente-finish" style="display: none;">
                    <i class="ti ti-check me-1"></i> Torna alla Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.wizard-docente-panel {
    animation: wizardDocFadeIn 0.3s ease-in-out;
}
@keyframes wizardDocFadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
#wizard-docente-steps .step-item {
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var currentStep = 1;
    var totalSteps = 4;

    function updateWizard() {
        for (var i = 1; i <= totalSteps; i++) {
            var panel = document.getElementById('step-docente-' + i);
            if (panel) {
                panel.style.display = i === currentStep ? 'block' : 'none';
                if (i === currentStep) {
                    panel.style.animation = 'none';
                    panel.offsetHeight;
                    panel.style.animation = 'wizardDocFadeIn 0.3s ease-in-out';
                }
            }
        }

        var stepItems = document.querySelectorAll('#wizard-docente-steps .step-item');
        stepItems.forEach(function(item, index) {
            item.classList.remove('active');
            if (index + 1 <= currentStep) {
                item.classList.add('active');
            }
        });

        document.getElementById('wizard-docente-prev').style.visibility = currentStep > 1 ? 'visible' : 'hidden';
        document.getElementById('wizard-docente-next').style.display = currentStep < totalSteps ? 'inline-block' : 'none';
        document.getElementById('wizard-docente-finish').style.display = currentStep === totalSteps ? 'inline-block' : 'none';
    }

    document.getElementById('wizard-docente-next').addEventListener('click', function() {
        if (currentStep < totalSteps) {
            currentStep++;
            updateWizard();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.getElementById('wizard-docente-prev').addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            updateWizard();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    var stepItems = document.querySelectorAll('#wizard-docente-steps .step-item');
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
