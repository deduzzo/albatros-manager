<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */
/* @var $assegnazioni \app\models\Assegnazione[] */

use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1" style="font-size:1.3rem; font-weight:700; color:#1e293b;">Personale</h2>
        <p class="text-secondary mb-0" style="font-size:0.875rem;"><?= Html::encode($corso->codice_avviso . ' — ' . $corso->titolo) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['/gestione/nuova-candidatura', 'corso_id' => $corso->id]) ?>" class="btn btn-sm" style="background:#4263eb; color:#fff; border-color:#4263eb;">
            <i class="ti ti-user-plus me-1"></i> Nuova Candidatura
        </a>
        <a href="<?= Url::to(['/assegnazione/index', 'AssegnazioneSearch[avviso_id]' => $corso->id]) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-external-link me-1"></i> Vista avanzata
        </a>
    </div>
</div>

<?php if (empty($assegnazioni)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-users-off text-secondary" style="font-size:3rem;"></i>
            <h3 class="mt-3 text-secondary">Nessuna assegnazione</h3>
            <p class="text-muted mb-4">Non ci sono docenti assegnati per questo corso.</p>
            <a class="btn btn-primary" href="<?= Url::to(['/gestione/nuova-candidatura', 'corso_id' => $corso->id]) ?>">
                <i class="ti ti-user-plus me-1"></i> Inserisci prima candidatura
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
                                    <span class="badge <?= $contratto->getStatusBadgeClass() ?>">
                                        <?= Html::encode($contratto->getStatusLabel()) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-orange text-orange-fg">
                                        <i class="ti ti-alert-circle me-1"></i>Mancante
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= Url::to(['/assegnazione/view', 'id' => $ass->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Dettaglio assegnazione">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="<?= Url::to(['/assegnazione/update', 'id' => $ass->id]) ?>"
                                       class="btn btn-sm btn-ghost-secondary" title="Modifica assegnazione">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                    <?php if (!$contratto): ?>
                                        <a href="<?= Url::to(['/contratto/create', 'assegnazione_id' => $ass->id]) ?>"
                                           class="btn btn-sm btn-warning" title="Genera contratto">
                                            <i class="ti ti-file-plus"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= Url::to(['/contratto/view', 'id' => $contratto->id]) ?>"
                                           class="btn btn-sm btn-ghost-secondary" title="Vai al contratto">
                                            <i class="ti ti-file-text"></i>
                                        </a>
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
