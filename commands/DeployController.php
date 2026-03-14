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
            $this->writeLocalVersion($versionData, $ftp);
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

        // 2. Verifica working directory pulita (ignora file non tracciati)
        $status = $this->exec('git status --porcelain --untracked-files=no');
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

        // 5. Auto-incremento versione
        $tag = $this->tag;
        if (empty($tag)) {
            $tag = $this->autoIncrementVersion($remoteVersion['tag'] ?? '');
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

        // 6. Aggiorna CHANGELOG.md: rinomina [Unreleased] → [tag] - data
        if ($tag) {
            $this->updateChangelog($tag);
        }

        // 7. Push su remote (necessario per creare la release sul commit aggiornato)
        if ($tag) {
            $this->stdout("\nPush su remote...\n", Console::BOLD);
            $pushOutput = $this->exec('git push 2>&1');
            $localCommit = $this->getLocalCommit(); // Aggiorna dopo il commit del changelog
            $this->stdout("  $pushOutput\n");
        }

        // 8. Crea GitHub release (se tag specificato)
        if ($tag) {
            $this->stdout("Creazione GitHub Release...\n", Console::BOLD);
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
            $this->writeLocalVersion($versionData, $ftp);

            $this->stdout("\n✓ Deploy completato con successo!\n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("  Commit: " . substr($localCommit, 0, 8));
            if ($tag) {
                $this->stdout(" ($tag)");
            }
            $this->stdout("\n  File aggiornati: " . count($toUpload) . "\n");
            $this->stdout("  File eliminati: " . count($toDelete) . "\n\n");

            // 10. Esegui migration remote
            $this->runRemoteMigrations();
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
        $output = $this->exec('git ls-files');
        $allFiles = array_filter(explode("\n", $output));
        $allFiles = $this->filterExclusions($allFiles);

        $this->stdout("File da uploadare: " . count($allFiles) . "\n\n");

        // Auto-incremento versione
        $remoteVersion = $this->getRemoteVersion($ftp);
        $tag = $this->tag;
        if (empty($tag)) {
            $tag = $this->autoIncrementVersion($remoteVersion['tag'] ?? '');
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
        $this->writeLocalVersion($versionData, $ftp);

        ftp_close($ftp);

        $this->stdout("\n✓ Deploy full completato. $errors errori.\n\n", $errors ? Console::FG_YELLOW : Console::FG_GREEN);

        if ($errors === 0) {
            $this->runRemoteMigrations();
        }

        return $errors === 0 ? ExitCode::OK : ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Aggiorna CHANGELOG.md: rinomina [Unreleased] con il tag e la data corrente,
     * poi aggiunge una nuova sezione [Unreleased] vuota in cima.
     * Committa automaticamente la modifica.
     */
    private function updateChangelog(string $tag): void
    {
        $file = \Yii::getAlias('@app/CHANGELOG.md');
        if (!file_exists($file)) {
            return;
        }

        $content = file_get_contents($file);
        $date = date('Y-m-d');

        // Trova la posizione di ## [Unreleased] e sostituisci con:
        // ## [Unreleased] (vuoto) + ## [tag] - data + contenuto che c'era sotto Unreleased
        $pattern = '/^(## \[Unreleased\])\s*\n/m';
        if (preg_match($pattern, $content)) {
            $newContent = preg_replace(
                $pattern,
                "## [Unreleased]\n\n## [$tag] - $date\n",
                $content,
                1
            );

            if ($newContent !== $content) {
                file_put_contents($file, $newContent);

                // Committa la modifica
                $this->exec('git add CHANGELOG.md');
                $this->exec(sprintf(
                    'git commit -m %s',
                    escapeshellarg("docs: release $tag - aggiornamento changelog")
                ));

                $this->stdout("  ✓ CHANGELOG.md aggiornato: [Unreleased] → [$tag] - $date\n", Console::FG_GREEN);
            }
        }
    }

    /**
     * Chiama production.php sul server remoto ripetutamente finché non risponde "Tutto aggiornato".
     * Esegue composer install + migration in un'unica chiamata.
     * Max 10 tentativi (il primo fa composer + migration, i successivi verificano).
     */
    private function runRemoteMigrations(): void
    {
        $url = 'https://albatros.robertodedomenico.it/production.php';
        $maxAttempts = 10;

        $this->stdout("Esecuzione post-deploy remoto (composer + migration)...\n", Console::BOLD);

        for ($i = 1; $i <= $maxAttempts; $i++) {
            $this->stdout("  [$i/$maxAttempts] Chiamata production.php ... ");

            // Usa curl per compatibilità SSL
            $response = $this->httpGet($url, 120);

            if ($response === false) {
                $this->stdout("ERRORE connessione\n", Console::FG_RED);
                sleep(3);
                continue;
            }

            $text = strip_tags($response);

            // Tutto completato: composer ok + migration ok
            if (str_contains($text, 'Tutto aggiornato') && str_contains($text, 'Composer e database sono allineati')) {
                $this->stdout("✓ Composer + migration OK!\n", Console::FG_GREEN);
                return;
            }

            // Tutto aggiornato ma senza la frase specifica (migration ok, composer era già ok)
            if (str_contains($text, 'Tutto aggiornato') && str_contains($text, 'Nessuna migration in attesa')) {
                $this->stdout("✓ Tutto aggiornato!\n", Console::FG_GREEN);
                return;
            }

            // Composer o migration eseguiti, potrebbe servire un altro giro
            if (str_contains($text, 'completato') || str_contains($text, 'applicata')) {
                $this->stdout("operazione eseguita, verifico...\n", Console::FG_YELLOW);
                sleep(2);
                continue;
            }

            // Errore
            if (str_contains($text, 'Errore') || str_contains($text, 'Completato con avvisi')) {
                $this->stderr("⚠ Problema riscontrato. Verifica https://albatros.robertodedomenico.it/production.php\n", Console::FG_RED);
                return;
            }

            // Risposta inattesa
            $preview = mb_substr($text, 0, 150);
            $this->stdout("risposta: $preview\n", Console::FG_YELLOW);
            sleep(2);
        }

        $this->stderr("  ⚠ Raggiunto il limite di $maxAttempts tentativi. Verifica manualmente.\n", Console::FG_RED);
    }

    /**
     * GET HTTP con fallback curl → file_get_contents.
     */
    private function httpGet(string $url, int $timeout = 30): string|false
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'AlbatrosManager-Deploy/1.0',
            ]);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                $this->stderr("curl error: $error\n", Console::FG_RED);
            }
            return $response;
        }

        // Fallback file_get_contents
        $ctx = stream_context_create([
            'http' => ['timeout' => $timeout],
            'ssl' => ['verify_peer' => true],
        ]);
        return @file_get_contents($url, false, $ctx);
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
    /**
     * Scrive il file versione locale e lo uploada via FTP.
     */
    private function writeLocalVersion(array $data, $ftp = null): void
    {
        $file = \Yii::getAlias('@app') . '/' . self::LOCAL_VERSION_FILE;
        $content = "<?php\n\n// File generato automaticamente dal deploy. Non modificare.\nreturn " . var_export($data, true) . ";\n";
        file_put_contents($file, $content);

        // Upload anche su FTP per il footer in produzione
        if ($ftp) {
            $config = $this->getFtpConfig();
            $remotePath = rtrim($config['remotePath'], '/');
            @ftp_put($ftp, $remotePath . '/' . self::LOCAL_VERSION_FILE, $file, FTP_ASCII);
        }
    }

    /**
     * Auto-incrementa la versione semver, chiedendo il tipo di bump.
     */
    private function autoIncrementVersion(string $currentTag): string
    {
        // Parse versione corrente
        $current = ltrim($currentTag, 'v');
        if (!preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $current, $m)) {
            $current = '0.0.0';
            $m = [0, 0, 0, 0];
        }

        $major = (int)$m[1];
        $minor = (int)$m[2];
        $patch = (int)$m[3];

        // Calcola le 3 opzioni
        $patchVersion = "$major.$minor." . ($patch + 1);
        $minorVersion = "$major." . ($minor + 1) . ".0";
        $majorVersion = ($major + 1) . ".0.0";

        $this->stdout("Versione corrente: ", Console::BOLD);
        $this->stdout("$currentTag\n\n");
        $this->stdout("Tipo di incremento:\n");
        $this->stdout("  [1] Patch  → v$patchVersion  (bugfix, piccole modifiche)\n");
        $this->stdout("  [2] Minor  → v$minorVersion  (nuove funzionalita)\n");
        $this->stdout("  [3] Major  → v$majorVersion  (breaking changes)\n");
        $this->stdout("  [4] Custom → inserisci manualmente\n");
        $this->stdout("  [0] Salta  → nessuna release GitHub\n\n");

        $choice = $this->prompt('Scelta', ['default' => '1']);

        switch ($choice) {
            case '1':
                return "v$patchVersion";
            case '2':
                return "v$minorVersion";
            case '3':
                return "v$majorVersion";
            case '4':
                return $this->prompt('Inserisci tag versione (es. v2.0.0-beta)');
            case '0':
                return '';
            default:
                return "v$patchVersion";
        }
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
