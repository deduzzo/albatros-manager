<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppGridView;
use app\models\RichiestaFatturazione;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RichiestaFatturazioneSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Richieste Fatturazione';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="richiesta-fatturazione-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-file-invoice me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione richieste fatture e cedolini
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::button('<i class="ti ti-send me-1"></i> Invio Massivo', [
                        'class' => 'btn btn-primary me-2',
                        'id' => 'btn-invio-massivo',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#modal-invio-massivo',
                    ]) ?>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Richiesta', ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'tipo',
                            'format' => 'raw',
                            'filter' => RichiestaFatturazione::getTipoList(),
                            'headerOptions' => ['style' => 'width: 140px;'],
                            'content' => function($model) {
                                return $model->getTipoBadge();
                            },
                        ],
                        [
                            'attribute' => 'docente_nome',
                            'label' => 'Docente',
                            'format' => 'raw',
                            'content' => function($model) {
                                $contratto = $model->contratto;
                                $assegnazione = $contratto ? $contratto->assegnazione : null;
                                $docente = $assegnazione ? $assegnazione->docente : null;
                                if (!$docente) return $model->destinatario_nome ?: '-';
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
                            'attribute' => 'importo',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'text-end'],
                            'content' => function($model) {
                                return Yii::$app->formatter->asCurrency($model->importo, 'EUR');
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => RichiestaFatturazione::getStatusLabels(),
                            'content' => function($model) {
                                return $model->getStatusBadge();
                            },
                        ],
                        [
                            'attribute' => 'data_richiesta',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> richieste
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Invio Massivo -->
<div class="modal modal-blur fade" id="modal-invio-massivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-send me-2"></i>Invio Massivo Richieste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Verranno inviate tutte le richieste attualmente in stato <span class="badge bg-default text-default-fg">Bozza</span>.</p>
                <div id="im-loading" class="text-center py-3" style="display: none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Invio in corso...</p>
                </div>
                <div id="im-result" style="display: none;">
                    <div class="text-center py-3">
                        <i class="ti ti-circle-check d-block mb-2" style="font-size: 3rem; color: green;"></i>
                        <h3 id="im-result-title">Invio completato</h3>
                        <p class="text-muted" id="im-result-detail"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Chiudi
                </button>
                <button type="button" class="btn btn-primary" id="im-btn-invia">
                    <i class="ti ti-send me-1"></i> Invia Tutte le Bozze
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$inviaUrl = Url::to(['invia-massiva']);
$csrfToken = Yii::$app->request->csrfToken;

// Raccogli ID delle bozze visibili
$bozzeIds = [];
foreach ($dataProvider->getModels() as $m) {
    if ($m->status === RichiestaFatturazione::STATUS_BOZZA) {
        $bozzeIds[] = $m->id;
    }
}
$bozzeIdsJson = json_encode($bozzeIds);

$js = <<<JS
(function() {
    var btnInvia = document.getElementById('im-btn-invia');
    var loading = document.getElementById('im-loading');
    var result = document.getElementById('im-result');
    var bozzeIds = {$bozzeIdsJson};

    if (bozzeIds.length === 0) {
        btnInvia.disabled = true;
        btnInvia.textContent = 'Nessuna bozza da inviare';
    }

    btnInvia.addEventListener('click', function() {
        if (!confirm('Confermi l\'invio di ' + bozzeIds.length + ' richieste?')) return;

        btnInvia.disabled = true;
        loading.style.display = 'block';

        fetch('{$inviaUrl}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '{$csrfToken}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: bozzeIds })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            loading.style.display = 'none';
            result.style.display = 'block';
            btnInvia.style.display = 'none';

            document.getElementById('im-result-title').textContent = data.success ? 'Invio completato' : 'Errore';
            document.getElementById('im-result-detail').textContent = data.count + ' richieste inviate.';
        })
        .catch(function() {
            loading.style.display = 'none';
            alert('Errore di rete.');
            btnInvia.disabled = false;
        });
    });

    document.getElementById('modal-invio-massivo').addEventListener('hidden.bs.modal', function() {
        if (result.style.display !== 'none') location.reload();
    });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
