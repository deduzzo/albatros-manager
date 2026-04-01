<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppGridView;
use app\models\Iscrizione;

/* @var $this yii\web\View */
/* @var $avviso app\models\Avviso */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Graduatoria Studenti';
$this->params['breadcrumbs'][] = ['label' => 'Iscrizioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Candidature', 'url' => ['candidature', 'avviso_id' => $avviso->id]];
$this->params['breadcrumbs'][] = 'Graduatoria';

// Conteggi per le statistiche
$models = $dataProvider->getModels();
$totale = count($models);
$ammessi = 0;
$ammessiRiserva = 0;
$rifiutati = 0;
$daValutare = 0;
foreach ($models as $m) {
    if ($m->status === Iscrizione::STATUS_AMMESSO) $ammessi++;
    elseif ($m->status === Iscrizione::STATUS_AMMESSO_CON_RISERVA) $ammessiRiserva++;
    elseif ($m->status === Iscrizione::STATUS_NON_AMMESSO) $rifiutati++;
    else $daValutare++;
}
?>

<div class="iscrizione-graduatoria">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-list-numbers me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Corso: <strong><?= Html::encode($avviso->titolo) ?></strong>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Candidature', ['candidature', 'avviso_id' => $avviso->id], ['class' => 'btn btn-secondary']) ?>
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
                                <div class="subheader">Totale Candidature</div>
                            </div>
                            <div class="h1 mb-0"><?= $totale ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-green">Ammessi</div>
                            </div>
                            <div class="h1 mb-0 text-green"><?= $ammessi ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-lime">Con Riserva</div>
                            </div>
                            <div class="h1 mb-0 text-lime"><?= $ammessiRiserva ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-red">Non Ammessi</div>
                            </div>
                            <div class="h1 mb-0 text-red"><?= $rifiutati ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabella graduatoria -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Graduatoria per Punteggio</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-success btn-sm" id="btn-ammetti-selezionati" disabled>
                            <i class="ti ti-check me-1"></i> Ammetti Selezionati
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover" id="tabella-graduatoria">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                </th>
                                <th style="width: 50px;">Pos.</th>
                                <th>Candidato</th>
                                <th style="width: 120px;">Data Iscrizione</th>
                                <th style="width: 120px;">Punteggio</th>
                                <th style="width: 150px;">Stato</th>
                                <th style="width: 220px;">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $posizione = 1; ?>
                            <?php foreach ($dataProvider->getModels() as $model): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input row-checkbox" value="<?= $model->id ?>"
                                            <?= in_array($model->status, [Iscrizione::STATUS_AMMESSO, Iscrizione::STATUS_AMMESSO_CON_RISERVA, Iscrizione::STATUS_NON_AMMESSO]) ? 'disabled' : '' ?>>
                                    </td>
                                    <td>
                                        <strong><?= $posizione++ ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($model->allievo): ?>
                                            <?= Html::a(Html::encode($model->allievo->cognome . ' ' . $model->allievo->nome), ['/allievo/view', 'id' => $model->allievo_codice_fiscale]) ?>
                                            <br><small class="text-muted"><?= Html::encode($model->allievo_codice_fiscale) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= Yii::$app->formatter->asDate($model->data_iscrizione) ?></td>
                                    <td>
                                        <?php if ($model->punteggio_totale !== null): ?>
                                            <strong class="h3 mb-0"><?= Yii::$app->formatter->asDecimal($model->punteggio_totale, 2) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <?= Html::a('Valuta', ['valuta', 'id' => $model->id], ['class' => 'text-orange']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!in_array($model->status, [Iscrizione::STATUS_AMMESSO, Iscrizione::STATUS_NON_AMMESSO])): ?>
                                            <?= Html::a('<i class="ti ti-check"></i>', ['ammetti', 'id' => $model->id], [
                                                'class' => 'btn btn-green btn-sm',
                                                'title' => 'Ammetti',
                                                'data-bs-toggle' => 'tooltip',
                                                'data-method' => 'post',
                                                'data-confirm' => 'Confermi l\'ammissione dello studente?',
                                            ]) ?>
                                            <?= Html::a('<i class="ti ti-check"></i> R', ['ammetti-con-riserva', 'id' => $model->id], [
                                                'class' => 'btn btn-lime btn-sm',
                                                'title' => 'Ammetti con Riserva',
                                                'data-bs-toggle' => 'tooltip',
                                                'data-method' => 'post',
                                                'data-confirm' => 'Confermi l\'ammissione con riserva?',
                                            ]) ?>
                                            <button type="button" class="btn btn-red btn-sm btn-rifiuta"
                                                    data-id="<?= $model->id ?>"
                                                    data-nome="<?= Html::encode($model->allievo ? $model->allievo->cognome . ' ' . $model->allievo->nome : '#' . $model->id) ?>"
                                                    title="Rifiuta" data-bs-toggle="tooltip">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                        <?= Html::a('<i class="ti ti-star"></i>', ['valuta', 'id' => $model->id], [
                                            'class' => 'btn btn-orange btn-sm',
                                            'title' => 'Valuta',
                                            'data-bs-toggle' => 'tooltip',
                                        ]) ?>
                                        <?= Html::a('<i class="ti ti-history"></i>', ['storico', 'id' => $model->id], [
                                            'class' => 'btn btn-secondary btn-sm',
                                            'title' => 'Storico',
                                            'data-bs-toggle' => 'tooltip',
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="4" class="text-end"><strong>Totale candidature:</strong></td>
                                <td colspan="3"><strong><?= $dataProvider->totalCount ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal rifiuto -->
<div class="modal modal-blur" id="modal-rifiuta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?= Html::beginForm('', 'POST', ['id' => 'form-rifiuta']) ?>
            <div class="modal-header">
                <h5 class="modal-title">Rifiuta Candidatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Stai rifiutando la candidatura di: <strong id="rifiuta-nome"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Motivazione <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="motivo_non_ammissione" rows="3" required placeholder="Inserire la motivazione del rifiuto..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="submit" class="btn btn-danger">
                    <i class="ti ti-x me-1"></i> Conferma Rifiuto
                </button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<?php
$ammettiUrl = Url::to(['ammetti', 'id' => '__ID__']);
$rifiutaUrl = Url::to(['rifiuta', 'id' => '__ID__']);

$js = <<<JS
// Select all checkbox
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox:not(:disabled)').forEach(function(cb) {
        cb.checked = this.checked;
    }.bind(this));
    updateBulkButton();
});

