<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */
/* @var $borse \app\models\BorsaStudio[] */

use yii\helpers\Html;
use yii\helpers\Url;

$statusColors = [
    'richiesta'          => 'bg-azure-lt text-azure',
    'approvata'          => 'bg-blue-lt text-blue',
    'in_erogazione'      => 'bg-indigo-lt text-indigo',
    'completata'         => 'bg-teal-lt text-teal',
    'revocata'           => 'bg-red-lt text-red',
    'sospesa'            => 'bg-orange-lt text-orange',
    'erogazione_bloccata'=> 'bg-yellow-lt text-yellow',
];
$statusLabels = [
    'richiesta'          => 'Richiesta',
    'approvata'          => 'Approvata',
    'in_erogazione'      => 'In Erogazione',
    'completata'         => 'Completata',
    'revocata'           => 'Revocata',
    'sospesa'            => 'Sospesa',
    'erogazione_bloccata'=> 'Erogazione Bloccata',
];
$tipologiaLabels = [
    'in_sede'   => 'In Sede',
    'pendolare' => 'Pendolare',
    'fuori_sede'=> 'Fuori Sede',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1" style="font-size:1.3rem; font-weight:700; color:#1e293b;">Borse di Studio</h2>
        <p class="text-secondary mb-0" style="font-size:0.875rem;"><?= Html::encode($corso->codice_avviso . ' — ' . $corso->titolo) ?></p>
    </div>
    <div class="d-flex gap-2">
        <?= Html::a(
            '<i class="ti ti-refresh me-1"></i> Ricalcola Frequenze',
            ['/borsa-studio/ricalcola-frequenze', 'progetto_id' => $corso->progetto_id],
            [
                'class' => 'btn btn-sm btn-outline-primary',
                'data' => [
                    'method' => 'post',
                    'confirm' => 'Ricalcolare le percentuali di frequenza per tutte le borse di questo corso?',
                ],
            ]
        ) ?>
        <a href="<?= Url::to(['/borsa-studio/export-sepa', 'progetto_id' => $corso->progetto_id]) ?>"
           class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-file-export me-1"></i> Export SEPA
        </a>
        <a href="<?= Url::to(['/borsa-studio/index', 'BorsaStudioSearch[progetto_id]' => $corso->progetto_id]) ?>"
           class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-external-link me-1"></i> Vista avanzata
        </a>
    </div>
</div>

<?php if (empty($borse)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-award-off text-secondary" style="font-size:3rem;"></i>
            <h3 class="mt-3 text-secondary">Nessuna borsa di studio</h3>
            <p class="text-muted mb-4">Non ci sono borse di studio per questo corso.</p>
            <a class="btn btn-primary" href="<?= Url::to(['/borsa-studio/create']) ?>">
                <i class="ti ti-plus me-1"></i> Crea prima borsa
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Studente</th>
                        <th>Tipologia Sede</th>
                        <th class="text-end">ISEE</th>
                        <th class="text-end">Importo Finale</th>
                        <th>Stato</th>
                        <th class="text-end">% Frequenza</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borse as $borsa): ?>
                        <?php $allievo = $borsa->allievo; ?>
                        <tr>
                            <td>
                                <?php if ($allievo): ?>
                                    <div class="fw-semibold"><?= Html::encode($allievo->cognome . ' ' . $allievo->nome) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= Html::encode($allievo->codice_fiscale) ?></div>
                                <?php else: ?>
                                    <code style="font-size:0.8rem;"><?= Html::encode($borsa->allievo_codice_fiscale) ?></code>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $tipologiaLabels[$borsa->tipologia_sede] ?? Html::encode($borsa->tipologia_sede) ?>
                            </td>
                            <td class="text-end">
                                <?= $borsa->isee_importo ? '€ ' . number_format($borsa->isee_importo, 2, ',', '.') : '—' ?>
                            </td>
                            <td class="text-end fw-semibold">
                                € <?= number_format($borsa->importo_finale, 2, ',', '.') ?>
                            </td>
                            <td>
                                <?php if ($borsa->status === 'erogazione_bloccata'): ?>
                                    <span class="badge <?= $statusColors[$borsa->status] ?? 'bg-default-lt' ?>"
                                          data-bs-toggle="tooltip"
                                          title="<?= Html::encode($borsa->note ?: 'Erogazione bloccata: frequenza < 80%') ?>">
                                        <?= $statusLabels[$borsa->status] ?? $borsa->status ?>
                                        <i class="ti ti-info-circle ms-1"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="badge <?= $statusColors[$borsa->status] ?? 'bg-default-lt' ?>">
                                        <?= $statusLabels[$borsa->status] ?? $borsa->status ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($borsa->percentuale_frequenza !== null): ?>
                                    <span class="fw-semibold <?= $borsa->percentuale_frequenza >= 80 ? 'text-green' : 'text-red' ?>">
                                        <?= number_format($borsa->percentuale_frequenza, 1, ',', '.') ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= Url::to(['/borsa-studio/view', 'id' => $borsa->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Dettaglio">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="<?= Url::to(['/borsa-studio/update', 'id' => $borsa->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Modifica">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                    <?php if ($borsa->status === 'approvata'): ?>
                                        <?= Html::a(
                                            '<i class="ti ti-cash"></i>',
                                            ['/borsa-studio/eroga', 'id' => $borsa->id],
                                            [
                                                'class' => 'btn btn-sm btn-ghost-success',
                                                'title' => 'Eroga borsa',
                                                'data' => [
                                                    'method' => 'post',
                                                    'confirm' => 'Confermare l\'erogazione della borsa?',
                                                ],
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
