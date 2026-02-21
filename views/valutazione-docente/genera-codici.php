<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Modulo $modulo */
/** @var array $codiciGenerati */
/** @var app\models\ValutazioneCodice[] $codiciEsistenti */

$this->title = 'Genera Codici Valutazione';
$this->params['breadcrumbs'][] = ['label' => 'Valutazioni Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Modulo: ' . $modulo->codice, 'url' => ['/modulo/view', 'id' => $modulo->id]];
$this->params['breadcrumbs'][] = $this->title;

$baseUrl = Yii::$app->request->hostInfo;

$nonUsati = 0;
$usati = 0;
foreach ($codiciEsistenti as $c) {
    if ($c->isUsato()) {
        $usati++;
    } else {
        $nonUsati++;
    }
}
?>

<div class="valutazione-docente-genera-codici">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-qrcode me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($modulo->codice . ' - ' . $modulo->titolo) ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?php if ($nonUsati > 0): ?>
                    <?= Html::a(
                        '<i class="ti ti-printer me-1"></i> Stampa Codici',
                        ['stampa-codici', 'modulo_id' => $modulo->id],
                        ['class' => 'btn btn-primary', 'target' => '_blank']
                    ) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Statistiche -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Totale Codici</div>
                            </div>
                            <div class="h1 mb-0"><?= count($codiciEsistenti) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Disponibili</div>
                            </div>
                            <div class="h1 mb-0 text-green"><?= $nonUsati ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Utilizzati</div>
                            </div>
                            <div class="h1 mb-0 text-blue"><?= $usati ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Tasso Risposta</div>
                            </div>
                            <div class="h1 mb-0">
                                <?= count($codiciEsistenti) > 0 ? round(($usati / count($codiciEsistenti)) * 100) : 0 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Form generazione -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-plus me-1"></i>Genera Nuovi Codici</h3>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                                <div class="mb-3">
                                    <label class="form-label">Numero di codici da generare</label>
                                    <input type="number" name="quantita" class="form-control" value="20" min="1" max="500">
                                    <small class="form-hint">Ogni codice puo essere usato una sola volta</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-sparkles me-1"></i>Genera Codici
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Lista codici -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-list me-1"></i>Codici Generati</h3>
                        </div>
                        <?php if (empty($codiciEsistenti)): ?>
                        <div class="card-body">
                            <div class="empty">
                                <div class="empty-icon"><i class="ti ti-qrcode" style="font-size: 3rem;"></i></div>
                                <p class="empty-title">Nessun codice generato</p>
                                <p class="empty-subtitle text-muted">
                                    Genera dei codici per permettere agli studenti di valutare il docente in modo anonimo.
                                </p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Codice</th>
                                        <th>Link</th>
                                        <th>Stato</th>
                                        <th>Creato il</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($codiciEsistenti as $codice): ?>
                                    <tr>
                                        <td>
                                            <code style="font-size: 1.1em; letter-spacing: 2px;"><?= Html::encode($codice->codice) ?></code>
                                        </td>
                                        <td>
                                            <?php if (!$codice->isUsato()): ?>
                                            <a href="<?= $baseUrl ?>/valutazione/<?= Html::encode($codice->codice) ?>" target="_blank" class="text-muted small">
                                                <i class="ti ti-external-link"></i> Apri
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($codice->isUsato()): ?>
                                            <span class="badge bg-blue text-blue-fg"><i class="ti ti-check me-1"></i>Utilizzato</span>
                                            <?php else: ?>
                                            <span class="badge bg-green text-green-fg">Disponibile</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?= Yii::$app->formatter->asDatetime($codice->created_at) ?>
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
        </div>
    </div>
</div>
