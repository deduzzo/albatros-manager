<?php

namespace app\components;

use Yii;

/**
 * Componente per la verifica di firme digitali P7M (CAdES) e PDF firmati (PAdES).
 * Estrae tutte le informazioni disponibili dal certificato X.509.
 */
class FirmaVerificator
{
    /**
     * Struttura risultato vuota con tutti i campi possibili.
     */
    private static function risultatoVuoto(): array
    {
        return [
            'valida' => false,
            'firmatario' => [
                'cn' => null,            // Common Name (nome completo)
                'cognome' => null,        // Cognome (GN o derivato da CN)
                'nome' => null,           // Nome (G o derivato da CN)
                'cf' => null,             // Codice Fiscale (serialNumber/TINIT)
                'email' => null,          // Email (emailAddress/rfc822Name)
                'organizzazione' => null, // Organization (O)
                'unita' => null,          // Organizational Unit (OU)
                'paese' => null,          // Country (C)
                'localita' => null,       // Locality (L)
                'provincia' => null,      // State (ST)
                'titolo' => null,         // Title (T)
                'dn_completo' => null,    // Distinguished Name completo
            ],
            'firma' => [
                'data_firma' => null,     // Data/ora della firma
                'algoritmo' => null,      // Algoritmo di firma (SHA256withRSA, etc.)
                'tipo_firma' => null,     // CAdES, PAdES
                'controfirme' => 0,       // Numero controfirme
                'marca_temporale' => null, // Timestamp Authority (se presente)
            ],
            'certificato' => [
                'emittente_cn' => null,   // CA Common Name
                'emittente_o' => null,    // CA Organization
                'emittente_dn' => null,   // CA Distinguished Name completo
                'valido_da' => null,      // Not Before
                'valido_a' => null,       // Not After
                'scaduto' => null,        // true/false
                'seriale' => null,        // Serial Number
                'versione' => null,       // Versione certificato (V3)
                'chiave_pubblica' => null, // Tipo e lunghezza (RSA 2048 bit)
            ],
            'tipologia' => [
                'key_usage' => [],        // digitalSignature, nonRepudiation, etc.
                'extended_key_usage' => [], // emailProtection, codeSigning, etc.
                'qualificato' => null,    // Certificato qualificato eIDAS (true/false/null)
                'policy_oid' => [],       // Policy OID del certificato
                'policy_nome' => [],      // Nome leggibile della policy
                'tipo_certificato' => null, // firma, autenticazione, cifratura, firma_qualificata
            ],
            'contenuto_path' => null,
            'errori' => [],
            'avvisi' => [],
        ];
    }

    /**
     * Auto-detect del tipo di firma.
     */
    public static function detectTipo($filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'p7m') {
            return 'p7m';
        }

        $handle = fopen($filePath, 'rb');
        if ($handle) {
            $header = fread($handle, 5);
            fclose($handle);
            if ($header === '%PDF-') {
                return 'pdf';
            }
        }

        if ($ext === 'pdf') {
            return 'pdf';
        }

        $content = file_get_contents($filePath, false, null, 0, 2);
        if ($content !== false && strlen($content) >= 1 && ord($content[0]) === 0x30) {
            return 'p7m';
        }

