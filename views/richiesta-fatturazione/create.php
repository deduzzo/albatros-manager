<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RichiestaFatturazione */
/* @var $contratti array */

$this->title = 'Nuova Richiesta Fatturazione';
$this->params['breadcrumbs'][] = ['label' => 'Richieste Fatturazione', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="richiesta-fatturazione-create">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <h2 class="page-title">
                <i class="ti ti-file-invoice me-2"></i>
                <?= Html::encode($this->title) ?>
            </h2>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', [
                'model' => $model,
                'contratti' => $contratti,
            ]) ?>
        </div>
    </div>
</div>
