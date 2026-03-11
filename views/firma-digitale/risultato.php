<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $risultato array */
/* @var $contenutoPath string|null */

$this->title = 'Risultato Verifica Firma';
$this->params['breadcrumbs'][] = ['label' => 'Verifica Firma Digitale', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$valida = $risultato['valida'] ?? false;
$firmatario = $risultato['firmatario'] ?? [];
$certificato = $risultato['certificato'] ?? [];
$tipo = $risultato['tipo'] ?? 'sconosciuto';
$dataFirma = $risultato['data_firma'] ?? null;
$errori = $risultato['errori'] ?? [];
$nomeFile = $risultato['nome_file'] ?? 'N/D';
$dimensione = $risultato['dimensione'] ?? 0;

$tipoLabel = match ($tipo) {
    'p7m' => 'CAdES (P7M)',
    'pdf' => 'PAdES (PDF)',
    default => 'Sconosciuto',
};
?>

<div class="firma-digitale-risultato">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-certificate me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Risultato principale -->
                    <div class="card mb-3">
                        <div class="card-body text-center py-4">
                            <?php if ($valida): ?>
                                <div class="mb-3">
                                    <span class="badge bg-green text-green-fg" style="font-size: 1.2rem; padding: 10px 20px;">
                                        <i class="ti ti-shield-check me-1"></i>
                                        FIRMA VALIDA
                                    </span>
                                </div>
                                <p class="text-muted">La firma digitale del documento e' stata verificata con successo.</p>
                            <?php else: ?>
                                <div class="mb-3">
                                    <span class="badge bg-red text-red-fg" style="font-size: 1.2rem; padding: 10px 20px;">
                                        <i class="ti ti-shield-x me-1"></i>
                                        FIRMA NON VALIDA
                                    </span>
                                </div>
                                <p class="text-muted">Non e' stato possibile verificare la firma digitale del documento.</p>
                                <?php if (!empty($errori)): ?>
                                    <div class="alert alert-danger text-start mt-3">
                                        <h4 class="alert-title">Errori riscontrati</h4>
                                        <ul class="mb-0">
                                            <?php foreach ($errori as $errore): ?>
                                                <li><?= Html::encode($errore) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dettagli file -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-file me-2"></i>
                                Dettagli File
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Nome file</div>
                                    <div class="datagrid-content"><?= Html::encode($nomeFile) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Dimensione</div>
                                    <div class="datagrid-content">
                                        <?php
                                        if ($dimensione > 1048576) {
                                            echo number_format($dimensione / 1048576, 1, ',', '.') . ' MB';
                                        } else {
                                            echo number_format($dimensione / 1024, 1, ',', '.') . ' KB';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Tipo firma</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-blue-lt"><?= Html::encode($tipoLabel) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Firmatario -->
                    <?php if (!empty($firmatario['cn']) || !empty($firmatario['cf']) || !empty($firmatario['o'])): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-user me-2"></i>
                                Firmatario
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <?php if (!empty($firmatario['cn'])): ?>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Nome (CN)</div>
                                    <div class="datagrid-content"><strong><?= Html::encode($firmatario['cn']) ?></strong></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($firmatario['cf'])): ?>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Codice Fiscale</div>
                                    <div class="datagrid-content"><code><?= Html::encode($firmatario['cf']) ?></code></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($firmatario['o'])): ?>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Organizzazione</div>
                                    <div class="datagrid-content"><?= Html::encode($firmatario['o']) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($firmatario['email'])): ?>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Email</div>
                                    <div class="datagrid-content"><?= Html::encode($firmatario['email']) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Certificato -->
                    <?php if (!empty($certificato['emittente']) || !empty($certificato['seriale']) || !empty($certificato['valido_da'])): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-certificate me-2"></i>
                                Certificato
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <?php if (!empty($certificato['emittente'])): ?>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Emittente (CA)</div>
                                    <div class="datagrid-content"><?= Html::encode($certificato['emittente']) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($certificato['valido_da'])): ?>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Valido da</div>
                                    <div class="datagrid-content"><?= Html::encode($certificato['valido_da']) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($certificato['valido_a'])): ?>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Valido fino a</div>
                                    <div class="datagrid-content"><?= Html::encode($certificato['valido_a']) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($certificato['seriale'])): ?>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Numero seriale</div>
                                    <div class="datagrid-content"><code><?= Html::encode($certificato['seriale']) ?></code></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Firma -->
                    <?php if ($dataFirma): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-clock me-2"></i>
                                Data e Ora Firma
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data firma</div>
                                    <div class="datagrid-content"><?= Html::encode($dataFirma) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Azioni -->
                    <div class="card">
                        <div class="card-body">
                            <div class="btn-list">
                                <?php if ($contenutoPath && $tipo === 'p7m'): ?>
                                    <?= Html::a('<i class="ti ti-download me-1"></i> Scarica documento originale', ['download-contenuto', 'path' => $contenutoPath], ['class' => 'btn btn-primary']) ?>
                                <?php endif; ?>
                                <?= Html::a('<i class="ti ti-refresh me-1"></i> Verifica un altro file', ['index'], ['class' => 'btn btn-outline-primary']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
