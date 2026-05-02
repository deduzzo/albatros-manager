<?php

use yii\helpers\Url;

$this->title = 'Guida Interattiva - Area Docenti';
?>

<div class="container-xl py-4">
    <div class="text-center mb-4">
        <a href="<?= Url::to(['/site/login']) ?>">
            <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-badge-small.png" height="40" alt="Albatros">
        </a>
        <h2 class="mt-3"><i class="ti ti-school me-2"></i>Guida Interattiva - Area Docenti</h2>
        <p class="text-secondary">Scopri tutte le funzionalita della tua area riservata</p>
    </div>

    <!-- Steps Progress -->
    <div class="steps steps-green mb-4" id="guide-steps">
        <a href="#" class="step-item active" data-step="1">Il Tuo Profilo</a>
        <a href="#" class="step-item" data-step="2">Corsi e Iscrizioni</a>
        <a href="#" class="step-item" data-step="3">Assegnazioni e Ore</a>
        <a href="#" class="step-item" data-step="4">Pagamenti</a>
    </div>

    <!-- Step 1: Profilo -->
    <div class="card guide-step" id="step-1">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <span class="avatar avatar-xl bg-green-lt rounded">
                    <i class="ti ti-user" style="font-size: 2.5rem;"></i>
                </span>
            </div>
            <h2>Benvenuto nella tua Area Riservata!</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">
                Da qui puoi gestire il tuo profilo, consultare i corsi aperti, candidarti per i moduli formativi,
                inserire le ore lavorate e seguire lo stato dei pagamenti.
            </p>
            <div class="mt-4">
                <div class="row g-3 justify-content-center">
                    <div class="col-md-4">
                        <div class="card card-sm border-start border-3 border-green">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-user-edit text-green me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>Profilo</strong>
                                        <div class="text-secondary small">Aggiorna i tuoi dati anagrafici, email e telefono</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-sm border-start border-3 border-azure">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-key text-azure me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>Accesso</strong>
                                        <div class="text-secondary small">Login con Codice Fiscale e password</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Corsi -->
    <div class="card guide-step" id="step-2" style="display: none;">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <span class="avatar avatar-xl bg-azure-lt rounded">
                    <i class="ti ti-clipboard-list" style="font-size: 2.5rem;"></i>
                </span>
            </div>
            <h2>Corsi Aperti e Iscrizioni</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">
                Consulta i corsi pubblicati e candidati per i moduli formativi di tuo interesse.
            </p>
            <div class="mt-4 text-start mx-auto" style="max-width: 600px;">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex">
                            <span class="badge bg-green-lt me-3 mt-1">1</span>
                            <div>
                                <strong>Consulta i corsi</strong>
                                <div class="text-secondary small">Vai su "Corsi Aperti" per vedere tutti i corsi con scadenza futura</div>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex">
                            <span class="badge bg-green-lt me-3 mt-1">2</span>
                            <div>
                                <strong>Scegli un modulo</strong>
                                <div class="text-secondary small">Clicca su un corso per vedere i moduli disponibili e i requisiti</div>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex">
                            <span class="badge bg-green-lt me-3 mt-1">3</span>
                            <div>
                                <strong>Iscriviti</strong>
                                <div class="text-secondary small">Compila i dati, seleziona le competenze, carica i documenti richiesti</div>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex">
                            <span class="badge bg-green-lt me-3 mt-1">4</span>
                            <div>
                                <strong>Segui lo stato</strong>
                                <div class="text-secondary small">In "Le Mie Iscrizioni" puoi vedere lo stato di ogni candidatura</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Assegnazioni e Ore -->
    <div class="card guide-step" id="step-3" style="display: none;">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <span class="avatar avatar-xl bg-orange-lt rounded">
                    <i class="ti ti-clock" style="font-size: 2.5rem;"></i>
                </span>
            </div>
            <h2>Assegnazioni e Inserimento Ore</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">
                Dopo l'assegnazione da parte del CTS, qui trovi i tuoi moduli e puoi rendicontare le ore lavorate.
            </p>
            <div class="mt-4 text-start mx-auto" style="max-width: 600px;">
                <div class="alert bg-azure-lt">
                    <div class="d-flex">
                        <i class="ti ti-info-circle me-2 mt-1"></i>
                        <div>
                            <strong>Come inserire le ore</strong><br>
                            Per ogni giornata di lezione, inserisci: data, numero ore, ora inizio e fine, e una breve nota.
                            Le ore vengono inviate all'amministrazione per la validazione.
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-4 text-center">
                        <div class="card bg-green-lt">
                            <div class="card-body py-3">
                                <i class="ti ti-check text-green" style="font-size: 1.5rem;"></i>
                                <div class="mt-1"><strong>Approvato</strong></div>
                                <div class="text-secondary small">Ore validate</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="card bg-orange-lt">
                            <div class="card-body py-3">
                                <i class="ti ti-clock text-orange" style="font-size: 1.5rem;"></i>
                                <div class="mt-1"><strong>Inserito</strong></div>
                                <div class="text-secondary small">In attesa</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="card bg-red-lt">
                            <div class="card-body py-3">
                                <i class="ti ti-x text-red" style="font-size: 1.5rem;"></i>
                                <div class="mt-1"><strong>Rifiutato</strong></div>
                                <div class="text-secondary small">Da correggere</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Pagamenti -->
    <div class="card guide-step" id="step-4" style="display: none;">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <span class="avatar avatar-xl bg-yellow-lt rounded">
                    <i class="ti ti-coin" style="font-size: 2.5rem;"></i>
                </span>
            </div>
            <h2>Stato Pagamenti</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">
                Segui lo stato dei tuoi pagamenti direttamente dalla dashboard.
                I pagamenti vengono elaborati dopo la validazione delle ore e la verifica della documentazione.
            </p>
            <div class="mt-4">
                <div class="alert bg-green-lt">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-circle-check me-2"></i>
                        <div>
                            <strong>Tutto pronto!</strong> Ora conosci tutte le funzionalita della tua area riservata.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="d-flex justify-content-between mt-3">
        <button class="btn" id="btn-prev" style="display: none;" onclick="prevStep()">
            <i class="ti ti-arrow-left me-1"></i> Indietro
        </button>
        <div class="ms-auto">
            <button class="btn btn-primary" id="btn-next" onclick="nextStep()">
                Avanti <i class="ti ti-arrow-right ms-1"></i>
            </button>
            <a href="<?= Url::to(['/area-docenti']) ?>" class="btn bg-green text-green-fg" id="btn-finish" style="display: none;">
                <i class="ti ti-check me-1"></i> Vai all'Area Docenti
            </a>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="<?= Url::to(['/site/manuale-docente']) ?>" class="text-secondary" target="_blank">
            <i class="ti ti-book me-1"></i> Consulta il Manuale Completo
        </a>
        <span class="mx-2 text-secondary">|</span>
        <a href="<?= Url::to(['/site/supporto']) ?>" class="text-secondary" target="_blank">
            <i class="ti ti-bug me-1"></i> Segnala un Problema
        </a>
    </div>
</div>

<script>
(function() {
    var currentStep = 1;
    var totalSteps = 4;

    function updateUI() {
        // Show/hide steps
        document.querySelectorAll('.guide-step').forEach(function(el) { el.style.display = 'none'; });
        document.getElementById('step-' + currentStep).style.display = 'block';

        // Update step indicators
        document.querySelectorAll('#guide-steps .step-item').forEach(function(el, i) {
            el.classList.toggle('active', (i + 1) === currentStep);
        });

        // Buttons
        document.getElementById('btn-prev').style.display = currentStep > 1 ? '' : 'none';
        document.getElementById('btn-next').style.display = currentStep < totalSteps ? '' : 'none';
        document.getElementById('btn-finish').style.display = currentStep === totalSteps ? '' : 'none';
    }

    window.nextStep = function() {
        if (currentStep < totalSteps) { currentStep++; updateUI(); }
    };
    window.prevStep = function() {
        if (currentStep > 1) { currentStep--; updateUI(); }
    };

    // Click on step indicator
    document.querySelectorAll('#guide-steps .step-item').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            currentStep = parseInt(this.getAttribute('data-step'));
            updateUI();
        });
    });
})();
</script>
