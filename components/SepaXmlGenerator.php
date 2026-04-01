<?php

namespace app\components;

use app\models\Configurazione;

/**
 * Generatore di file SEPA XML pain.001.001.03
 * per il pagamento massivo delle borse di studio.
 */
class SepaXmlGenerator
{
    /**
     * Genera il contenuto XML SEPA pain.001.001.03
     *
     * @param array $pagamenti Array di pagamenti, ciascuno con:
     *   - beneficiario (string): nome e cognome del beneficiario
     *   - iban (string): IBAN del beneficiario
     *   - importo (float): importo in EUR
     *   - causale (string): causale del bonifico
     * @return string Contenuto XML
     */
    public static function genera($pagamenti)
    {
        $ibanMittente = Configurazione::get('banca_iban_fondazione');
        $bicMittente = Configurazione::get('banca_bic');
        $nomeMittente = Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';

        $msgId = 'ALB-' . date('YmdHis') . '-' . rand(1000, 9999);
        $pmtInfId = 'PMT-' . date('YmdHis');
        $totale = array_sum(array_column($pagamenti, 'importo'));
        $numTransazioni = count($pagamenti);

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('Document');
        $xml->writeAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');
        $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $xml->startElement('CstmrCdtTrfInitn');

        // Group Header
        $xml->startElement('GrpHdr');
        $xml->writeElement('MsgId', $msgId);
        $xml->writeElement('CreDtTm', date('Y-m-d\TH:i:s'));
        $xml->writeElement('NbOfTxs', (string)$numTransazioni);
        $xml->writeElement('CtrlSum', number_format($totale, 2, '.', ''));
        $xml->startElement('InitgPty');
        $xml->writeElement('Nm', $nomeMittente);
        $xml->endElement();
        $xml->endElement(); // GrpHdr

        // Payment Information
        $xml->startElement('PmtInf');
        $xml->writeElement('PmtInfId', $pmtInfId);
        $xml->writeElement('PmtMtd', 'TRF');
        $xml->writeElement('NbOfTxs', (string)$numTransazioni);
        $xml->writeElement('CtrlSum', number_format($totale, 2, '.', ''));

        $xml->startElement('PmtTpInf');
        $xml->startElement('SvcLvl');
        $xml->writeElement('Cd', 'SEPA');
        $xml->endElement();
        $xml->endElement(); // PmtTpInf

        $xml->writeElement('ReqdExctnDt', date('Y-m-d'));

        // Debtor (mittente)
        $xml->startElement('Dbtr');
        $xml->writeElement('Nm', $nomeMittente);
        $xml->endElement();

        $xml->startElement('DbtrAcct');
        $xml->startElement('Id');
        $xml->writeElement('IBAN', str_replace(' ', '', $ibanMittente));
        $xml->endElement();
        $xml->endElement(); // DbtrAcct

        $xml->startElement('DbtrAgt');
        $xml->startElement('FinInstnId');
        $xml->writeElement('BIC', $bicMittente);
        $xml->endElement();
        $xml->endElement(); // DbtrAgt

        // Transazioni (una per ogni beneficiario)
        foreach ($pagamenti as $pag) {
            $xml->startElement('CdtTrfTxInf');

            $xml->startElement('PmtId');
            $xml->writeElement('EndToEndId', 'E2E-' . date('Ymd') . '-' . rand(10000, 99999));
            $xml->endElement();

            $xml->startElement('Amt');
            $xml->startElement('InstdAmt');
            $xml->writeAttribute('Ccy', 'EUR');
            $xml->text(number_format($pag['importo'], 2, '.', ''));
            $xml->endElement();
            $xml->endElement(); // Amt

            $xml->startElement('Cdtr');
            $xml->writeElement('Nm', $pag['beneficiario']);
            $xml->endElement();

            $xml->startElement('CdtrAcct');
            $xml->startElement('Id');
            $xml->writeElement('IBAN', str_replace(' ', '', $pag['iban']));
            $xml->endElement();
            $xml->endElement(); // CdtrAcct

            $xml->startElement('RmtInf');
            $xml->writeElement('Ustrd', $pag['causale']);
            $xml->endElement();

            $xml->endElement(); // CdtTrfTxInf
        }

        $xml->endElement(); // PmtInf
        $xml->endElement(); // CstmrCdtTrfInitn
        $xml->endElement(); // Document
        $xml->endDocument();
        return $xml->outputMemory();
    }
}
