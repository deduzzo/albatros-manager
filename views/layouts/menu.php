<?php
use yii\helpers\Url;

$currentController = Yii::$app->controller->id;
$currentAction = Yii::$app->controller->action->id;

// Helper per verificare se un menu è attivo
$isActive = function($controllers) use ($currentController) {
    $controllers = (array) $controllers;
    return in_array($currentController, $controllers) ? 'active' : '';
};
?>

<header class="navbar-expand-md">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
            <div class="container-xl">
                <ul class="navbar-nav">
                    <!-- Dashboard -->
                    <li class="nav-item <?= $isActive('site') ?>">
                        <a class="nav-link" href="<?= Url::to(['/site/index']) ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-home"></i>
                            </span>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </li>

                    <!-- Configurazione -->
                    <li class="nav-item dropdown <?= $isActive(['fonte-finanziamento', 'conto-corrente', 'sede', 'tipo-documento']) ?>">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-settings"></i>
                            </span>
                            <span class="nav-link-title">Configurazione</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item <?= $currentController == 'fonte-finanziamento' ? 'active' : '' ?>" href="<?= Url::to(['/fonte-finanziamento/index']) ?>">
                                <i class="ti ti-credit-card me-2"></i> Fonti di Finanziamento
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-building-bank me-2"></i> Conti Correnti
                            </a>
                            <a class="dropdown-item <?= $currentController == 'sede' ? 'active' : '' ?>" href="<?= Url::to(['/sede/index']) ?>">
                                <i class="ti ti-map-pin me-2"></i> Sedi
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-file-text me-2"></i> Tipi Documento
                            </a>
                        </div>
                    </li>

                    <!-- Progetti -->
                    <li class="nav-item dropdown <?= $isActive(['progetto', 'avviso', 'modulo']) ?>">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-folder"></i>
                            </span>
                            <span class="nav-link-title">Progetti</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item <?= $currentController == 'progetto' ? 'active' : '' ?>" href="<?= Url::to(['/progetto/index']) ?>">
                                <i class="ti ti-folder me-2"></i> Progetti
                            </a>
                            <a class="dropdown-item <?= $currentController == 'avviso' ? 'active' : '' ?>" href="<?= Url::to(['/avviso/index']) ?>">
                                <i class="ti ti-file me-2"></i> Avvisi/Bandi
                            </a>
                            <a class="dropdown-item <?= $currentController == 'modulo' ? 'active' : '' ?>" href="<?= Url::to(['/modulo/index']) ?>">
                                <i class="ti ti-book me-2"></i> Moduli/Corsi
                            </a>
                        </div>
                    </li>

                    <!-- Personale -->
                    <li class="nav-item dropdown <?= $isActive(['docente', 'allievo', 'tag-categoria', 'tag-curriculum']) ?>">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-users"></i>
                            </span>
                            <span class="nav-link-title">Personale</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item <?= $currentController == 'docente' ? 'active' : '' ?>" href="<?= Url::to(['/docente/index']) ?>">
                                <i class="ti ti-user me-2"></i> Docenti
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-users me-2"></i> Allievi
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-file-plus me-2"></i> Documentazione
                            </a>
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-header">Tag Curriculum</span>
                            <a class="dropdown-item <?= $currentController == 'tag-categoria' ? 'active' : '' ?>" href="#">
                                <i class="ti ti-category me-2"></i> Categorie Tag
                            </a>
                            <a class="dropdown-item <?= $currentController == 'tag-curriculum' ? 'active' : '' ?>" href="#">
                                <i class="ti ti-tags me-2"></i> Gestione Tag
                            </a>
                        </div>
                    </li>

                    <!-- Gestione -->
                    <li class="nav-item dropdown <?= $isActive(['istanza', 'assegnazione', 'contratto', 'calendario']) ?>">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-clipboard"></i>
                            </span>
                            <span class="nav-link-title">Gestione</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item <?= $currentController == 'istanza' ? 'active' : '' ?>" href="<?= Url::to(['/istanza/index']) ?>">
                                <i class="ti ti-send me-2"></i> Istanze
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-circle-check me-2"></i> Assegnazioni
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-file-text me-2"></i> Contratti
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-calendar me-2"></i> Calendario
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-checkbox me-2"></i> Presenze
                            </a>
                        </div>
                    </li>

                    <!-- Pagamenti -->
                    <li class="nav-item <?= $isActive('pagamento') ?>">
                        <a class="nav-link" href="#">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-currency-euro"></i>
                            </span>
                            <span class="nav-link-title">Pagamenti</span>
                        </a>
                    </li>

                    <!-- Report -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-chart-bar"></i>
                            </span>
                            <span class="nav-link-title">Report</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-chart-pie me-2"></i> Statistiche
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-download me-2"></i> Export
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="ti ti-activity me-2"></i> Audit Log
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
