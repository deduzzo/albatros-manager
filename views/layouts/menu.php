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
                    <li class="nav-item dropdown <?= $isActive(['fonte-finanziamento', 'conto-corrente', 'sede', 'tipo-documento', 'configurazione', 'criterio-valutazione', 'utente']) ?>">
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
                            <a class="dropdown-item <?= $currentController == 'conto-corrente' ? 'active' : '' ?>" href="<?= Url::to(['/conto-corrente/index']) ?>">
                                <i class="ti ti-building-bank me-2"></i> Conti Correnti
                            </a>
                            <a class="dropdown-item <?= $currentController == 'sede' ? 'active' : '' ?>" href="<?= Url::to(['/sede/index']) ?>">
                                <i class="ti ti-map-pin me-2"></i> Sedi
                            </a>
                            <a class="dropdown-item <?= $currentController == 'tipo-documento' ? 'active' : '' ?>" href="<?= Url::to(['/tipo-documento/index']) ?>">
                                <i class="ti ti-file-text me-2"></i> Tipi Documento
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item <?= $currentController == 'configurazione' ? 'active' : '' ?>" href="<?= Url::to(['/configurazione/index']) ?>">
                                <i class="ti ti-building me-2"></i> Dati Fondazione
                            </a>
                            <a class="dropdown-item <?= $currentController == 'criterio-valutazione' ? 'active' : '' ?>" href="<?= Url::to(['/criterio-valutazione/index']) ?>">
                                <i class="ti ti-star me-2"></i> Criteri Valutazione
                            </a>
                            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()): ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item <?= $currentController == 'utente' ? 'active' : '' ?>" href="<?= Url::to(['/utente/index']) ?>">
                                    <i class="ti ti-users-cog me-2"></i> Gestione Utenti
                                </a>
                            <?php endif; ?>
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
                    <li class="nav-item dropdown <?= $isActive(['docente', 'allievo', 'tag-categoria', 'tag-curriculum', 'rimborso-spesa', 'richiesta-fatturazione']) ?>">
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
                            <a class="dropdown-item <?= $currentController == 'docente' && $currentAction == 'albo' ? 'active' : '' ?>" href="<?= Url::to(['/docente/albo']) ?>">
                                <i class="ti ti-address-book me-2"></i> Albo Docenti
                            </a>
                            <a class="dropdown-item <?= $currentController == 'allievo' ? 'active' : '' ?>" href="<?= Url::to(['/allievo/index']) ?>">
                                <i class="ti ti-school me-2"></i> Allievi
                            </a>
                            <a class="dropdown-item <?= $currentController == 'documentazione' ? 'active' : '' ?>" href="<?= Url::to(['/documentazione/index']) ?>">
                                <i class="ti ti-file-plus me-2"></i> Documentazione
                            </a>
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-header">Amministrazione Docenti</span>
                            <a class="dropdown-item <?= $currentController == 'richiesta-fatturazione' ? 'active' : '' ?>" href="<?= Url::to(['/richiesta-fatturazione/index']) ?>">
                                <i class="ti ti-file-invoice me-2"></i> Fatturazione / Cedolini
                            </a>
                            <a class="dropdown-item <?= $currentController == 'rimborso-spesa' ? 'active' : '' ?>" href="<?= Url::to(['/rimborso-spesa/index']) ?>">
                                <i class="ti ti-receipt-refund me-2"></i> Rimborsi Spese
                            </a>
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-header">Tag Curriculum</span>
                            <a class="dropdown-item <?= $currentController == 'tag-categoria' ? 'active' : '' ?>" href="<?= Url::to(['/tag-categoria/index']) ?>">
                                <i class="ti ti-category me-2"></i> Categorie Tag
                            </a>
                            <a class="dropdown-item <?= $currentController == 'tag-curriculum' ? 'active' : '' ?>" href="<?= Url::to(['/tag-curriculum/index']) ?>">
                                <i class="ti ti-tags me-2"></i> Gestione Tag
                            </a>
                        </div>
                    </li>

                    <!-- Gestione -->
                    <li class="nav-item dropdown <?= $isActive(['istanza', 'assegnazione', 'contratto', 'calendario', 'presenza-allievo', 'iscrizione', 'valutazione-docente', 'criterio-valutazione-docente', 'rendicontazione-ore', 'notifica']) ?>">
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
                            <a class="dropdown-item <?= $currentController == 'assegnazione' ? 'active' : '' ?>" href="<?= Url::to(['/assegnazione/index']) ?>">
                                <i class="ti ti-circle-check me-2"></i> Assegnazioni
                            </a>
                            <a class="dropdown-item <?= $currentController == 'contratto' ? 'active' : '' ?>" href="<?= Url::to(['/contratto/index']) ?>">
                                <i class="ti ti-file-text me-2"></i> Contratti
                            </a>
                            <a class="dropdown-item <?= $currentController == 'rendicontazione-ore' ? 'active' : '' ?>" href="<?= Url::to(['/rendicontazione-ore/dashboard']) ?>">
                                <i class="ti ti-clock-hour-4 me-2"></i> Rendicontazione Ore
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item <?= $currentController == 'calendario' ? 'active' : '' ?>" href="<?= Url::to(['/calendario/index']) ?>">
                                <i class="ti ti-calendar me-2"></i> Calendario
                            </a>
                            <a class="dropdown-item <?= $currentController == 'iscrizione' ? 'active' : '' ?>" href="<?= Url::to(['/iscrizione/index']) ?>">
                                <i class="ti ti-user-plus me-2"></i> Iscrizioni
                            </a>
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-header">Graduatorie</span>
                            <a class="dropdown-item <?= $currentController == 'istanza' && $currentAction == 'graduatoria' ? 'active' : '' ?>" href="<?= Url::to(['/istanza/graduatoria']) ?>">
                                <i class="ti ti-list-numbers me-2"></i> Graduatoria Docenti
                            </a>
                            <a class="dropdown-item <?= $currentController == 'presenza-allievo' ? 'active' : '' ?>" href="<?= Url::to(['/presenza-allievo/index']) ?>">
                                <i class="ti ti-checkbox me-2"></i> Presenze
                            </a>
                            <a class="dropdown-item <?= $currentController == 'iscrizione' && in_array($currentAction, ['candidature', 'graduatoria-studenti', 'valuta']) ? 'active' : '' ?>" href="<?= Url::to(['/iscrizione/candidature']) ?>">
                                <i class="ti ti-users me-2"></i> Candidature Studenti
                            </a>
                            <a class="dropdown-item <?= $currentController == 'valutazione-docente' ? 'active' : '' ?>" href="<?= Url::to(['/valutazione-docente/index']) ?>">
                                <i class="ti ti-star me-2"></i> Valutazioni Docenti
                            </a>
                            <a class="dropdown-item <?= $currentController == 'criterio-valutazione-docente' ? 'active' : '' ?>" href="<?= Url::to(['/criterio-valutazione-docente/index']) ?>">
                                <i class="ti ti-star-half me-2"></i> Criteri Valutazione Docente
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item <?= $currentController == 'notifica' ? 'active' : '' ?>" href="<?= Url::to(['/notifica/index']) ?>">
                                <i class="ti ti-bell-plus me-2"></i> Notifiche
                            </a>
                        </div>
                    </li>

                    <!-- Contabilita' -->
                    <li class="nav-item dropdown <?= $isActive(['pagamento', 'bilancio', 'movimento-spesa', 'tranche-finanziamento', 'voce-spesa']) ?>">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-currency-euro"></i>
                            </span>
                            <span class="nav-link-title">Contabilita'</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item <?= $currentController == 'bilancio' ? 'active' : '' ?>" href="<?= Url::to(['/bilancio/index']) ?>">
                                <i class="ti ti-report-analytics me-2"></i> Bilancio
                            </a>
                            <a class="dropdown-item <?= $currentController == 'pagamento' ? 'active' : '' ?>" href="<?= Url::to(['/pagamento/index']) ?>">
                                <i class="ti ti-cash me-2"></i> Pagamenti
                            </a>
                            <a class="dropdown-item <?= $currentController == 'movimento-spesa' ? 'active' : '' ?>" href="<?= Url::to(['/movimento-spesa/index']) ?>">
                                <i class="ti ti-arrows-exchange me-2"></i> Movimenti Spesa
                            </a>
                            <a class="dropdown-item <?= $currentController == 'tranche-finanziamento' ? 'active' : '' ?>" href="<?= Url::to(['/tranche-finanziamento/index']) ?>">
                                <i class="ti ti-coins me-2"></i> Tranche Finanziamento
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item <?= $currentController == 'voce-spesa' ? 'active' : '' ?>" href="<?= Url::to(['/voce-spesa/index']) ?>">
                                <i class="ti ti-list-tree me-2"></i> Voci di Spesa
                            </a>
                        </div>
                    </li>

                    <!-- Borse di Studio -->
                    <li class="nav-item <?= $isActive('borsa-studio') ?>">
                        <a class="nav-link" href="<?= Url::to(['/borsa-studio/index']) ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-school"></i>
                            </span>
                            <span class="nav-link-title">Borse di Studio</span>
                        </a>
                    </li>

                    <!-- Strumenti -->
                    <li class="nav-item <?= $isActive('firma-digitale') ?>">
                        <a class="nav-link" href="<?= Url::to(['/firma-digitale/index']) ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-certificate"></i>
                            </span>
                            <span class="nav-link-title">Firma Digitale</span>
                        </a>
                    </li>

                    <!-- Report -->
                    <li class="nav-item dropdown <?= $isActive(['report', 'audit-log']) ?>">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-chart-bar"></i>
                            </span>
                            <span class="nav-link-title">Report</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item <?= $currentController == 'report' && $currentAction == 'statistiche' ? 'active' : '' ?>" href="<?= Url::to(['/report/statistiche']) ?>">
                                <i class="ti ti-chart-pie me-2"></i> Statistiche
                            </a>
                            <a class="dropdown-item <?= $currentController == 'report' && $currentAction == 'export' ? 'active' : '' ?>" href="<?= Url::to(['/report/export']) ?>">
                                <i class="ti ti-download me-2"></i> Export
                            </a>
                            <a class="dropdown-item <?= $currentController == 'audit-log' ? 'active' : '' ?>" href="<?= Url::to(['/audit-log/index']) ?>">
                                <i class="ti ti-activity me-2"></i> Audit Log
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
