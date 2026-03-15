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
$firma = $risultato['firma'] ?? [];
$certificato = $risultato['certificato'] ?? [];
$tipologia = $risultato['tipologia'] ?? [];
$tipo = $risultato['tipo'] ?? 'sconosciuto';
$errori = $risultato['errori'] ?? [];
$avvisi = $risultato['avvisi'] ?? [];
$nomeFile = $risultato['nome_file'] ?? 'N/D';
$dimensione = $risultato['dimensione'] ?? 0;

$tipoLabel = match ($tipo) {
    'p7m' => 'CAdES (P7M)',
    'pdf' => 'PAdES (PDF)',
    default => 'Sconosciuto',
};

// Helper per renderizzare una riga del datagrid
$renderItem = function($label, $value, $badge = false, $badgeClass = 'bg-blue-lt') {
    if ($value === null || $value === '' || $value === []) return '';
    if (is_array($value)) $value = implode(', ', $value);
    $display = $badge
        ? '<span class="badge ' . $badgeClass . '">' . Html::encode($value) . '</span>'
        : Html::encode($value);
    return '<div class="datagrid-item"><div class="datagrid-title">' . Html::encode($label) . '</div><div class="datagrid-content">' . $display . '</div></div>';
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
                <div class="col-lg-9">

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
                                <p class="text-muted mb-0">La firma digitale del documento e' stata verificata con successo.</p>
                            <?php else: ?>
                                <div class="mb-3">
                                    <span class="badge bg-red text-red-fg" style="font-size: 1.2rem; padding: 10px 20px;">
                                        <i class="ti ti-shield-x me-1"></i>
                                        FIRMA NON VALIDA
                                    </span>
                                </div>
                                <p class="text-muted mb-0">Non e' stato possibile verificare la firma digitale del documento.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($errori)): ?>
                    <div class="alert alert-danger">
                        <h4 class="alert-title"><i class="ti ti-alert-circle me-1"></i>Errori</h4>
                        <ul class="mb-0">
                            <?php foreach ($errori as $errore): ?>
                                <li><?= Html::encode($errore) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($avvisi)): ?>
                    <div class="alert alert-warning">
                        <h4 class="alert-title"><i class="ti ti-alert-triangle me-1"></i>Avvisi</h4>
                        <ul class="mb-0">
                            <?php foreach ($avvisi as $avviso): ?>
                                <li><?= Html::encode($avviso) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- Colonna sinistra -->
                        <div class="col-lg-6">

                            <!-- FIRMATARIO -->
                            <?php if (!empty(array_filter($firmatario, fn($v) => $v !== null && $v !== ''))): ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="ti ti-user me-2"></i>Firmatario</h3>
                                </div>
                                <div class="card-body">
                                    <div class="datagrid">
                                        <?= $renderItem('Nome Completo (CN)', $firmatario['cn'] ?? null) ?>
                                        <?= $renderItem('Cognome', $firmatario['cognome'] ?? null) ?>
                                        <?= $renderItem('Nome', $firmatario['nome'] ?? null) ?>
                                        <?= $renderItem('Codice Fiscale', $firmatario['cf'] ?? null, true, 'bg-azure-lt') ?>
                                        <?= $renderItem('Email', $firmatario['email'] ?? null) ?>
                                        <?= $renderItem('Organizzazione', $firmatario['organizzazione'] ?? null) ?>
                                        <?= $renderItem('Unita Organizzativa', $firmatario['unita'] ?? null) ?>
                                        <?= $renderItem('Titolo', $firmatario['titolo'] ?? null) ?>
                                        <?= $renderItem('Paese', $firmatario['paese'] ?? null) ?>
                                        <?= $renderItem('Localita', $firmatario['localita'] ?? null) ?>
                                        <?= $renderItem('Provincia/Stato', $firmatario['provincia'] ?? null) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- DATI FIRMA -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="ti ti-writing-sign me-2"></i>Dati Firma</h3>
                                </div>
                                <div class="card-body">
                                    <div class="datagrid">
                                        <?= $renderItem('Tipo Firma', $firma['tipo_firma'] ?? null, true, 'bg-blue-lt') ?>
                                        <?= $renderItem('Data e Ora Firma', $firma['data_firma'] ?? null) ?>
                                        <?= $renderItem('Algoritmo', $firma['algoritmo'] ?? null) ?>
                                        <?php if (($firma['controfirme'] ?? 0) > 0): ?>
                                            <?= $renderItem('Controfirme', $firma['controfirme']) ?>
                                        <?php endif; ?>
                                        <?= $renderItem('Marca Temporale', $firma['marca_temporale'] ?? null, true, 'bg-green-lt') ?>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Colonna destra -->
                        <div class="col-lg-6">

                            <!-- TIPOLOGIA CERTIFICATO -->
                            <?php if (!empty($tipologia['tipo_certificato']) || !empty($tipologia['key_usage']) || !empty($tipologia['policy_nome'])): ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="ti ti-category me-2"></i>Tipologia</h3>
                                </div>
                                <div class="card-body">
                                    <div class="datagrid">
                                        <?php
                                        $tipoCert = $tipologia['tipo_certificato'] ?? null;
                                        $tipoBadgeClass = 'bg-blue-lt';
                                        if ($tipoCert && str_contains($tipoCert, 'Qualificata')) $tipoBadgeClass = 'bg-green-lt';
                                        elseif ($tipoCert && str_contains($tipoCert, 'Firma')) $tipoBadgeClass = 'bg-azure-lt';
                                        ?>
                                        <?= $renderItem('Tipo Certificato', $tipoCert, true, $tipoBadgeClass) ?>
                                        <?php if ($tipologia['qualificato'] === true): ?>
                                            <?= $renderItem('eIDAS', 'Certificato Qualificato', true, 'bg-green-lt') ?>
                                        <?php endif; ?>
                                        <?php if (!empty($tipologia['key_usage'])): ?>
                                            <?= $renderItem('Key Usage', implode(', ', $tipologia['key_usage'])) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($tipologia['extended_key_usage'])): ?>
                                            <?= $renderItem('Extended Key Usage', implode(', ', $tipologia['extended_key_usage'])) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($tipologia['policy_nome'])): ?>
                                            <?= $renderItem('Policy Certificato', implode(', ', $tipologia['policy_nome'])) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($tipologia['policy_oid'])): ?>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Policy OID</div>
                                            <div class="datagrid-content">
                                                <?php foreach ($tipologia['policy_oid'] as $oid): ?>
                                                    <code class="me-1"><?= Html::encode($oid) ?></code>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- EMITTENTE (CA) -->
                            <?php if (!empty($certificato['emittente_cn']) || !empty($certificato['emittente_o']) || !empty($certificato['emittente_dn'])): ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="ti ti-building-bank me-2"></i>Autorita di Certificazione (CA)</h3>
                                </div>
                                <div class="card-body">
                                    <div class="datagrid">
                                        <?= $renderItem('Nome CA', $certificato['emittente_cn'] ?? null) ?>
                                        <?= $renderItem('Organizzazione', $certificato['emittente_o'] ?? null) ?>
                                        <?php if (!empty($certificato['emittente_dn']) && empty($certificato['emittente_cn'])): ?>
                                            <?= $renderItem('DN Emittente', $certificato['emittente_dn']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- CERTIFICATO (dettagli tecnici) -->
                            <?php if (!empty($certificato['seriale']) || !empty($certificato['valido_da'])): ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="ti ti-lock me-2"></i>Certificato X.509</h3>
                                </div>
                                <div class="card-body">
                                    <div class="datagrid">
                                        <?= $renderItem('Versione', $certificato['versione'] ?? null) ?>
                                        <?= $renderItem('Numero Seriale', $certificato['seriale'] ?? null) ?>
                                        <?= $renderItem('Valido da', $certificato['valido_da'] ?? null) ?>
                                        <?php
                                        $scadutoHtml = '';
                                        if (!empty($certificato['valido_a'])) {
                                            $scaduto = $certificato['scaduto'] ?? null;
                                            if ($scaduto === true) {
                                                $scadutoHtml = '<div class="datagrid-item"><div class="datagrid-title">Valido fino a</div><div class="datagrid-content">'
                                                    . Html::encode($certificato['valido_a'])
                                                    . ' <span class="badge bg-red-lt ms-1">SCADUTO</span></div></div>';
                                            } else {
                                                $scadutoHtml = '<div class="datagrid-item"><div class="datagrid-title">Valido fino a</div><div class="datagrid-content">'
                                                    . Html::encode($certificato['valido_a'])
                                                    . ' <span class="badge bg-green-lt ms-1">Attivo</span></div></div>';
                                            }
                                        }
                                        echo $scadutoHtml;
                                        ?>
                                        <?= $renderItem('Chiave Pubblica', $certificato['chiave_pubblica'] ?? null) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <!-- Dettagli file e DN completo -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-file me-2"></i>Dettagli File</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <?= $renderItem('Nome file', $nomeFile) ?>
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
                                <?= $renderItem('Formato', $tipoLabel, true, 'bg-blue-lt') ?>
                            </div>
                            <?php if (!empty($firmatario['dn_completo'])): ?>
                            <div class="mt-3">
                                <small class="text-muted">Distinguished Name completo:</small>
                                <code class="d-block mt-1 p-2 bg-light rounded" style="word-break: break-all; font-size: 0.75rem;">
                                    <?= Html::encode($firmatario['dn_completo']) ?>
                                </code>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

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
