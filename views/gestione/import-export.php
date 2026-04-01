<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */

use yii\helpers\Url;
use yii\helpers\Html;
?>

<div class="mb-4">
    <h2 class="mb-1" style="font-size:1.3rem; font-weight:700; color:#1e293b;">Import / Export</h2>
    <p class="text-secondary mb-0" style="font-size:0.875rem;"><?= Html::encode($corso->codice_avviso . ' — ' . $corso->titolo) ?></p>
</div>

<div class="row row-cards">

    <!-- Import Progetto Completo -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="bg-indigo-lt p-3 rounded-2">
                        <i class="ti ti-file-upload text-indigo" style="font-size:1.5rem;"></i>
                    </span>
                    <div>
                        <h4 class="mb-0" style="font-size:1rem;">Import Progetto Completo</h4>
                        <p class="text-muted mb-0" style="font-size:0.8rem;">Importa moduli, assegnazioni e docenti da Excel</p>
                    </div>
                </div>
                <p class="text-secondary mb-3" style="font-size:0.875rem;">
                    Usa il foglio Excel standard per caricare in blocco i dati del corso:
                    moduli, assegnazioni docenti e relative informazioni salariali.
                </p>
                <div class="d-flex gap-2">
                    <a href="<?= Url::to(['/import/index']) ?>" class="btn btn-sm btn-primary" style="background:#4263eb; border-color:#4263eb;">
                        <i class="ti ti-upload me-1"></i> Vai all'Import
                    </a>
                    <a href="<?= Url::to(['/import/download-template']) ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-download me-1"></i> Scarica Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Consulente del Lavoro -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="bg-teal-lt p-3 rounded-2">
                        <i class="ti ti-file-spreadsheet text-teal" style="font-size:1.5rem;"></i>
                    </span>
                    <div>
                        <h4 class="mb-0" style="font-size:1rem;">Export Consulente del Lavoro</h4>
                        <p class="text-muted mb-0" style="font-size:0.8rem;">Genera file XLSX per il consulente</p>
                    </div>
                </div>
                <p class="text-secondary mb-3" style="font-size:0.875rem;">
                    Esporta i dati del corso in formato Excel per il consulente del lavoro,
                    con tutti i compensi e le ore dei docenti.
                </p>
                <a href="<?= Url::to(['/report/export-consulente', 'progetto_id' => $corso->progetto_id]) ?>"
                   class="btn btn-sm btn-teal" style="background:#0ca678; color:#fff; border-color:#0ca678;">
                    <i class="ti ti-download me-1"></i> Export XLSX
                </a>
            </div>
        </div>
    </div>

    <!-- Export SEPA Borse di Studio -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="bg-blue-lt p-3 rounded-2">
                        <i class="ti ti-credit-card text-blue" style="font-size:1.5rem;"></i>
                    </span>
                    <div>
                        <h4 class="mb-0" style="font-size:1rem;">Export SEPA Borse di Studio</h4>
                        <p class="text-muted mb-0" style="font-size:0.8rem;">Genera file XML per pagamenti bancari</p>
                    </div>
                </div>
                <p class="text-secondary mb-3" style="font-size:0.875rem;">
                    Genera il file SEPA XML (pain.001) per il pagamento massivo delle borse di studio
                    tramite bonifico bancario.
                </p>
                <a href="<?= Url::to(['/borsa-studio/export-sepa', 'progetto_id' => $corso->progetto_id]) ?>"
                   class="btn btn-sm btn-blue" style="background:#206bc4; color:#fff; border-color:#206bc4;">
                    <i class="ti ti-file-export me-1"></i> Export SEPA XML
                </a>
            </div>
        </div>
    </div>

    <!-- Vista Avanzata -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="bg-default-lt p-3 rounded-2">
                        <i class="ti ti-layout-grid text-secondary" style="font-size:1.5rem;"></i>
                    </span>
                    <div>
                        <h4 class="mb-0" style="font-size:1rem;">Modalità Avanzata</h4>
                        <p class="text-muted mb-0" style="font-size:0.8rem;">Accedi a tutti gli strumenti admin</p>
                    </div>
                </div>
                <p class="text-secondary mb-3" style="font-size:0.875rem;">
                    Accedi alla dashboard avanzata per operazioni più complesse,
                    report personalizzati e configurazioni avanzate.
                </p>
                <a href="<?= Url::to(['/site/index']) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-arrow-right me-1"></i> Vai alla Dashboard
                </a>
            </div>
        </div>
    </div>

</div>
