<?php

namespace app\components;

use Yii;

/**
 * Parser per CHANGELOG.md strutturato.
 * Legge il file, lo parsa in versioni con sezioni taggate per tipo utente.
 */
class ChangelogParser
{
    private string $filePath;
    private ?array $parsed = null;

    public function __construct(?string $filePath = null)
    {
        $this->filePath = $filePath ?? Yii::getAlias('@app/CHANGELOG.md');
    }

    /**
     * Parsa CHANGELOG.md e restituisce array strutturato.
     * @return array [
     *   ['version' => '1.5.0', 'date' => '2026-03-14', 'sections' => [
     *     ['title' => 'Novità', 'tag' => 'tutti', 'items' => ['...', '...']],
     *     ['title' => 'Novità', 'tag' => 'admin', 'items' => ['...']],
     *   ]],
     * ]
     */
    public function parse(): array
    {
        if ($this->parsed !== null) {
            return $this->parsed;
        }

        if (!file_exists($this->filePath)) {
            $this->parsed = [];
            return $this->parsed;
        }

        $content = file_get_contents($this->filePath);
        $lines = explode("\n", $content);

        $versions = [];
        $currentVersion = null;
        $currentSectionIndex = -1;

        foreach ($lines as $line) {
            // Versione: ## [1.5.0] - 2026-03-14
            if (preg_match('/^## \[(.+?)\] - (\d{4}-\d{2}-\d{2})/', $line, $m)) {
                if ($currentVersion !== null) {
                    $versions[] = $currentVersion;
                }
                $currentVersion = [
                    'version' => $m[1],
                    'date' => $m[2],
                    'sections' => [],
                ];
                $currentSectionIndex = -1;
                continue;
            }

            // Sezione: ### Novità {admin}
            if (preg_match('/^### (.+?) \{(.+?)\}/', $line, $m)) {
                if ($currentVersion !== null) {
                    $currentVersion['sections'][] = [
                        'title' => trim($m[1]),
                        'tag' => trim($m[2]),
                        'items' => [],
                    ];
                    $currentSectionIndex = count($currentVersion['sections']) - 1;
                }
                continue;
            }

            // Item: - testo
            if (preg_match('/^- (.+)/', $line, $m) && $currentVersion !== null && $currentSectionIndex >= 0) {
                $currentVersion['sections'][$currentSectionIndex]['items'][] = trim($m[1]);
            }
        }

        if ($currentVersion !== null) {
            $versions[] = $currentVersion;
        }

        $this->parsed = $versions;
        return $this->parsed;
    }

    /**
     * Filtra le versioni per tipo utente.
     * Un admin vede sezioni con tag 'tutti' e 'admin'.
     */
    public function getForUserType(string $type): array
    {
        $versions = $this->parse();
        $allowedTags = ['tutti', $type];
        $filtered = [];

        foreach ($versions as $version) {
            $filteredSections = [];
            foreach ($version['sections'] as $section) {
                if (in_array($section['tag'], $allowedTags, true)) {
                    $filteredSections[] = $section;
                }
            }
            if (!empty($filteredSections)) {
                $filtered[] = [
                    'version' => $version['version'],
                    'date' => $version['date'],
                    'sections' => $filteredSections,
                ];
            }
        }

        return $filtered;
    }

    /**
     * Restituisce la versione più recente.
     */
    public function getLatestVersion(): ?string
    {
        $versions = $this->parse();
        return !empty($versions) ? $versions[0]['version'] : null;
    }

    /**
     * Conta le voci non lette per un tipo utente.
     */
    public function countUnreadItems(string $type, ?string $lastSeen): int
    {
        $versions = $this->getForUserType($type);
        $count = 0;

        foreach ($versions as $version) {
            // Se lastSeen è null, tutte le versioni sono "non lette"
            // Se la versione è > lastSeen, è non letta
            if ($lastSeen !== null && version_compare($version['version'], $lastSeen, '<=')) {
                break;
            }
            foreach ($version['sections'] as $section) {
                $count += count($section['items']);
            }
        }

        return $count;
    }
}
