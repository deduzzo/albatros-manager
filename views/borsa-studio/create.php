<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\BorsaStudio $model */
/** @var array $progetti */

$this->title = 'Nuova Borsa di Studio';
$this->params['breadcrumbs'][] = ['label' => 'Borse di Studio', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="borsa-studio-create">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-plus me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Registra un nuovo beneficiario di borsa di studio PNRR</div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', ['model' => $model, 'progetti' => $progetti]) ?>
        </div>
    </div>
</div>