        return 'sconosciuto';
    }

    /**
     * Verifica un file determinandone automaticamente il tipo.
     */
    public static function verifica($filePath): array
    {
        $tipo = self::detectTipo($filePath);

        $risultato = match ($tipo) {
            'p7m' => self::verificaP7M($filePath),
            'pdf' => self::verificaPDF($filePath),
            default => array_merge(self::risultatoVuoto(), [
                'errori' => ['Formato file non supportato.'],
            ]),
        };

        $risultato['tipo'] = $tipo;
        return $risultato;
    }

    /**
     * Verifica una firma P7M (CAdES).
     */
    public static function verificaP7M($filePath): array
    {
        $risultato = self::risultatoVuoto();
        $risultato['firma']['tipo_firma'] = 'CAdES (P7M)';

        if (!file_exists($filePath)) {
            $risultato['errori'][] = 'File non trovato: ' . $filePath;
            return $risultato;
        }

        // 1. Verifica firma con openssl
        $outputFile = Yii::getAlias('@runtime') . '/firma_contenuto_' . uniqid() . '.tmp';
        $cmd = sprintf(
            'openssl smime -verify -in %s -inform DER -noverify -out %s 2>&1',
            escapeshellarg($filePath),
            escapeshellarg($outputFile)
        );
        $output = [];
        exec($cmd, $output, $returnCode);
        $outputStr = implode("\n", $output);

        if ($returnCode === 0 || strpos($outputStr, 'Verification successful') !== false) {
            $risultato['valida'] = true;
        } else {
            $risultato['errori'][] = 'Verifica firma fallita: ' . $outputStr;
        }

        // 2. Estrai contenuto
        if (file_exists($outputFile) && filesize($outputFile) > 0) {
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

        // 3. Estrai certificati PEM
        $cmdCerts = sprintf(
            'openssl pkcs7 -in %s -inform DER -print_certs 2>&1',
            escapeshellarg($filePath)
        );
        $certOutput = [];
        exec($cmdCerts, $certOutput);
        $certStr = implode("\n", $certOutput);

        // Parsing completo del certificato
        self::parseCertificateComplete($certStr, $risultato);

        // 4. Estrai info PKCS7 (signingTime, algoritmo, controfirme)
        $cmdInfo = sprintf(
            'openssl pkcs7 -in %s -inform DER -print 2>&1',
            escapeshellarg($filePath)
        );
        $infoOutput = [];
        exec($cmdInfo, $infoOutput);
        $infoStr = implode("\n", $infoOutput);

        // Data firma (signingTime)
        if (preg_match('/signingTime.*?(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $infoStr, $matches)) {
            $risultato['firma']['data_firma'] = sprintf('%s-%s-%s %s:%s:%s',
                $matches[1], $matches[2], $matches[3],
                $matches[4], $matches[5], $matches[6]
            );
        }

        // Algoritmo di firma
        if (preg_match('/digest_algorithm.*?algorithm:\s*([\w-]+)/is', $infoStr, $m)) {
            $digest = self::oidToName($m[1]);
            $risultato['firma']['algoritmo'] = $digest;
        }

        // Controfirme
        $risultato['firma']['controfirme'] = substr_count($infoStr, 'countersignature');

        // Marca temporale
        if (preg_match('/timestamp/i', $infoStr)) {
            $risultato['firma']['marca_temporale'] = 'Presente';
        }

        return $risultato;
    }

    /**
     * Verifica un PDF firmato (PAdES).
     */
    public static function verificaPDF($filePath): array
    {
        $risultato = self::risultatoVuoto();
        $risultato['firma']['tipo_firma'] = 'PAdES (PDF)';

        if (!file_exists($filePath)) {
            $risultato['errori'][] = 'File non trovato: ' . $filePath;
            return $risultato;
        }

        // Prova con pdfsig
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
                if (strpos($outputStr, 'is Valid') !== false) {
                    $risultato['valida'] = true;
                }

                if (preg_match('/Signer Certificate Common Name:\s*(.+)/i', $outputStr, $m)) {
                    $risultato['firmatario']['cn'] = trim($m[1]);
                }

                if (preg_match('/Signer full Distinguished Name:\s*(.+)/i', $outputStr, $m)) {
                    $dn = trim($m[1]);
                    $risultato['firmatario']['dn_completo'] = $dn;
                    self::parseDN($dn, $risultato);
                }

                if (preg_match('/Signing Time:\s*(.+)/i', $outputStr, $m)) {
                    $risultato['firma']['data_firma'] = trim($m[1]);
                }

                if (preg_match('/Signing Hash Algorithm:\s*(.+)/i', $outputStr, $m)) {
                    $risultato['firma']['algoritmo'] = trim($m[1]);
                }

                // Conta firme multiple
                preg_match_all('/Signature #\d+/', $outputStr, $sigMatches);
                $risultato['firma']['controfirme'] = max(0, count($sigMatches[0]) - 1);
            } else {
                $risultato['errori'][] = 'Nessuna firma digitale trovata nel PDF.';
            }
        } else {
            // Fallback: analisi base dal contenuto PDF
            $content = file_get_contents($filePath);
            if ($content !== false) {
                if (preg_match('/\/Type\s*\/Sig/', $content)) {
                    $risultato['avvisi'][] = 'pdfsig (poppler-utils) non installato. Analisi limitata.';

                    if (preg_match('/\/Name\s*\(([^)]+)\)/', $content, $m)) {
                        $risultato['firmatario']['cn'] = $m[1];
                    }
                    if (preg_match('/\/M\s*\(D:(\d{14})/', $content, $m)) {
                        $dateStr = $m[1];
                        $risultato['firma']['data_firma'] = substr($dateStr, 0, 4) . '-' . substr($dateStr, 4, 2) . '-' . substr($dateStr, 6, 2)
                            . ' ' . substr($dateStr, 8, 2) . ':' . substr($dateStr, 10, 2) . ':' . substr($dateStr, 12, 2);
                    }
                    if (preg_match('/\/Filter\s*\/([^\s\/]+)/', $content, $m)) {
                        $risultato['firma']['algoritmo'] = $m[1];
                    }
                    if (preg_match('/\/SubFilter\s*\/([^\s\/]+)/', $content, $m)) {
                        $risultato['firma']['tipo_firma'] .= ' (' . $m[1] . ')';
                    }

                    // Conta firme
                    preg_match_all('/\/Type\s*\/Sig/', $content, $sigMatches);
                    if (count($sigMatches[0]) > 1) {
                        $risultato['firma']['controfirme'] = count($sigMatches[0]) - 1;
                    }

                    // Prova ad estrarre il certificato embedded
                    if (preg_match('/\/Cert\s*<([0-9a-fA-F]+)>/', $content, $certHex)) {
                        $certDer = hex2bin($certHex[1]);
                        if ($certDer) {
                            $tempCert = Yii::getAlias('@runtime') . '/pdf_cert_' . uniqid() . '.der';
                            file_put_contents($tempCert, $certDer);
                            self::parseX509FromFile($tempCert, $risultato, 'DER');
                            @unlink($tempCert);
                        }
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
     * Parsing completo dal testo PEM dei certificati.
     */
    private static function parseCertificateComplete(string $certStr, array &$risultato): void
    {
        // Subject e Issuer dal print_certs output
        if (preg_match('/subject\s*[:=]\s*(.+)/i', $certStr, $m)) {
            $subject = trim($m[1]);
            $risultato['firmatario']['dn_completo'] = $subject;
            self::parseDN($subject, $risultato);
        }

        if (preg_match('/issuer\s*[:=]\s*(.+)/i', $certStr, $m)) {
            self::parseIssuerDN(trim($m[1]), $risultato);
        }

        // Estrai primo certificato PEM per analisi dettagliata
        if (preg_match('/(-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----)/s', $certStr, $pemMatch)) {
            $tempCert = Yii::getAlias('@runtime') . '/cert_temp_' . uniqid() . '.pem';
            file_put_contents($tempCert, $pemMatch[1]);
            self::parseX509FromFile($tempCert, $risultato, 'PEM');
            @unlink($tempCert);
        }
    }

    /**
     * Analisi completa di un file certificato X.509.
     */
    private static function parseX509FromFile(string $certFile, array &$risultato, string $inform = 'PEM'): void
    {
        // Dettagli base: subject, issuer, dates, serial
        $cmd = sprintf(
            'openssl x509 -in %s -inform %s -noout -subject -issuer -dates -serial -fingerprint -text 2>&1',
            escapeshellarg($certFile),
            escapeshellarg($inform)
        );
        $output = [];
        exec($cmd, $output);
        $detailStr = implode("\n", $output);

        // Subject (se non ancora estratto)
        if (empty($risultato['firmatario']['cn'])) {
            if (preg_match('/subject\s*=\s*(.+)/i', $detailStr, $m)) {
                self::parseDN(trim($m[1]), $risultato);
            }
        }

        // Issuer dettagliato
        if (preg_match('/issuer\s*=\s*(.+)/i', $detailStr, $m)) {
            self::parseIssuerDN(trim($m[1]), $risultato);
        }

        // Seriale
        if (preg_match('/serial\s*=\s*([A-F0-9]+)/i', $detailStr, $m)) {
            $risultato['certificato']['seriale'] = $m[1];
        }

        // Date validita
        if (preg_match('/notBefore\s*=\s*(.+)/i', $detailStr, $m)) {
            $risultato['certificato']['valido_da'] = trim($m[1]);
        }
        if (preg_match('/notAfter\s*=\s*(.+)/i', $detailStr, $m)) {
            $validoA = trim($m[1]);
            $risultato['certificato']['valido_a'] = $validoA;
            // Verifica scadenza
            $ts = strtotime($validoA);
            if ($ts !== false) {
                $risultato['certificato']['scaduto'] = ($ts < time());
            }
        }

        // Versione
        if (preg_match('/Version:\s*(\d+)/i', $detailStr, $m)) {
            $risultato['certificato']['versione'] = 'V' . $m[1];
        }

        // Chiave pubblica
        if (preg_match('/Public Key Algorithm:\s*(.+)/i', $detailStr, $m)) {
            $algo = trim($m[1]);
            $bits = '';
            if (preg_match('/Public-Key:\s*\((\d+)\s*bit\)/i', $detailStr, $bm)) {
                $bits = ' ' . $bm[1] . ' bit';
            }
            $risultato['certificato']['chiave_pubblica'] = $algo . $bits;
        }

        // Algoritmo di firma del certificato
        if (empty($risultato['firma']['algoritmo'])) {
            if (preg_match('/Signature Algorithm:\s*(.+)/i', $detailStr, $m)) {
                $risultato['firma']['algoritmo'] = trim($m[1]);
            }
        }

        // Key Usage
        if (preg_match('/X509v3 Key Usage:\s*(?:critical\s*,?\s*)?(.+)/i', $detailStr, $m)) {
            $usages = array_map('trim', explode(',', trim($m[1])));
            $risultato['tipologia']['key_usage'] = $usages;
        }

        // Extended Key Usage
        if (preg_match('/X509v3 Extended Key Usage:\s*(?:critical\s*,?\s*)?(.+)/i', $detailStr, $m)) {
            $extUsages = array_map('trim', explode(',', trim($m[1])));
            $risultato['tipologia']['extended_key_usage'] = $extUsages;
        }

        // Certificate Policies (OID)
        if (preg_match_all('/Policy:\s*([\d.]+)/i', $detailStr, $policyMatches)) {
            $risultato['tipologia']['policy_oid'] = $policyMatches[1];
            foreach ($policyMatches[1] as $oid) {
                $nome = self::policyOidToName($oid);
                if ($nome) {
                    $risultato['tipologia']['policy_nome'][] = $nome;
                }
            }
        }

        // QcStatements (certificato qualificato eIDAS)
        if (strpos($detailStr, 'qcStatements') !== false || strpos($detailStr, 'QcCompliance') !== false) {
            $risultato['tipologia']['qualificato'] = true;
        }

        // Determina tipo certificato dalla key usage
        self::determinaTipoCertificato($risultato);
    }

    /**
     * Parsing del Distinguished Name del soggetto.
     */
    private static function parseDN(string $dn, array &$risultato): void
    {
        $risultato['firmatario']['dn_completo'] = $dn;

        // CN - Common Name
        if (preg_match('/CN\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['cn'] = trim($m[1]);

            // Prova a dividere nome/cognome dal CN
            $cn = trim($m[1]);
            $parts = explode(' ', $cn, 2);
            if (count($parts) === 2) {
                // Convenzione italiana: COGNOME NOME (tutto maiuscolo)
                if (strtoupper($cn) === $cn) {
                    $risultato['firmatario']['cognome'] = $parts[0];
                    $risultato['firmatario']['nome'] = $parts[1];
                }
            }
        }

        // GN - Given Name (nome)
        if (preg_match('/(?:GN|G|givenName)\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['nome'] = trim($m[1]);
        }

        // SN - Surname (cognome)
        if (preg_match('/(?:SN|surname)\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['cognome'] = trim($m[1]);
        }

        // O - Organization
        if (preg_match('/O\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['organizzazione'] = trim($m[1]);
        }

        // OU - Organizational Unit
        if (preg_match('/OU\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['unita'] = trim($m[1]);
        }

        // CF - serialNumber (TINIT-)
        if (preg_match('/serialNumber\s*=\s*(?:TINIT-)?([A-Z0-9]{16})/i', $dn, $m)) {
            $risultato['firmatario']['cf'] = strtoupper($m[1]);
        }

        // Email
        if (preg_match('/emailAddress\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['email'] = trim($m[1]);
        }

        // C - Country
        if (preg_match('/C\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['paese'] = trim($m[1]);
        }

        // L - Locality
        if (preg_match('/L\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['localita'] = trim($m[1]);
        }

        // ST - State/Province
        if (preg_match('/ST\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['provincia'] = trim($m[1]);
        }

        // T - Title
        if (preg_match('/(?:T|title)\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['titolo'] = trim($m[1]);
        }

        // dnQualifier (ID firma)
        if (preg_match('/dnQualifier\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['firmatario']['id_firma'] = trim($m[1]);
        }
    }

    /**
     * Parsing del Distinguished Name dell'emittente.
     */
    private static function parseIssuerDN(string $dn, array &$risultato): void
    {
        $risultato['certificato']['emittente_dn'] = $dn;

        if (preg_match('/CN\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['certificato']['emittente_cn'] = trim($m[1]);
        }

        if (preg_match('/O\s*=\s*([^\/,\n]+)/i', $dn, $m)) {
            $risultato['certificato']['emittente_o'] = trim($m[1]);
        }
    }

    /**
     * Determina il tipo di certificato dalle estensioni.
     */
    private static function determinaTipoCertificato(array &$risultato): void
    {
        $keyUsage = $risultato['tipologia']['key_usage'] ?? [];
        $extUsage = $risultato['tipologia']['extended_key_usage'] ?? [];
        $qualificato = $risultato['tipologia']['qualificato'] ?? false;

        $hasNonRepudiation = false;
        $hasDigitalSignature = false;
        $hasKeyEncipherment = false;

        foreach ($keyUsage as $usage) {
            $u = strtolower(trim($usage));
            if (str_contains($u, 'non repudiation') || str_contains($u, 'content commitment')) {
                $hasNonRepudiation = true;
            }
            if (str_contains($u, 'digital signature')) {
                $hasDigitalSignature = true;
            }
            if (str_contains($u, 'key encipherment')) {
                $hasKeyEncipherment = true;
            }
        }

        if ($hasNonRepudiation && $qualificato) {
            $risultato['tipologia']['tipo_certificato'] = 'Firma Digitale Qualificata (eIDAS)';
        } elseif ($hasNonRepudiation) {
            $risultato['tipologia']['tipo_certificato'] = 'Firma Digitale';
        } elseif ($hasDigitalSignature && $hasKeyEncipherment) {
            $risultato['tipologia']['tipo_certificato'] = 'Autenticazione e Cifratura';
        } elseif ($hasDigitalSignature) {
            $risultato['tipologia']['tipo_certificato'] = 'Autenticazione';
        } elseif ($hasKeyEncipherment) {
            $risultato['tipologia']['tipo_certificato'] = 'Cifratura';
        } else {
            $risultato['tipologia']['tipo_certificato'] = 'Generico';
        }
    }

    /**
     * Mappa OID di algoritmi a nomi leggibili.
     */
    private static function oidToName(string $oid): string
    {
        $map = [
            'sha256' => 'SHA-256',
            'sha384' => 'SHA-384',
            'sha512' => 'SHA-512',
            'sha1' => 'SHA-1 (deprecato)',
            'md5' => 'MD5 (insicuro)',
            'sha256WithRSAEncryption' => 'SHA-256 con RSA',
            'sha384WithRSAEncryption' => 'SHA-384 con RSA',
            'sha512WithRSAEncryption' => 'SHA-512 con RSA',
            'ecdsa-with-SHA256' => 'ECDSA con SHA-256',
            'ecdsa-with-SHA384' => 'ECDSA con SHA-384',
        ];
        return $map[strtolower($oid)] ?? $oid;
    }

    /**
     * Mappa Policy OID noti a nomi leggibili (CA italiane).
     */
    private static function policyOidToName(string $oid): ?string
    {
        $map = [
            // AgID / QTSP italiani
            '1.3.76.16' => 'Firma Qualificata AgID',
            '1.3.76.16.6' => 'Certificato Qualificato AgID',
            '1.3.76.16.4.2.1' => 'Firma Digitale Qualificata',
            '1.3.76.16.4.2.2' => 'Firma Remota Qualificata',
            // ETSI
            '0.4.0.194112.1.2' => 'QCP-n (ETSI - Persona Fisica)',
            '0.4.0.194112.1.0' => 'QCP-n-qscd (ETSI - con QSCD)',
            '0.4.0.194112.1.1' => 'QCP-l (ETSI - Persona Giuridica)',
            '0.4.0.194112.1.3' => 'QCP-l-qscd (ETSI - PG con QSCD)',
            // eIDAS
            '0.4.0.194112.1.4' => 'QCP-w (ETSI - Sito Web)',
            // CA comuni
            '2.16.840.1.113733' => 'VeriSign/Symantec',
            '1.3.6.1.4.1.6449' => 'Comodo/Sectigo',
            '2.16.840.1.114412' => 'DigiCert',
        ];

        // Cerca match esatto o prefisso
        if (isset($map[$oid])) {
            return $map[$oid];
        }
        foreach ($map as $prefix => $name) {
            if (str_starts_with($oid, $prefix)) {
                return $name;
            }
        }
        return null;
    }
}
