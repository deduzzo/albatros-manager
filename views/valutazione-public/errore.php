<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Codice non valido';
?>

<div class="container container-tight py-4">
    <div class="text-center mb-4">
        <a href="/" class="navbar-brand navbar-brand-autodark">
            <i class="ti ti-school" style="font-size: 2rem;"></i>
        </a>
    </div>

    <div class="card card-md">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="ti ti-alert-triangle" style="font-size: 4rem; color: #d63939;"></i>
            </div>
            <h2>Codice non valido</h2>
            <p class="text-muted mt-3">
                Il codice inserito non esiste oppure e stato gia utilizzato per una valutazione.
            </p>
            <p class="text-muted">
                Ogni codice puo essere utilizzato una sola volta.
            </p>
        </div>
    </div>
</div>
