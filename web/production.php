<?php
/**
 * Script di produzione: composer install + migration Yii2 via browser.
 * Esegue composer install (se necessario) e poi applica le migration pendenti.
 * Protetto da basic auth del server.
 */

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Albatros - Production</title>';
echo '<style>body{font-family:monospace;max-width:800px;margin:40px auto;padding:0 20px;background:#1e1e2e;color:#cdd6f4}';
echo '.ok{color:#a6e3a1}.err{color:#f38ba8}.warn{color:#fab387}.info{color:#89b4fa}pre{background:#313244;padding:15px;border-radius:8px;overflow-x:auto;white-space:pre-wrap}</style></head><body>';
echo '<h2>🐦 Albatros Manager - Production</h2>';
echo '<p class="info">' . date('Y-m-d H:i:s') . '</p>';

$basePath = dirname(__DIR__);
$allDone = true;

// ============================================================
// FASE 1: Composer Install
// ============================================================
echo '<h3>📦 Fase 1: Composer</h3>';

$lockFile = $basePath . '/composer.lock';
$vendorDir = $basePath . '/vendor';
$composerStamp = $vendorDir . '/.composer-lock-hash';

// Controlla se composer install è necessario
$currentHash = file_exists($lockFile) ? md5_file($lockFile) : '';
$installedHash = file_exists($composerStamp) ? trim(file_get_contents($composerStamp)) : '';

if ($currentHash === $installedHash && is_dir($vendorDir . '/yiisoft')) {
    echo '<div class="ok">✓ Dipendenze aggiornate. Nessun composer install necessario.</div>';
} else {
    echo '<div class="warn">⚡ composer.lock cambiato — esecuzione composer install...</div>';
    echo '<pre>';

    $composerPhar = $basePath . '/composer.phar';
    $composerCmd = null;

    // Trova il PHP CLI corretto — PHP_BINARY su Plesk è php-fpm, non utilizzabile per CLI
    $phpBinary = null;
    $phpFpm = PHP_BINARY ?: '';

    // Su Plesk: /opt/plesk/php/8.3/sbin/php-fpm → /opt/plesk/php/8.3/bin/php
    if (str_contains($phpFpm, '/sbin/php-fpm')) {
        $phpCli = str_replace('/sbin/php-fpm', '/bin/php', $phpFpm);
        if (file_exists($phpCli)) {
            $phpBinary = $phpCli;
        }
    }

    // Fallback: cerca PHP CLI in posizioni comuni
    if ($phpBinary === null) {
        $phpPaths = [
            '/opt/plesk/php/8.3/bin/php',
            '/opt/plesk/php/8.2/bin/php',
            '/opt/plesk/php/8.1/bin/php',
            '/usr/bin/php',
            '/usr/local/bin/php',
        ];
        foreach ($phpPaths as $path) {
            if (file_exists($path)) {
                $phpBinary = $path;
                break;
            }
        }
    }

    if ($phpBinary === null) {
        echo '</pre>';
        echo '<div class="err">✗ PHP CLI non trovato.</div>';
        echo '<div class="info">PHP_BINARY: ' . htmlspecialchars($phpFpm) . '</div>';
        $allDone = false;
    } else {
        // Cerca composer.phar (non lo shell wrapper di Plesk)
        $composerPharPaths = [
            $composerPhar,                                      // progetto/composer.phar
            '/opt/psa/var/modules/composer/composer.phar',      // Plesk
            '/usr/local/share/composer/composer.phar',
        ];
        foreach ($composerPharPaths as $path) {
            if (file_exists($path)) {
                $composerCmd = escapeshellarg($phpBinary) . ' ' . escapeshellarg($path);
                break;
            }
        }
    }

    if ($composerCmd === null && $phpBinary !== null) {
        echo '</pre>';
        echo '<div class="err">✗ Composer non trovato. Carica composer.phar nella root del progetto.</div>';
        echo '<div class="info">PHP CLI: ' . htmlspecialchars($phpBinary) . '</div>';
        $allDone = false;
    } elseif ($composerCmd === null) {
        // phpBinary è null — già gestito sopra
    } else {
        echo 'PHP CLI: ' . htmlspecialchars($phpBinary) . "\n";
        echo 'Composer: ' . htmlspecialchars($composerCmd) . "\n\n";

        $cmd = sprintf(
            'cd %s && HOME=%s %s install --no-dev --no-interaction --optimize-autoloader 2>&1',
            escapeshellarg($basePath),
            escapeshellarg($basePath),
            $composerCmd
        );

        $output = shell_exec($cmd);
        echo htmlspecialchars($output);
        echo '</pre>';

        // Verifica se ha funzionato
        if (str_contains($output, 'Generating optimized autoload files') || str_contains($output, 'Nothing to install, update or remove')) {
            // Salva hash per evitare re-run
            file_put_contents($composerStamp, $currentHash);
            echo '<div class="ok">✓ Composer install completato.</div>';
        } else {
            echo '<div class="err">✗ Composer install potrebbe aver avuto problemi. Verifica il log sopra.</div>';
            $allDone = false;
        }
    }
}

// ============================================================
// FASE 2: Migration
// ============================================================
echo '<h3>🗃️ Fase 2: Migration</h3>';

// Ricarica autoloader (potrebbe essere cambiato da composer)
require $basePath . '/vendor/autoload.php';
require $basePath . '/vendor/yiisoft/yii2/Yii.php';

$config = require $basePath . '/config/console.php';
$dbConfig = $basePath . '/config/db.php';
if (file_exists($dbConfig)) {
    $config['components']['db'] = require $dbConfig;
}

$app = new yii\console\Application($config);

ob_start();

try {
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
        echo '<div class="ok">✓ Tutto aggiornato! Nessuna migration in attesa.</div>';
    } else {
        echo "<div class='warn'>⚡ $pendingCount migration in attesa</div>";
        echo '<pre>';
        $exitCode = $app->runAction('migrate/up', ['interactive' => false]);
        echo '</pre>';

        if ($exitCode === 0) {
            echo '<div class="ok">✓ Migration completate con successo!</div>';
        } else {
            echo '<div class="err">✗ Errore durante le migration (exit code: ' . $exitCode . ')</div>';
            $allDone = false;
        }
    }
} catch (\Throwable $e) {
    echo '<div class="err">✗ Errore: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $allDone = false;
}

$output = ob_get_clean();
echo $output;

// ============================================================
// RIEPILOGO
// ============================================================
echo '<hr style="border-color:#45475a">';
if ($allDone) {
    echo '<div class="ok"><h3>✓ Tutto aggiornato!</h3><p>Composer e database sono allineati.</p></div>';
} else {
    echo '<div class="warn"><h3>⚠ Completato con avvisi</h3><p>Verifica i messaggi sopra.</p></div>';
}

echo '</body></html>';
