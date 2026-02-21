<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $istanze app\models\Istanza[] */
/* @var $avviso app\models\Avviso|null */
/* @var $modulo app\models\Modulo|null */
/* @var $avviso_id int|null */
/* @var $modulo_id int|null */
/* @var $bandiList array */
/* @var $moduliList array */

$this->title = 'Graduatoria';
$this->params['breadcrumbs'][] = ['label' => 'Istanze', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Graduatoria';
?>

<div class="istanza-graduatoria">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-list-numbers me-2"></i>
                        Graduatoria Candidature
                    </h2>
                    <?php if ($modulo): ?>
                        <div class="text-muted mt-1">
                            Modulo: <strong><?= Html::encode($modulo->codice . ' - ' . $modulo->titolo) ?></strong>
                        </div>
                    <?php elseif ($avviso): ?>
                        <div class="text-muted mt-1">
                            Bando: <strong><?= Html::encode($avviso->titolo) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alle istanze', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filtri -->
            <div class="card mb-3">
                <div class="card-body">
                    <?= Html::beginForm(['graduatoria'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                    <div class="col-md-5">
                        <label class="form-label">Bando/Avviso</label>
                        <?= Html::dropDownList('avviso_id', $avviso_id, $bandiList, [
                            'class' => 'form-select',
                            'prompt' => '-- Tutti i bandi --',
                            'id' => 'filtro-avviso',
                        ]) ?>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Modulo</label>
                        <?= Html::dropDownList('modulo_id', $modulo_id, $moduliList, [
                            'class' => 'form-select',
                            'prompt' => '-- Tutti i moduli --',
                            'id' => 'filtro-modulo',
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= Html::submitButton('<i class="ti ti-filter me-1"></i> Filtra', ['class' => 'btn btn-primary w-100']) ?>
                    </div>
                    <?= Html::endForm() ?>
                </div>
            </div>

            <!-- Stat cards -->
            <?php
            $totale = count($istanze);
            $assegnati = 0;
            $idonei = 0;
            $nonIdonei = 0;
            foreach ($istanze as $ist) {
                if ($ist->esito_cts === 'assegnato') $assegnati++;
                elseif ($ist->esito_cts === 'idoneo') $idonei++;
                elseif ($ist->esito_cts === 'non_idoneo') $nonIdonei++;
            }
            ?>
            <div class="row row-deck mb-3">
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
                                <div class="subheader text-green">Assegnati</div>
                            </div>
                            <div class="h1 mb-0 text-green"><?= $assegnati ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-azure">Idonei (Long List)</div>
                            </div>
                            <div class="h1 mb-0 text-azure"><?= $idonei ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-red">Non Idonei</div>
                            </div>
                            <div class="h1 mb-0 text-red"><?= $nonIdonei ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabella graduatoria -->
            <?php if (!empty($istanze)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-trophy me-2"></i>
                        Graduatoria
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th class="w-1">Pos.</th>
                                <th>Docente</th>
                                <?php if (!$modulo_id): ?>
                                <th>Modulo</th>
                                <?php endif; ?>
                                <?php if (!$avviso_id && !$modulo_id): ?>
                                <th>Bando</th>
                                <?php endif; ?>
                                <th>Provenienza</th>
                                <th class="text-center">P. Titoli</th>
                                <th class="text-center">P. Esperienza</th>
                                <th class="text-center">P. Totale</th>
                                <th>Esito CTS</th>
                                <th>Stato</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($istanze as $i => $istanza): ?>
                            <?php $docente = $istanza->docente; ?>
                            <tr class="<?= $istanza->isIdoneo() ? '' : ($istanza->isAssegnato() ? '' : '') ?>">
                                <td>
                                    <?php if ($istanza->posizione_graduatoria): ?>
                                        <span class="badge bg-default"><?= $istanza->posizione_graduatoria ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><?= $i + 1 ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm me-2 <?= $istanza->isAssegnato() ? 'bg-green-lt text-green' : ($istanza->isIdoneo() ? 'bg-azure-lt text-azure' : 'bg-default') ?>">
                                            <?= strtoupper(substr($docente->cognome ?? '?', 0, 1) . substr($docente->nome ?? '?', 0, 1)) ?>
                                        </span>
                                        <div>
                                            <?= Html::a(Html::encode($docente ? $docente->getNomeCompleto() : 'N/D'), ['/docente/view', 'id' => $istanza->docente_codice_fiscale]) ?>
                                            <div class="text-muted small"><code><?= Html::encode($istanza->docente_codice_fiscale) ?></code></div>
                                        </div>
                                    </div>
                                </td>
                                <?php if (!$modulo_id): ?>
                                <td>
                                    <?php if ($istanza->modulo): ?>
                                        <?= Html::a(Html::encode($istanza->modulo->codice), ['/modulo/view', 'id' => $istanza->modulo_id], ['class' => 'text-reset']) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <?php if (!$avviso_id && !$modulo_id): ?>
                                <td>
                                    <?= $istanza->avviso ? Html::encode($istanza->avviso->titolo) : '-' ?>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($docente && $docente->provenienza): ?>
                                        <span class="badge <?= $docente->provenienza === 'RU' ? 'bg-purple-lt' : 'bg-cyan-lt' ?>">
                                            <?= Html::encode($docente->provenienza) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $istanza->punteggio_titoli !== null ? number_format($istanza->punteggio_titoli, 1) : '-' ?></td>
                                <td class="text-center"><?= $istanza->punteggio_esperienza !== null ? number_format($istanza->punteggio_esperienza, 1) : '-' ?></td>
                                <td class="text-center">
                                    <strong><?= $istanza->punteggio_totale !== null ? number_format($istanza->punteggio_totale, 1) : '-' ?></strong>
                                </td>
                                <td><?= $istanza->getEsitoCtsBadgeHtml() ?></td>
                                <td>
                                    <span class="badge <?= $istanza->getStatusBadgeClass() ?>"><?= $istanza->getStatusLabel() ?></span>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('<i class="ti ti-eye"></i>', ['view', 'id' => $istanza->id], ['class' => 'btn btn-sm btn-ghost-primary', 'title' => 'Dettaglio']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <span class="avatar avatar-xl bg-default mb-3">
                        <i class="ti ti-list-numbers"></i>
                    </span>
                    <h3 class="text-muted">Nessuna candidatura valutata</h3>
                    <p class="text-secondary">Seleziona un bando o un modulo per visualizzare la graduatoria.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// AJAX per caricare moduli quando si seleziona un bando
$moduliUrl = Url::to(['/istanza/moduli-per-avviso']);
$js = <<<JS
document.getElementById('filtro-avviso').addEventListener('change', function() {
    var avvisoId = this.value;
    var moduloSelect = document.getElementById('filtro-modulo');
    moduloSelect.innerHTML = '<option value="">-- Tutti i moduli --</option>';

    if (avvisoId) {
        fetch('{$moduliUrl}?avviso_id=' + avvisoId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                data.forEach(function(m) {
                    var opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.text;
                    moduloSelect.appendChild(opt);
                });
            });
    }
});
JS;
$this->registerJs($js);
?>
