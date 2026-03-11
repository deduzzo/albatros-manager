<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Gestione deploy via FTP basato su commit Git e GitHub Releases.
 */
class DeployController extends Controller
{
    /** @var bool Mostra cosa farebbe senza eseguire */
    public $dryRun = false;

    /** @var string Tag versione per la release (es. v1.2.0) */
    public $tag = '';

    /** @var string Messaggio per la release GitHub */
    public $message = '';

    /** Nome file versione sul server FTP */
    const VERSION_FILE = '.deploy-version';

    /** Nome file versione locale (per il footer) */
    const LOCAL_VERSION_FILE = 'config/version.php';

    /** Pattern da escludere dall'upload */
    const EXCLUDE_PATTERNS = [
        'docker-compose.yml',
        'Dockerfile',
        'vagrant/',
        'PRODUCTION_SERVER.md',
        '.claude/',
        '.auto-claude/',
        '.worktrees/',
        'CLAUDE.md',
        'docs/superpowers/',
        '.github/',
        'tests/',
        '.gitignore',
        '.git/',
    ];

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['dryRun', 'tag', 'message']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'n' => 'dryRun',
            't' => 'tag',
            'm' => 'message',
        ]);
    }

    /**
     * Mostra lo stato: versione server vs locale.
     */
    public function actionStatus()
    {
        $this->stdout("\n=== Deploy Status ===\n\n", Console::BOLD);

        $localCommit = $this->getLocalCommit();
        $localBranch = $this->exec('git rev-parse --abbrev-ref HEAD');
        $this->stdout("Branch locale: ", Console::BOLD);
        $this->stdout("$localBranch\n");
        $this->stdout("Commit locale: ", Console::BOLD);
        $this->stdout(substr($localCommit, 0, 8) . "\n");

        // Ultimo tag locale
        $lastTag = $this->exec('git describe --tags --abbrev=0 2>/dev/null');
        if ($lastTag) {
            $this->stdout("Ultimo tag:    ", Console::BOLD);
            $this->stdout("$lastTag\n");
        }

        // Ultima release GitHub
        $ghRelease = $this->getLatestGitHubRelease();
        if ($ghRelease) {
            $this->stdout("GitHub Release:", Console::BOLD);
            $this->stdout(" {$ghRelease['tag']} - {$ghRelease['name']}\n");
        } else {
            $this->stdout("GitHub Release:", Console::BOLD);
            $this->stdout(" nessuna\n", Console::FG_YELLOW);
        }

        $this->stdout("\n");

        // Versione sul server FTP
        $ftp = $this->connectFtp();
        if (!$ftp) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $remoteVersion = $this->getRemoteVersion($ftp);
        ftp_close($ftp);

        if ($remoteVersion) {
            $this->stdout("Server FTP:\n", Console::BOLD);
            $this->stdout("  Commit:   {$remoteVersion['commit']}\n");
            if (!empty($remoteVersion['tag'])) {
                $this->stdout("  Versione: {$remoteVersion['tag']}\n");
            }
            if (!empty($remoteVersion['date'])) {
                $this->stdout("  Deploy:   {$remoteVersion['date']}\n");
            }

            if ($remoteVersion['commit'] === $localCommit) {
                $this->stdout("\n✓ Server allineato con il commit locale.\n", Console::FG_GREEN);
            } else {
                $changedFiles = $this->getChangedFiles($remoteVersion['commit'], $localCommit);
                $this->stdout("\n✗ Server NON allineato. ", Console::FG_RED);
                $this->stdout(count($changedFiles) . " file da aggiornare.\n");
            }
        } else {
            $this->stdout("Server FTP: ", Console::BOLD);
            $this->stdout("non inizializzato (esegui deploy/init)\n", Console::FG_YELLOW);
        }

        $this->stdout("\n");
        return ExitCode::OK;
    }

    /**
     * Inizializza il file versione sul server FTP con il commit corrente.
     * Non uploada file, segna solo il punto di partenza.
     */
    public function actionInit()
    {
        $this->stdout("\n=== Deploy Init ===\n\n", Console::BOLD);

        $ftp = $this->connectFtp();
        if (!$ftp) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $remoteVersion = $this->getRemoteVersion($ftp);
        if ($remoteVersion) {
            $this->stdout("Il server ha gia un file versione:\n", Console::FG_YELLOW);
            $this->stdout("  Commit: {$remoteVersion['commit']}\n");
            if (!empty($remoteVersion['tag'])) {
                $this->stdout("  Tag: {$remoteVersion['tag']}\n");
            }
            if (!$this->confirm('Vuoi sovrascriverlo?')) {
                ftp_close($ftp);
                return ExitCode::OK;
            }
        }

        $commit = $this->getLocalCommit();
        $lastTag = $this->exec('git describe --tags --abbrev=0 2>/dev/null');

        $versionData = [
            'commit' => $commit,
            'tag' => $lastTag,
            'date' => date('Y-m-d H:i:s'),
            'branch' => $this->exec('git rev-parse --abbrev-ref HEAD'),
        ];

        if ($this->dryRun) {
            $this->stdout("[DRY-RUN] Scriverei .deploy-version con commit " . substr($commit, 0, 8) . "\n", Console::FG_CYAN);
        } else {
            $this->writeRemoteVersion($ftp, $versionData);
            $this->writeLocalVersion($versionData);
            $this->stdout("✓ Versione inizializzata: " . substr($commit, 0, 8), Console::FG_GREEN);
            if ($lastTag) {
                $this->stdout(" ($lastTag)");
            }
            $this->stdout("\n");
        }

        ftp_close($ftp);
        return ExitCode::OK;
    }

    /**
     * Deploy incrementale: crea release GitHub + upload file cambiati via FTP.
     *
     * Uso: php yii deploy/update --tag=v1.2.0 --message="Descrizione release"
     */
    public function actionUpdate()
    {
        $this->stdout("\n=== Deploy Update ===\n\n", Console::BOLD);

        // 1. Verifica che siamo su main
        $branch = $this->exec('git rev-parse --abbrev-ref HEAD');
        if ($branch !== 'main') {
            $this->stderr("Errore: devi essere sul branch 'main' (sei su '$branch').\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // 2. Verifica working directory pulita
        $status = $this->exec('git status --porcelain');
        if (!empty($status)) {
            $this->stderr("Errore: ci sono modifiche non committate.\n", Console::FG_RED);
            $this->stderr($status . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // 3. Connetti FTP e leggi versione corrente
        $ftp = $this->connectFtp();
        if (!$ftp) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $remoteVersion = $this->getRemoteVersion($ftp);
        if (!$remoteVersion) {
            $this->stderr("Errore: server non inizializzato. Esegui prima: php yii deploy/init\n", Console::FG_RED);
            ftp_close($ftp);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $localCommit = $this->getLocalCommit();
        $remoteCommit = $remoteVersion['commit'];

        if ($localCommit === $remoteCommit) {
            $this->stdout("✓ Server gia allineato (commit " . substr($localCommit, 0, 8) . ").\n", Console::FG_GREEN);
            ftp_close($ftp);
            return ExitCode::OK;
        }

        // 4. Calcola file cambiati
        $changedFiles = $this->getChangedFiles($remoteCommit, $localCommit);
        if (empty($changedFiles)) {
            $this->stdout("Nessun file da aggiornare.\n", Console::FG_YELLOW);
            ftp_close($ftp);
            return ExitCode::OK;
        }

        // Separa file modificati/aggiunti da quelli eliminati
        $toUpload = [];
        $toDelete = [];
        foreach ($changedFiles as $file) {
            if ($file['status'] === 'D') {
                $toDelete[] = $file['path'];
            } else {
                $toUpload[] = $file['path'];
            }
        }

        $this->stdout("Commit remoto: " . substr($remoteCommit, 0, 8) . "\n");
        $this->stdout("Commit locale: " . substr($localCommit, 0, 8) . "\n\n");
        $this->stdout("File da uploadare: " . count($toUpload) . "\n", Console::FG_GREEN);
        $this->stdout("File da eliminare: " . count($toDelete) . "\n", Console::FG_RED);
        $this->stdout("\n");

        // Mostra lista file
        foreach ($toUpload as $f) {
            $this->stdout("  + $f\n", Console::FG_GREEN);
        }
        foreach ($toDelete as $f) {
            $this->stdout("  - $f\n", Console::FG_RED);
        }
        $this->stdout("\n");

        // 5. Tag e release GitHub
        $tag = $this->tag;
        if (empty($tag)) {
            $tag = $this->prompt('Tag versione (es. v1.0.1, invio per saltare)');
        }

        if ($this->dryRun) {
            if ($tag) {
                $this->stdout("[DRY-RUN] Creerei GitHub release: $tag\n", Console::FG_CYAN);
            }
            foreach ($toUpload as $f) {
                $this->stdout("[DRY-RUN] Upload: $f\n", Console::FG_CYAN);
            }
            foreach ($toDelete as $f) {
                $this->stdout("[DRY-RUN] Delete: $f\n", Console::FG_CYAN);
            }
            ftp_close($ftp);
            return ExitCode::OK;
        }

        if (!$this->confirm('Procedere con il deploy?')) {
            ftp_close($ftp);
            return ExitCode::OK;
        }

        // 6. Crea GitHub release (se tag specificato)
        if ($tag) {
            $this->stdout("\nCreazione GitHub Release...\n", Console::BOLD);
            if (!$this->createGitHubRelease($tag, $localCommit, $toUpload, $toDelete)) {
                if (!$this->confirm('Release GitHub fallita. Continuare comunque con il deploy FTP?')) {
                    ftp_close($ftp);
                    return ExitCode::UNSPECIFIED_ERROR;
                }
            }
        }

        // 7. Upload file
        $this->stdout("\nUpload file via FTP...\n", Console::BOLD);
        $config = $this->getFtpConfig();
        $remotePath = rtrim($config['remotePath'], '/');
        $errors = 0;

        foreach ($toUpload as $i => $file) {
            $localFile = \Yii::getAlias('@app') . '/' . $file;
            $remoteFile = $remotePath . '/' . $file;

            // Crea directory remote se necessario
            $dir = dirname($remoteFile);
            $this->ftpMkdirRecursive($ftp, $dir);

            $n = $i + 1;
            $total = count($toUpload);
            if (@ftp_put($ftp, $remoteFile, $localFile, FTP_BINARY)) {
                $this->stdout("  [$n/$total] ✓ $file\n", Console::FG_GREEN);
            } else {
                $this->stdout("  [$n/$total] ✗ $file\n", Console::FG_RED);
                $errors++;
            }
        }

        // 8. Elimina file rimossi
        foreach ($toDelete as $file) {
            $remoteFile = $remotePath . '/' . $file;
            if (@ftp_delete($ftp, $remoteFile)) {
                $this->stdout("  ✓ Eliminato: $file\n", Console::FG_YELLOW);
            } else {
                $this->stdout("  ✗ Non trovato/errore: $file\n", Console::FG_YELLOW);
            }
        }

        // 9. Aggiorna versione
        if ($errors === 0) {
            $versionData = [
                'commit' => $localCommit,
                'tag' => $tag ?: ($remoteVersion['tag'] ?? ''),
                'date' => date('Y-m-d H:i:s'),
                'branch' => $branch,
            ];
            $this->writeRemoteVersion($ftp, $versionData);
            $this->writeLocalVersion($versionData);

            $this->stdout("\n✓ Deploy completato con successo!\n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("  Commit: " . substr($localCommit, 0, 8));
            if ($tag) {
                $this->stdout(" ($tag)");
            }
            $this->stdout("\n  File aggiornati: " . count($toUpload) . "\n");
            $this->stdout("  File eliminati: " . count($toDelete) . "\n\n");
        } else {
            $this->stderr("\n⚠ Deploy completato con $errors errori. Versione NON aggiornata.\n", Console::FG_RED);
        }

        ftp_close($ftp);
        return $errors === 0 ? ExitCode::OK : ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Deploy completo: forza upload di tutti i file tracciati da git.
     */
    public function actionFull()
    {
        $this->stdout("\n=== Deploy Full ===\n\n", Console::BOLD);
        $this->stdout("ATTENZIONE: questo comando uploada TUTTI i file tracciati da git.\n", Console::FG_YELLOW);

        if (!$this->confirm('Sei sicuro?')) {
            return ExitCode::OK;
        }

        $ftp = $this->connectFtp();
        if (!$ftp) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Prendi tutti i file tracciati
        $output = shell_exec('git ls-files');
        $allFiles = array_filter(explode("\n", trim($output)));
        $allFiles = $this->filterExclusions($allFiles);

        $this->stdout("File da uploadare: " . count($allFiles) . "\n\n");

        // Tag
        $tag = $this->tag;
        if (empty($tag)) {
            $tag = $this->prompt('Tag versione (es. v1.0.0, invio per saltare)');
        }

        if ($this->dryRun) {
            foreach ($allFiles as $f) {
                $this->stdout("[DRY-RUN] Upload: $f\n", Console::FG_CYAN);
            }
            ftp_close($ftp);
            return ExitCode::OK;
        }

        $localCommit = $this->getLocalCommit();

        // GitHub release
        if ($tag) {
            $this->stdout("Creazione GitHub Release...\n", Console::BOLD);
            $this->createGitHubRelease($tag, $localCommit, $allFiles, []);
        }

        // Upload
        $config = $this->getFtpConfig();
        $remotePath = rtrim($config['remotePath'], '/');
        $errors = 0;

        foreach ($allFiles as $i => $file) {
            $localFile = \Yii::getAlias('@app') . '/' . $file;
            $remoteFile = $remotePath . '/' . $file;

            $dir = dirname($remoteFile);
            $this->ftpMkdirRecursive($ftp, $dir);

            $n = $i + 1;
            $total = count($allFiles);
            if (@ftp_put($ftp, $remoteFile, $localFile, FTP_BINARY)) {
                $this->stdout("  [$n/$total] ✓ $file\n", Console::FG_GREEN);
            } else {
                $this->stdout("  [$n/$total] ✗ $file\n", Console::FG_RED);
                $errors++;
            }
        }

        // Aggiorna versione
        $versionData = [
            'commit' => $localCommit,
            'tag' => $tag ?: '',
            'date' => date('Y-m-d H:i:s'),
            'branch' => $this->exec('git rev-parse --abbrev-ref HEAD'),
        ];
        $this->writeRemoteVersion($ftp, $versionData);
        $this->writeLocalVersion($versionData);

        ftp_close($ftp);

        $this->stdout("\n✓ Deploy full completato. $errors errori.\n\n", $errors ? Console::FG_YELLOW : Console::FG_GREEN);
        return $errors === 0 ? ExitCode::OK : ExitCode::UNSPECIFIED_ERROR;
    }

    // === Helper Methods ===

    /**
     * Restituisce la configurazione FTP.
     */
    private function getFtpConfig(): array
    {
        $configFile = \Yii::getAlias('@app/config/ftp.php');
        if (!file_exists($configFile)) {
            throw new \RuntimeException("File config/ftp.php non trovato. Crealo con le credenziali FTP.");
        }
        return require $configFile;
    }

    /**
     * Connette al server FTP.
     */
    private function connectFtp()
    {
        $config = $this->getFtpConfig();

        $this->stdout("Connessione FTP a {$config['host']}...\n");

        $ftp = @ftp_connect($config['host'], $config['port'], 30);
        if (!$ftp) {
            $this->stderr("Errore: impossibile connettersi a {$config['host']}:{$config['port']}\n", Console::FG_RED);
            return false;
        }

        if (!@ftp_login($ftp, $config['username'], $config['password'])) {
            $this->stderr("Errore: login FTP fallito.\n", Console::FG_RED);
            ftp_close($ftp);
            return false;
        }

        ftp_pasv($ftp, true);
        $this->stdout("✓ Connesso.\n\n", Console::FG_GREEN);

        return $ftp;
    }

    /**
     * Legge il file versione dal server FTP.
     */
    private function getRemoteVersion($ftp): ?array
    {
        $config = $this->getFtpConfig();
        $remotePath = rtrim($config['remotePath'], '/');
        $tempFile = tempnam(sys_get_temp_dir(), 'deploy');

        if (@ftp_get($ftp, $tempFile, $remotePath . '/' . self::VERSION_FILE, FTP_ASCII)) {
            $content = file_get_contents($tempFile);
            unlink($tempFile);
            $data = json_decode($content, true);
            return $data ?: null;
        }

        @unlink($tempFile);
        return null;
    }

    /**
     * Scrive il file versione sul server FTP.
     */
    private function writeRemoteVersion($ftp, array $data): bool
    {
        $config = $this->getFtpConfig();
        $remotePath = rtrim($config['remotePath'], '/');
        $tempFile = tempnam(sys_get_temp_dir(), 'deploy');

        file_put_contents($tempFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $result = @ftp_put($ftp, $remotePath . '/' . self::VERSION_FILE, $tempFile, FTP_ASCII);
        unlink($tempFile);

        return $result;
    }

    /**
     * Scrive il file versione locale per il footer.
     */
    private function writeLocalVersion(array $data): void
    {
        $file = \Yii::getAlias('@app') . '/' . self::LOCAL_VERSION_FILE;
        $content = "<?php\n\n// File generato automaticamente dal deploy. Non modificare.\nreturn " . var_export($data, true) . ";\n";
        file_put_contents($file, $content);
    }

    /**
     * Esegue un comando shell e restituisce l'output trimmato (safe per PHP 8.4).
     */
    private function exec(string $cmd): string
    {
        return trim(shell_exec($cmd) ?? '');
    }

    /**
     * Restituisce il commit SHA corrente.
     */
    private function getLocalCommit(): string
    {
        return $this->exec('git rev-parse HEAD');
    }

    /**
     * Restituisce l'ultima release GitHub.
     */
    private function getLatestGitHubRelease(): ?array
    {
        $output = shell_exec('gh release view --json tagName,name,publishedAt 2>/dev/null');
        if (!$output) {
            return null;
        }
        $data = json_decode($output, true);
        if (!$data) {
            return null;
        }
        return [
            'tag' => $data['tagName'] ?? '',
            'name' => $data['name'] ?? '',
            'date' => $data['publishedAt'] ?? '',
        ];
    }

    /**
     * Crea una release su GitHub.
     */
    private function createGitHubRelease(string $tag, string $commit, array $uploaded, array $deleted): bool
    {
        // Costruisci note della release
        $notes = "## Deploy $tag\n\n";
        $notes .= "**Data:** " . date('Y-m-d H:i:s') . "\n";
        $notes .= "**Commit:** " . substr($commit, 0, 8) . "\n\n";

        if (!empty($uploaded)) {
            $notes .= "### File aggiornati (" . count($uploaded) . ")\n";
            foreach (array_slice($uploaded, 0, 50) as $f) {
                $notes .= "- `$f`\n";
            }
            if (count($uploaded) > 50) {
                $notes .= "- ... e altri " . (count($uploaded) - 50) . " file\n";
            }
            $notes .= "\n";
        }
        if (!empty($deleted)) {
            $notes .= "### File rimossi (" . count($deleted) . ")\n";
            foreach ($deleted as $f) {
                $notes .= "- `$f`\n";
            }
        }

        $message = $this->message ?: "Release $tag";

        // Usa gh CLI
        $tempNotes = tempnam(sys_get_temp_dir(), 'release_notes');
        file_put_contents($tempNotes, $notes);

        $cmd = sprintf(
            'gh release create %s --target %s --title %s --notes-file %s 2>&1',
            escapeshellarg($tag),
            escapeshellarg($commit),
            escapeshellarg($message),
            escapeshellarg($tempNotes)
        );

        $output = shell_exec($cmd);
        unlink($tempNotes);

        if (strpos($output, 'https://') !== false) {
            $this->stdout("  ✓ Release creata: $output", Console::FG_GREEN);
            return true;
        } else {
            $this->stderr("  ✗ Errore release: $output\n", Console::FG_RED);
            return false;
        }
    }

    /**
     * Restituisce i file cambiati tra due commit, filtrati per esclusioni.
     */
    private function getChangedFiles(string $fromCommit, string $toCommit): array
    {
        $cmd = sprintf(
            'git diff --name-status %s..%s',
            escapeshellarg($fromCommit),
            escapeshellarg($toCommit)
        );
        $output = $this->exec($cmd);
        if (empty($output)) {
            return [];
        }

        $files = [];
        foreach (explode("\n", $output) as $line) {
            $parts = preg_split('/\s+/', $line, 2);
            if (count($parts) === 2) {
                $status = $parts[0][0]; // A, M, D, R, C
                $path = $parts[1];

                // Per rename (R100 old new), prendi il nuovo path
                if ($status === 'R') {
                    $paths = preg_split('/\s+/', $parts[1], 2);
                    $path = $paths[1] ?? $paths[0];
                }

                $files[] = ['status' => $status, 'path' => $path];
            }
        }

        // Filtra esclusioni
        return array_values(array_filter($files, function ($file) {
            return !$this->isExcluded($file['path']);
        }));
    }

    /**
     * Verifica se un file va escluso dall'upload.
     */
    private function isExcluded(string $path): bool
    {
        foreach (self::EXCLUDE_PATTERNS as $pattern) {
            // Pattern directory (termina con /)
            if (str_ends_with($pattern, '/')) {
                if (str_starts_with($path, rtrim($pattern, '/'))) {
                    return true;
                }
            } else {
                if ($path === $pattern) {
                    return true;
                }
            }
        }

        // Escludi anche ciò che è nel .gitignore ma tracciato (vendor, node_modules, etc.)
        $gitignoreExclusions = ['vendor/', 'node_modules/', 'runtime/', 'web/assets/'];
        foreach ($gitignoreExclusions as $dir) {
            if (str_starts_with($path, rtrim($dir, '/'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filtra array di file rimuovendo le esclusioni.
     */
    private function filterExclusions(array $files): array
    {
        return array_values(array_filter($files, function ($path) {
            return !$this->isExcluded($path);
        }));
    }

    /**
     * Crea ricorsivamente directory su FTP.
     */
    private function ftpMkdirRecursive($ftp, string $dir): void
    {
        if ($dir === '/' || $dir === '.' || $dir === '') {
            return;
        }

        // Prova a entrare nella directory, se fallisce la crea
        if (!@ftp_chdir($ftp, $dir)) {
            $this->ftpMkdirRecursive($ftp, dirname($dir));
            @ftp_mkdir($ftp, $dir);
        }

        // Torna alla root
        $config = $this->getFtpConfig();
        @ftp_chdir($ftp, $config['remotePath']);
    }

    /**
     * Restituisce la versione corrente dal file locale (per uso nelle view).
     */
    public static function getVersion(): array
    {
        $file = \Yii::getAlias('@app') . '/' . self::LOCAL_VERSION_FILE;
        if (file_exists($file)) {
            return require $file;
        }
        return [
            'commit' => 'dev',
            'tag' => '',
            'date' => '',
            'branch' => '',
        ];
    }
}
