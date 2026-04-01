<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Assegnazione;

/* @var $this yii\web\View */
/* @var $corso app\models\Avviso */
/* @var $moduli app\models\Modulo[] */
/* @var $docenti app\models\Docente[] */

$this->title = 'Nuova Candidatura - ' . $corso->titolo;
?>

<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Nuova Candidatura Multi-modulo</h2>
                <div class="text-muted mt-1">
                    <i class="ti ti-school me-1"></i><?= Html::encode($corso->codice_avviso . ' — ' . $corso->titolo) ?>
                </div>
            </div>
            <div class="col-auto ms-auto">
                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna al Piano Formativo',
                    ['/gestione/sezione', 'id' => $corso->id, 'section' => 'piano-formativo'],
                    ['class' => 'btn btn-ghost-default']) ?>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible mb-4" role="alert">
                <i class="ti ti-alert-circle me-2"></i>
                <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

            <!-- Selezione Docente -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user me-2"></i>Seleziona Docente</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <label class="form-label required">Docente</label>
                            <select name="docente_codice_fiscale" class="form-select tom-select" required>
                                <option value="">-- Seleziona docente --</option>
                                <?php foreach ($docenti as $doc): ?>
                                    <option value="<?= Html::encode($doc->codice_fiscale) ?>">
                                        <?= Html::encode($doc->cognome . ' ' . $doc->nome . ' (' . $doc->codice_fiscale . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-hint">Cerca per cognome, nome o codice fiscale.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selezione Moduli -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-books me-2"></i>Seleziona Moduli</h3>
                    <div class="card-actions">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" id="select-all-moduli">
                            <span class="form-check-label fw-medium">Seleziona tutti</span>
                        </label>
                    </div>
                </div>
                <?php if (empty($moduli)): ?>
                    <div class="card-body text-muted text-center py-4">
                        <i class="ti ti-inbox fs-2 d-block mb-2"></i>
                        Nessun modulo disponibile per questo corso.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th class="w-1"></th>
                                    <th>Codice</th>
                                    <th>Modulo</th>
                                    <th>Ore</th>
                                    <th>Compenso/h</th>
                                    <th>Stato assegnazione</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($moduli as $m): ?>
                                    <?php
                                    $assegnato = Assegnazione::find()
                                        ->where(['modulo_id' => $m->id, 'deleted_at' => null])
                                        ->exists();
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                   class="form-check-input modulo-cb"
                                                   name="moduli_ids[]"
                                                   value="<?= $m->id ?>">
                                        </td>
                                        <td>
                                            <span class="badge bg-blue-lt"><?= Html::encode($m->codice) ?></span>
                                        </td>
                                        <td><?= Html::encode($m->titolo) ?></td>
                                        <td><?= $m->ore_totali ?> h</td>
                                        <td>
                                            <?= $m->compenso_orario
                                                ? '€&nbsp;' . number_format($m->compenso_orario, 2, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td>
                                            <?= $assegnato
                                                ? '<span class="badge bg-green-lt">Assegnato</span>'
                                                : '<span class="badge bg-default-lt">Da assegnare</span>' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="card-footer">
                    <button type="submit" class="btn btn-indigo" id="btn-submit" <?= empty($moduli) ? 'disabled' : '' ?>>
                        <i class="ti ti-send me-1"></i> Inserisci Candidatura
                    </button>
                    <?= Html::a('<i class="ti ti-x me-1"></i> Annulla',
                        ['/gestione/sezione', 'id' => $corso->id, 'section' => 'piano-formativo'],
                        ['class' => 'btn btn-ghost-default ms-2']) ?>
                </div>
            </div>
        </form>

    </div>
</div>

<?php
$js = <<<JS
// Seleziona/deseleziona tutti
document.getElementById('select-all-moduli').addEventListener('change', function () {
    document.querySelectorAll('.modulo-cb').forEach(function (cb) {
        cb.checked = this.checked;
    }, this);
});

// Sincronizza il checkbox "seleziona tutti" quando si clicca sui singoli
document.querySelectorAll('.modulo-cb').forEach(function (cb) {
    cb.addEventListener('change', function () {
        var all = document.querySelectorAll('.modulo-cb');
        var checked = document.querySelectorAll('.modulo-cb:checked');
        document.getElementById('select-all-moduli').checked = all.length === checked.length;
        document.getElementById('select-all-moduli').indeterminate = checked.length > 0 && checked.length < all.length;
    });
});
JS;
$this->registerJs($js);
?>
