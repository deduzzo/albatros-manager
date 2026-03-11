<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $rimborsi app\models\RimborsoSpesa[] */
/* @var $assegnazioni app\models\Assegnazione[] */

$this->title = 'I Miei Rimborsi Spesa';
$this->params['breadcrumbs'][] = $this->title;

$totaleRichiesto = 0;
$totaleApprovato = 0;
foreach ($rimborsi as $r) {
    if ($r->status === 'approvato') {
        $totaleApprovato += $r->importo;
    }
    $totaleRichiesto += $r->importo;
}
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-receipt-2 me-2"></i> <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="page-subtitle text-secondary">
                        Gestisci le tue richieste di rimborso spese
                    </div>
                </div>
                <div class="col-auto">
                    <?php if (!empty($assegnazioni)): ?>
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ti ti-plus me-1"></i> Nuovo Rimborso
                            </button>
                            <div class="dropdown-menu">
                                <?php foreach ($assegnazioni as $ass): ?>
                                    <?php $modulo = $ass->modulo; ?>
                                    <?= Html::a(
                                        '<span class="badge bg-azure-lt me-1">' . Html::encode($modulo->codice) . '</span> ' . Html::encode($modulo->titolo),
                                        ['nuovo-rimborso', 'assegnazione_id' => $ass->id],
                                        ['class' => 'dropdown-item']
                                    ) ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Totale richieste</div>
                        <div class="h1 mb-0"><?= count($rimborsi) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Importo totale</div>
                        <div class="h1 mb-0"><?= Yii::$app->formatter->asCurrency($totaleRichiesto, 'EUR') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Totale approvato</div>
                        <div class="h1 mb-0 text-success"><?= Yii::$app->formatter->asCurrency($totaleApprovato, 'EUR') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista Rimborsi -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-list me-2"></i> Elenco Rimborsi</h3>
            </div>
            <?php if (empty($rimborsi)): ?>
                <div class="card-body">
                    <div class="text-center py-4 text-muted">
                        <i class="ti ti-receipt-off" style="font-size: 3rem;"></i>
                        <p class="mt-2">Non hai ancora richiesto rimborsi spesa.</p>
                        <?php if (!empty($assegnazioni)): ?>
                            <p>Seleziona un'assegnazione per richiedere il primo rimborso.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Modulo</th>
                                <th>Tipo</th>
                                <th>Descrizione</th>
                                <th class="text-end">Importo</th>
                                <th>Stato</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rimborsi as $rimborso): ?>
                                <?php $modulo = $rimborso->assegnazione ? $rimborso->assegnazione->modulo : null; ?>
                                <tr>
                                    <td><?= Yii::$app->formatter->asDate($rimborso->data_spesa) ?></td>
                                    <td>
                                        <?php if ($modulo): ?>
                                            <span class="badge bg-azure-lt"><?= Html::encode($modulo->codice) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $rimborso->getTipoBadge() ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            <?= Html::encode($rimborso->descrizione) ?>
                                        </div>
                                    </td>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($rimborso->importo, 'EUR') ?></td>
                                    <td><?= $rimborso->getStatusBadge() ?></td>
                                    <td>
                                        <?php if ($rimborso->isModificabile()): ?>
                                            <?= Html::a('<i class="ti ti-edit"></i>', ['modifica-rimborso', 'id' => $rimborso->id], [
                                                'class' => 'btn btn-sm btn-primary',
                                                'title' => 'Modifica',
                                                'data-bs-toggle' => 'tooltip',
                                            ]) ?>
                                        <?php endif; ?>
                                        <?php if ($rimborso->motivo_rifiuto): ?>
                                            <span class="ms-1" data-bs-toggle="tooltip" title="<?= Html::encode($rimborso->motivo_rifiuto) ?>">
                                                <i class="ti ti-info-circle text-red"></i>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($rimborso->allegato_path): ?>
                                            <?= Html::a('<i class="ti ti-paperclip"></i>', Yii::getAlias('@web/' . $rimborso->allegato_path), [
                                                'class' => 'btn btn-sm btn-outline-blue ms-1',
                                                'title' => 'Visualizza allegato',
                                                'target' => '_blank',
                                                'data-bs-toggle' => 'tooltip',
                                            ]) ?>
                                        <?php endif; ?>
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
