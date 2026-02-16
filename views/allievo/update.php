<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Allievo */

$this->title = 'Modifica: ' . $model->getNomeCompleto();
$this->params['breadcrumbs'][] = ['label' => 'Allievi', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->getNomeCompleto(), 'url' => ['view', 'id' => $model->codice_fiscale]];
$this->params['breadcrumbs'][] = 'Modifica';

$iniziali = strtoupper(mb_substr($model->cognome, 0, 1) . mb_substr($model->nome, 0, 1));
?>

<div class="allievo-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg bg-green-lt">
                        <?= Html::encode($iniziali) ?>
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
