<?php

/* @var $this \yii\web\View */
/* @var $corsoId int */
/* @var $currentSection string */
/* @var $alertCounts array */

use yii\helpers\Url;

$sections = [
    'panoramica'      => ['label' => 'Panoramica',       'icon' => 'ti-dashboard'],
    'piano-formativo' => ['label' => 'Piano Formativo',  'icon' => 'ti-book'],
    'personale'       => ['label' => 'Personale',        'icon' => 'ti-users'],
    'studenti'        => ['label' => 'Studenti',         'icon' => 'ti-school'],
    'calendario'      => ['label' => 'Calendario',       'icon' => 'ti-calendar'],
    'contratti'       => ['label' => 'Contratti',        'icon' => 'ti-file-text'],
    'borse-studio'    => ['label' => 'Borse di Studio',  'icon' => 'ti-award'],
    'import-export'   => ['label' => 'Import / Export',  'icon' => 'ti-database-import'],
];

$alertCounts = $alertCounts ?? [];
?>
<aside class="sidebar-gestione">
    <nav>
        <ul class="nav flex-column">
            <?php foreach ($sections as $key => $section): ?>
                <?php $count = (int) ($alertCounts[$key] ?? 0); ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentSection === $key) ? 'active' : '' ?>"
                       href="<?= Url::to(['/gestione/sezione', 'id' => $corsoId, 'section' => $key]) ?>">
                        <i class="ti <?= $section['icon'] ?>"></i>
                        <span style="flex-grow:1;"><?= $section['label'] ?></span>
                        <?php if ($count > 0): ?>
                            <span class="badge bg-red text-red-fg ms-auto"
                                  title="<?= $count ?> alert da gestire"><?= $count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="sidebar-divider mt-4"></div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?= Url::to(['/site/index']) ?>" style="color:#94a3b8; font-size:0.8rem;">
                    <i class="ti ti-layout-grid"></i>
                    Modalità avanzata
                </a>
            </li>
        </ul>
    </nav>
</aside>
