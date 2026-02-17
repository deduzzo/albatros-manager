<?php

use yii\helpers\Html;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model app\models\AuditLog */

$this->title = 'Dettaglio Audit #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Audit Log', 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;

$oldValues = $model->getOldValuesArray();
$newValues = $model->getNewValuesArray();
$changedFields = $model->getChangedFieldsArray();
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto">
                <h2 class="page-title">
                    <i class="ti ti-activity me-2"></i><?= Html::encode($this->title) ?>
                </h2>
            </div>
            <div class="col-auto ms-auto">
                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <!-- Informazioni -->
            <div class="col-lg-5">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Informazioni</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-vcenter card-table">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 140px;">Tabella</td>
                                    <td><span class="badge bg-blue-lt"><?= Html::encode($model->table_name) ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">ID Record</td>
                                    <td><code><?= Html::encode($model->record_id) ?></code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Azione</td>
                                    <td><?= $model->getActionBadge() ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Utente</td>
                                    <td>
                                        <?= Html::encode($model->username ?: '-') ?>
                                        <?php if ($model->user_type): ?>
                                            <small class="text-muted">(<?= Html::encode($model->user_type) ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Indirizzo IP</td>
                                    <td><code><?= Html::encode($model->ip_address ?: '-') ?></code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">URL Richiesta</td>
                                    <td><small><?= Html::encode($model->request_url ?: '-') ?></small></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Metodo HTTP</td>
                                    <td><span class="badge bg-default text-default-fg"><?= Html::encode($model->request_method ?: '-') ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">ID Sessione</td>
                                    <td><small class="text-break"><?= Html::encode($model->session_id ?: '-') ?></small></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Data/Ora</td>
                                    <td><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i:s') ?></td>
                                </tr>
                                <?php if ($model->note): ?>
                                <tr>
                                    <td class="text-muted">Note</td>
                                    <td><?= Html::encode($model->note) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modifiche -->
            <div class="col-lg-7">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-git-compare me-2"></i>Modifiche</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($model->action === 'INSERT'): ?>
                            <!-- INSERT: solo nuovi valori in verde -->
                            <?php if (!empty($newValues)): ?>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th>Valore</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($newValues as $field => $value): ?>
                                        <tr>
                                            <td class="text-muted"><?= Html::encode($field) ?></td>
                                            <td>
                                                <span class="text-success">
                                                    <?= Html::encode(is_array($value) ? Json::encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string)$value) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                                <div class="card-body text-muted text-center">Nessun dato disponibile</div>
                            <?php endif; ?>

                        <?php elseif ($model->action === 'UPDATE'): ?>
                            <!-- UPDATE: diff campi modificati -->
                            <?php
                            $fieldsToShow = !empty($changedFields) ? $changedFields : array_keys(array_merge($oldValues, $newValues));
                            ?>
                            <?php if (!empty($fieldsToShow)): ?>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th>Valore Precedente</th>
                                            <th>Nuovo Valore</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($fieldsToShow as $field): ?>
                                        <?php
                                            $oldVal = $oldValues[$field] ?? '';
                                            $newVal = $newValues[$field] ?? '';
                                            if ((string)$oldVal === (string)$newVal) continue;
                                        ?>
                                        <tr>
                                            <td class="text-muted fw-bold"><?= Html::encode($field) ?></td>
                                            <td class="bg-red-lt">
                                                <span class="text-red">
                                                    <?= Html::encode(is_array($oldVal) ? Json::encode($oldVal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string)$oldVal) ?>
                                                </span>
                                            </td>
                                            <td class="bg-green-lt">
                                                <span class="text-green">
                                                    <?= Html::encode(is_array($newVal) ? Json::encode($newVal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string)$newVal) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                                <div class="card-body text-muted text-center">Nessuna modifica rilevata</div>
                            <?php endif; ?>

                        <?php elseif (in_array($model->action, ['DELETE', 'SOFT_DELETE'])): ?>
                            <!-- DELETE/SOFT_DELETE: vecchi valori in rosso -->
                            <?php if (!empty($oldValues)): ?>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th>Valore</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($oldValues as $field => $value): ?>
                                        <tr>
                                            <td class="text-muted"><?= Html::encode($field) ?></td>
                                            <td>
                                                <span class="text-danger">
                                                    <?= Html::encode(is_array($value) ? Json::encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string)$value) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                                <div class="card-body text-muted text-center">Nessun dato disponibile</div>
                            <?php endif; ?>

                        <?php elseif ($model->action === 'RESTORE'): ?>
                            <!-- RESTORE: valori ripristinati in verde -->
                            <?php if (!empty($newValues)): ?>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th>Valore Ripristinato</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($newValues as $field => $value): ?>
                                        <tr>
                                            <td class="text-muted"><?= Html::encode($field) ?></td>
                                            <td>
                                                <span class="text-success">
                                                    <?= Html::encode(is_array($value) ? Json::encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string)$value) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                                <div class="card-body text-muted text-center">Nessun dato disponibile</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="card-body text-muted text-center">Tipo azione non riconosciuto</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
