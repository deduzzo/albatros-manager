<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\TagCurriculum */

$this->title = 'Nuovo Tag';
$this->params['breadcrumbs'][] = ['label' => 'Gestione Tag', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Nuovo';
?>

<div class="tag-curriculum-create">
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
