<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Notifica */

$this->title = 'Nuova Notifica';
$this->params['breadcrumbs'][] = ['label' => 'Notifiche', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Summernote Lite CSS/JS da CDN
$this->registerCssFile('https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-it-IT.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="notifica-create">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-bell-plus me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', ['model' => $model]) ?>
        </div>
    </div>
</div>

<?php
$js = <<<JS
$('#contenuto-editor').summernote({
    lang: 'it-IT',
    height: 250,
    toolbar: [
        ['style', ['bold', 'italic', 'underline']],
        ['para', ['ul', 'ol']],
        ['insert', ['link']],
        ['view', ['codeview']]
    ],
    callbacks: {
        onChange: function(contents) {
            // Sincronizza il contenuto con il textarea nascosto
            $('#contenuto-editor').val(contents);
        }
    }
});
JS;
$this->registerJs($js);
?>
