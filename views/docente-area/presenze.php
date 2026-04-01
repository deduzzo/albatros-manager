<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $docente app\models\Docente */
/* @var $lezioni array */
/* @var $calendari app\models\Calendario[] */
/* @var $moduliIscritti array */

$this->title = 'Gestione Presenze';
$this->params['breadcrumbs'][] = $this->title;

$oggi = date('Y-m-d');
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-clipboard-check me-2"></i> Gestione Presenze
                    </h2>
                    <div class="page-subtitle text-secondary">
                        Registra le presenze degli studenti per le lezioni dei tuoi moduli
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-calendar me-2"></i> Le mie lezioni</h3>
            </div>
            <?php if (empty($lezioni)): ?>
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-calendar-off" style="font-size: 3rem; color: var(--tblr-secondary);"></i>
                </div>
                <h3 class="text-secondary">Nessuna lezione trovata</h3>
                <p class="text-secondary">Non sei associato a nessun modulo con lezioni programmate.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Modulo</th>
                            <th>Orario</th>
                            <th>Presenze Studenti</th>
                            <th>Presenza Docente</th>
                            <th class="w-1">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lezioni as $lezione): ?>
                        <?php
                            $isPast = $lezione['data'] < $oggi;
                            $totIscritti = $moduliIscritti[$lezione['modulo_id']] ?? 0;
                            $acquisite = (int)($lezione['presenze_acquisite'] ?? 0);
                            $mancanti = $isPast && $totIscritti > 0 && $acquisite < $totIscritti;
                            $calendario = $calendari[$lezione['id']] ?? null;
                            $docentePresente = $lezione['docente_presente'] ?? null;
                            $docentePresenteBadge = match((string)$docentePresente) {
                                '1' => 'bg-green-lt',
                                '0' => 'bg-red-lt',
                                default => 'bg-default-lt',
                            };
                            $docentePresenteLabel = match((string)$docentePresente) {
                                '1' => 'Presente',
                                '0' => 'Assente',
                                default => 'Non acquisito',
                            };
                            $rowClass = ($mancanti || ($isPast && $docentePresente === null)) ? 'table-warning' : '';
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td>
                                <strong><?= Yii::$app->formatter->asDate($lezione['data']) ?></strong>
                                <?php if ($isPast): ?>
                                    <span class="badge bg-default-lt ms-1">Passata</span>
                                <?php else: ?>
                                    <span class="badge bg-azure-lt ms-1">Futura</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($calendario && $calendario->modulo): ?>
                                    <div><?= Html::encode($calendario->modulo->titolo) ?></div>
                                    <div class="text-secondary small"><?= Html::encode($calendario->argomento ?? '') ?></div>
                                <?php else: ?>
                                    <span class="text-secondary">Modulo #<?= $lezione['modulo_id'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= Html::encode(substr($lezione['ora_inizio'], 0, 5)) ?> – <?= Html::encode(substr($lezione['ora_fine'], 0, 5)) ?>
                                <div class="text-secondary small"><?= $lezione['ore'] ?> h</div>
                            </td>
                            <td>
                                <?php if ($totIscritti > 0): ?>
                                    <span class="badge <?= $acquisite >= $totIscritti ? 'bg-green-lt' : ($isPast ? 'bg-orange-lt' : 'bg-default-lt') ?>">
                                        <?= $acquisite ?>/<?= $totIscritti ?> acquisite
                                    </span>
                                <?php else: ?>
                                    <span class="text-secondary small">Nessuno iscritto</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $docentePresenteBadge ?>">
                                    <?= $docentePresenteLabel ?>
                                </span>
                            </td>
                            <td>
                                <?= Html::a(
                                    '<i class="ti ti-edit me-1"></i> Compila',
                                    Url::to(['/docente-area/presenze-lezione', 'calendario_id' => $lezione['id']]),
                                    ['class' => 'btn btn-sm btn-primary']
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
