<?php

/* @var $this \yii\web\View */
/* @var $corsi \app\models\Avviso[] */
/* @var $corsiStats array */

use yii\helpers\Url;

$this->title = 'I tuoi Corsi';
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1" style="font-size:1.5rem; font-weight:700; color:#1e293b;">I tuoi Corsi</h2>
        <p class="text-secondary mb-0" style="font-size:0.9rem;">Vista semplificata per la gestione operativa</p>
    </div>
    <a href="<?= Url::to(['/avviso/create']) ?>" class="btn btn-primary" style="background:var(--gestione-primary,#4263eb); border-color:var(--gestione-primary,#4263eb);">
        <i class="ti ti-plus me-1"></i> Nuovo Corso
    </a>
</div>

<?php if (empty($corsi)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-folder-off text-secondary" style="font-size:3rem;"></i>
            <h3 class="mt-3 text-secondary">Nessun corso trovato</h3>
            <p class="text-muted mb-4">Non sono ancora stati creati corsi. Inizia creando il primo.</p>
            <a href="<?= Url::to(['/avviso/create']) ?>" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Crea il primo corso
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row row-cards">
        <?php foreach ($corsi as $corso): ?>
            <div class="col-md-6 col-xl-4">
                <?= $this->render('_corso-card', [
                    'corso' => $corso,
                    'stats' => $corsiStats[$corso->id] ?? ['moduliTotali' => 0, 'moduliAssegnati' => 0, 'docenti' => 0, 'studenti' => 0],
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
