<?php

namespace app\components\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle per l'assistente AI basato su WebLLM.
 * Registra solo il CSS. I file JS sono ES modules caricati direttamente nel widget HTML.
 */
class WebLlmAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/assistant.css',
    ];

    public $js = [];

    public $depends = [
        'app\components\assets\TablerAsset',
    ];
}
