<?php

namespace app\components\assets;

use yii\web\AssetBundle;

/**
 * Tabler UI Kit Asset Bundle
 * Versione: 1.4.0
 *
 * @see https://tabler.io
 */
class TablerAsset extends AssetBundle
{
    public $basePath = '@webroot/assets/tabler';
    public $baseUrl = '@web/assets/tabler';

    public $css = [
        'css/tabler.min.css',
        'css/tabler-vendors.min.css',
        'css/tabler-icons.min.css',
    ];

    public $js = [
        'js/tabler.min.js',
    ];

    public $jsOptions = [
        'defer' => true,
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
