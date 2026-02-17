<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $iscrizioni app\models\Istanza[] */

$this->title = 'Le Mie Iscrizioni';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-list-check me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
            <div class="page-subtitle text-secondary">
                Storico delle tue candidature e stato delle domande
            </div>
        </div>

        <?php if (empty($iscrizioni)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <span class="avatar avatar-xl bg-secondary-lt">
                            <i class="ti ti-file-off"></i>
                        </span>
                    </div>
                    <h3>Nessuna iscrizione</h3>
                    <p class="text-secondary">Non hai ancora presentato nessuna candidatura.</p>
                    <?= Html::a('<i class="ti ti-file-text me-2"></i> Esplora i bandi aperti', ['bandi'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Bando</th>
                                <th>Modulo</th>
                                <th>Stato</th>
                                <th>Punteggio</th>
                                <th>Posizione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($iscrizioni as $istanza): ?>
                                <tr>
                                    <td class="text-secondary">
                                        <?= Yii::$app->formatter->asDate($istanza->data_presentazione) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt"><?= Html::encode($istanza->avviso->codice_avviso ?? '-') ?></span>
                                        <div class="small text-muted"><?= Html::encode($istanza->avviso->titolo ?? '') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-azure-lt"><?= Html::encode($istanza->modulo->codice ?? '-') ?></span>
                                        <div class="small text-muted"><?= Html::encode($istanza->modulo->titolo ?? '') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $istanza->getStatusBadgeClass() ?>">
                                            <?= $istanza->getStatusLabel() ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($istanza->punteggio_totale !== null): ?>
                                            <strong><?= number_format($istanza->punteggio_totale, 2) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($istanza->posizione_graduatoria): ?>
                                            <span class="badge bg-blue text-blue-fg">#<?= $istanza->posizione_graduatoria ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <p class="text-muted small">
                    <i class="ti ti-info-circle me-1"></i>
                    Lo stato delle tue candidature viene aggiornato automaticamente. Controlla periodicamente questa pagina.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
