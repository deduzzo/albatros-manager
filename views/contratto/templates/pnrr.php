<?php
/**
 * Template contratto PNRR - Piano Nazionale di Ripresa e Resilienza
 *
 * @var app\models\Contratto $contratto
 * @var app\models\Assegnazione $assegnazione
 * @var app\models\Docente $docente
 * @var app\models\Modulo $modulo
 * @var app\models\Avviso $avviso
 * @var app\models\Progetto $progetto
 * @var array $configurazione Dati fondazione dalla tabella configurazione
 */

use yii\helpers\Html;

$nomeFondazione = $configurazione['nome_fondazione'] ?? 'Fondazione Albatros';
$indirizzoFondazione = $configurazione['indirizzo'] ?? 'Viale Giostra 2, 98124 Messina';
$cfEnte = $configurazione['codice_fiscale_ente'] ?? '03110100835';
$telefonoFondazione = $configurazione['telefono'] ?? '';
$emailFondazione = $configurazione['email'] ?? '';
$pecFondazione = $configurazione['pec'] ?? '';

$compensoOrarioFormatted = number_format($contratto->compenso_orario, 2, ',', '.');
$compensoTotaleFormatted = number_format($contratto->compenso_totale, 2, ',', '.');
$dataStipula = date('d/m/Y', strtotime($contratto->data_stipula));
$dataInizio = date('d/m/Y', strtotime($contratto->data_inizio));
$dataFine = date('d/m/Y', strtotime($contratto->data_fine));

$tipoDocente = $docente->provenienza === 'RU' ? 'Ricercatore Universitario' : 'Professionista';

