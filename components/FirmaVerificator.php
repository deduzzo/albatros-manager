<?php

namespace app\components;

use Yii;

/**
 * Componente per la verifica di firme digitali P7M (CAdES) e PDF firmati (PAdES).
 * Utilizza openssl e pdfsig (poppler-utils) quando disponibili.
 */
class FirmaVerificator
{
    /**
     * Auto-detect del tipo di firma.
     * @param string $filePath Percorso del file
     * @return string 'p7m', 'pdf' o 'sconosciuto'
     */
    public static function detectTipo($filePath)
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'p7m') {
            return 'p7m';
        }

        // Verifica se e' un PDF leggendo i magic bytes
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            $header = fread($handle, 5);
            fclose($handle);
            if ($header === '%PDF-') {
                return 'pdf';
            }
        }

        // Se l'estensione suggerisce PDF
        if ($ext === 'pdf') {
            return 'pdf';
        }

        // Prova a trattarlo come P7M (DER encoded PKCS7)
        $content = file_get_contents($filePath, false, null, 0, 2);
        if ($content !== false && ord($content[0]) === 0x30) {
            return 'p7m';
        }

        return 'sconosciuto';
    }

    /**
     * Verifica una firma P7M (CAdES).
     * @param string $filePath Percorso del file .p7m
     * @return array Risultato della verifica con chiavi:
     *   - valida: bool
     *   - firmatario: array (cn, o, cf, email)
     *   - data_firma: string|null
     *   - certificato: array (emittente, valido_da, valido_a, seriale)
     *   - contenuto_path: string|null (percorso del contenuto estratto)
     *   - errori: array
     */
    public static function verificaP7M($filePath)
    {
        $risultato = [
            'valida' => false,
            'firmatario' => [
                'cn' => null,
                'o' => null,
                'cf' => null,
                'email' => null,
            ],
            'data_firma' => null,
            'certificato' => [
                'emittente' => null,
                'valido_da' => null,
                'valido_a' => null,
                'seriale' => null,
            ],
            'contenuto_path' => null,
            'errori' => [],
        ];

        if (!file_exists($filePath)) {
            $risultato['errori'][] = 'File non trovato: ' . $filePath;
            return $risultato;
        }

        // Verifica firma con openssl
        $outputFile = Yii::getAlias('@runtime') . '/firma_contenuto_' . uniqid() . '.tmp';

        // Prova la verifica (con -noverify per non richiedere la CA chain completa)
        $cmd = sprintf(
            'openssl smime -verify -in %s -inform DER -noverify -out %s 2>&1',
            escapeshellarg($filePath),
            escapeshellarg($outputFile)
        );

        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        $outputStr = implode("\n", $output);

        if ($returnCode === 0 || strpos($outputStr, 'Verification successful') !== false) {
            $risultato['valida'] = true;
        } else {
            $risultato['errori'][] = 'Verifica firma fallita: ' . $outputStr;
        }

        // Se il contenuto e' stato estratto, salvalo
        if (file_exists($outputFile) && filesize($outputFile) > 0) {
            // Determina estensione dal nome originale
            $nomeOriginale = pathinfo($filePath, PATHINFO_FILENAME);
            $extOriginale = pathinfo($nomeOriginale, PATHINFO_EXTENSION) ?: 'pdf';
            $contenutoPath = Yii::getAlias('@webroot') . '/uploads/firme/estratto_' . uniqid() . '.' . $extOriginale;

            $dirContenuto = dirname($contenutoPath);
            if (!is_dir($dirContenuto)) {
                mkdir($dirContenuto, 0775, true);
            }

            if (copy($outputFile, $contenutoPath)) {
                $risultato['contenuto_path'] = $contenutoPath;
            }
            @unlink($outputFile);
        } else {
            @unlink($outputFile);
        }

        // Estrai informazioni del certificato
        $cmdCerts = sprintf(
            'openssl pkcs7 -in %s -inform DER -print_certs 2>&1',
            escapeshellarg($filePath)
        );

        $certOutput = [];
        exec($cmdCerts, $certOutput, $certReturn);
        $certStr = implode("\n", $certOutput);

        // Parsing del certificato
        self::parseCertificateInfo($certStr, $risultato);

        // Prova a estrarre la data della firma dal PKCS7
        $cmdInfo = sprintf(
            'openssl pkcs7 -in %s -inform DER -print 2>&1 | head -50',
            escapeshellarg($filePath)
        );
        $infoOutput = [];
        exec($cmdInfo, $infoOutput);
        $infoStr = implode("\n", $infoOutput);

        // Cerca la data della firma nel campo signingTime
        if (preg_match('/signingTime.*?(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $infoStr, $matches)) {
            $risultato['data_firma'] = sprintf('%s-%s-%s %s:%s:%s',
                $matches[1], $matches[2], $matches[3],
                $matches[4], $matches[5], $matches[6]
            );
        }

        return $risultato;
    }

    /**
     * Verifica un PDF firmato (PAdES).
     * @param string $filePath Percorso del file PDF
     * @return array Risultato della verifica (stessa struttura di verificaP7M)
     */
    public static function verificaPDF($filePath)
    {
        $risultato = [
            'valida' => false,
            'firmatario' => [
                'cn' => null,
                'o' => null,
                'cf' => null,
                'email' => null,
            ],
            'data_firma' => null,
            'certificato' => [
                'emittente' => null,
                'valido_da' => null,
                'valido_a' => null,
                'seriale' => null,
            ],
            'contenuto_path' => null,
            'errori' => [],
        ];

        if (!file_exists($filePath)) {
            $risultato['errori'][] = 'File non trovato: ' . $filePath;
            return $risultato;
        }

        // Prova con pdfsig (poppler-utils) se disponibile
        $pdfsigAvailable = false;
        exec('which pdfsig 2>/dev/null', $which, $whichReturn);
        if ($whichReturn === 0) {
            $pdfsigAvailable = true;
        }

        if ($pdfsigAvailable) {
            $cmd = sprintf('pdfsig %s 2>&1', escapeshellarg($filePath));
            $output = [];
            exec($cmd, $output, $returnCode);
            $outputStr = implode("\n", $output);

            if (strpos($outputStr, 'Signature #1') !== false || strpos($outputStr, 'Digital Signature') !== false) {
                // Ha almeno una firma
                if (strpos($outputStr, 'Signature Validation: Signature is Valid') !== false
                    || strpos($outputStr, 'is Valid') !== false) {
                    $risultato['valida'] = true;
                }

                // Estrai nome firmatario dal subject
                if (preg_match('/Signer Certificate Common Name:\s*(.+)/i', $outputStr, $m)) {
                    $risultato['firmatario']['cn'] = trim($m[1]);
                }
                if (preg_match('/Signer full Distinguished Name:\s*(.+)/i', $outputStr, $m)) {
                    $dn = trim($m[1]);
                    // Estrai CF dal campo serialNumber o TINIT
                    if (preg_match('/serialNumber\s*=\s*(?:TINIT-)?([A-Z0-9]{16})/i', $dn, $cfMatch)) {
                        $risultato['firmatario']['cf'] = strtoupper($cfMatch[1]);
                    }
                    if (preg_match('/O\s*=\s*([^,\/]+)/i', $dn, $oMatch)) {
                        $risultato['firmatario']['o'] = trim($oMatch[1]);
                    }
                }
                if (preg_match('/Signing Time:\s*(.+)/i', $outputStr, $m)) {
                    $risultato['data_firma'] = trim($m[1]);
                }
            } else {
                $risultato['errori'][] = 'Nessuna firma digitale trovata nel PDF.';
            }
        } else {
            // Analisi base: cerca la struttura /Sig nel PDF
            $content = file_get_contents($filePath);
            if ($content !== false) {
                if (preg_match('/\/Type\s*\/Sig/', $content)) {
                    $risultato['errori'][] = 'Il PDF sembra contenere una firma digitale, ma pdfsig (poppler-utils) non e\' installato per la verifica completa.';

                    // Prova a estrarre informazioni base
                    if (preg_match('/\/Name\s*\(([^)]+)\)/', $content, $m)) {
                        $risultato['firmatario']['cn'] = $m[1];
                    }
                    if (preg_match('/\/M\s*\(D:(\d{14})/', $content, $m)) {
                        $dateStr = $m[1];
                        $risultato['data_firma'] = substr($dateStr, 0, 4) . '-' . substr($dateStr, 4, 2) . '-' . substr($dateStr, 6, 2)
                            . ' ' . substr($dateStr, 8, 2) . ':' . substr($dateStr, 10, 2) . ':' . substr($dateStr, 12, 2);
                    }
                } else {
                    $risultato['errori'][] = 'Nessuna firma digitale trovata nel PDF.';
                }
            } else {
                $risultato['errori'][] = 'Impossibile leggere il file PDF.';
            }
        }

        return $risultato;
    }

    /**
     * Verifica un file determinandone automaticamente il tipo.
     * @param string $filePath Percorso del file
     * @return array Risultato della verifica + chiave 'tipo' ('p7m', 'pdf', 'sconosciuto')
     */
    public static function verifica($filePath)
    {
        $tipo = self::detectTipo($filePath);

        $risultato = match ($tipo) {
            'p7m' => self::verificaP7M($filePath),
            'pdf' => self::verificaPDF($filePath),
            default => [
                'valida' => false,
                'firmatario' => ['cn' => null, 'o' => null, 'cf' => null, 'email' => null],
                'data_firma' => null,
                'certificato' => ['emittente' => null, 'valido_da' => null, 'valido_a' => null, 'seriale' => null],
                'contenuto_path' => null,
                'errori' => ['Formato file non supportato.'],
            ],
        };

        $risultato['tipo'] = $tipo;
        return $risultato;
    }

    /**
     * Parsing delle informazioni del certificato dal testo PEM.
     * @param string $certStr Output di openssl print_certs
     * @param array &$risultato Array risultato da popolare
     */
    private static function parseCertificateInfo($certStr, &$risultato)
    {
        // Subject
        if (preg_match('/subject\s*[:=]\s*(.+)/i', $certStr, $m)) {
            $subject = $m[1];

            // Common Name
            if (preg_match('/CN\s*=\s*([^\/,\n]+)/i', $subject, $cnMatch)) {
                $risultato['firmatario']['cn'] = trim($cnMatch[1]);
            }

            // Organization
            if (preg_match('/O\s*=\s*([^\/,\n]+)/i', $subject, $oMatch)) {
                $risultato['firmatario']['o'] = trim($oMatch[1]);
            }

            // Codice Fiscale (serialNumber con TINIT- prefix o diretto)
            if (preg_match('/serialNumber\s*=\s*(?:TINIT-)?([A-Z0-9]{16})/i', $subject, $cfMatch)) {
                $risultato['firmatario']['cf'] = strtoupper($cfMatch[1]);
            }

            // Email
            if (preg_match('/emailAddress\s*=\s*([^\/,\n]+)/i', $subject, $emailMatch)) {
                $risultato['firmatario']['email'] = trim($emailMatch[1]);
            }
        }

        // Issuer
        if (preg_match('/issuer\s*[:=]\s*(.+)/i', $certStr, $m)) {
            $risultato['certificato']['emittente'] = trim($m[1]);
        }

        // Per avere dettagli del certificato, proviamo con openssl x509
        // Estraiamo il primo certificato PEM
        if (preg_match('/(-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----)/s', $certStr, $pemMatch)) {
            $tempCert = Yii::getAlias('@runtime') . '/cert_temp_' . uniqid() . '.pem';
            file_put_contents($tempCert, $pemMatch[1]);

            // Dettagli certificato
            $cmdDetail = sprintf('openssl x509 -in %s -noout -subject -issuer -dates -serial 2>&1', escapeshellarg($tempCert));
            $detailOutput = [];
            exec($cmdDetail, $detailOutput);
            $detailStr = implode("\n", $detailOutput);

            // Seriale
            if (preg_match('/serial\s*=\s*([A-F0-9]+)/i', $detailStr, $serialMatch)) {
                $risultato['certificato']['seriale'] = $serialMatch[1];
            }

            // Date validita
            if (preg_match('/notBefore\s*=\s*(.+)/i', $detailStr, $dateMatch)) {
                $risultato['certificato']['valido_da'] = trim($dateMatch[1]);
            }
            if (preg_match('/notAfter\s*=\s*(.+)/i', $detailStr, $dateMatch)) {
                $risultato['certificato']['valido_a'] = trim($dateMatch[1]);
            }

            // Issuer piu dettagliato
            if (preg_match('/issuer\s*=\s*(.+)/i', $detailStr, $issuerMatch)) {
                $risultato['certificato']['emittente'] = trim($issuerMatch[1]);
            }

            // Se non abbiamo ancora il subject, riproviamo
            if (empty($risultato['firmatario']['cn'])) {
                if (preg_match('/subject\s*=\s*(.+)/i', $detailStr, $subjMatch)) {
                    $subject = $subjMatch[1];
                    if (preg_match('/CN\s*=\s*([^\/,\n]+)/i', $subject, $cnMatch)) {
                        $risultato['firmatario']['cn'] = trim($cnMatch[1]);
                    }
                    if (preg_match('/O\s*=\s*([^\/,\n]+)/i', $subject, $oMatch)) {
                        $risultato['firmatario']['o'] = trim($oMatch[1]);
                    }
                    if (preg_match('/serialNumber\s*=\s*(?:TINIT-)?([A-Z0-9]{16})/i', $subject, $cfMatch)) {
                        $risultato['firmatario']['cf'] = strtoupper($cfMatch[1]);
                    }
                }
            }

            @unlink($tempCert);
        }
    }
}
