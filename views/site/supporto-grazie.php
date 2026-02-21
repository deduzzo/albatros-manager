<?php
/**
 * Conferma segnalazione inviata
 */

use yii\helpers\Url;

$this->title = 'Segnalazione Inviata';
?>

<div class="container-xl py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-green-lt">
                            <i class="ti ti-check" style="font-size: 2rem;"></i>
                        </span>
                    </div>
                    <h2 class="mb-2">Grazie per la segnalazione!</h2>
                    <p class="text-secondary mb-4" style="max-width: 500px; margin: auto;">
                        La tua segnalazione e stata ricevuta correttamente. Il team di sviluppo la prendera in carico il prima possibile.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?= Url::to(['/site/supporto']) ?>" class="btn btn-outline-primary">
                            <i class="ti ti-plus me-1"></i> Nuova segnalazione
                        </a>
                        <a href="<?= Url::to(['/site/index']) ?>" class="btn btn-primary">
                            <i class="ti ti-home me-1"></i> Torna alla Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
