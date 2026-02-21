<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $counts array */
/* @var $progetti app\models\Progetto[] */

$this->title = 'Export Dati';
$this->params['breadcrumbs'][] = ['label' => 'Report', 'url' => ['statistiche']];
$this->params['breadcrumbs'][] = 'Export Dati';

$entities = [
    'docenti' => [
        'icon' => 'ti-users',
        'nome' => 'Docenti',
        'descrizione' => 'Anagrafica completa dei docenti con recapiti e dati professionali',
    ],
    'pagamenti' => [
        'icon' => 'ti-cash',
        'nome' => 'Pagamenti',
        'descrizione' => 'Elenco pagamenti con importi, date e riferimenti contabili',
    ],
    'contratti' => [
        'icon' => 'ti-file-text',
        'nome' => 'Contratti',
        'descrizione' => 'Contratti stipulati con compensi, date e stato',
    ],
    'moduli' => [
        'icon' => 'ti-book',
        'nome' => 'Moduli',
        'descrizione' => 'Moduli formativi con ore, compensi e stato avanzamento',
    ],
    'progetti' => [
        'icon' => 'ti-folder',
        'nome' => 'Progetti',
        'descrizione' => 'Progetti finanziati con budget, date e stato',
    ],
];
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto">
                <h2 class="page-title">
                    <i class="ti ti-download me-2"></i><?= Html::encode($this->title) ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Seleziona i dati da esportare</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($entities as $key => $entity): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-sm border">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="avatar bg-blue-lt me-3">
                                        <i class="ti <?= $entity['icon'] ?>"></i>
                                    </span>
                                    <div>
                                        <h3 class="mb-0"><?= Html::encode($entity['nome']) ?></h3>
                                        <span class="badge bg-blue-lt"><?= $counts[$key] ?> record</span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-3"><?= Html::encode($entity['descrizione']) ?></p>
                                <div class="d-flex gap-2">
                                    <?= Html::a(
                                        '<i class="ti ti-file-type-csv me-1"></i> CSV',
                                        ['export-csv', 'entity' => $key],
                                        ['class' => 'btn btn-sm btn-warning']
                                    ) ?>
                                    <?= Html::a(
                                        '<i class="ti ti-file-spreadsheet me-1"></i> Excel',
                                        ['export-excel', 'entity' => $key],
                                        ['class' => 'btn btn-sm btn-warning']
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Export per Consulente del Lavoro -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-briefcase me-2"></i>Export per Consulente del Lavoro</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Genera un file Excel con tutti i dati anagrafici, contrattuali e retributivi dei docenti per un progetto specifico.
                    Il file contiene le informazioni necessarie al consulente del lavoro per la gestione dei contratti.
                </p>
                <?php if (!empty($progetti)): ?>
                <div class="row align-items-end g-3">
                    <div class="col-md-6">
                        <label class="form-label">Seleziona Progetto</label>
                        <select id="consulente-progetto-id" class="form-select">
                            <option value="">-- Seleziona un progetto --</option>
                            <?php foreach ($progetti as $p): ?>
                            <option value="<?= $p->id ?>"><?= Html::encode($p->codice_progetto . ' - ' . $p->titolo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" id="btn-export-consulente" class="btn bg-azure text-azure-fg" disabled>
                            <i class="ti ti-file-spreadsheet me-1"></i> Scarica Excel
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-muted text-center py-3">
                    <i class="ti ti-folder-off mb-2" style="font-size: 2rem;"></i>
                    <p>Nessun progetto disponibile</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$exportUrl = Url::to(['report/export-consulente']);
$js = <<<JS
    var select = document.getElementById('consulente-progetto-id');
    var btn = document.getElementById('btn-export-consulente');
    if (select && btn) {
        select.addEventListener('change', function() {
            btn.disabled = !this.value;
        });
        btn.addEventListener('click', function() {
            var progettoId = select.value;
            if (progettoId) {
                window.location.href = '{$exportUrl}?progetto_id=' + progettoId;
            }
        });
    }
JS;
$this->registerJs($js);
?>
