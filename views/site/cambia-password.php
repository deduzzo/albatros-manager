<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $isForced bool */

$this->title = 'Cambia Password';
if (!$isForced) {
    $this->params['breadcrumbs'][] = ['label' => 'Profilo', 'url' => ['profilo']];
    $this->params['breadcrumbs'][] = $this->title;
}
?>

<?php if ($isForced): ?>
<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <div class="mb-3">
                <span class="avatar avatar-xl bg-orange-lt">
                    <i class="ti ti-key" style="font-size: 2.5rem;"></i>
                </span>
            </div>
            <h2>Cambio Password Obbligatorio</h2>
        </div>

        <div class="card card-md">
            <div class="card-body">
                <div class="alert alert-warning">
                    <div class="d-flex">
                        <div>
                            <i class="ti ti-alert-triangle icon me-2"></i>
                        </div>
                        <div>
                            <p class="mb-0">
                                Il tuo amministratore richiede il cambio della password prima di poter continuare ad utilizzare il portale.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="post">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                    <div class="mb-3">
                        <label class="form-label">Nuova password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6" autofocus>
                        <div class="form-hint">Minimo 6 caratteri</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Conferma nuova password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-key me-1"></i> Cambia Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-key me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-key me-2"></i>Cambia Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                            <div class="mb-3">
                                <label class="form-label">Password corrente</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nuova password</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                                <div class="form-hint">Minimo 6 caratteri</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Conferma nuova password</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>

                            <div class="d-flex justify-content-between">
                                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Annulla', ['profilo'], ['class' => 'btn btn-ghost-secondary']) ?>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-key me-1"></i> Cambia Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
