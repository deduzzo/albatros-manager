<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Pagamento */
/* @var $contratti array */

$this->title = 'Modifica Pagamento #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Pagamenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Pagamento #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="pagamento-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <span class="badge bg-blue-lt"><?= Html::encode($model->getTipoLabel()) ?></span>
                        &mdash;
                        <?= Yii::$app->formatter->asCurrency($model->importo_lordo, 'EUR') ?>
                    </div>
                </div>
            </div>
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
