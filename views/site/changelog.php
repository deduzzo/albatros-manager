<?php

/** @var yii\web\View $this */
/** @var array $versions */

use yii\helpers\Html;

$this->title = 'Novità del Portale';
$this->params['breadcrumbs'][] = $this->title;

// Mappa colori per tipo sezione
$sectionColors = [
    'Novità' => 'bg-green-lt',
    'Correzioni' => 'bg-azure-lt',
    'Miglioramenti' => 'bg-yellow-lt',
];

/**
 * Formatta la data in italiano
 */
function formatDateIT(string $date): string
{
    $mesi = [
        '01' => 'Gennaio', '02' => 'Febbraio', '03' => 'Marzo',
        '04' => 'Aprile', '05' => 'Maggio', '06' => 'Giugno',
        '07' => 'Luglio', '08' => 'Agosto', '09' => 'Settembre',
        '10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre',
    ];
    $parts = explode('-', $date);
    $mese = $mesi[$parts[1]] ?? $parts[1];
    return (int) $parts[2] . ' ' . $mese . ' ' . $parts[0];
}

?>

<div class="page-body">
    <div class="container-xl">
        <!-- Page Header -->
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg bg-green-lt">
                        <i class="ti ti-sparkles fs-1"></i>
                    </span>
                </div>
                <div class="col">
                    <h2 class="page-title"><?= Html::encode($this->title) ?></h2>
                    <div class="text-secondary">
                        Tutte le modifiche e le novità del portale Albatros Manager
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($versions)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-notes-off" style="font-size: 3rem; color: var(--tblr-secondary);"></i>
                    </div>
                    <h3 class="text-secondary">Nessuna novità disponibile</h3>
                </div>
            </div>
        <?php else: ?>
            <!-- Timeline -->
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <?php foreach ($versions as $i => $version): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="d-flex align-items-center w-100">
                                    <span class="badge bg-blue text-blue-fg me-3"><?= Html::encode($version['version']) ?></span>
                                    <h3 class="card-title mb-0">
                                        <?= formatDateIT($version['date']) ?>
                                    </h3>
                                    <?php if ($i === 0): ?>
                                        <span class="badge bg-green-lt ms-auto">Ultima versione</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php foreach ($version['sections'] as $section): ?>
                                    <div class="mb-3">
                                        <div class="mb-2">
                                            <span class="badge <?= $sectionColors[$section['title']] ?? 'bg-default-lt' ?>">
                                                <?= Html::encode($section['title']) ?>
                                            </span>
                                        </div>
                                        <ul class="list-unstyled ms-1">
                                            <?php foreach ($section['items'] as $item): ?>
                                                <li class="mb-1">
                                                    <i class="ti ti-point-filled text-green me-1" style="font-size: 0.75rem;"></i>
                                                    <?= Html::encode($item) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
