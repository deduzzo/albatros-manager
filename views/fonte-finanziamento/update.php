<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\FonteFinanziamento */

$this->title = 'Modifica Fonte: ' . $model->codice;
$this->params['breadcrumbs'][] = ['label' => 'Fonti di Finanziamento', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->codice, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="fonte-finanziamento-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>
