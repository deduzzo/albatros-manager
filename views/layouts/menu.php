<?php
use yii\helpers\Url;
?>
<div class="header collapse d-lg-flex p-0" id="headerMenuCollapse">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-3 ml-auto">
                <form class="input-icon my-3 my-lg-0">
                    <input type="search" class="form-control header-search" placeholder="Cerca..." tabindex="1">
                    <div class="input-icon-addon">
                        <i class="fe fe-search"></i>
                    </div>
                </form>
            </div>
            <div class="col-lg order-lg-first">
                <ul class="nav nav-tabs border-0 flex-column flex-lg-row">
                    <li class="nav-item">
                        <a href="<?= Url::to(['/site/index']) ?>" class="nav-link active">
                            <i class="fe fe-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown">
                            <i class="fe fe-users"></i> Utenti
                        </a>
                        <div class="dropdown-menu dropdown-menu-arrow">
                            <a href="#" class="dropdown-item">Lista Utenti</a>
                            <a href="#" class="dropdown-item">Nuovo Utente</a>
                            <a href="#" class="dropdown-item">Ruoli e Permessi</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown">
                            <i class="fe fe-box"></i> Gestione
                        </a>
                        <div class="dropdown-menu dropdown-menu-arrow">
                            <a href="#" class="dropdown-item">Prodotti</a>
                            <a href="#" class="dropdown-item">Ordini</a>
                            <a href="#" class="dropdown-item">Inventario</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown">
                            <i class="fe fe-bar-chart-2"></i> Report
                        </a>
                        <div class="dropdown-menu dropdown-menu-arrow">
                            <a href="#" class="dropdown-item">Statistiche</a>
                            <a href="#" class="dropdown-item">Analisi</a>
                            <a href="#" class="dropdown-item">Export</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fe fe-settings"></i> Impostazioni
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
