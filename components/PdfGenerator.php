<?php

namespace app\components;

use Yii;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Classe utility per la generazione di PDF tramite DOMPDF.
 * Funziona sia in Docker che in locale.
 */
class PdfGenerator
{
    /**
     * Genera un PDF da HTML e lo restituisce come stringa.
     * @param string $html Contenuto HTML completo
     * @param string $orientation 'portrait' o 'landscape'
     * @param string $paper Formato carta (default: 'A4')
     * @return string Contenuto PDF binario
     */
    public static function generate($html, $orientation = 'portrait', $paper = 'A4')
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isPhpEnabled', false);
        $options->set('tempDir', Yii::getAlias('@runtime'));

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Renderizza una view Yii2 e genera un PDF.
     * @param string $view Percorso della view (es. '/contratto/templates/fse')
     * @param array $params Parametri da passare alla view
     * @param string $orientation 'portrait' o 'landscape'
     * @param string $paper Formato carta
     * @return string Contenuto PDF binario
     */
    public static function renderView($view, $params = [], $orientation = 'portrait', $paper = 'A4')
    {
        $controller = Yii::$app->controller;
        $html = $controller->renderPartial($view, $params);

        return self::generate($html, $orientation, $paper);
    }

    /**
     * Genera un PDF e lo salva su file.
     * @param string $html Contenuto HTML
     * @param string $filePath Percorso file di output
     * @param string $orientation Orientamento
     * @param string $paper Formato carta
     * @return bool True se salvato con successo
     */
    public static function saveToFile($html, $filePath, $orientation = 'portrait', $paper = 'A4')
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $pdf = self::generate($html, $orientation, $paper);
        return file_put_contents($filePath, $pdf) !== false;
    }

    /**
     * Genera un PDF da view Yii2 e lo salva su file.
     * @param string $view Percorso della view
     * @param array $params Parametri
     * @param string $filePath Percorso file di output
     * @param string $orientation Orientamento
     * @param string $paper Formato carta
     * @return bool True se salvato con successo
     */
    public static function saveViewToFile($view, $params, $filePath, $orientation = 'portrait', $paper = 'A4')
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $pdf = self::renderView($view, $params, $orientation, $paper);
        return file_put_contents($filePath, $pdf) !== false;
    }

    /**
     * Invia un PDF come risposta HTTP (download o inline).
     * @param string $pdfContent Contenuto PDF binario
     * @param string $filename Nome file
     * @param bool $inline Se true mostra inline, altrimenti forza download
     * @return \yii\web\Response
     */
    public static function sendResponse($pdfContent, $filename, $inline = false)
    {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'application/pdf');

        $disposition = $inline ? 'inline' : 'attachment';
        $response->headers->set('Content-Disposition', $disposition . '; filename="' . $filename . '"');
        $response->headers->set('Content-Length', strlen($pdfContent));

        $response->content = $pdfContent;
        return $response;
    }
}