// Row checkboxes
document.querySelectorAll('.row-checkbox').forEach(function(cb) {
    cb.addEventListener('change', updateBulkButton);
});

function updateBulkButton() {
    var checked = document.querySelectorAll('.row-checkbox:checked').length;
    document.getElementById('btn-ammetti-selezionati').disabled = checked === 0;
    document.getElementById('btn-ammetti-selezionati').textContent = checked > 0
        ? 'Ammetti Selezionati (' + checked + ')'
        : 'Ammetti Selezionati';
}

// Ammetti selezionati (bulk)
document.getElementById('btn-ammetti-selezionati').addEventListener('click', function() {
    var ids = [];
    document.querySelectorAll('.row-checkbox:checked').forEach(function(cb) {
        ids.push(cb.value);
    });
    if (ids.length === 0) return;
    if (!confirm('Confermi l\\'ammissione di ' + ids.length + ' candidati?')) return;

    // Invia form per ogni id in sequenza
    ids.forEach(function(id) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{$ammettiUrl}'.replace('__ID__', id);

        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = yii.getCsrfParam();
        csrf.value = yii.getCsrfToken();
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    });
});

// Rifiuta modal
document.querySelectorAll('.btn-rifiuta').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var nome = this.dataset.nome;
        document.getElementById('rifiuta-nome').textContent = nome;
        document.getElementById('form-rifiuta').action = '{$rifiutaUrl}'.replace('__ID__', id);
        new (window.bootstrap || window.tabler).Modal(document.getElementById('modal-rifiuta')).show();
    });
});
JS;
$this->registerJs($js);
?>
