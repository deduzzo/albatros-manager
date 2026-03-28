<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $contratti app\models\Contratto[] */
/* @var $docente app\models\Docente */

$this->title = 'I Miei Contratti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">

        <!-- Header -->
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-file-certificate me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-secondary mt-1">
                        Elenco dei tuoi contratti stipulati
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="row row-deck row-cards mb-4">
            <?php
            $totaleContratti = count($contratti);
            $contrattiAttivi = 0;
            $compensoTotale = 0;
            $totalePagato = 0;
            foreach ($contratti as $c) {
                if (in_array($c->status, ['attivo', 'firmato'])) {
                    $contrattiAttivi++;
                }
                $compensoTotale += $c->compenso_totale;
                $totalePagato += $c->getTotalePagato();
            }
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Totale Contratti</div>
                        </div>
                        <div class="h1 mb-0 mt-2"><?= $totaleContratti ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Contratti Attivi</div>
                        </div>
                        <div class="h1 mb-0 mt-2 text-green"><?= $contrattiAttivi ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Compenso Totale</div>
                        </div>
                        <div class="h1 mb-0 mt-2"><?= Yii::$app->formatter->asCurrency($compensoTotale) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Totale Pagato</div>
                        </div>
                        <div class="h1 mb-0 mt-2 text-blue"><?= Yii::$app->formatter->asCurrency($totalePagato) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($contratti)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="ti ti-file-certificate" style="font-size: 3rem;"></i>
                        </div>
                        <p class="empty-title">Nessun contratto</p>
                        <p class="empty-subtitle text-secondary">
                            Non hai ancora contratti stipulati.
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-list me-2"></i>
                        Contratti (<?= $totaleContratti ?>)
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover card-table">
                        <thead>
                            <tr>
                                <th>Modulo / Progetto</th>
                                <th>N. Contratto</th>
                                <th>Tipo</th>
                                <th>Data Stipula</th>
                                <th>Compenso</th>
                                <th>Pagato</th>
                                <th>Stato</th>
                                <th>Documenti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contratti as $contratto): ?>
                                <?php
                                $assegnazione = $contratto->assegnazione;
                                $modulo = $assegnazione ? $assegnazione->modulo : null;
                                $avviso = $modulo ? $modulo->avviso : null;
                                $pagato = $contratto->getTotalePagato();
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= Html::encode($modulo ? $modulo->titolo : 'N/D') ?></div>
                                        <?php if ($avviso): ?>
                                            <div class="text-secondary small"><?= Html::encode($avviso->titolo) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($contratto->numero_contratto): ?>
                                            <span class="badge bg-blue-lt"><?= Html::encode($contratto->numero_contratto) ?></span>
                                        <?php else: ?>
                                            <span class="text-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= Html::encode($contratto->getTipoLabel()) ?></td>
                                    <td><?= Yii::$app->formatter->asDate($contratto->data_stipula) ?></td>
                                    <td>
                                        <div><?= Yii::$app->formatter->asCurrency($contratto->compenso_totale) ?></div>
                                        <div class="text-secondary small">
                                            <?= $contratto->ore_contrattualizzate ?> ore x <?= Yii::$app->formatter->asCurrency($contratto->compenso_orario) ?>/h
                                        </div>
                                    </td>
                                    <td>
                                        <?= Yii::$app->formatter->asCurrency($pagato) ?>
                                        <?php if ($contratto->compenso_totale > 0): ?>
                                            <?php $percentuale = round(($pagato / $contratto->compenso_totale) * 100); ?>
                                            <div class="progress progress-sm mt-1" style="width: 80px;">
                                                <div class="progress-bar bg-green" style="width: <?= $percentuale ?>%"></div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $contratto->getStatusBadgeClass() ?>">
                                            <?= Html::encode($contratto->getStatusLabel()) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-list">
                                            <?php if ($contratto->hasDocumentoGenerato()): ?>
                                                <a href="<?= Url::to(['/docente-area/contratto-download', 'id' => $contratto->id, 'tipo' => 'generato']) ?>"
                                                   class="btn btn-sm btn-outline-blue"
                                                   title="Scarica Contratto"
                                                   data-bs-toggle="tooltip">
                                                    <i class="ti ti-download"></i>
                                                    <span class="d-none d-lg-inline">Contratto</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($contratto->documento_path)): ?>
                                                <a href="<?= Url::to(['/docente-area/contratto-download', 'id' => $contratto->id, 'tipo' => 'firmato']) ?>"
                                                   class="btn btn-sm btn-outline-green"
                                                   title="Scarica Contratto Firmato"
                                                   data-bs-toggle="tooltip">
                                                    <i class="ti ti-certificate"></i>
                                                    <span class="d-none d-lg-inline">Firmato</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!$contratto->hasDocumentoGenerato() && empty($contratto->documento_path)): ?>
                                                <span class="text-secondary small">Nessun documento</span>
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

    </div>
</div>
