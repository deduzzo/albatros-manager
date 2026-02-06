<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Progetto */
/* @var $fontiList array */

$this->title = 'Modifica: ' . $model->codice_progetto;
$this->params['breadcrumbs'][] = ['label' => 'Progetti', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->codice_progetto, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="progetto-update">
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
                'fontiList' => $fontiList,
            ]) ?>
        </div>
    </div>
</div>
