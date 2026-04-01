<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */
/* @var $contratti \app\models\Contratto[] */

use yii\helpers\Html;
use yii\helpers\Url;

$statusColors = [
    'bozza'     => 'bg-orange-lt text-orange',
    'firmato'   => 'bg-blue-lt text-blue',
    'attivo'    => 'bg-green-lt text-green',
    'sospeso'   => 'bg-yellow-lt text-yellow',
    'concluso'  => 'bg-teal-lt text-teal',
    'annullato' => 'bg-red-lt text-red',
];
$statusLabels = [
    'bozza'     => 'Bozza',
    'firmato'   => 'Firmato',
    'attivo'    => 'Attivo',
    'sospeso'   => 'Sospeso',
    'concluso'  => 'Concluso',
    'annullato' => 'Annullato',
];
$tipoLabels = [
    'cococo'      => 'CoCoCo',
    'partita_iva' => 'Partita IVA',
    'occasionale' => 'Occasionale',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1" style="font-size:1.3rem; font-weight:700; color:#1e293b;">Contratti</h2>
        <p class="text-secondary mb-0" style="font-size:0.875rem;"><?= Html::encode($corso->codice_avviso . ' — ' . $corso->titolo) ?></p>
    </div>
    <a href="<?= Url::to(['/contratto/index']) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-external-link me-1"></i> Vista avanzata
    </a>
</div>

<?php if (empty($contratti)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-file-off text-secondary" style="font-size:3rem;"></i>
            <h3 class="mt-3 text-secondary">Nessun contratto</h3>
            <p class="text-muted">Non ci sono contratti per questo corso.</p>
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
                        <th>Tipo</th>
                        <th>Stato</th>
                        <th class="text-end">Importo</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contratti as $contratto): ?>
                        <?php
                        $ass = $contratto->assegnazione;
                        $docente = $ass ? $ass->docente : null;
                        $modulo = $ass ? $ass->modulo : null;
                        ?>
                        <tr>
                            <td>
                                <?php if ($docente): ?>
                                    <div class="fw-semibold"><?= Html::encode($docente->cognome . ' ' . $docente->nome) ?></div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $modulo ? Html::encode(mb_substr($modulo->titolo, 0, 45)) : '—' ?>
                            </td>
                            <td>
                                <span class="badge bg-default-lt text-default">
                                    <?= $tipoLabels[$contratto->tipo] ?? Html::encode($contratto->tipo) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $statusColors[$contratto->status] ?? 'bg-default-lt' ?>">
                                    <?= $statusLabels[$contratto->status] ?? $contratto->status ?>
                                </span>
                            </td>
                            <td class="text-end fw-semibold">
                                € <?= number_format($contratto->compenso_totale, 2, ',', '.') ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= Url::to(['/contratto/genera', 'id' => $contratto->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Genera contratto">
                                        <i class="ti ti-file-arrow-right"></i>
                                    </a>
                                    <?php if ($contratto->documento_generato_path || $contratto->documento_path): ?>
                                        <a href="<?= Url::to(['/contratto/download', 'id' => $contratto->id]) ?>"
                                           class="btn btn-sm btn-ghost-secondary" title="Download">
                                            <i class="ti ti-download"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= Url::to(['/contratto/view', 'id' => $contratto->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Vista avanzata">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
