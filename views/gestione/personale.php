<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */
/* @var $assegnazioni \app\models\Assegnazione[] */

use yii\helpers\Html;
use yii\helpers\Url;

$contractStatusColors = [
    'bozza'     => 'bg-orange-lt text-orange',
    'firmato'   => 'bg-blue-lt text-blue',
    'attivo'    => 'bg-green-lt text-green',
    'sospeso'   => 'bg-yellow-lt text-yellow',
    'concluso'  => 'bg-teal-lt text-teal',
    'annullato' => 'bg-red-lt text-red',
];
$contractStatusLabels = [
    'bozza'     => 'Bozza',
    'firmato'   => 'Firmato',
    'attivo'    => 'Attivo',
    'sospeso'   => 'Sospeso',
    'concluso'  => 'Concluso',
    'annullato' => 'Annullato',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1" style="font-size:1.3rem; font-weight:700; color:#1e293b;">Personale</h2>
        <p class="text-secondary mb-0" style="font-size:0.875rem;"><?= Html::encode($corso->codice_avviso . ' — ' . $corso->titolo) ?></p>
    </div>
    <a href="<?= Url::to(['/assegnazione/index', 'AssegnazioneSearch[avviso_id]' => $corso->id]) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-external-link me-1"></i> Vista avanzata
    </a>
</div>

<?php if (empty($assegnazioni)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-users-off text-secondary" style="font-size:3rem;"></i>
            <h3 class="mt-3 text-secondary">Nessuna assegnazione</h3>
            <p class="text-muted">Non ci sono docenti assegnati per questo corso.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Docente</th>
                        <th>Modulo</th>
                        <th class="text-end">Compenso/h</th>
                        <th class="text-end">Ore</th>
                        <th>Contratto</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assegnazioni as $ass): ?>
                        <?php
                        $docente = $ass->docente;
                        $modulo = $ass->modulo;
                        $contratto = $ass->contratto;
                        ?>
                        <tr>
                            <td>
                                <?php if ($docente): ?>
                                    <a href="<?= Url::to(['/docente/view', 'cf' => $docente->codice_fiscale]) ?>" class="text-decoration-none fw-semibold">
                                        <?= Html::encode($docente->cognome . ' ' . $docente->nome) ?>
                                    </a>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= Html::encode($docente->codice_fiscale) ?></div>
                                <?php else: ?>
                                    <span class="text-muted"><?= Html::encode($ass->docente_codice_fiscale) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $modulo ? Html::encode($modulo->titolo) : '—' ?>
                            </td>
                            <td class="text-end">
                                <?= $contratto ? '€ ' . number_format($contratto->compenso_orario, 2, ',', '.') : ($modulo && $modulo->compenso_orario ? '€ ' . number_format($modulo->compenso_orario, 2, ',', '.') : '—') ?>
                            </td>
                            <td class="text-end">
                                <?= $contratto ? $contratto->ore_contrattualizzate : ($modulo ? $modulo->ore_totali : '—') ?>
                            </td>
                            <td>
                                <?php if ($contratto): ?>
                                    <span class="badge <?= $contractStatusColors[$contratto->status] ?? 'bg-default-lt' ?>">
                                        <?= $contractStatusLabels[$contratto->status] ?? $contratto->status ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-orange-lt text-orange">
                                        <i class="ti ti-alert-circle me-1"></i>Mancante
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= Url::to(['/assegnazione/view', 'id' => $ass->id]) ?>"
                                   class="btn btn-sm btn-ghost-secondary" title="Dettaglio assegnazione">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
