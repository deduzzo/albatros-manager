<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $iscrizione app\models\Iscrizione|null */

$this->title = 'Iscrizione Confermata';
?>

<div class="page-body">
    <div class="container-xl">
        <div class="empty py-5">
            <div class="empty-img">
                <span class="avatar avatar-2xl bg-green-lt">
                    <i class="ti ti-circle-check" style="font-size: 4rem;"></i>
                </span>
            </div>
            <p class="empty-title h1 mt-4">Iscrizione Inviata!</p>
            <p class="empty-subtitle text-secondary">
                La tua richiesta di iscrizione è stata inviata con successo.<br>
                Riceverai una email di conferma all'indirizzo indicato.
            </p>

            <?php if ($iscrizione && $iscrizione->avviso): ?>
            <div class="card mt-4" style="max-width: 600px; margin: auto;">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-file-check me-2"></i> Riepilogo Iscrizione</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-4">Corso:</dt>
                        <dd class="col-8"><strong><?= Html::encode($iscrizione->avviso->titolo) ?></strong></dd>

                        <dt class="col-4">Codice:</dt>
                        <dd class="col-8"><span class="badge bg-azure-lt"><?= Html::encode($iscrizione->avviso->codice_avviso) ?></span></dd>

                        <dt class="col-4">Data Iscrizione:</dt>
                        <dd class="col-8"><?= Yii::$app->formatter->asDate($iscrizione->data_iscrizione) ?></dd>

                        <?php if ($iscrizione->allievo): ?>
                        <dt class="col-4">Nominativo:</dt>
                        <dd class="col-8"><?= Html::encode($iscrizione->allievo->getNomeCompleto()) ?></dd>

                        <dt class="col-4">Email:</dt>
                        <dd class="col-8"><?= Html::encode($iscrizione->allievo->email) ?></dd>
                        <?php endif; ?>

                        <dt class="col-4">Stato:</dt>
                        <dd class="col-8"><span class="badge <?= $iscrizione->getStatusBadgeClass() ?>"><?= $iscrizione->getStatusLabel() ?></span></dd>
                    </dl>

                    <?php $moduli = $iscrizione->avviso->moduli; ?>
                    <?php if (!empty($moduli)): ?>
                    <hr>
                    <h4 class="mb-2"><i class="ti ti-book me-1"></i> Corsi inclusi</h4>
                    <?php foreach ($moduli as $modulo): ?>
                    <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-check text-success me-2"></i>
                        <div>
                            <div class="fw-bold small"><?= Html::encode($modulo->titolo) ?></div>
                            <div class="text-secondary" style="font-size: 0.75rem;"><?= $modulo->ore_totali ?> ore - <?= Html::encode($modulo->sede->nome ?? 'Da definire') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card mt-4" style="max-width: 500px; margin: auto;">
                <div class="card-body">
                    <h4 class="mb-3">Prossimi passi:</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex">
                            <span class="avatar avatar-sm bg-blue-lt me-3">1</span>
                            <div>
                                <strong>Verifica email</strong>
                                <div class="text-secondary small">Controlla la tua casella email e conferma l'indirizzo</div>
                            </div>
                        </div>
                        <div class="list-group-item d-flex">
                            <span class="avatar avatar-sm bg-blue-lt me-3">2</span>
                            <div>
                                <strong>Valutazione documenti</strong>
                                <div class="text-secondary small">I nostri operatori verificheranno i dati inseriti</div>
                            </div>
                        </div>
                        <div class="list-group-item d-flex">
                            <span class="avatar avatar-sm bg-blue-lt me-3">3</span>
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
                <?= Html::a('<i class="ti ti-book me-2"></i> Vedi altri corsi', ['index'], ['class' => 'btn btn-outline-primary ms-2']) ?>
            </div>
        </div>
    </div>
</div>
