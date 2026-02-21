<?php

use yii\helpers\Html;
use app\models\Iscrizione;

/* @var $this yii\web\View */
/* @var $iscrizione app\models\Iscrizione */
/* @var $storico app\models\StoricoIscrizione[] */

$this->title = 'Storico Iscrizione #' . $iscrizione->id;
$this->params['breadcrumbs'][] = ['label' => 'Iscrizioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Iscrizione #' . $iscrizione->id, 'url' => ['view', 'id' => $iscrizione->id]];
$this->params['breadcrumbs'][] = 'Storico';

// Mappa colori per status
$statusColors = [
    Iscrizione::STATUS_CANDIDATO => 'azure',
    Iscrizione::STATUS_IN_VALUTAZIONE => 'orange',
    Iscrizione::STATUS_AMMESSO => 'green',
    Iscrizione::STATUS_AMMESSO_CON_RISERVA => 'lime',
    Iscrizione::STATUS_NON_AMMESSO => 'red',
    Iscrizione::STATUS_ISCRITTO => 'azure',
    Iscrizione::STATUS_FREQUENTANTE => 'blue',
    Iscrizione::STATUS_COMPLETATO => 'teal',
    Iscrizione::STATUS_RITIRATO => 'default',
];
?>

<div class="iscrizione-storico">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-history me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Candidato: <strong><?= $iscrizione->allievo ? Html::encode($iscrizione->allievo->cognome . ' ' . $iscrizione->allievo->nome) : '-' ?></strong>
                        | Stato attuale: <span class="badge <?= $iscrizione->getStatusBadgeClass() ?>"><?= Html::encode($iscrizione->getStatusLabel()) ?></span>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Dettaglio Iscrizione', ['view', 'id' => $iscrizione->id], ['class' => 'btn btn-secondary']) ?>
                    <?php if ($iscrizione->avviso_id): ?>
                        <?= Html::a('<i class="ti ti-list-numbers me-1"></i> Graduatoria', ['graduatoria-studenti', 'avviso_id' => $iscrizione->avviso_id], ['class' => 'btn btn-primary']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-history me-2"></i>Timeline Cambiamenti di Stato</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($storico)): ?>
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-history-off" style="font-size: 3rem;"></i></div>
                            <p class="empty-title">Nessun cambio di stato registrato</p>
                            <p class="empty-subtitle text-muted">Lo storico verra aggiornato automaticamente ad ogni cambio di stato.</p>
                        </div>
                    <?php else: ?>
                        <ul class="timeline">
                            <?php foreach ($storico as $index => $record): ?>
                                <?php
                                    $colore = $statusColors[$record->status_nuovo] ?? 'default';
                                    $isUltimo = ($index === count($storico) - 1);
                                ?>
                                <li class="timeline-event">
                                    <div class="timeline-event-icon bg-<?= $colore ?>">
                                        <i class="ti ti-<?= $record->status_nuovo === Iscrizione::STATUS_AMMESSO ? 'check' :
                                            ($record->status_nuovo === Iscrizione::STATUS_NON_AMMESSO ? 'x' :
                                            ($record->status_nuovo === Iscrizione::STATUS_RITIRATO ? 'logout' :
                                            ($record->status_nuovo === Iscrizione::STATUS_COMPLETATO ? 'trophy' : 'arrow-right'))) ?>"></i>
                                    </div>
                                    <div class="card <?= $isUltimo ? 'border-' . $colore : '' ?>">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <?php if ($record->status_precedente): ?>
                                                    <span class="badge bg-<?= $statusColors[$record->status_precedente] ?? 'default' ?>-lt">
                                                        <?= Html::encode($record->getStatusPrecedenteLabel()) ?>
                                                    </span>
                                                    <i class="ti ti-arrow-right mx-2 text-muted"></i>
                                                <?php endif; ?>
                                                <span class="badge bg-<?= $colore ?> text-<?= $colore ?>-fg">
                                                    <?= Html::encode($record->getStatusNuovoLabel()) ?>
                                                </span>
                                            </div>

                                            <?php if ($record->note): ?>
                                                <p class="text-muted mb-1">
                                                    <i class="ti ti-note me-1"></i><?= Html::encode($record->note) ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="mt-2 small text-muted">
                                                <i class="ti ti-clock me-1"></i><?= Yii::$app->formatter->asDatetime($record->created_at) ?>
                                                <?php if ($record->createdByUser): ?>
                                                    | <i class="ti ti-user me-1"></i><?= Html::encode($record->createdByUser->username) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    list-style: none;
    padding: 0;
    position: relative;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--tblr-border-color);
}
.timeline-event {
    position: relative;
    padding-left: 50px;
    margin-bottom: 1rem;
}
.timeline-event-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    z-index: 1;
}
</style>
