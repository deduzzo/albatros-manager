<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Notifica $notifica */
/** @var string $userType */
/** @var string $userId */

$this->title = $notifica->titolo;
$this->params['breadcrumbs'][] = ['label' => 'Notifiche', 'url' => ['notifiche']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-bell me-2"></i> <?= Html::encode($notifica->titolo) ?>
                </h3>
            </div>
            <div class="card-body">
                <!-- Metadati -->
                <div class="datagrid mb-4">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Data</div>
                        <div class="datagrid-content">
                            <?= Yii::$app->formatter->asDatetime($notifica->created_at, 'long') ?>
                            <span class="text-secondary ms-1">(<?= Yii::$app->formatter->asRelativeTime($notifica->created_at) ?>)</span>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Mittente</div>
                        <div class="datagrid-content"><?= Html::encode($notifica->getCreatoDaLabel()) ?></div>
                    </div>
                </div>

                <!-- Contenuto -->
                <div class="border rounded p-3 mb-3">
                    <?= $notifica->contenuto ?>
                </div>

                <!-- Link esterno -->
                <?php if (!empty($notifica->link)): ?>
                    <div class="mb-3">
                        <?= Html::a('<i class="ti ti-external-link me-1"></i> Vai alla pagina', $notifica->link, [
                            'class' => 'btn btn-primary',
                            'target' => '_blank',
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="<?= Url::to(['notifiche']) ?>" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Torna alle notifiche
                </a>
            </div>
        </div>
    </div>
</div>
