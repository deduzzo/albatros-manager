<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $docenti */
/** @var app\models\Progetto[] $progetti */
/** @var int|null $progetto_id */

$this->title = 'Docenti da Contrattualizzare';
$this->params['breadcrumbs'][] = ['label' => 'Report', 'url' => ['/report/statistiche']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="page-pretitle">Report</span>
                <h2 class="page-title">
                    <i class="ti ti-alert-triangle me-2 text-orange"></i>
                    <?= Html::encode($this->title) ?>
                </h2>
                <div class="text-muted mt-1">
                    Docenti con lezioni in calendario ma senza un contratto attivo
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <!-- Filtro progetto -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['/report/docenti-da-contrattualizzare']) ?>" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Filtra per Progetto</label>
                        <select name="progetto_id" class="form-select">
                            <option value="">-- Tutti i progetti --</option>
                            <?php foreach ($progetti as $p): ?>
                                <option value="<?= $p->id ?>" <?= $progetto_id == $p->id ? 'selected' : '' ?>>
                                    <?= Html::encode($p->codice_progetto . ' - ' . $p->titolo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn bg-blue text-blue-fg">
                            <i class="ti ti-filter me-1"></i> Filtra
                        </button>
                        <?php if ($progetto_id): ?>
                            <a href="<?= Url::to(['/report/docenti-da-contrattualizzare']) ?>" class="btn bg-default text-default-fg ms-1">
                                <i class="ti ti-x me-1"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($docenti)): ?>
            <!-- Nessun risultato -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-circle-check text-green" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="text-green">Tutti i docenti hanno un contratto</h3>
                    <p class="text-muted">
                        Non ci sono docenti con lezioni in calendario senza un contratto attivo.
                    </p>
                </div>
            </div>
        <?php else: ?>
            <?php
            // Calcola statistiche
            $docentiUnici = count(array_unique(array_column($docenti, 'codice_fiscale')));
            $moduliSenzaContratto = count($docenti);
            $oreTotali = array_sum(array_column($docenti, 'ore_totali'));
            $compensoStimato = 0;
            foreach ($docenti as $d) {
                $compensoOrario = (float)($d['compenso_orario'] ?? 0);
                if ($compensoOrario <= 0) {
                    // Fallback su provenienza
                    $compensoOrario = ($d['provenienza'] === 'RU') ? 70 : 60;
                }
                $compensoStimato += $compensoOrario * (float)$d['ore_totali'];
            }
            ?>

            <!-- Stat cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Docenti unici</div>
                            </div>
                            <div class="h1 mb-0 mt-2"><?= $docentiUnici ?></div>
                        </div>
                        <div class="card-footer bg-orange-lt">
                            <i class="ti ti-users me-1"></i> Senza contratto
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Moduli senza contratto</div>
                            </div>
                            <div class="h1 mb-0 mt-2"><?= $moduliSenzaContratto ?></div>
                        </div>
                        <div class="card-footer bg-azure-lt">
                            <i class="ti ti-book me-1"></i> Assegnazioni mancanti
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Ore totali</div>
                            </div>
                            <div class="h1 mb-0 mt-2"><?= number_format($oreTotali, 1, ',', '.') ?></div>
                        </div>
                        <div class="card-footer bg-blue-lt">
                            <i class="ti ti-clock me-1"></i> Da contrattualizzare
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Compenso stimato</div>
                            </div>
                            <div class="h1 mb-0 mt-2">&euro; <?= number_format($compensoStimato, 2, ',', '.') ?></div>
                        </div>
                        <div class="card-footer bg-indigo-lt">
                            <i class="ti ti-currency-euro me-1"></i> Da liquidare
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabella risultati -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-alert-triangle me-2 text-orange"></i>
                        Docenti senza contratto con lezioni programmate
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Docente</th>
                                <th>Modulo</th>
                                <th>Progetto</th>
                                <th class="text-center">Lezioni</th>
                                <th class="text-center">Ore</th>
                                <th class="text-end">Compenso stimato</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($docenti as $d): ?>
                                <?php
                                $compensoOrario = (float)($d['compenso_orario'] ?? 0);
                                if ($compensoOrario <= 0) {
                                    $compensoOrario = ($d['provenienza'] === 'RU') ? 70 : 60;
                                }
                                $compensoRiga = $compensoOrario * (float)$d['ore_totali'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= Html::encode($d['cognome'] . ' ' . $d['nome']) ?></div>
                                        <div class="text-muted small"><?= Html::encode($d['codice_fiscale']) ?></div>
                                        <span class="badge <?= $d['provenienza'] === 'RU' ? 'bg-purple-lt' : 'bg-cyan-lt' ?> mt-1">
                                            <?= Html::encode($d['provenienza']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= Html::encode($d['modulo_codice']) ?></div>
                                        <div class="text-muted small"><?= Html::encode($d['modulo_titolo']) ?></div>
                                    </td>
                                    <td>
                                        <div class="text-muted small"><?= Html::encode($d['progetto_titolo']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-orange-lt"><?= (int)$d['num_lezioni'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format((float)$d['ore_totali'], 1, ',', '.') ?>
                                    </td>
                                    <td class="text-end">
                                        &euro; <?= number_format($compensoRiga, 2, ',', '.') ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= Url::to(['/assegnazione/index', 'AssegnazioneSearch[docente_codice_fiscale]' => $d['codice_fiscale']]) ?>" class="btn btn-sm bg-blue text-blue-fg">
                                            <i class="ti ti-file-plus me-1"></i> Contrattualizza
                                        </a>
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
