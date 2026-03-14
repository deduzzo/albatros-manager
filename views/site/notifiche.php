<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $tab */
/** @var int $nonLetteCount */
/** @var string $userType */
/** @var string $userId */

$this->title = 'Notifiche';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Page header -->
        <div class="page-header d-print-none mb-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-bell me-2"></i> Notifiche
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?php if ($nonLetteCount > 0): ?>
                        <?= Html::beginForm(['segna-tutte-lette'], 'post') ?>
                            <?= Html::submitButton('<i class="ti ti-checks me-1"></i> Segna tutte come lette', [
                                'class' => 'btn btn-outline-primary',
                            ]) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'non-lette' ? 'active' : '' ?>" href="<?= Url::to(['notifiche', 'tab' => 'non-lette']) ?>">
                    Non lette
                    <?php if ($nonLetteCount > 0): ?>
                        <span class="badge bg-red text-red-fg ms-1"><?= $nonLetteCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'tutte' ? 'active' : '' ?>" href="<?= Url::to(['notifiche', 'tab' => 'tutte']) ?>">
                    Tutte
                </a>
            </li>
        </ul>

        <!-- Lista notifiche -->
        <?php if (empty($dataProvider->models)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-bell-off" style="font-size: 3rem; color: var(--tblr-secondary);"></i>
                    </div>
                    <h3 class="text-secondary">Nessuna notifica</h3>
                    <p class="text-secondary">
                        <?= $tab === 'non-lette' ? 'Non hai notifiche da leggere.' : 'Non hai ancora ricevuto notifiche.' ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($dataProvider->models as $notifica): ?>
                <?php $isNonLetta = !$notifica->isLettaDa($userType, $userId); ?>
                <div class="card mb-2 <?= $isNonLetta ? 'border-start border-primary border-3' : '' ?>">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-1">
                                    <a href="<?= Url::to(['notifica-view', 'id' => $notifica->id]) ?>" class="text-reset">
                                        <?= Html::encode($notifica->titolo) ?>
                                    </a>
                                    <?php if ($isNonLetta): ?>
                                        <span class="badge bg-blue-lt ms-2">Nuova</span>
                                    <?php endif; ?>
                                </h3>
                                <div class="text-secondary">
                                    <?= mb_substr(strip_tags($notifica->contenuto), 0, 100) ?>...
                                </div>
                            </div>
                            <div class="col-auto text-secondary">
                                <?= Yii::$app->formatter->asRelativeTime($notifica->created_at) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Paginazione -->
            <div class="d-flex justify-content-center mt-3">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination'],
                    'linkOptions' => ['class' => 'page-link'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
