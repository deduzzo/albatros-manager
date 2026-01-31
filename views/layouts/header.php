<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="header py-4">
    <div class="container">
        <div class="d-flex">
            <a class="header-brand" href="<?= Url::home() ?>">
                <i class="fe fe-feather" style="font-size: 2rem; color: #467fcf;"></i>
                <span class="ml-2" style="font-size: 1.25rem; font-weight: 600;">Albatros Manager</span>
            </a>
            <div class="d-flex order-lg-2 ml-auto">
                <div class="dropdown d-none d-md-flex">
                    <a class="nav-link icon" data-toggle="dropdown">
                        <i class="fe fe-bell"></i>
                        <span class="nav-unread"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                        <a href="#" class="dropdown-item d-flex">
                            <div>
                                <strong>Sistema</strong> Benvenuto in Albatros Manager
                                <div class="small text-muted">Adesso</div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item text-center text-muted-dark">Segna tutto come letto</a>
                    </div>
                </div>
                <div class="dropdown">
                    <a href="#" class="nav-link pr-0 leading-none" data-toggle="dropdown">
                        <span class="avatar bg-blue-lt">A</span>
                        <span class="ml-2 d-none d-lg-block">
                            <span class="text-default">Admin</span>
                            <small class="text-muted d-block mt-1">Amministratore</small>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                        <a class="dropdown-item" href="#">
                            <i class="dropdown-icon fe fe-user"></i> Profilo
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="dropdown-icon fe fe-settings"></i> Impostazioni
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <i class="dropdown-icon fe fe-log-out"></i> Esci
                        </a>
                    </div>
                </div>
            </div>
            <a href="#" class="header-toggler d-lg-none ml-3 ml-lg-0" data-toggle="collapse" data-target="#headerMenuCollapse">
                <span class="header-toggler-icon"></span>
            </a>
        </div>
    </div>
</div>
