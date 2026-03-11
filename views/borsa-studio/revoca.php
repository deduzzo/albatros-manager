<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\BorsaStudio $model */

$this->title = 'Revoca Borsa di Studio #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Borse di Studio', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Borsa #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Revoca';

$formatter = Yii::$app->formatter;
?>
<div class="borsa-studio-revoca">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-ban me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <!-- Riepilogo Borsa (readonly) -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Riepilogo Borsa di Studio</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Studente</div>
                            <div class="datagrid-content">
                                <?= $model->allievo ? Html::encode($model->allievo->getNomeCompleto()) : $model->allievo_codice_fiscale ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Codice Fiscale</div>
                            <div class="datagrid-content"><code><?= Html::encode($model->allievo_codice_fiscale) ?></code></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Progetto</div>
                            <div class="datagrid-content"><?= $model->progetto ? Html::encode($model->progetto->titolo) : '-' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Importo Finale</div>
                            <div class="datagrid-content"><strong><?= $formatter->asCurrency($model->importo_finale, 'EUR') ?></strong></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Gia Erogato</div>
                            <div class="datagrid-content text-green"><?= $formatter->asCurrency($model->totale_erogato, 'EUR') ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Stato Attuale</div>
                            <div class="datagrid-content"><?= $model->getStatusBadge() ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Warning -->
            <div class="alert alert-warning mb-3">
                <div class="d-flex">
                    <div><i class="ti ti-alert-triangle icon me-2"></i></div>
                    <div>
                        <h4 class="alert-title">Attenzione</h4>
                        <div class="text-secondary">
                            Revocando la borsa di studio, lo studente dovra restituire gli importi percepiti
                            (<?= $formatter->asCurrency($model->totale_erogato, 'EUR') ?>) entro 30 giorni.
                            Questa azione non e reversibile.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Revoca -->
            <form method="post" action="<?= \yii\helpers\Url::to(['revoca', 'id' => $model->id]) ?>" class="card">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                <div class="card-header"><h3 class="card-title"><i class="ti ti-ban me-2"></i>Motivo Revoca</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Motivo della revoca <span class="text-danger">*</span></label>
                        <textarea name="motivo_revoca" class="form-control" rows="4"
                            placeholder="Inserisci il motivo della revoca della borsa di studio..." required><?= Html::encode($model->motivo_revoca) ?></textarea>
                        <small class="text-muted">Il motivo sara registrato nel sistema e comunicato allo studente.</small>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary me-2']) ?>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler revocare questa borsa di studio?')">
                        <i class="ti ti-ban me-1"></i> Conferma Revoca
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
