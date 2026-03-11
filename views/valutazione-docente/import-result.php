<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

$this->title = 'Risultato Import Valutazioni';
$this->params['breadcrumbs'][] = ['label' => 'Valutazioni Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$totale = $result['totale_righe'];
$importate = $result['importate'];
$errori = $result['errori'];
$matchati = $result['docenti_matchati'];
$nonMatchati = $result['docenti_non_matchati'];
$nonMatchatiDettaglio = $result['docenti_non_matchati_dettaglio'];
$log = $result['log'];
?>
<div class="valutazione-docente-import-result">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-clipboard-check me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-upload me-1"></i> Nuovo Import', ['import'], ['class' => 'btn btn-secondary me-2']) ?>
                    <?= Html::a('<i class="ti ti-list me-1"></i> Vai alle Valutazioni', ['index'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <!-- Stat Cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Totale Righe</div>
                            </div>
                            <div class="h1 mb-0"><?= $totale ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-green">Importate</div>
                            </div>
                            <div class="h1 mb-0 text-green"><?= $importate ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-red">Errori</div>
                            </div>
                            <div class="h1 mb-0 text-red"><?= $errori ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Docenti Matchati</div>
                            </div>
                            <div class="h1 mb-0">
                                <span class="text-green"><?= $matchati ?></span>
                                <span class="text-muted">/</span>
                                <span class="text-red"><?= $nonMatchati ?></span>
                            </div>
                            <div class="text-muted small mt-1">matchati / non matchati</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress bar -->
            <?php if ($totale > 0): ?>
            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small">Risultato:</span>
                        <div class="progress flex-grow-1" style="height: 12px;">
                            <?php $pctOk = round(($importate / $totale) * 100); ?>
                            <?php $pctErr = round(($errori / $totale) * 100); ?>
                            <div class="progress-bar bg-green" style="width: <?= $pctOk ?>%"><?= $pctOk ?>%</div>
                            <?php if ($errori > 0): ?>
                            <div class="progress-bar bg-red" style="width: <?= $pctErr ?>%"><?= $pctErr ?>%</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Docenti non matchati -->
            <?php if (!empty($nonMatchatiDettaglio)): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user-question me-2"></i>Docenti non matchati (<?= count($nonMatchatiDettaglio) ?>)</h3>
                    <div class="card-actions">
                        <span class="badge bg-orange-lt">Richiede matching manuale</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nome dal CSV</th>
                                <th class="text-center">Valutazioni</th>
                                <th>Assegna Docente</th>
                                <th class="text-center">Azione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nonMatchatiDettaglio as $docenteNome => $info): ?>
                            <tr id="row-<?= md5($docenteNome) ?>">
                                <td>
                                    <strong><?= Html::encode($info['nome']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-blue-lt"><?= $info['count'] ?></span>
                                </td>
                                <td>
                                    <?= Html::dropDownList(
                                        'docente_cf_' . md5($docenteNome),
                                        null,
                                        $docenti,
                                        [
                                            'class' => 'form-select form-select-sm docente-select',
                                            'prompt' => '-- Seleziona docente --',
                                            'data-ids' => Json::encode($info['valutazione_ids']),
                                            'id' => 'select-' . md5($docenteNome),
                                        ]
                                    ) ?>
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-primary btn-sm btn-match"
                                            data-hash="<?= md5($docenteNome) ?>"
                                            data-nome="<?= Html::encode($info['nome']) ?>"
                                            disabled>
                                        <i class="ti ti-link me-1"></i> Associa
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Log dettagliato -->
            <?php if (!empty($log)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-list me-2"></i>Log Dettagliato</h3>
                    <div class="card-actions">
                        <a href="#" class="btn btn-sm btn-secondary" onclick="document.getElementById('log-body').classList.toggle('d-none'); return false;">
                            <i class="ti ti-eye me-1"></i> Mostra/Nascondi
                        </a>
                    </div>
                </div>
                <div class="card-body d-none" id="log-body">
                    <div class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 0.8rem;">
                        <?php foreach ($log as $entry): ?>
                            <div class="<?= strpos($entry, 'errore') !== false ? 'text-red' : (strpos($entry, 'non matchato') !== false ? 'text-orange' : 'text-green') ?>">
                                <?= Html::encode($entry) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$matchUrl = Url::to(['match-docente']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
// Abilita pulsante quando si seleziona un docente
document.querySelectorAll('.docente-select').forEach(select => {
    select.addEventListener('change', function() {
        const hash = this.id.replace('select-', '');
        const btn = document.querySelector('.btn-match[data-hash="' + hash + '"]');
        btn.disabled = !this.value;
    });
});

// Click su "Associa"
document.querySelectorAll('.btn-match').forEach(btn => {
    btn.addEventListener('click', function() {
        const hash = this.dataset.hash;
        const nome = this.dataset.nome;
        const select = document.getElementById('select-' + hash);
        const docenteCf = select.value;
        const ids = JSON.parse(select.dataset.ids);

        if (!docenteCf || ids.length === 0) return;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const formData = new FormData();
        formData.append('{$csrfParam}', '{$csrfToken}');
        formData.append('docente_cf', docenteCf);
        ids.forEach(id => formData.append('valutazione_ids[]', id));

        fetch('{$matchUrl}', {
            method: 'POST',
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            const row = document.getElementById('row-' + hash);
            if (data.success) {
                row.classList.add('table-success');
                row.querySelector('td:last-child').innerHTML = '<span class="badge bg-green text-green-fg"><i class="ti ti-check me-1"></i>Associato</span>';
                select.disabled = true;
            } else {
                this.disabled = false;
                this.innerHTML = '<i class="ti ti-link me-1"></i> Associa';
                alert(data.message || 'Errore durante il matching.');
            }
        })
        .catch(() => {
            this.disabled = false;
            this.innerHTML = '<i class="ti ti-link me-1"></i> Associa';
            alert('Errore di rete.');
        });
    });
});
JS;
$this->registerJs($js);
?>
