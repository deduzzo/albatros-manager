<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\Pagamento;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PagamentoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Pagamenti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="pagamento-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-currency-euro me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione pagamenti docenti
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::button('<i class="ti ti-cash-banknote me-1"></i> Liquidazione Massiva', [
                        'class' => 'btn btn-primary me-2',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#modal-liquidazione-massiva',
                        'id' => 'btn-liquidazione-massiva',
                    ]) ?>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Pagamento', ['create'], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <?= AppGridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'headerOptions' => ['style' => 'width: 70px;'],
                        ],
                        [
                            'attribute' => 'docente_nome',
                            'label' => 'Docente',
                            'format' => 'raw',
                            'content' => function($model) {
                                $contratto = $model->contratto;
                                $assegnazione = $contratto ? $contratto->assegnazione : null;
                                $docente = $assegnazione ? $assegnazione->docente : null;
                                if (!$docente) return '-';
                                return Html::a(
                                    Html::encode($docente->cognome . ' ' . $docente->nome),
                                    ['/docente/view', 'id' => $docente->codice_fiscale],
                                    ['class' => 'text-reset']
                                );
                            },
                        ],
                        [
                            'attribute' => 'numero_contratto',
                            'label' => 'N. Contratto',
                            'format' => 'raw',
                            'content' => function($model) {
                                if (!$model->contratto) return '-';
                                $label = $model->contratto->numero_contratto ?: '#' . $model->contratto_id;
                                return Html::a(
                                    '<code>' . Html::encode($label) . '</code>',
                                    ['/contratto/view', 'id' => $model->contratto_id],
                                    ['class' => 'text-reset']
                                );
                            },
                        ],
                        [
                            'attribute' => 'tipo',
                            'format' => 'raw',
                            'filter' => Pagamento::getTipoLabels(),
                            'headerOptions' => ['style' => 'width: 110px;'],
                            'content' => function($model) {
                                return '<span class="badge bg-blue-lt">' . Html::encode($model->getTipoLabel()) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'importo_lordo',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'text-end'],
                            'content' => function($model) {
                                return Yii::$app->formatter->asCurrency($model->importo_lordo, 'EUR');
                            },
                        ],
                        [
                            'attribute' => 'importo_netto',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'text-end'],
                            'content' => function($model) {
                                return Yii::$app->formatter->asCurrency($model->importo_netto, 'EUR');
                            },
                        ],
                        [
                            'attribute' => 'data_pagamento',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => Pagamento::getStatusLabels(),
                            'content' => function($model) {
                                return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                            },
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> pagamenti
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Liquidazione Massiva -->
<div class="modal modal-blur fade" id="modal-liquidazione-massiva" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-cash-banknote me-2"></i>Liquidazione Massiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <!-- Loading -->
                <div id="lm-loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Caricamento contratti con saldo...</p>
                </div>

                <!-- Contenuto -->
                <div id="lm-content" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label required">Tipo Pagamento</label>
                        <select id="lm-tipo" class="form-select">
                            <option value="saldo">Saldo</option>
                            <option value="acconto">Acconto</option>
                            <option value="rata">Rata</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted" id="lm-count-label">0 contratti trovati</span>
                        <div>
                            <button class="btn btn-sm btn-ghost-primary" id="lm-select-all">Seleziona tutti</button>
                            <button class="btn btn-sm btn-ghost-secondary" id="lm-deselect-all">Deseleziona</button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-vcenter table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 30px;"><input type="checkbox" id="lm-check-all" class="form-check-input"></th>
                                    <th>Contratto</th>
                                    <th>Docente</th>
                                    <th>Modulo</th>
                                    <th class="text-end">Compenso</th>
                                    <th class="text-end">Pagato</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody id="lm-tbody"></tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0" id="lm-riepilogo" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong id="lm-selected-count">0</strong> contratti selezionati</span>
                            <span>Totale saldo: <strong id="lm-selected-total">&euro; 0,00</strong></span>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div id="lm-empty" class="text-center py-4" style="display: none;">
                        <i class="ti ti-check d-block mb-2" style="font-size: 2rem; color: green;"></i>
                        <p class="text-muted mb-0">Tutti i contratti sono stati liquidati. Nessun saldo residuo.</p>
                    </div>
                </div>

                <!-- Risultato -->
                <div id="lm-result" style="display: none;">
                    <div class="text-center py-3">
                        <i class="ti ti-circle-check d-block mb-2" style="font-size: 3rem; color: green;"></i>
                        <h3 id="lm-result-title">Liquidazione completata</h3>
                        <p class="text-muted" id="lm-result-detail"></p>
                    </div>
                    <div id="lm-result-errors" class="alert alert-warning mt-2" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="lm-btn-close">
                    <i class="ti ti-x me-1"></i> Chiudi
                </button>
                <button type="button" class="btn btn-primary" id="lm-btn-esegui" disabled>
                    <i class="ti ti-cash-banknote me-1"></i> Esegui Liquidazione
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$contrattiUrl = \yii\helpers\Url::to(['contratti-con-saldo']);
$liquidaUrl = \yii\helpers\Url::to(['liquida-massiva']);
$csrfToken = Yii::$app->request->csrfToken;
$js = <<<JS
(function() {
    var modal = document.getElementById('modal-liquidazione-massiva');
    var tbody = document.getElementById('lm-tbody');
    var loading = document.getElementById('lm-loading');
    var content = document.getElementById('lm-content');
    var result = document.getElementById('lm-result');
    var emptyState = document.getElementById('lm-empty');
    var btnEsegui = document.getElementById('lm-btn-esegui');
    var contrattiData = [];

    // Carica contratti quando si apre il modal
    modal.addEventListener('show.bs.modal', function() {
        loading.style.display = 'block';
        content.style.display = 'none';
        result.style.display = 'none';
        btnEsegui.style.display = '';
        btnEsegui.disabled = true;

        fetch('{$contrattiUrl}', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            contrattiData = data;
            loading.style.display = 'none';
            content.style.display = 'block';

            if (data.length === 0) {
                emptyState.style.display = 'block';
                document.querySelector('.table-responsive').style.display = 'none';
                document.getElementById('lm-riepilogo').style.display = 'none';
                return;
            }

            emptyState.style.display = 'none';
            document.querySelector('#modal-liquidazione-massiva .table-responsive').style.display = '';
            document.getElementById('lm-count-label').textContent = data.length + ' contratti trovati';

            tbody.innerHTML = '';
            data.forEach(function(c) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><input type="checkbox" class="form-check-input lm-check" data-id="' + c.id + '" data-saldo="' + c.saldo_rimanente + '"></td>' +
                    '<td><code>' + escapeHtml(c.numero_contratto) + '</code></td>' +
                    '<td>' + escapeHtml(c.docente) + '</td>' +
                    '<td><span class="badge bg-blue-lt">' + escapeHtml(c.modulo) + '</span></td>' +
                    '<td class="text-end">&euro; ' + formatNum(c.compenso_totale) + '</td>' +
                    '<td class="text-end">&euro; ' + formatNum(c.totale_pagato) + '</td>' +
                    '<td class="text-end fw-bold">&euro; ' + formatNum(c.saldo_rimanente) + '</td>';
                tbody.appendChild(tr);
            });
            updateRiepilogo();
        });
    });

    // Seleziona/Deseleziona tutti
    document.getElementById('lm-select-all').addEventListener('click', function() {
        tbody.querySelectorAll('.lm-check').forEach(function(cb) { cb.checked = true; });
        document.getElementById('lm-check-all').checked = true;
        updateRiepilogo();
    });
    document.getElementById('lm-deselect-all').addEventListener('click', function() {
        tbody.querySelectorAll('.lm-check').forEach(function(cb) { cb.checked = false; });
        document.getElementById('lm-check-all').checked = false;
        updateRiepilogo();
    });
    document.getElementById('lm-check-all').addEventListener('change', function() {
        var checked = this.checked;
        tbody.querySelectorAll('.lm-check').forEach(function(cb) { cb.checked = checked; });
        updateRiepilogo();
    });
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('lm-check')) updateRiepilogo();
    });

    function updateRiepilogo() {
        var checked = tbody.querySelectorAll('.lm-check:checked');
        var totale = 0;
        checked.forEach(function(cb) { totale += parseFloat(cb.dataset.saldo); });

        var riepilogo = document.getElementById('lm-riepilogo');
        document.getElementById('lm-selected-count').textContent = checked.length;
        document.getElementById('lm-selected-total').textContent = '\u20AC ' + formatNum(totale);
        riepilogo.style.display = checked.length > 0 ? 'block' : 'none';
        btnEsegui.disabled = checked.length === 0;
    }

    // Esegui liquidazione
    btnEsegui.addEventListener('click', function() {
        var checked = tbody.querySelectorAll('.lm-check:checked');
        var ids = [];
        checked.forEach(function(cb) { ids.push(parseInt(cb.dataset.id)); });

        if (ids.length === 0) return;
        if (!confirm('Confermi la creazione di ' + ids.length + ' pagamenti?')) return;

        btnEsegui.disabled = true;
        btnEsegui.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Elaborazione...';

        fetch('{$liquidaUrl}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '{$csrfToken}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                contratti_ids: ids,
                tipo: document.getElementById('lm-tipo').value
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            content.style.display = 'none';
            result.style.display = 'block';
            btnEsegui.style.display = 'none';

            if (data.success) {
                document.getElementById('lm-result-title').textContent = 'Liquidazione completata';
                document.getElementById('lm-result-detail').textContent =
                    data.count + ' pagamenti creati per un totale di \u20AC ' + formatNum(data.totale);

                if (data.errori && data.errori.length > 0) {
                    var errDiv = document.getElementById('lm-result-errors');
                    errDiv.style.display = 'block';
                    errDiv.innerHTML = '<strong>Attenzione:</strong><ul class="mb-0 mt-1">' +
                        data.errori.map(function(e) { return '<li>' + escapeHtml(e) + '</li>'; }).join('') +
                        '</ul>';
                }
            } else {
                document.getElementById('lm-result-title').textContent = 'Errore';
                document.getElementById('lm-result-detail').textContent = data.message || 'Errore sconosciuto';
            }
        })
        .catch(function() {
            btnEsegui.disabled = false;
            btnEsegui.innerHTML = '<i class="ti ti-cash-banknote me-1"></i> Esegui Liquidazione';
            alert('Errore di rete.');
        });
    });

    // Reload pagina alla chiusura se ci sono risultati
    modal.addEventListener('hidden.bs.modal', function() {
        if (result.style.display !== 'none') {
            location.reload();
        }
    });

    function formatNum(n) { return parseFloat(n).toLocaleString('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
