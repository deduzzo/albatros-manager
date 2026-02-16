<?php
use yii\helpers\Html;
use app\models\ValutazioneDocente;

$this->title = 'Valutazione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Valutazioni Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="valutazione-docente-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti ti-star me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => ['confirm' => 'Sei sicuro di voler eliminare questa valutazione?', 'method' => 'post'],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <!-- Punteggi -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Valutazione Globale</div>
                            <div class="mt-2"><?= ValutazioneDocente::getStarsHtml($model->punteggio_globale) ?></div>
                            <div class="h1 mt-1"><?= $model->punteggio_globale ?>/5</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Chiarezza</div>
                            <div class="mt-2"><?= ValutazioneDocente::getStarsHtml($model->punteggio_chiarezza) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Competenza</div>
                            <div class="mt-2"><?= ValutazioneDocente::getStarsHtml($model->punteggio_competenza) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="subheader">Disponibilità</div>
                            <div class="mt-2"><?= ValutazioneDocente::getStarsHtml($model->punteggio_disponibilita) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dettagli -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Dettagli Valutazione</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ID</div>
                            <div class="datagrid-content"><?= $model->id ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Docente</div>
                            <div class="datagrid-content">
                                <?= $model->docente
                                    ? Html::a('<i class="ti ti-user me-1"></i>' . Html::encode($model->docente->cognome . ' ' . $model->docente->nome), ['/docente/view', 'id' => $model->docente_codice_fiscale])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Modulo</div>
                            <div class="datagrid-content">
                                <?= $model->modulo
                                    ? Html::a('<i class="ti ti-book me-1"></i>' . Html::encode($model->modulo->titolo), ['/modulo/view', 'id' => $model->modulo_id])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Data Valutazione</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDate($model->data_valutazione) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Materiali</div>
                            <div class="datagrid-content"><?= ValutazioneDocente::getStarsHtml($model->punteggio_materiali) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Creato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->commento): ?>
            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-message me-2"></i>Commento</h3></div>
                <div class="card-body"><?= nl2br(Html::encode($model->commento)) ?></div>
            </div>
            <?php endif; ?>

            <div class="card mt-3"><div class="card-footer"><?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?></div></div>
        </div>
    </div>
</div>
