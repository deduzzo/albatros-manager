<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Docente */

$this->title = 'Modifica: ' . $model->getNomeCompleto();
$this->params['breadcrumbs'][] = ['label' => 'Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->getNomeCompleto(), 'url' => ['view', 'id' => $model->codice_fiscale]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="docente-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg bg-blue-lt">
                        <?= Html::encode($model->getIniziali()) ?>
                    </span>
                </div>
                <div class="col">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <code><?= Html::encode($model->codice_fiscale) ?></code>
                    </div>
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
