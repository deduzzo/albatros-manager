<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */
/* @var $iscrizioni \app\models\Iscrizione[] */

use yii\helpers\Html;
use yii\helpers\Url;

$statusColors = [
    'candidato'          => 'bg-azure-lt text-azure',
    'in_valutazione'     => 'bg-orange-lt text-orange',
    'ammesso'            => 'bg-green-lt text-green',
    'ammesso_con_riserva'=> 'bg-lime-lt text-lime',
    'non_ammesso'        => 'bg-red-lt text-red',
    'iscritto'           => 'bg-blue-lt text-blue',
    'frequentante'       => 'bg-blue-lt text-blue',
    'completato'         => 'bg-teal-lt text-teal',
    'ritirato'           => 'bg-default-lt text-default',
];
$statusLabels = [
    'candidato'          => 'Candidato',
    'in_valutazione'     => 'In Valutazione',
    'ammesso'            => 'Ammesso',
    'ammesso_con_riserva'=> 'Ammesso con Riserva',
    'non_ammesso'        => 'Non Ammesso',
    'iscritto'           => 'Iscritto',
    'frequentante'       => 'Frequentante',
    'completato'         => 'Completato',
    'ritirato'           => 'Ritirato',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1" style="font-size:1.3rem; font-weight:700; color:#1e293b;">Studenti</h2>
        <p class="text-secondary mb-0" style="font-size:0.875rem;"><?= Html::encode($corso->codice_avviso . ' — ' . $corso->titolo) ?></p>
    </div>
    <a href="<?= Url::to(['/iscrizione/candidature', 'avviso_id' => $corso->id]) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-external-link me-1"></i> Vista avanzata
    </a>
</div>

<?php if (empty($iscrizioni)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-school text-secondary" style="font-size:3rem;"></i>
            <h3 class="mt-3 text-secondary">Nessuno studente</h3>
            <p class="text-muted">Non ci sono iscrizioni per questo corso.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Studente</th>
                        <th>Codice Fiscale</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($iscrizioni as $iscrizione): ?>
                        <?php $allievo = $iscrizione->allievo; ?>
                        <tr>
                            <td>
                                <?php if ($allievo): ?>
                                    <div class="fw-semibold"><?= Html::encode($allievo->cognome . ' ' . $allievo->nome) ?></div>
                                    <?php if ($allievo->email): ?>
                                        <div class="text-muted" style="font-size:0.75rem;"><?= Html::encode($allievo->email) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code style="font-size:0.8rem;"><?= Html::encode($iscrizione->allievo_codice_fiscale) ?></code>
                            </td>
                            <td>
                                <span class="badge <?= $statusColors[$iscrizione->status] ?? 'bg-default-lt' ?>">
                                    <?= $statusLabels[$iscrizione->status] ?? $iscrizione->status ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($allievo): ?>
                                    <a href="<?= Url::to(['/allievo/view', 'cf' => $allievo->codice_fiscale]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Dettaglio studente">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= Url::to(['/iscrizione/view', 'id' => $iscrizione->id]) ?>"
                                   class="btn btn-sm btn-ghost-secondary" title="Dettaglio iscrizione">
                                    <i class="ti ti-clipboard-list"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
