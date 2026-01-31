<?php

/* @var $this yii\web\View */

$this->title = 'Dashboard';
?>
<div class="my-3 my-md-5">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
        </div>

        <!-- Stats Cards -->
        <div class="row row-cards">
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="text-right text-green">
                            6% <i class="fe fe-chevron-up"></i>
                        </div>
                        <div class="h1 m-0">43</div>
                        <div class="text-muted mb-4">Nuovi Ordini</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="text-right text-red">
                            -3% <i class="fe fe-chevron-down"></i>
                        </div>
                        <div class="h1 m-0">17</div>
                        <div class="text-muted mb-4">Completati Oggi</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="text-right text-green">
                            9% <i class="fe fe-chevron-up"></i>
                        </div>
                        <div class="h1 m-0">7</div>
                        <div class="text-muted mb-4">In Lavorazione</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="text-right text-green">
                            3% <i class="fe fe-chevron-up"></i>
                        </div>
                        <div class="h1 m-0">1.2K</div>
                        <div class="text-muted mb-4">Clienti</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="text-right text-green">
                            12% <i class="fe fe-chevron-up"></i>
                        </div>
                        <div class="h1 m-0">&euro;9.5K</div>
                        <div class="text-muted mb-4">Ricavi Mensili</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="text-right text-red">
                            -1% <i class="fe fe-chevron-down"></i>
                        </div>
                        <div class="h1 m-0">621</div>
                        <div class="text-muted mb-4">Prodotti</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row row-cards">
            <div class="col-sm-6 col-lg-3">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-blue mr-3">
                            <i class="fe fe-dollar-sign"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="javascript:void(0)">132 <small>Vendite</small></a></h4>
                            <small class="text-muted">12 in attesa</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-green mr-3">
                            <i class="fe fe-shopping-cart"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="javascript:void(0)">78 <small>Ordini</small></a></h4>
                            <small class="text-muted">32 spediti</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-red mr-3">
                            <i class="fe fe-users"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="javascript:void(0)">1,352 <small>Utenti</small></a></h4>
                            <small class="text-muted">163 registrati oggi</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-yellow mr-3">
                            <i class="fe fe-message-square"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="javascript:void(0)">132 <small>Messaggi</small></a></h4>
                            <small class="text-muted">16 da leggere</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Cards -->
        <div class="row row-cards">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="h5">Feedback Nuovo</div>
                        <div class="display-4 font-weight-bold mb-4">62</div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-red" style="width: 28%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="h5">Profitto Giornaliero</div>
                        <div class="display-4 font-weight-bold mb-4">&euro;652</div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-green" style="width: 84%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="h5">Ordini Completati</div>
                        <div class="display-4 font-weight-bold mb-4">89%</div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-blue" style="width: 89%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="h5">Soddisfazione</div>
                        <div class="display-4 font-weight-bold mb-4">95%</div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-yellow" style="width: 95%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ordini Recenti</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap">
                            <thead>
                                <tr>
                                    <th class="w-1">N.</th>
                                    <th>Descrizione</th>
                                    <th>Cliente</th>
                                    <th>Data</th>
                                    <th>Stato</th>
                                    <th>Importo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="text-muted">001401</span></td>
                                    <td><a href="#" class="text-inherit">Servizio Premium</a></td>
                                    <td>Mario Rossi</td>
                                    <td>15 Gen 2026</td>
                                    <td><span class="status-icon bg-success"></span> Pagato</td>
                                    <td>&euro;887</td>
                                    <td class="text-right">
                                        <a href="#" class="btn btn-secondary btn-sm">Gestisci</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="text-muted">001402</span></td>
                                    <td><a href="#" class="text-inherit">Consulenza</a></td>
                                    <td>Anna Bianchi</td>
                                    <td>12 Gen 2026</td>
                                    <td><span class="status-icon bg-warning"></span> In Attesa</td>
                                    <td>&euro;1,200</td>
                                    <td class="text-right">
                                        <a href="#" class="btn btn-secondary btn-sm">Gestisci</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="text-muted">001403</span></td>
                                    <td><a href="#" class="text-inherit">Sviluppo Web</a></td>
                                    <td>Giuseppe Verdi</td>
                                    <td>23 Gen 2026</td>
                                    <td><span class="status-icon bg-warning"></span> In Attesa</td>
                                    <td>&euro;534</td>
                                    <td class="text-right">
                                        <a href="#" class="btn btn-secondary btn-sm">Gestisci</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="text-muted">001404</span></td>
                                    <td><a href="#" class="text-inherit">Manutenzione</a></td>
                                    <td>Laura Neri</td>
                                    <td>2 Gen 2026</td>
                                    <td><span class="status-icon bg-success"></span> Completato</td>
                                    <td>&euro;1,500</td>
                                    <td class="text-right">
                                        <a href="#" class="btn btn-secondary btn-sm">Gestisci</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="text-muted">001405</span></td>
                                    <td><a href="#" class="text-inherit">Formazione</a></td>
                                    <td>Marco Polo</td>
                                    <td>29 Gen 2026</td>
                                    <td><span class="status-icon bg-danger"></span> Scaduto</td>
                                    <td>&euro;648</td>
                                    <td class="text-right">
                                        <a href="#" class="btn btn-secondary btn-sm">Gestisci</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side by Side Cards -->
        <div class="row row-cards row-deck">
            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Stato Progetti</h4>
                    </div>
                    <table class="table card-table">
                        <tr>
                            <td>Dashboard Admin</td>
                            <td class="text-right"><span class="badge badge-default">65%</span></td>
                        </tr>
                        <tr>
                            <td>Landing Page</td>
                            <td class="text-right"><span class="badge badge-success">Completato</span></td>
                        </tr>
                        <tr>
                            <td>Backend API</td>
                            <td class="text-right"><span class="badge badge-danger">Bloccato</span></td>
                        </tr>
                        <tr>
                            <td>App Mobile</td>
                            <td class="text-right"><span class="badge badge-default">40%</span></td>
                        </tr>
                        <tr>
                            <td>Template Email</td>
                            <td class="text-right"><span class="badge badge-warning">In Review</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Browser Stats</h4>
                    </div>
                    <table class="table card-table">
                        <tr>
                            <td width="1"><i class="fa fa-chrome text-muted"></i></td>
                            <td>Google Chrome</td>
                            <td class="text-right"><span class="text-muted">45%</span></td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-firefox text-muted"></i></td>
                            <td>Mozilla Firefox</td>
                            <td class="text-right"><span class="text-muted">20%</span></td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-safari text-muted"></i></td>
                            <td>Apple Safari</td>
                            <td class="text-right"><span class="text-muted">18%</span></td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-edge text-muted"></i></td>
                            <td>Microsoft Edge</td>
                            <td class="text-right"><span class="text-muted">12%</span></td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-opera text-muted"></i></td>
                            <td>Opera</td>
                            <td class="text-right"><span class="text-muted">5%</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Team Recente</h3>
                    </div>
                    <div class="card-body o-auto" style="height: 15rem">
                        <ul class="list-unstyled list-separated">
                            <li class="list-separated-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-md d-block bg-blue-lt">MR</span>
                                    </div>
                                    <div class="col">
                                        <div><a href="#" class="text-inherit">Mario Rossi</a></div>
                                        <small class="d-block text-muted">m.rossi@example.com</small>
                                    </div>
                                </div>
                            </li>
                            <li class="list-separated-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-md d-block bg-green-lt">AB</span>
                                    </div>
                                    <div class="col">
                                        <div><a href="#" class="text-inherit">Anna Bianchi</a></div>
                                        <small class="d-block text-muted">a.bianchi@example.com</small>
                                    </div>
                                </div>
                            </li>
                            <li class="list-separated-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-md d-block bg-yellow-lt">GV</span>
                                    </div>
                                    <div class="col">
                                        <div><a href="#" class="text-inherit">Giuseppe Verdi</a></div>
                                        <small class="d-block text-muted">g.verdi@example.com</small>
                                    </div>
                                </div>
                            </li>
                            <li class="list-separated-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-md d-block bg-red-lt">LN</span>
                                    </div>
                                    <div class="col">
                                        <div><a href="#" class="text-inherit">Laura Neri</a></div>
                                        <small class="d-block text-muted">l.neri@example.com</small>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
