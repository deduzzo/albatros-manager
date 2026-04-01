<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $stats array */

use yii\helpers\Html;
use yii\helpers\Url;

$tablerColor = $corso->getTablerColor();
$pct = $stats['moduliTotali'] > 0
    ? round(($stats['moduliAssegnati'] / $stats['moduliTotali']) * 100)
    : 0;

$statusBadge = match($corso->status) {
    'pubblicato' => ['class' => 'bg-green text-green-fg', 'label' => 'Pubblicato'],
    'chiuso'     => ['class' => 'bg-default text-default-fg', 'label' => 'Chiuso'],
    'annullato'  => ['class' => 'bg-red text-red-fg', 'label' => 'Annullato'],
    default      => ['class' => 'bg-orange text-orange-fg', 'label' => 'Bozza'],
};
?>
<a href="<?= Url::to(['/gestione/corso', 'id' => $corso->id]) ?>" class="card card-link card-link-pop text-decoration-none" style="border-top: 3px solid var(--tblr-<?= $tablerColor ?>);">
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between mb-2">
            <div>
                <div class="text-muted" style="font-size:0.75rem; font-weight:600; letter-spacing:0.05em; text-transform:uppercase;">
                    <?= Html::encode($corso->codice_avviso) ?>
                </div>
                <h3 class="card-title mb-0 mt-1" style="font-size:1rem; color:#1e293b;">
                    <?= Html::encode(mb_substr($corso->titolo, 0, 60)) ?>
                    <?= mb_strlen($corso->titolo) > 60 ? '…' : '' ?>
                </h3>
            </div>
            <span class="badge <?= $statusBadge['class'] ?> ms-2 flex-shrink-0"><?= $statusBadge['label'] ?></span>
        </div>

        <?php if ($corso->progetto): ?>
            <div class="text-muted mb-3" style="font-size:0.8rem;">
                <i class="ti ti-folder me-1"></i><?= Html::encode($corso->progetto->titolo ?? $corso->progetto->codice) ?>
            </div>
        <?php endif; ?>

        <div class="row g-2 text-center mb-3">
            <div class="col-4">
                <div class="fw-bold" style="font-size:1.2rem; color:#1e293b;"><?= $stats['moduliTotali'] ?></div>
                <div class="text-muted" style="font-size:0.72rem;">Moduli</div>
            </div>
            <div class="col-4">
                <div class="fw-bold" style="font-size:1.2rem; color:#1e293b;"><?= $stats['docenti'] ?></div>
                <div class="text-muted" style="font-size:0.72rem;">Docenti</div>
            </div>
            <div class="col-4">
                <div class="fw-bold" style="font-size:1.2rem; color:#1e293b;"><?= $stats['studenti'] ?></div>
                <div class="text-muted" style="font-size:0.72rem;">Studenti</div>
            </div>
        </div>

        <!-- Progress bar moduli assegnati -->
        <div>
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted" style="font-size:0.75rem;">Moduli assegnati</span>
                <span class="fw-semibold" style="font-size:0.75rem; color:var(--tblr-<?= $tablerColor ?>);"><?= $pct ?>%</span>
            </div>
            <div class="progress" style="height:5px;">
                <div class="progress-bar bg-<?= $tablerColor ?>" style="width:<?= $pct ?>%;" role="progressbar"></div>
            </div>
        </div>
    </div>
</a>
