<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $docente app\models\Docente */
/* @var $calendario app\models\Calendario */
/* @var $iscrizioni app\models\Iscrizione[] */
/* @var $presenzeEsistenti app\models\PresenzaAllievo[] */

$this->title = 'Presenze Lezione';
$this->params['breadcrumbs'][] = ['label' => 'Gestione Presenze', 'url' => ['/docente-area/presenze']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-clipboard-check me-2"></i> Presenze Lezione
                    </h2>
                    <div class="page-subtitle text-secondary">
                        <?= Yii::$app->formatter->asDate($calendario->data) ?>
                        — <?= Html::encode(substr($calendario->ora_inizio, 0, 5)) ?> / <?= Html::encode(substr($calendario->ora_fine, 0, 5)) ?>
                        <?php if ($calendario->modulo): ?>
                            — <?= Html::encode($calendario->modulo->titolo) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $formAction = Url::to(['/docente-area/salva-presenze', 'calendario_id' => $calendario->id]); ?>
        <form method="POST" action="<?= $formAction ?>">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>

            <!-- Sezione Docente -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user-check me-2"></i> Presenza Docente</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="mb-2 fw-semibold">
                                <?= Html::encode($docente->cognome . ' ' . $docente->nome) ?>
                            </div>
                            <div class="text-secondary small"><?= Html::encode($docente->codice_fiscale) ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Presenza</label>
                            <div class="d-flex gap-3">
                                <label class="form-check">
                                    <input class="form-check-input" type="radio" name="docente_presente" value=""
                                        <?= $calendario->docente_presente === null ? 'checked' : '' ?>>
                                    <span class="form-check-label text-secondary">N/A</span>
                                </label>
                                <label class="form-check">
                                    <input class="form-check-input" type="radio" name="docente_presente" value="1"
                                        <?= $calendario->docente_presente == 1 ? 'checked' : '' ?>>
                                    <span class="form-check-label text-success">Presente</span>
                                </label>
                                <label class="form-check">
                                    <input class="form-check-input" type="radio" name="docente_presente" value="0"
                                        <?= $calendario->docente_presente !== null && $calendario->docente_presente == 0 ? 'checked' : '' ?>>
                                    <span class="form-check-label text-danger">Assente</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ore effettive</label>
                            <input type="number" class="form-control" name="docente_ore_effettive"
                                value="<?= Html::encode($calendario->docente_ore_effettive ?? '') ?>"
                                min="0" max="24" step="0.5" placeholder="Es. <?= $calendario->ore ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sezione Studenti -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title"><i class="ti ti-users me-2"></i> Presenze Studenti</h3>
                    <span class="badge bg-blue-lt ms-2"><?= count($iscrizioni) ?> iscritti</span>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success" onclick="setAllPresente(1)">
                            <i class="ti ti-check"></i> Tutti Presenti
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="setAllPresente(0)">
                            <i class="ti ti-x"></i> Tutti Assenti
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="setAllPresente('')">
                            <i class="ti ti-minus"></i> Reset
                        </button>
                    </div>
                </div>
                <?php if (empty($iscrizioni)): ?>
                <div class="card-body text-center py-4 text-secondary">
                    Nessuno studente iscritto a questo modulo.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Studente</th>
                                <th>Codice Fiscale</th>
                                <th>Presenza</th>
                                <th style="width: 130px;">Ore presenza</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($iscrizioni as $iscrizione): ?>
                            <?php
                                $cf = $iscrizione->allievo_codice_fiscale;
                                $presenza = $presenzeEsistenti[$cf] ?? null;
                                $presenteVal = $presenza ? $presenza->presente : null;
                                $oreVal = $presenza ? $presenza->ore_presenza : null;
                                $noteVal = $presenza ? $presenza->note : null;
                            ?>
                            <tr>
                                <td>
                                    <?php if ($iscrizione->allievo): ?>
                                        <strong><?= Html::encode($iscrizione->allievo->cognome . ' ' . $iscrizione->allievo->nome) ?></strong>
                                    <?php else: ?>
                                        <span class="text-secondary"><?= Html::encode($cf) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-secondary small"><?= Html::encode($cf) ?></td>
                                <td>
                                    <div class="d-flex gap-3 presenza-radio-group">
                                        <label class="form-check mb-0">
                                            <input class="form-check-input presenza-radio" type="radio"
                                                name="studenti[<?= Html::encode($cf) ?>][presente]" value=""
                                                <?= $presenteVal === null ? 'checked' : '' ?>>
                                            <span class="form-check-label text-secondary">N/A</span>
                                        </label>
                                        <label class="form-check mb-0">
                                            <input class="form-check-input presenza-radio" type="radio"
                                                name="studenti[<?= Html::encode($cf) ?>][presente]" value="1"
                                                <?= $presenteVal == 1 ? 'checked' : '' ?>>
                                            <span class="form-check-label text-success">P</span>
                                        </label>
                                        <label class="form-check mb-0">
                                            <input class="form-check-input presenza-radio" type="radio"
                                                name="studenti[<?= Html::encode($cf) ?>][presente]" value="0"
                                                <?= ($presenteVal !== null && $presenteVal == 0) ? 'checked' : '' ?>>
                                            <span class="form-check-label text-danger">A</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm"
                                        name="studenti[<?= Html::encode($cf) ?>][ore_presenza]"
                                        value="<?= Html::encode($oreVal ?? '') ?>"
                                        min="0" max="24" step="0.5" placeholder="—">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        name="studenti[<?= Html::encode($cf) ?>][note]"
                                        value="<?= Html::encode($noteVal ?? '') ?>"
                                        placeholder="Note opzionali">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pulsanti -->
            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Salva Presenze
                </button>
                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Annulla', Url::to(['/docente-area/presenze']), ['class' => 'btn btn-secondary']) ?>
            </div>
        </form>
    </div>
</div>

<?php $this->registerJs(<<<JS
function setAllPresente(val) {
    document.querySelectorAll('.presenza-radio').forEach(function(radio) {
        if (radio.value === String(val)) {
            radio.checked = true;
        }
    });
}
JS); ?>
