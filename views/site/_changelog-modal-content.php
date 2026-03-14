<?php

/** @var array $versions */
/** @var bool $isUnread */

use yii\helpers\Html;
use yii\helpers\Url;

// Determina l'URL base per il changelog completo in base al controller corrente
$controllerId = Yii::$app->controller->id;
$changelogUrl = match ($controllerId) {
    'docente-area' => Url::to(['/docente-area/changelog']),
    'studente-area' => Url::to(['/studente-area/changelog']),
    default => Url::to(['/site/changelog']),
};

?>

<div class="modal-header">
    <h5 class="modal-title">
        <i class="ti ti-sparkles me-2 text-green"></i>
        <?= $isUnread ? 'Novità dalla tua ultima visita' : 'Novità del Portale' ?>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
</div>
<div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
    <?php if (empty($versions)): ?>
        <p class="text-secondary text-center py-3">Nessuna novità al momento.</p>
    <?php else: ?>
        <?php foreach ($versions as $version): ?>
            <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-blue me-2">v<?= Html::encode($version['version']) ?></span>
                    <small class="text-secondary"><?= Html::encode($version['date']) ?></small>
                </div>
                <?php foreach ($version['sections'] as $section): ?>
                    <ul class="list-unstyled ms-2 mb-2">
                        <?php foreach ($section['items'] as $item): ?>
                            <li class="mb-1">
                                <i class="ti ti-point-filled text-green me-1" style="font-size: 0.75rem;"></i>
                                <?= Html::encode($item) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<div class="modal-footer">
    <a href="<?= $changelogUrl ?>" class="btn btn-primary">
        <i class="ti ti-list me-1"></i> Vedi storico completo
    </a>
    <button type="button" class="btn" data-bs-dismiss="modal">Chiudi</button>
</div>
