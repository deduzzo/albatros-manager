<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Iscrizione Confermata';
?>

<div class="page-body">
    <div class="container-xl">
        <div class="empty py-5">
            <div class="empty-img">
                <span class="avatar avatar-2xl bg-success-lt">
                    <i class="ti ti-circle-check" style="font-size: 4rem;"></i>
                </span>
            </div>
            <p class="empty-title h1 mt-4">Iscrizione Inviata!</p>
            <p class="empty-subtitle text-secondary">
                La tua richiesta di iscrizione è stata inviata con successo.<br>
                Riceverai una email di conferma all'indirizzo indicato.
            </p>

            <div class="card mt-4" style="max-width: 500px; margin: auto;">
                <div class="card-body">
                    <h4 class="mb-3">Prossimi passi:</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex">
                            <span class="avatar avatar-sm bg-primary-lt me-3">1</span>
                            <div>
                                <strong>Verifica email</strong>
                                <div class="text-secondary small">Controlla la tua casella email e conferma l'indirizzo</div>
                            </div>
                        </div>
                        <div class="list-group-item d-flex">
                            <span class="avatar avatar-sm bg-primary-lt me-3">2</span>
                            <div>
                                <strong>Valutazione documenti</strong>
                                <div class="text-secondary small">I nostri operatori verificheranno i documenti caricati</div>
                            </div>
                        </div>
                        <div class="list-group-item d-flex">
                            <span class="avatar avatar-sm bg-primary-lt me-3">3</span>
                            <div>
                                <strong>Conferma iscrizione</strong>
                                <div class="text-secondary small">Riceverai conferma definitiva entro 5 giorni lavorativi</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="empty-action mt-4">
                <?= Html::a('<i class="ti ti-home me-2"></i> Torna alla Home', ['index'], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="ti ti-book me-2"></i> Vedi altri corsi', ['corsi'], ['class' => 'btn btn-outline-primary ms-2']) ?>
            </div>
        </div>
    </div>
</div>
