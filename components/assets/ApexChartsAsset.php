<?php

namespace app\components\assets;

use yii\web\AssetBundle;

/**
 * ApexCharts Asset Bundle
 * Libreria per grafici interattivi
 *
 * @see https://apexcharts.com
 */
class ApexChartsAsset extends AssetBundle
{
    public $sourcePath = null;

    public $js = [
        'https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js',
    ];

    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
    ];

    public $depends = [
        'app\components\assets\TablerAsset',
    ];
}
