<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'Albatros Manager',
    'language' => 'it-IT',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'XKEFvCN2RoVE_SiPduJHzhmp2hhkKJP7',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'docenteUser' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\DocenteAuth',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_docenteIdentity', 'httpOnly' => true],
            'idParam' => '__docenteId',
            'authTimeoutParam' => '__docenteAuthExpire',
            'absoluteAuthTimeoutParam' => '__docenteAbsoluteAuthExpire',
            'returnUrlParam' => '__docenteReturnUrl',
            'loginUrl' => ['docente-area/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'locale' => 'it-IT',
            'dateFormat' => 'php:d/m/Y',
            'datetimeFormat' => 'php:d/m/Y H:i',
            'timeFormat' => 'php:H:i',
            'decimalSeparator' => ',',
            'thousandSeparator' => '.',
            'currencyCode' => 'EUR',
            'nullDisplay' => '-',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Dashboard
                '' => 'site/index',

                // Area Docenti (priorità alta)
                'area-docenti' => 'docente-area/index',
                'area-docenti/login' => 'docente-area/login',
                'area-docenti/registrazione' => 'docente-area/registrazione',
                'area-docenti/verifica-email/<token:[\w-]+>' => 'docente-area/verifica-email',
                'area-docenti/password-dimenticata' => 'docente-area/password-dimenticata',
                'area-docenti/reset-password/<token:[\w-]+>' => 'docente-area/reset-password',
                'area-docenti/logout' => 'docente-area/logout',
                'area-docenti/profilo' => 'docente-area/profilo',
                'area-docenti/bandi' => 'docente-area/bandi',
                'area-docenti/bandi/<id:\d+>' => 'docente-area/bando',
                'area-docenti/iscrizione/<moduloId:\d+>' => 'docente-area/iscrizione',
                'area-docenti/le-mie-iscrizioni' => 'docente-area/le-mie-iscrizioni',
                'area-docenti/le-mie-assegnazioni' => 'docente-area/le-mie-assegnazioni',
                'area-docenti/inserisci-ore/<assegnazione_id:\d+>' => 'docente-area/inserisci-ore',
                'area-docenti/le-mie-ore/<assegnazione_id:\d+>' => 'docente-area/le-mie-ore',
                'area-docenti/modifica-ore/<id:\d+>' => 'docente-area/modifica-ore',
                'area-docenti/elimina-ore/<id:\d+>' => 'docente-area/elimina-ore',
                'area-docenti/guida' => 'docente-area/guida',

                // Valutazione pubblica anonima
                'valutazione/<codice:[A-Z0-9]{8}>' => 'valutazione-public/index',
                'valutazione-public/<action:[\w-]+>' => 'valutazione-public/<action>',

                // CRUD standard per ogni entità
                '<controller:[\w-]+>' => '<controller>/index',
                '<controller:[\w-]+>/<id:\d+>' => '<controller>/view',
                '<controller:[\w-]+>/<action:[\w-]+>' => '<controller>/<action>',
                '<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => '<controller>/<action>',

                // Entità con codice fiscale come PK
                'docenti/<cf:[A-Z0-9]{16}>' => 'docente/view',
                'docenti/<action:[\w-]+>/<cf:[A-Z0-9]{16}>' => 'docente/<action>',
                'allievi/<cf:[A-Z0-9]{16}>' => 'allievo/view',
                'allievi/<action:[\w-]+>/<cf:[A-Z0-9]{16}>' => 'allievo/<action>',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'], // Permetti da tutti in Docker
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'], // Permetti da tutti in Docker
    ];
}

return $config;
