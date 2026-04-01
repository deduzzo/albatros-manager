<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $iscrizione app\models\Iscrizione */
/* @var $criteri app\models\CriterioValutazioneStudente[] */
/* @var $punteggiEsistenti array [criterio_id => PunteggioIscrizione] */

$this->title = 'Valutazione Candidatura';
$this->params['breadcrumbs'][] = ['label' => 'Iscrizioni', 'url' => ['index']];
if ($iscrizione->avviso_id) {
    $this->params['breadcrumbs'][] = ['label' => 'Candidature', 'url' => ['candidature', 'avviso_id' => $iscrizione->avviso_id]];
}
$this->params['breadcrumbs'][] = 'Valuta #' . $iscrizione->id;
?>

<div class="iscrizione-valuta">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-star me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Candidato: <strong><?= $iscrizione->allievo ? Html::encode($iscrizione->allievo->cognome . ' ' . $iscrizione->allievo->nome) : '-' ?></strong>
                        <?php if ($iscrizione->avviso): ?>
                            | Corso: <strong><?= Html::encode($iscrizione->avviso->titolo) ?></strong>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Info candidato -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user me-2"></i>Dati Candidato</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nome Completo</div>
                            <div class="datagrid-content"><?= $iscrizione->allievo ? Html::encode($iscrizione->allievo->cognome . ' ' . $iscrizione->allievo->nome) : '-' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Codice Fiscale</div>
                            <div class="datagrid-content"><code><?= Html::encode($iscrizione->allievo_codice_fiscale) ?></code></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Data Iscrizione</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDate($iscrizione->data_iscrizione) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Stato Attuale</div>
                            <div class="datagrid-content"><span class="badge <?= $iscrizione->getStatusBadgeClass() ?>"><?= Html::encode($iscrizione->getStatusLabel()) ?></span></div>
                        </div>
                        <?php if ($iscrizione->titolo_studio): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Titolo di Studio</div>
                            <div class="datagrid-content"><?= Html::encode(Iscrizione::getTitoloStudioLabels()[$iscrizione->titolo_studio] ?? $iscrizione->titolo_studio) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($iscrizione->stato_occupazionale): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Stato Occupazionale</div>
                            <div class="datagrid-content"><?= Html::encode(Iscrizione::getStatoOccupazionaleLabels()[$iscrizione->stato_occupazionale] ?? $iscrizione->stato_occupazionale) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form punteggi -->
            <?= Html::beginForm(['valuta', 'id' => $iscrizione->id], 'POST') ?>

            <?php if (empty($criteri)): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-star-off" style="font-size: 3rem;"></i></div>
                            <p class="empty-title">Nessun criterio di valutazione</p>
                            <p class="empty-subtitle text-muted">
                                Configura i criteri di valutazione in
                                <?= Html::a('Configurazione > Criteri Valutazione', ['/criterio-valutazione/index']) ?>.
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-star me-2"></i>Punteggi per Criterio</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Criterio</th>
                                    <th style="width: 20%;">Descrizione</th>
                                    <th style="width: 10%;">Range</th>
                                    <th style="width: 8%;">Peso</th>
                                    <th style="width: 12%;">Punteggio</th>
                                    <th style="width: 20%;">Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criteri as $criterio): ?>
                                    <?php
                                        $punteggio = $punteggiEsistenti[$criterio->id] ?? null;
                                        $valoreCorrente = $punteggio ? $punteggio->punteggio : '';
                                        $noteCorrente = $punteggio ? $punteggio->note : '';
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= Html::encode($criterio->nome) ?></strong>
                                            <?php if ($criterio->isGlobale()): ?>
                                                <span class="badge bg-azure-lt ms-1">Globale</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small"><?= Html::encode($criterio->descrizione ?: '-') ?></td>
                                        <td>
                                            <span class="badge bg-blue-lt"><?= $criterio->punteggio_min ?> - <?= $criterio->punteggio_max ?></span>
                                        </td>
                                        <td>
                                            <strong><?= Yii::$app->formatter->asDecimal($criterio->peso, 2) ?></strong>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control form-control-sm punteggio-input"
                                                   name="Punteggio[<?= $criterio->id ?>]"
                                                   value="<?= Html::encode($valoreCorrente) ?>"
                                                   min="<?= $criterio->punteggio_min ?>"
                                                   max="<?= $criterio->punteggio_max ?>"
                                                   step="0.1"
                                                   data-peso="<?= $criterio->peso ?>"
                                                   data-min="<?= $criterio->punteggio_min ?>"
                                                   data-max="<?= $criterio->punteggio_max ?>"
                                                   placeholder="<?= $criterio->punteggio_min ?>-<?= $criterio->punteggio_max ?>">
                                        </td>
                                        <td>
                                            <textarea class="form-control form-control-sm"
                                                      name="PunteggioNote[<?= $criterio->id ?>]"
                                                      rows="1"
                                                      placeholder="Note..."><?= Html::encode($noteCorrente) ?></textarea>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td colspan="4" class="text-end"><strong>Punteggio Totale Pesato:</strong></td>
                                    <td colspan="2">
                                        <span id="punteggio-totale-live" class="h3 mb-0 text-primary">
                                            <?= $iscrizione->punteggio_totale !== null ? Yii::$app->formatter->asDecimal($iscrizione->punteggio_totale, 2) : '0,00' ?>
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-footer text-end">
                        <?php if ($iscrizione->avviso_id): ?>
                            <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['candidature', 'avviso_id' => $iscrizione->avviso_id], ['class' => 'btn btn-secondary']) ?>
                        <?php else: ?>
                            <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['view', 'id' => $iscrizione->id], ['class' => 'btn btn-secondary']) ?>
                        <?php endif; ?>
                        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva Punteggi', ['class' => 'btn btn-success ms-2']) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<?php
// JavaScript per calcolo live punteggio totale
$js = <<<JS
document.querySelectorAll('.punteggio-input').forEach(function(input) {
    input.addEventListener('input', function() {
        calcolaPunteggioTotale();
    });
});

function calcolaPunteggioTotale() {
    var totale = 0;
    document.querySelectorAll('.punteggio-input').forEach(function(input) {
        var valore = parseFloat(input.value) || 0;
        var peso = parseFloat(input.dataset.peso) || 0;
        var min = parseFloat(input.dataset.min) || 0;
        var max = parseFloat(input.dataset.max) || 10;

        // Clamp al range
        if (valore < min) valore = min;
        if (valore > max) valore = max;

        totale += valore * peso;
    });

    var formattato = totale.toFixed(2).replace('.', ',');
    document.getElementById('punteggio-totale-live').textContent = formattato;
}
JS;
$this->registerJs($js);
?>
