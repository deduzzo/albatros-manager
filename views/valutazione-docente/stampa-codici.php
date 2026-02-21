<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Modulo $modulo */
/** @var app\models\ValutazioneCodice[] $codici */

$this->title = 'Stampa Codici Valutazione';

$baseUrl = Yii::$app->request->hostInfo;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            @page { margin: 1cm; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 20px;
            color: #1e293b;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        .header h1 { font-size: 1.5rem; margin-bottom: 5px; }
        .header p { color: #64748b; font-size: 0.9rem; }
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        .codice-card {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 16px 8px;
            text-align: center;
            page-break-inside: avoid;
        }
        .codice-card .label {
            font-size: 0.7rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .codice-card .code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 3px;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .codice-card .url {
            font-size: 0.65rem;
            color: #94a3b8;
            word-break: break-all;
        }
        .btn-print {
            display: block;
            margin: 20px auto;
            padding: 10px 30px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
        }
        .btn-print:hover { background: #1d4ed8; }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.8rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Stampa Codici</button>
    </div>

    <div class="header">
        <h1>Codici Valutazione Docente</h1>
        <p><?= Html::encode($modulo->codice . ' - ' . $modulo->titolo) ?></p>
        <p><?= count($codici) ?> codici disponibili</p>
    </div>

    <?php if (empty($codici)): ?>
    <p style="text-align: center; color: #94a3b8;">Nessun codice disponibile per la stampa.</p>
    <?php else: ?>
    <div class="grid">
        <?php foreach ($codici as $codice): ?>
        <div class="codice-card">
            <div class="label">Valutazione Anonima</div>
            <div class="code"><?= Html::encode($codice->codice) ?></div>
            <div class="url"><?= Html::encode($baseUrl) ?>/valutazione/<?= Html::encode($codice->codice) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        Ogni codice puo essere utilizzato una sola volta. Visitare l'indirizzo indicato per compilare la valutazione.
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Stampa Codici</button>
    </div>
</body>
</html>
