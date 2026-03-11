<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RichiestaFatturazione */
/* @var $contratti array */

$this->title = 'Modifica Richiesta #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Richieste Fatturazione', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="richiesta-fatturazione-update">
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
