<?php

namespace app\components\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Tom Select Asset Bundle
 * Ricerca testuale automatica per dropdown select
 *
 * Si auto-inizializza su tutti i <select.form-select> con 5+ opzioni.
 * Escludere: classe 'no-tom-select'. Forzare: classe 'tom-select'.
 *
 * @see https://tom-select.js.org
 */
class TomSelectAsset extends AssetBundle
{
    public $basePath = '@webroot/assets/tom-select';
    public $baseUrl = '@web/assets/tom-select';

    public $css = [
        'tom-select-init.css',
    ];

    public $js = [
        'tom-select-init.js',
    ];

    public $depends = [
        'app\components\assets\TablerAsset',
    ];

    public function registerAssetFiles($view)
    {
        // Registra CDN Tom Select prima dei nostri file
        $view->registerCssFile(
            'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css',
            ['crossorigin' => 'anonymous', 'position' => View::POS_HEAD]
        );
        $view->registerJsFile(
            'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js',
            ['crossorigin' => 'anonymous', 'position' => View::POS_HEAD]
        );

        parent::registerAssetFiles($view);
    }
}
