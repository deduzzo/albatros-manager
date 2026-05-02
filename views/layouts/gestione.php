<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\components\assets\TablerAsset;
use app\components\assets\TomSelectAsset;
use app\models\Avviso;
use yii\helpers\Html;
use yii\helpers\Url;

TablerAsset::register($this);
TomSelectAsset::register($this);

// Determina se c'è un corso selezionato
$corsoId = Yii::$app->request->get('id');
$currentSection = Yii::$app->request->get('section', 'panoramica');

// Carica lista corsi per il selettore rapido
$corsiList = Avviso::find()
    ->andWhere(['deleted_at' => null])
    ->with('progetto')
    ->orderBy(['codice_avviso' => SORT_ASC])
    ->all();

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="light">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - Gestione Semplificata</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php $this->head() ?>
    <style>
        :root {
            --tblr-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            --gestione-primary: #4263eb;
            --gestione-primary-dark: #3451c7;
            --gestione-secondary: #7048e8;
        }
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
        .navbar-gestione {
            background: linear-gradient(135deg, #4263eb 0%, #7048e8 100%);
            box-shadow: 0 2px 12px rgba(66, 99, 235, 0.35);
        }
        .navbar-gestione .navbar-brand,
        .navbar-gestione .nav-link,
        .navbar-gestione .navbar-toggler-icon {
            color: #fff !important;
        }
        .navbar-gestione .nav-link:hover {
            color: rgba(255,255,255,0.8) !important;
        }
        .navbar-gestione .badge-gestione {
            background: rgba(255,255,255,0.22);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            font-size: 0.65rem;
            letter-spacing: 0.05em;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .sidebar-gestione {
            background: #fff;
            border-right: 1px solid #e6e7e9;
            min-height: calc(100vh - 64px);
            padding: 1.25rem 0;
            width: 220px;
            flex-shrink: 0;
        }
        .sidebar-gestione .nav-link {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.5rem 1.25rem;
            color: #475569;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0;
            transition: all 0.15s;
        }
        .sidebar-gestione .nav-link:hover {
            background: #f1f5f9;
            color: var(--gestione-primary);
        }
        .sidebar-gestione .nav-link.active {
            background: #eff3ff;
            color: var(--gestione-primary);
            border-right: 3px solid var(--gestione-primary);
        }
        .sidebar-gestione .nav-link i {
            font-size: 1.1rem;
            opacity: 0.75;
        }
        .sidebar-gestione .nav-link.active i {
            opacity: 1;
        }
        .sidebar-gestione .sidebar-divider {
            border-top: 1px solid #e6e7e9;
            margin: 0.75rem 1.25rem;
        }
        .gestione-content {
            flex: 1;
            min-width: 0;
            padding: 1.5rem;
        }
        .gestione-layout {
            display: flex;
            align-items: flex-start;
        }
        .gestione-selector select {
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            color: #fff;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.85rem;
            cursor: pointer;
            max-width: 260px;
        }
        .gestione-selector select option {
            color: #222;
            background: #fff;
        }
        .gestione-selector select:focus {
            outline: none;
            border-color: rgba(255,255,255,0.6);
            box-shadow: 0 0 0 2px rgba(255,255,255,0.2);
        }
        /* Alert flash */
        .gestione-flash {
            padding: 0 1.5rem;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="page">
    <!-- Navbar Gestione -->
    <header class="navbar navbar-expand-md navbar-gestione d-print-none">
        <div class="container-fluid px-3">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#gestione-menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Brand -->
            <a href="<?= Url::to(['/gestione']) ?>" class="navbar-brand d-flex align-items-center gap-2">
                <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-badge-small.png" alt="Albatros" height="28" style="filter: brightness(0) invert(1);">
                <span class="fw-bold" style="color:#fff;">Albatros</span>
                <span class="badge-gestione ms-1">Gestione Semplificata</span>
            </a>

            <div class="collapse navbar-collapse" id="gestione-menu">
                <div class="ms-auto d-flex align-items-center gap-3">
                    <!-- Selettore rapido corso -->
                    <div class="gestione-selector">
                        <select onchange="if(this.value) window.location='/gestione/corso/'+this.value; else window.location='/gestione';">
                            <option value="">-- Seleziona corso --</option>
                            <?php foreach ($corsiList as $corso): ?>
                                <option value="<?= $corso->id ?>" <?= ($corsoId == $corso->id) ? 'selected' : '' ?>>
                                    <?= Html::encode($corso->codice_avviso . ' - ' . mb_substr($corso->titolo, 0, 35)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Link modalità avanzata -->
                    <a href="<?= Url::to(['/site/index']) ?>" class="nav-link d-flex align-items-center gap-1" title="Modalità Avanzata" style="color:rgba(255,255,255,0.85);">
                        <i class="ti ti-settings"></i>
                        <span class="d-none d-lg-inline" style="font-size:0.82rem;">Avanzata</span>
                    </a>

                    <!-- Logout -->
                    <?php if (!Yii::$app->user->isGuest): ?>
                        <?= Html::a(
                            '<i class="ti ti-logout"></i><span class="d-none d-lg-inline ms-1" style="font-size:0.82rem;">Esci</span>',
                            ['/site/logout'],
                            ['class' => 'nav-link', 'data-method' => 'post', 'style' => 'color:rgba(255,255,255,0.85);']
                        ) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <?php
    // Calcola contatori alert per la sidebar (solo se c'e un corso selezionato)
    $alertCounts = [
        'panoramica'      => 0,
        'piano-formativo' => 0,
        'personale'       => 0,
        'studenti'        => 0,
        'calendario'      => 0,
        'contratti'       => 0,
        'borse-studio'    => 0,
        'import-export'   => 0,
    ];

    if ($corsoId) {
        $corsoModel = Avviso::findOne($corsoId);

        // personale: docenti assegnati senza contratto attivo
        $alertCounts['personale'] = (int) \app\models\Assegnazione::find()
            ->alias('a')
            ->innerJoin('{{%modulo}} m', 'm.id = a.modulo_id AND m.deleted_at IS NULL')
            ->leftJoin('{{%contratto}} co', 'co.assegnazione_id = a.id AND co.deleted_at IS NULL')
            ->where(['a.deleted_at' => null, 'co.id' => null, 'm.avviso_id' => $corsoId])
            ->count();

        // calendario: presenze docente non acquisite (data passata, docente_presente IS NULL)
        $alertCounts['calendario'] = (int) \app\models\Calendario::find()
            ->alias('c')
            ->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')
            ->where(['c.deleted_at' => null, 'c.docente_presente' => null, 'm.avviso_id' => $corsoId])
            ->andWhere(['<', 'c.data', date('Y-m-d')])
            ->count();

        // piano-formativo: moduli scoperti (senza assegnazione attiva)
        $alertCounts['piano-formativo'] = (int) \app\models\Modulo::find()
            ->alias('m')
            ->leftJoin('{{%assegnazione}} a', 'a.modulo_id = m.id AND a.deleted_at IS NULL')
            ->where(['m.deleted_at' => null, 'a.id' => null, 'm.avviso_id' => $corsoId])
            ->count();

        // borse-studio: borse con erogazione bloccata sul progetto del corso
        if ($corsoModel) {
            $alertCounts['borse-studio'] = (int) \app\models\BorsaStudio::find()
                ->where([
                    'progetto_id' => $corsoModel->progetto_id,
                    'status'      => \app\models\BorsaStudio::STATUS_EROGAZIONE_BLOCCATA,
                    'deleted_at'  => null,
                ])
                ->count();
        }

        // panoramica: somma di tutti gli alert
        $alertCounts['panoramica'] = $alertCounts['personale']
            + $alertCounts['calendario']
            + $alertCounts['borse-studio']
            + $alertCounts['piano-formativo'];
    }
    ?>
    <?php if ($corsoId): ?>
    <!-- Layout con sidebar -->
    <div class="gestione-layout">
        <?= $this->render('_gestione_sidebar', ['corsoId' => $corsoId, 'currentSection' => $currentSection, 'alertCounts' => $alertCounts]) ?>
        <div class="gestione-content">
            <!-- Flash Messages -->
            <?php foreach (Yii::$app->session->getAllFlashes() as $type => $messages): ?>
                <?php $alertClass = $type === 'success' ? 'alert-success' : ($type === 'error' ? 'alert-danger' : ($type === 'warning' ? 'alert-warning' : 'alert-info')); ?>
                <?php foreach ((array)$messages as $message): ?>
                    <div class="alert <?= $alertClass ?> alert-dismissible mb-3" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <?= $content ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Layout centrato (index) -->
    <div class="page-wrapper">
        <div class="container-xl py-4">
            <!-- Flash Messages -->
            <?php foreach (Yii::$app->session->getAllFlashes() as $type => $messages): ?>
                <?php $alertClass = $type === 'success' ? 'alert-success' : ($type === 'error' ? 'alert-danger' : ($type === 'warning' ? 'alert-warning' : 'alert-info')); ?>
                <?php foreach ((array)$messages as $message): ?>
                    <div class="alert <?= $alertClass ?> alert-dismissible mb-3" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <?= $content ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
