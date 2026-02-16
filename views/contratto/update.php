<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Contratto */
/* @var $assegnazioni array */

$this->title = 'Modifica Contratto #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Contratti', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Contratto #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="contratto-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?php if ($model->numero_contratto): ?>
                            <code><?= Html::encode($model->numero_contratto) ?></code>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', [
                'model' => $model,
                'assegnazioni' => $assegnazioni,
            ]) ?>
        </div>
    </div>
</div>
