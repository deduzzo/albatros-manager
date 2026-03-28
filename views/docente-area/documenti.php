<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $richieste app\models\RichiestaFatturazione[] */
/* @var $docente app\models\Docente */

$this->title = 'Documenti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">

        <!-- Header -->
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-file-invoice me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-secondary mt-1">
                        Fatture e cedolini relativi ai tuoi contratti
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="row row-deck row-cards mb-4">
            <?php
            $totaleFatture = 0;
            $totaleCedolini = 0;
            $importoTotale = 0;
            $documentiRicevuti = 0;
            foreach ($richieste as $r) {
                if ($r->tipo === 'fattura') {
                    $totaleFatture++;
                } else {
                    $totaleCedolini++;
                }
                $importoTotale += $r->importo;
                if ($r->hasDocumentoRicevuto()) {
                    $documentiRicevuti++;
                }
            }
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Totale Richieste</div>
                        </div>
                        <div class="h1 mb-0 mt-2"><?= count($richieste) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Fatture / Cedolini</div>
                        </div>
                        <div class="h1 mb-0 mt-2">
                            <span class="text-blue"><?= $totaleFatture ?></span>
                            /
                            <span class="text-purple"><?= $totaleCedolini ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Importo Totale</div>
                        </div>
                        <div class="h1 mb-0 mt-2"><?= Yii::$app->formatter->asCurrency($importoTotale) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Documenti Ricevuti</div>
                        </div>
                        <div class="h1 mb-0 mt-2 text-green"><?= $documentiRicevuti ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($richieste)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="ti ti-file-invoice" style="font-size: 3rem;"></i>
                        </div>
                        <p class="empty-title">Nessun documento</p>
                        <p class="empty-subtitle text-secondary">
                            Non ci sono ancora fatture o cedolini relativi ai tuoi contratti.
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-list me-2"></i>
                        Fatture e Cedolini (<?= count($richieste) ?>)
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover card-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Modulo</th>
                                <th>Periodo</th>
                                <th>Importo</th>
                                <th>Data Richiesta</th>
                                <th>Scadenza</th>
                                <th>Stato</th>
                                <th>Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($richieste as $richiesta): ?>
                                <?php
                                $contratto = $richiesta->contratto;
                                $assegnazione = $contratto ? $contratto->assegnazione : null;
                                $modulo = $assegnazione ? $assegnazione->modulo : null;
                                ?>
                                <tr>
                                    <td><?= $richiesta->getTipoBadge() ?></td>
                                    <td>
                                        <div class="fw-bold"><?= Html::encode($modulo ? $modulo->titolo : 'N/D') ?></div>
                                        <?php if ($contratto && $contratto->numero_contratto): ?>
                                            <div class="text-secondary small">
                                                Contratto: <?= Html::encode($contratto->numero_contratto) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $richiesta->periodo_riferimento ? Html::encode($richiesta->periodo_riferimento) : '<span class="text-secondary">-</span>' ?>
                                    </td>
                                    <td class="fw-bold"><?= Yii::$app->formatter->asCurrency($richiesta->importo) ?></td>
                                    <td><?= Yii::$app->formatter->asDate($richiesta->data_richiesta) ?></td>
                                    <td>
                                        <?php if ($richiesta->data_scadenza): ?>
                                            <?php
                                            $scadenza = new DateTime($richiesta->data_scadenza);
                                            $oggi = new DateTime();
                                            $isScaduta = $scadenza < $oggi && $richiesta->status !== 'verificata' && $richiesta->status !== 'ricevuta';
                                            ?>
                                            <span class="<?= $isScaduta ? 'text-red fw-bold' : '' ?>">
                                                <?= Yii::$app->formatter->asDate($richiesta->data_scadenza) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $richiesta->getStatusBadge() ?></td>
                                    <td>
                                        <?php if ($richiesta->hasDocumentoRicevuto()): ?>
                                            <a href="<?= Url::to('@web/' . $richiesta->documento_ricevuto_path) ?>"
                                               class="btn btn-sm btn-outline-green"
                                               title="Scarica Documento"
                                               data-bs-toggle="tooltip"
                                               target="_blank">
                                                <i class="ti ti-download"></i>
                                                <span class="d-none d-lg-inline">Scarica</span>
                                            </a>
                                        <?php else: ?>
                                            <?php if ($richiesta->status === 'inviata'): ?>
                                                <span class="badge bg-orange-lt">In attesa</span>
                                            <?php else: ?>
                                                <span class="text-secondary small">-</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
