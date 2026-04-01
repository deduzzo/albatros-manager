<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */
/* @var $lezioni \app\models\Calendario[] */

use yii\helpers\Html;
use yii\helpers\Url;

$statusColors = [
    'programmata' => 'bg-azure-lt text-azure',
    'completata'  => 'bg-green-lt text-green',
    'annullata'   => 'bg-red-lt text-red',
    'recupero'    => 'bg-yellow-lt text-yellow',
];
$statusLabels = [
    'programmata' => 'Programmata',
    'completata'  => 'Completata',
    'annullata'   => 'Annullata',
    'recupero'    => 'Recupero',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1" style="font-size:1.3rem; font-weight:700; color:#1e293b;">Calendario</h2>
        <p class="text-secondary mb-0" style="font-size:0.875rem;"><?= Html::encode($corso->codice_avviso . ' — ' . $corso->titolo) ?></p>
    </div>
    <a href="<?= Url::to(['/calendario/index']) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-external-link me-1"></i> Vista avanzata
    </a>
</div>

<?php if (empty($lezioni)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-calendar-off text-secondary" style="font-size:3rem;"></i>
            <h3 class="mt-3 text-secondary">Nessuna lezione</h3>
            <p class="text-muted">Non ci sono lezioni programmate per questo corso.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Modulo</th>
                        <th>Docente</th>
                        <th>Orario</th>
                        <th>Stato</th>
                        <th>Presenza Docente</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lezioni as $lezione): ?>
                        <?php
                        $modulo = $lezione->modulo;
                        $docente = $lezione->docente;
                        $dataPassata = strtotime($lezione->data) < strtotime('today');

                        // Presenza docente: null=non acquisito, 1=presente, 0=assente
                        if (isset($lezione->docente_presente)) {
                            if ($lezione->docente_presente === null || $lezione->docente_presente === '') {
                                $presenzaBadge = $dataPassata
                                    ? '<span class="badge bg-orange-lt text-orange"><i class="ti ti-clock me-1"></i>Non acquisita</span>'
                                    : '<span class="badge bg-default-lt text-default">—</span>';
                            } elseif ($lezione->docente_presente) {
                                $presenzaBadge = '<span class="badge bg-green-lt text-green"><i class="ti ti-check me-1"></i>Presente</span>';
                            } else {
                                $presenzaBadge = '<span class="badge bg-red-lt text-red"><i class="ti ti-x me-1"></i>Assente</span>';
                            }
                        } else {
                            $presenzaBadge = '<span class="badge bg-default-lt text-default">—</span>';
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= Yii::$app->formatter->asDate($lezione->data, 'php:d/m/Y') ?></div>
                            </td>
                            <td>
                                <?= $modulo ? Html::encode(mb_substr($modulo->titolo, 0, 40)) : '—' ?>
                            </td>
                            <td>
                                <?= $docente ? Html::encode($docente->cognome . ' ' . $docente->nome) : Html::encode($lezione->docente_codice_fiscale) ?>
                            </td>
                            <td>
                                <?= substr($lezione->ora_inizio, 0, 5) ?> – <?= substr($lezione->ora_fine, 0, 5) ?>
                            </td>
                            <td>
                                <span class="badge <?= $statusColors[$lezione->status] ?? 'bg-default-lt' ?>">
                                    <?= $statusLabels[$lezione->status] ?? $lezione->status ?>
                                </span>
                            </td>
                            <td><?= $presenzaBadge ?></td>
                            <td>
                                <a href="<?= Url::to(['/calendario/view', 'id' => $lezione->id]) ?>"
                                   class="btn btn-sm btn-ghost-secondary" title="Dettaglio lezione">
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
