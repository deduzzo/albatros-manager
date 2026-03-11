<?php
use yii\helpers\Html;
use yii\helpers\Json;
use app\models\ValutazioneDocente;
use app\components\assets\ApexChartsAsset;

ApexChartsAsset::register($this);

$this->title = 'Valutazione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Valutazioni Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$punteggiDettagliati = $model->punteggiDettagliati;
$haDettagli = !empty($punteggiDettagliati);
$media = $model->getMediaPunteggi();

// Prepara dati per radar chart
$radarLabels = [];
$radarValues = [];
if ($haDettagli) {
    foreach ($punteggiDettagliati as $p) {
        if ($p->criterio) {
            $radarLabels[] = $p->criterio->nome;
            $radarValues[] = $p->punteggio;
        }
    }
}
?>
<div class="valutazione-docente-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti ti-star me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1"><?= $model->getSorgenteTipoBadge() ?></div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => ['confirm' => 'Sei sicuro di voler eliminare questa valutazione?', 'method' => 'post'],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <!-- Media punteggio + info -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Media Punteggi</div>
                            <?php if ($media !== null): ?>
                                <div class="h1 mt-2 mb-0 <?= $media >= 8 ? 'text-green' : ($media >= 5 ? 'text-orange' : 'text-red') ?>"><?= $media ?>/10</div>
                                <div class="mt-2"><?= ValutazioneDocente::getProgressBarHtml(round($media)) ?></div>
                            <?php elseif ($model->punteggio_globale): ?>
                                <div class="h1 mt-2 mb-0"><?= $model->punteggio_globale ?>/10</div>
                                <div class="mt-2"><?= ValutazioneDocente::getStarsHtml($model->punteggio_globale) ?></div>
                            <?php else: ?>
                                <div class="h3 mt-2 text-muted">-</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Docente</div>
                            <div class="mt-2">
                                <?php if ($model->docente): ?>
                                    <?= Html::a('<i class="ti ti-user me-1"></i>' . Html::encode($model->docente->cognome . ' ' . $model->docente->nome), ['/docente/view', 'id' => $model->docente_codice_fiscale]) ?>
                                <?php elseif ($model->docente_nome): ?>
                                    <span class="text-muted"><?= Html::encode($model->docente_nome) ?></span>
                                    <br><span class="badge bg-orange-lt mt-1">Non collegato</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Modulo</div>
                            <div class="mt-2">
                                <?= $model->modulo
                                    ? Html::a('<i class="ti ti-book me-1"></i>' . Html::encode($model->modulo->titolo), ['/modulo/view', 'id' => $model->modulo_id])
                                    : '<span class="text-muted">-</span>' ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Data Valutazione</div>
                            <div class="h3 mt-2"><?= Yii::$app->formatter->asDate($model->data_valutazione) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($haDettagli): ?>
            <div class="row row-deck row-cards mb-3">
                <!-- Radar Chart -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-chart-radar me-2"></i>Radar Punteggi</h3>
                        </div>
                        <div class="card-body">
                            <div id="radar-chart" style="min-height: 350px;"></div>
                        </div>
                    </div>
                </div>
                <!-- Tabella dettaglio -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-list-details me-2"></i>Dettaglio Punteggi</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Criterio</th>
                                        <th style="width: 200px;">Punteggio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($punteggiDettagliati as $p): ?>
                                    <?php if ($p->criterio): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= Html::encode($p->criterio->nome) ?></div>
                                            <?php if ($p->criterio->descrizione): ?>
                                            <div class="text-muted small"><?= Html::encode(mb_substr($p->criterio->descrizione, 0, 100)) ?><?= mb_strlen($p->criterio->descrizione) > 100 ? '...' : '' ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= ValutazioneDocente::getProgressBarHtml($p->punteggio) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Vecchi punteggi fissi -->
            <div class="row row-deck row-cards mb-3">
                <?php if ($model->punteggio_chiarezza): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Chiarezza</div>
                            <div class="mt-2"><?= ValutazioneDocente::getStarsHtml($model->punteggio_chiarezza) ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($model->punteggio_competenza): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Competenza</div>
                            <div class="mt-2"><?= ValutazioneDocente::getStarsHtml($model->punteggio_competenza) ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($model->punteggio_disponibilita): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Disponibilità</div>
                            <div class="mt-2"><?= ValutazioneDocente::getStarsHtml($model->punteggio_disponibilita) ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($model->punteggio_materiali): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Materiali</div>
                            <div class="mt-2"><?= ValutazioneDocente::getStarsHtml($model->punteggio_materiali) ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Info studente e sorgente -->
            <?php if ($model->studente_nome || $model->studente_email || $model->sorgente_url): ?>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-user me-2"></i>Informazioni Sorgente</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <?php if ($model->studente_nome): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nome Studente</div>
                            <div class="datagrid-content"><?= Html::encode($model->studente_nome) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->studente_email): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Email Studente</div>
                            <div class="datagrid-content"><?= Html::mailto($model->studente_email) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->sorgente_url): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">URL Sorgente</div>
                            <div class="datagrid-content">
                                <?= Html::a(Html::encode(mb_substr($model->sorgente_url, 0, 60)) . '...', $model->sorgente_url, ['target' => '_blank', 'rel' => 'noopener']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Sorgente</div>
                            <div class="datagrid-content"><?= $model->getSorgenteTipoBadge() ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Creato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Commento/Note -->
            <?php if ($model->commento || $model->note): ?>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-message me-2"></i>Note e Commenti</h3></div>
                <div class="card-body">
                    <?php if ($model->commento): ?>
                        <h4>Commento</h4>
                        <p><?= nl2br(Html::encode($model->commento)) ?></p>
                    <?php endif; ?>
                    <?php if ($model->note): ?>
                        <h4>Note</h4>
                        <p><?= nl2br(Html::encode($model->note)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-footer">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($haDettagli && !empty($radarLabels)):
$radarLabelsJson = Json::encode($radarLabels);
$radarValuesJson = Json::encode($radarValues);
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    var options = {
        series: [{
            name: 'Punteggio',
            data: {$radarValuesJson}
        }],
        chart: {
            height: 350,
            type: 'radar',
            toolbar: { show: false },
            background: 'transparent'
        },
        theme: {
            mode: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light'
        },
        xaxis: {
            categories: {$radarLabelsJson},
            labels: {
                style: { fontSize: '11px' }
            }
        },
        yaxis: {
            min: 0,
            max: 10,
            tickAmount: 5
        },
        fill: {
            opacity: 0.3
        },
        stroke: {
            width: 2
        },
        markers: {
            size: 4
        },
        colors: ['#206bc4']
    };

    var chart = new ApexCharts(document.querySelector("#radar-chart"), options);
    chart.render();
});
JS;
$this->registerJs($js);
endif; ?>
