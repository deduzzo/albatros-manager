<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\FonteFinanziamento */

$this->title = 'Nuova Fonte di Finanziamento';
$this->params['breadcrumbs'][] = ['label' => 'Fonti di Finanziamento', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Nuova';
?>

<div class="fonte-finanziamento-create">
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
