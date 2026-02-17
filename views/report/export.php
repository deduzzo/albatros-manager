<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $counts array */

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
    </div>
</div>
