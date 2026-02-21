<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $candidature app\models\Iscrizione[] */

$this->title = 'Le Mie Candidature';
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

        <?php if (empty($candidature)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <span class="avatar avatar-xl bg-purple-lt">
                            <i class="ti ti-file-off"></i>
                        </span>
                    </div>
                    <h3>Nessuna candidatura</h3>
                    <p class="text-secondary">Non hai ancora presentato nessuna candidatura.</p>
                    <?= Html::a('<i class="ti ti-file-text me-2"></i> Esplora i bandi aperti', ['bandi'], [
                        'class' => 'btn',
                        'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;',
                    ]) ?>
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
                                <th>Stato</th>
                                <th>Punteggio</th>
                                <th>Posizione</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidature as $candidatura): ?>
                                <tr>
                                    <td class="text-secondary">
                                        <?= Yii::$app->formatter->asDate($candidatura->data_iscrizione) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-purple-lt"><?= Html::encode($candidatura->avviso->codice_avviso ?? '-') ?></span>
                                        <div class="small text-muted"><?= Html::encode($candidatura->avviso->titolo ?? '') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $candidatura->getStatusBadgeClass() ?>">
                                            <?= $candidatura->getStatusLabel() ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($candidatura->punteggio_totale !== null): ?>
                                            <strong><?= number_format($candidatura->punteggio_totale, 2, ',', '.') ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($candidatura->posizione_graduatoria): ?>
                                            <span class="badge bg-purple text-purple-fg">#<?= $candidatura->posizione_graduatoria ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?= Html::a('<i class="ti ti-eye"></i> Dettaglio', ['candidatura-view', 'id' => $candidatura->id], [
                                            'class' => 'btn btn-sm btn-outline-purple',
                                        ]) ?>
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
