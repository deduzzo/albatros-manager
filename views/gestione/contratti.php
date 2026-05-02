<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */
/* @var $contratti \app\models\Contratto[] */

use yii\helpers\Html;
use yii\helpers\Url;

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
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['/gestione/sezione', 'id' => $corso->id, 'section' => 'personale']) ?>" class="btn btn-sm btn-outline-primary">
            <i class="ti ti-users me-1"></i> Vai al personale
        </a>
        <a href="<?= Url::to(['/contratto/index']) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-external-link me-1"></i> Vista avanzata
        </a>
    </div>
</div>

<?php if (empty($contratti)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-file-off text-secondary" style="font-size:3rem;"></i>
            <h3 class="mt-3 text-secondary">Nessun contratto</h3>
            <p class="text-muted mb-4">Non ci sono contratti per questo corso. I contratti si generano dalla sezione Personale.</p>
            <a class="btn btn-primary" href="<?= Url::to(['/gestione/sezione', 'id' => $corso->id, 'section' => 'personale']) ?>">
                <i class="ti ti-users me-1"></i> Vai al personale per generare contratti
            </a>
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
                                <span class="badge <?= $contratto->getStatusBadgeClass() ?>">
                                    <?= Html::encode($contratto->getStatusLabel()) ?>
                                </span>
                            </td>
                            <td class="text-end fw-semibold">
                                € <?= number_format($contratto->compenso_totale, 2, ',', '.') ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= Url::to(['/contratto/view', 'id' => $contratto->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Dettaglio">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="<?= Url::to(['/contratto/update', 'id' => $contratto->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Modifica">
                                        <i class="ti ti-pencil"></i>
                                    </a>
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
                                    <a href="<?= Url::to(['/contratto/upload-firmato', 'id' => $contratto->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Carica firmato">
                                        <i class="ti ti-upload"></i>
                                    </a>
                                    <a href="<?= Url::to(['/contratto/upload-allegato', 'id' => $contratto->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Allegati">
                                        <i class="ti ti-paperclip"></i>
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
