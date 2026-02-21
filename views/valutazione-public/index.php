<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ValutazioneCodice $valutazioneCodice */
/** @var app\models\Modulo $modulo */
/** @var app\models\Docente|null $docente */

$this->title = 'Valutazione Docente';

$nomeDocente = $docente ? Html::encode($docente->cognome . ' ' . $docente->nome) : 'Docente';
$titoloModulo = Html::encode($modulo->titolo);

$criteri = [
    1 => 'Preparazione sulla materia',
    2 => 'Capacita di spiegazione',
    3 => 'Disponibilita e cortesia',
    4 => 'Puntualita e organizzazione',
    5 => 'Valutazione complessiva',
];
?>

<style>
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 4px;
}
.star-rating input {
    display: none;
}
.star-rating label {
    cursor: pointer;
    font-size: 1.75rem;
    color: #d1d5db;
    transition: color 0.15s ease;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #f59f00;
}
.star-rating label .ti-star-filled {
    display: none;
}
.star-rating label .ti-star {
    display: inline;
}
.star-rating input:checked ~ label .ti-star-filled,
.star-rating label:hover .ti-star-filled,
.star-rating label:hover ~ label .ti-star-filled {
    display: inline;
}
.star-rating input:checked ~ label .ti-star,
.star-rating label:hover .ti-star,
.star-rating label:hover ~ label .ti-star {
    display: none;
}
.criterio-row {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e6e7e9;
}
.criterio-row:last-child {
    border-bottom: none;
}
</style>

<div class="container container-tight py-4">
    <div class="text-center mb-4">
        <a href="/" class="navbar-brand navbar-brand-autodark">
            <i class="ti ti-school" style="font-size: 2rem;"></i>
        </a>
    </div>

    <?php
    $flashes = Yii::$app->session->getAllFlashes();
    foreach ($flashes as $type => $message) {
        $alertClass = $type === 'error' ? 'alert-danger' : 'alert-success';
        echo '<div class="alert ' . $alertClass . ' alert-dismissible" role="alert">';
        echo '<div class="d-flex"><div>' . Html::encode($message) . '</div></div>';
        echo '<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>';
        echo '</div>';
    }
    ?>

    <div class="card card-md">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <i class="ti ti-star me-2"></i>Valutazione Docente
                </h3>
                <p class="card-subtitle mt-1">
                    <?= $titoloModulo ?> &mdash; <?= $nomeDocente ?>
                </p>
            </div>
        </div>
        <div class="card-body">
            <form method="post" action="<?= \yii\helpers\Url::to(['valutazione-public/submit']) ?>" id="valutazione-form">
                <input type="hidden" name="codice" value="<?= Html::encode($valutazioneCodice->codice) ?>">

                <p class="text-muted mb-4">
                    Esprimi la tua valutazione su ciascun criterio selezionando da 1 a 5 stelle.
                    La valutazione e completamente anonima.
                </p>

                <?php foreach ($criteri as $num => $label): ?>
                <div class="criterio-row">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6">
                            <strong><?= $num ?>. <?= $label ?></strong>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="star-rating">
                                <?php for ($s = 5; $s >= 1; $s--): ?>
                                <input type="radio" id="criterio_<?= $num ?>_<?= $s ?>" name="criterio_<?= $num ?>" value="<?= $s ?>" required>
                                <label for="criterio_<?= $num ?>_<?= $s ?>" title="<?= $s ?> stelle">
                                    <i class="ti ti-star"></i>
                                    <i class="ti ti-star-filled"></i>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="mb-3 mt-4">
                    <label class="form-label" for="commento">Commento (facoltativo)</label>
                    <textarea class="form-control" id="commento" name="commento" rows="3" placeholder="Lascia un commento sulla tua esperienza..."></textarea>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-send me-1"></i>Invia Valutazione
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-muted mt-3">
        <small><i class="ti ti-lock me-1"></i>Le risposte sono completamente anonime</small>
    </div>
</div>
