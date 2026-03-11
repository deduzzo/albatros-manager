<?php
/**
 * Migration automatiche Yii2 via browser.
 * Basta collegarsi e applica tutto ciò che manca.
 * Protetto da basic auth del server.
 */

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/console.php';

$dbConfig = __DIR__ . '/../config/db.php';
if (file_exists($dbConfig)) {
    $config['components']['db'] = require $dbConfig;
}

$app = new yii\console\Application($config);

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Albatros - Migration</title>';
echo '<style>body{font-family:monospace;max-width:800px;margin:40px auto;padding:0 20px;background:#1e1e2e;color:#cdd6f4}';
echo '.ok{color:#a6e3a1}.err{color:#f38ba8}.warn{color:#fab387}.info{color:#89b4fa}pre{background:#313244;padding:15px;border-radius:8px;overflow-x:auto}</style></head><body>';
echo '<h2>🐦 Albatros Manager - Migration</h2>';
echo '<p class="info">' . date('Y-m-d H:i:s') . '</p>';

ob_start();

try {
    // Controlla se ci sono migration pendenti
    $pendingCount = 0;
    $migrationPath = Yii::getAlias('@app/migrations');
    $appliedMigrations = Yii::$app->db->createCommand("SELECT version FROM {{%migration}} ORDER BY apply_time DESC")->queryColumn();

    $files = glob($migrationPath . '/m*.php');
    $pending = [];
    foreach ($files as $file) {
        $name = pathinfo($file, PATHINFO_FILENAME);
        if (!in_array($name, $appliedMigrations)) {
            $pending[] = $name;
        }
    }
    sort($pending);
    $pendingCount = count($pending);

    if ($pendingCount === 0) {
        echo '<div class="ok"><h3>✓ Tutto aggiornato!</h3>';
        echo '<p>Nessuna migration in attesa. Il database è allineato.</p></div>';
        echo '<h4>Ultime migration applicate:</h4><pre>';
        $app->runAction('migrate/history', ['limit' => 10, 'interactive' => false]);
        echo '</pre>';
    } else {
        echo "<div class='warn'><h3>⚡ $pendingCount migration in attesa</h3><ul>";
        foreach ($pending as $m) {
            echo "<li>$m</li>";
        }
        echo '</ul></div>';

        echo '<h4>Applicazione in corso...</h4><pre>';
        $exitCode = $app->runAction('migrate/up', ['interactive' => false]);
        echo '</pre>';

        if ($exitCode === 0) {
            echo '<div class="ok"><h3>✓ Migration completate con successo!</h3></div>';
        } else {
            echo '<div class="err"><h3>✗ Errore durante le migration (exit code: ' . $exitCode . ')</h3>';
            echo '<p>Controlla il log sopra per i dettagli.</p></div>';
        }
    }
} catch (\Throwable $e) {
    echo '<div class="err"><h3>✗ Errore</h3>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n\n";
    echo htmlspecialchars($e->getFile() . ':' . $e->getLine()) . '</pre></div>';
}

$output = ob_get_clean();
echo $output;
echo '</body></html>';