// Genera numero protocollo PNRR
$protocollo = $contratto->numero_contratto ?: ('N. ' . $contratto->id . 'D_ PNRR-M4C1I1.5/F/' . date('Y', strtotime($contratto->data_stipula)));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 2cm 2.5cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a472a;
            padding-bottom: 15px;
        }
        .header-logos {
            text-align: center;
            margin-bottom: 10px;
            font-size: 9pt;
            color: #666;
        }
        .header h1 {
            font-size: 14pt;
            color: #1a472a;
            margin: 5px 0;
        }
        .header h2 {
            font-size: 11pt;
            color: #1a472a;
            margin: 3px 0;
            font-weight: normal;
        }
        .header .subtitle {
            font-size: 9pt;
            color: #666;
        }
        .protocollo {
            text-align: right;
            font-size: 10pt;
            color: #555;
            margin: 10px 0;
        }
        .data-luogo {
            text-align: right;
            margin: 20px 0 10px;
            font-size: 10pt;
        }
        .cup-box {
            margin: 10px 0;
            padding: 8px 15px;
            background: #e8f5e9;
            border: 1px solid #4caf50;
            font-size: 10pt;
        }
        .oggetto {
            margin: 20px 0;
            padding: 10px 15px;
            background: #f1f8e9;
            border-left: 4px solid #1a472a;
        }
        .oggetto strong {
            color: #1a472a;
        }
        .dati-docente {
            margin: 15px 0;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }
        .dati-docente table {
            width: 100%;
            border-collapse: collapse;
        }
        .dati-docente td {
            padding: 3px 5px;
            font-size: 10pt;
        }
        .dati-docente td:first-child {
            width: 180px;
            font-weight: bold;
            color: #555;
        }
        .corpo {
            margin: 15px 0;
            text-align: justify;
        }
        .clausole {
            margin: 15px 0;
        }
        .clausole ol {
            padding-left: 20px;
        }
        .clausole li {
            margin-bottom: 8px;
            text-align: justify;
        }
        .tabella-compenso {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .tabella-compenso th, .tabella-compenso td {
            border: 1px solid #999;
            padding: 8px 10px;
            font-size: 10pt;
        }
        .tabella-compenso th {
            background: #1a472a;
            color: #fff;
            text-align: left;
        }
        .tabella-compenso td.importo {
            text-align: right;
            font-weight: bold;
        }
        .firme {
            margin-top: 50px;
            width: 100%;
        }
        .firme table {
            width: 100%;
        }
        .firme td {
            width: 45%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .firma-linea {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
            font-size: 10pt;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #888;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .nota-pnrr {
            margin: 15px 0;
            padding: 10px 15px;
            background: #fff3e0;
            border: 1px solid #ff9800;
            font-size: 9pt;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-logos">
        <?= Html::encode(strtoupper($nomeFondazione)) ?> &bull; FUTURA &bull; MIM &bull; ITALIA DOMANI &bull; UNIONE EUROPEA - NextGenerationEU
    </div>
    <h1><?= Html::encode($nomeFondazione) ?></h1>
    <h2>Piano Nazionale di Ripresa e Resilienza</h2>
    <h2>Missione 4 - Componente 1 - Investimento 1.5</h2>
    <div class="subtitle">
        C.F. <?= Html::encode($cfEnte) ?> &mdash; <?= Html::encode($indirizzoFondazione) ?>
    </div>
</div>

<div class="protocollo">
    <?= Html::encode($protocollo) ?> del <?= $dataStipula ?>
</div>

<?php if ($progetto->codice_progetto): ?>
<div class="cup-box">
    <strong>Progetto:</strong> <?= Html::encode($progetto->codice_progetto) ?> &mdash; "<?= Html::encode($progetto->titolo) ?>"
</div>
<?php endif; ?>

<div class="data-luogo">
    Messina, <?= $dataStipula ?>
</div>

<div class="dati-docente">
    <table>
        <tr>
            <td>Docente:</td>
            <td><strong><?= Html::encode($docente->cognome . ' ' . $docente->nome) ?></strong></td>
        </tr>
        <tr>
            <td>Codice Fiscale:</td>
            <td><?= Html::encode($docente->codice_fiscale) ?></td>
        </tr>
        <?php if ($docente->indirizzo): ?>
        <tr>
            <td>Indirizzo:</td>
            <td><?= Html::encode($docente->indirizzo) ?><?php if ($docente->cap || $docente->citta): ?>, <?= Html::encode(trim($docente->cap . ' ' . $docente->citta)) ?><?php endif; ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td>Tipologia:</td>
            <td><?= Html::encode($tipoDocente) ?></td>
        </tr>
    </table>
</div>

<div class="oggetto">
    <strong>OGGETTO:</strong> Incarico di docenza nell'ambito del PNRR &mdash;
    AVVISO N. <?= Html::encode($avviso->codice_avviso) ?><br>
    Progetto: <?= Html::encode($progetto->codice_progetto) ?> &mdash; "<?= Html::encode($progetto->titolo) ?>"<br>
    Modulo: <?= Html::encode($modulo->codice) ?> &mdash; "<?= Html::encode($modulo->titolo) ?>"
</div>

<div class="corpo">
    <p>
        Il/La Presidente della <?= Html::encode($nomeFondazione) ?>, con sede in <?= Html::encode($indirizzoFondazione) ?>,
        C.F. <?= Html::encode($cfEnte) ?>, in qualita' di Soggetto Attuatore del Progetto
        <strong><?= Html::encode($progetto->codice_progetto) ?></strong> &mdash; "<?= Html::encode($progetto->titolo) ?>",
        finanziato nell'ambito del Piano Nazionale di Ripresa e Resilienza (PNRR),
        Missione 4 "Istruzione e Ricerca", Componente 1, Investimento 1.5,
        finanziato dall'Unione Europea - NextGenerationEU,
    </p>

    <p style="text-align: center; font-weight: bold; font-size: 12pt; margin: 15px 0;">CONFERISCE</p>

    <p>
        al/alla Dott./Dott.ssa/Prof./Prof.ssa <strong><?= Html::encode($docente->cognome . ' ' . $docente->nome) ?></strong>,
        C.F. <?= Html::encode($docente->codice_fiscale) ?>,
        l'incarico di docenza per il modulo <strong>"<?= Html::encode($modulo->titolo) ?>"</strong>
        (codice <?= Html::encode($modulo->codice) ?>), alle condizioni di seguito specificate.
    </p>
</div>

<table class="tabella-compenso">
    <tr>
        <th>Descrizione</th>
        <th style="text-align: right;">Valore</th>
    </tr>
    <tr>
        <td>Modulo</td>
        <td class="importo"><?= Html::encode($modulo->codice . ' - ' . $modulo->titolo) ?></td>
    </tr>
    <tr>
        <td>Ore di docenza</td>
        <td class="importo"><?= $contratto->ore_contrattualizzate ?> ore</td>
    </tr>
    <tr>
        <td>Compenso orario (<?= Html::encode($tipoDocente) ?>)</td>
        <td class="importo">&euro; <?= $compensoOrarioFormatted ?></td>
    </tr>
    <tr>
        <td><strong>Compenso totale lordo</strong></td>
        <td class="importo"><strong>&euro; <?= $compensoTotaleFormatted ?></strong></td>
    </tr>
    <tr>
        <td>Periodo contratto</td>
        <td class="importo">dal <?= $dataInizio ?> al <?= $dataFine ?></td>
    </tr>
</table>

<div class="clausole">
    <p><strong>CLAUSOLE E CONDIZIONI:</strong></p>
    <ol>
        <li>
            Il docente si impegna a svolgere l'attivita' di docenza per complessive
            <strong><?= $contratto->ore_contrattualizzate ?> ore</strong>, secondo il calendario che sara'
            comunicato dal Coordinatore/Direttore del corso.
        </li>
        <li>
            Il compenso omnicomprensivo e' fissato in &euro; <?= $compensoOrarioFormatted ?>/ora
            per un totale di &euro; <?= $compensoTotaleFormatted ?>, al lordo di ogni ritenuta
            fiscale, previdenziale e assistenziale, come previsto dalla normativa vigente.
        </li>
        <li>
            Il pagamento sara' effettuato al termine dell'incarico, previa verifica della
            regolare esecuzione dell'attivita' e acquisizione della documentazione prevista.
        </li>
        <li>
            Il docente e' tenuto alla compilazione del <strong>diario di bordo</strong>,
            del registro delle presenze, alla sottoscrizione del foglio firma per ogni
            giornata di lezione e alla redazione della relazione finale.
        </li>
        <li>
            Il docente si impegna a rispettare la <strong>regolamentazione PNRR</strong> e a
            produrre tutta la documentazione richiesta dalla normativa vigente in materia
            di attuazione del Piano Nazionale di Ripresa e Resilienza.
        </li>
        <li>
            Il presente incarico non configura in alcun modo un rapporto di lavoro
            subordinato con la <?= Html::encode($nomeFondazione) ?>.
        </li>
        <li>
            Per quanto non espressamente previsto dal presente contratto, si fa riferimento
            alla normativa vigente in materia di PNRR e alle disposizioni dell'Unita'
            di Missione del MIM.
        </li>
    </ol>
</div>

<div class="nota-pnrr">
    <strong>NOTA:</strong> Eventuali rimborsi spese per trasferte e spostamenti potranno essere
    riconosciuti previa autorizzazione del Direttore del corso e presentazione della
    documentazione giustificativa, nei limiti previsti dalla normativa PNRR vigente.
</div>

<div class="firme">
    <table>
        <tr>
            <td>
                <div>Il Presidente</div>
                <div class="firma-linea">
                    Prof.ssa Antonina Sidoti
                </div>
            </td>
            <td style="width: 10%;">&nbsp;</td>
            <td>
                <div>Il Docente</div>
                <div class="firma-linea">
                    <?= Html::encode($docente->cognome . ' ' . $docente->nome) ?>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    <?= Html::encode($nomeFondazione) ?> &mdash; C.F. <?= Html::encode($cfEnte) ?> &mdash; <?= Html::encode($indirizzoFondazione) ?>
    <?php if ($telefonoFondazione): ?>
        &mdash; Tel. <?= Html::encode($telefonoFondazione) ?>
    <?php endif; ?>
    <?php if ($pecFondazione): ?>
        &mdash; PEC: <?= Html::encode($pecFondazione) ?>
    <?php endif; ?>
    <br>
    Finanziato dall'Unione Europea - NextGenerationEU &mdash; PNRR M4C1I1.5
</div>

</body>
</html>
