<?php
/**
 * Template contratto Co.Co.Co. PNRR - Collaborazione Coordinata e Continuativa
 * Piano Nazionale di Ripresa e Resilienza
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
$annoStipula = date('Y', strtotime($contratto->data_stipula));

$tipoDocente = $docente->provenienza === 'RU' ? 'Ricercatore Universitario' : 'Professionista';

// Numero protocollo PNRR
$protocollo = $contratto->numero_contratto ?: ('N.___ PNRR-M4C1I1.5/F/' . $annoStipula);
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
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #1a472a;
            padding-bottom: 12px;
        }
        .header-logos {
            text-align: center;
            margin-bottom: 8px;
            font-size: 8pt;
            color: #666;
            letter-spacing: 0.5pt;
        }
        .header h1 {
            font-size: 13pt;
            color: #1a472a;
            margin: 5px 0;
        }
        .header h2 {
            font-size: 10pt;
            color: #1a472a;
            margin: 3px 0;
            font-weight: normal;
        }
        .header .subtitle {
            font-size: 8pt;
            color: #666;
        }
        .protocollo {
            text-align: right;
            font-size: 9pt;
            color: #555;
            margin: 8px 0;
        }
        .titolo-contratto {
            text-align: center;
            margin: 20px 0;
        }
        .titolo-contratto h2 {
            font-size: 12pt;
            color: #1a472a;
            margin: 3px 0;
            text-transform: uppercase;
        }
        .titolo-contratto .sottotitolo {
            font-size: 9pt;
            color: #555;
        }
        .cup-box {
            margin: 10px 0;
            padding: 6px 12px;
            background: #e8f5e9;
            border: 1px solid #4caf50;
            font-size: 9pt;
        }
        .sezione {
            margin: 12px 0;
            text-align: justify;
        }
        .sezione-titolo {
            font-weight: bold;
            color: #1a472a;
            font-size: 10pt;
            margin: 15px 0 8px;
            text-transform: uppercase;
        }
        .articolo {
            margin: 12px 0;
            text-align: justify;
        }
        .articolo-titolo {
            font-weight: bold;
            color: #1a472a;
            font-size: 10pt;
            margin-bottom: 5px;
        }
        .premesse-lista {
            margin: 8px 0;
            padding-left: 15px;
        }
        .premesse-lista li {
            margin-bottom: 6px;
            list-style-type: none;
            text-indent: -10px;
            padding-left: 10px;
        }
        .premesse-lista li:before {
            content: "- ";
            font-weight: bold;
        }
        .tabella-compenso {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .tabella-compenso th, .tabella-compenso td {
            border: 1px solid #999;
            padding: 5px 8px;
            font-size: 9pt;
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
        .allegati {
            margin: 15px 0;
        }
        .allegati ul {
            padding-left: 20px;
        }
        .allegati li {
            margin-bottom: 4px;
            font-size: 9pt;
        }
        .firme {
            margin-top: 40px;
            width: 100%;
        }
        .firme table {
            width: 100%;
        }
        .firme td {
            width: 45%;
            text-align: center;
            vertical-align: top;
            padding: 8px;
        }
        .firma-linea {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 9pt;
        }
        .accettazione {
            margin-top: 30px;
            font-size: 9pt;
            font-style: italic;
            text-align: center;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #888;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-logos">
        <?= Html::encode(strtoupper($nomeFondazione)) ?> &bull; FUTURA &bull; MIM &bull; ITALIA DOMANI &bull; NextGenerationEU
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

<div class="titolo-contratto">
    <h2>Collaborazione Coordinata e Continuativa</h2>
    <div class="sottotitolo">(ART. 409 c.p.c. e art. 2 D.Lgs. n. 81/2015)</div>
</div>

<div class="sezione">
    <p>
        Contratto di collaborazione coordinata e continuativa per attivita' di docenza nell'ambito del
        Progetto <strong><?= Html::encode($progetto->codice_progetto) ?></strong> &mdash;
        "<?= Html::encode($progetto->titolo) ?>", finanziato dal Piano Nazionale di Ripresa e Resilienza (PNRR),
        Missione 4 "Istruzione e Ricerca", Componente 1, Investimento 1.5,
        finanziato dall'Unione Europea - NextGenerationEU.
    </p>
</div>

<div class="sezione-titolo">TRA</div>
<div class="sezione">
    <p>
        La <strong><?= Html::encode(strtoupper($nomeFondazione)) ?></strong>, con sede in
        <?= Html::encode($indirizzoFondazione) ?>, codice fiscale <?= Html::encode($cfEnte) ?>,
        (d'ora in avanti denominata anche "Committente"), nella persona del Legale Rappresentante pro tempore
        Prof.ssa Antonina Sidoti, C.F.: [C.F. LEGALE RAPPRESENTANTE],
    </p>
</div>

<div class="sezione-titolo">E</div>
<div class="sezione">
    <p>
        <?= Html::encode($docente->cognome . ' ' . $docente->nome) ?>
        C.F.: <?= Html::encode($docente->codice_fiscale) ?>,
        <?php if ($docente->data_nascita): ?>
            nato/a a <?= Html::encode($docente->luogo_nascita) ?> il <?= date('d/m/Y', strtotime($docente->data_nascita)) ?>,
        <?php endif; ?>
        residente in <?= Html::encode($docente->citta ?: '_______________') ?>
        Via <?= Html::encode($docente->indirizzo ?: '_______________') ?>
        CAP <?= Html::encode($docente->cap ?: '_____') ?>
        <?php if ($docente->provincia): ?>(<?= Html::encode($docente->provincia) ?>)<?php endif; ?>,
        di seguito denominato/a "Collaboratore",
    </p>
</div>

<div class="sezione-titolo">PREMESSO</div>
<div class="sezione">
    <ul class="premesse-lista">
        <li>
            Che la <?= Html::encode($nomeFondazione) ?> e' un Istituto Tecnologico Superiore (ITS Academy)
            di alta specializzazione tecnologica con sede in Sicilia, soggetto attuatore di percorsi
            formativi finanziati dal PNRR;
        </li>
        <li>
            Che e' stato attivato il Percorso ITS Academy
            "<?= Html::encode($progetto->titolo) ?>"
            (codice <?= Html::encode($progetto->codice_progetto) ?>), finanziato nell'ambito del PNRR
            Missione 4 - Componente 1 - Investimento 1.5;
        </li>
        <li>
            Che con avviso prot. n. <?= Html::encode($avviso->codice_avviso) ?>
            "<?= Html::encode($avviso->titolo) ?>" e' stata avviata la procedura di selezione
            per il conferimento di incarichi di docenza;
        </li>
        <li>
            Che il Collaboratore e' in possesso dei requisiti professionali e dei titoli richiesti
            dall'avviso di selezione ed e' risultato assegnatario del modulo
            "<?= Html::encode($modulo->titolo) ?>" (codice <?= Html::encode($modulo->codice) ?>);
        </li>
        <li>
            Che le parti sono concordi nello stipulare un contratto di collaborazione coordinata
            e continuativa ai sensi dell'art. 409 c.p.c. e dell'art. 2 del D.Lgs. n. 81/2015;
        </li>
    </ul>
</div>

<div class="sezione-titolo">TUTTO CIO' PREMESSO</div>
<div class="sezione">
    <p>Le parti, come sopra identificate, convengono e stipulano quanto segue:</p>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 1 - Premesse</div>
    <p>
        Le premesse costituiscono parte integrante e sostanziale del presente contratto e ne formano
        il presupposto giuridico e fattuale.
    </p>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 2 - Natura dell'incarico e Finalita'</div>
    <p>
        Il presente contratto ha per oggetto una collaborazione coordinata e continuativa ai sensi
        dell'art. 2222 e seguenti del Codice Civile. L'attivita' del Collaboratore sara' svolta
        in piena autonomia organizzativa, senza vincolo di subordinazione e senza obbligo di
        osservare un orario di lavoro predeterminato, nel rispetto del coordinamento con l'organizzazione
        del Committente.
    </p>
    <p>
        L'incarico riguarda l'attivita' di docenza per il modulo
        <strong>"<?= Html::encode($modulo->titolo) ?>"</strong>
        (codice <?= Html::encode($modulo->codice) ?>), nell'ambito del Percorso ITS Academy
        "<?= Html::encode($progetto->titolo) ?>".
        La presente collaborazione non configura in alcun modo un rapporto di lavoro subordinato.
    </p>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 3 - Attivita'</div>
    <p>
        L'incaricato si impegna ad eseguire l'incarico conferito con la massima diligenza e
        professionalita'. In particolare, il Collaboratore dovra':
    </p>
    <ul style="padding-left: 20px; font-size: 9pt;">
        <li>svolgere l'attivita' didattica secondo il programma e il calendario concordato;</li>
        <li>compilare il registro delle presenze e il diario di bordo;</li>
        <li>sottoscrivere il foglio firma per ogni giornata di lezione;</li>
        <li>predisporre il materiale didattico necessario;</li>
        <li>redigere la relazione finale sull'attivita' svolta;</li>
        <li>collaborare con il Coordinatore/Direttore del corso per il buon andamento delle attivita';</li>
        <li>rispettare la normativa PNRR e produrre la documentazione richiesta.</li>
    </ul>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 4 - Sede</div>
    <p>
        L'attivita' di docenza sara' svolta presso la sede indicata dal Committente per l'erogazione
        del modulo "<?= Html::encode($modulo->titolo) ?>", ovvero presso altra sede che sara'
        comunicata dal Coordinatore/Direttore del corso.
    </p>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 5 - Durata</div>
    <p>
        Il presente contratto ha durata dal <strong><?= $dataInizio ?></strong> al
        <strong><?= $dataFine ?></strong>, per complessive
        <strong><?= Html::encode($contratto->ore_contrattualizzate) ?> ore</strong> di docenza.
        Il calendario delle lezioni sara' concordato con il Coordinatore/Direttore del corso.
    </p>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 6 - Compenso</div>
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
            <td class="importo"><?= Html::encode($contratto->ore_contrattualizzate) ?> ore</td>
        </tr>
        <tr>
            <td>Compenso orario lordo (<?= Html::encode($tipoDocente) ?>)</td>
            <td class="importo">&euro; <?= $compensoOrarioFormatted ?></td>
        </tr>
        <tr>
            <td><strong>Compenso totale lordo</strong></td>
            <td class="importo"><strong>&euro; <?= $compensoTotaleFormatted ?></strong></td>
        </tr>
    </table>
    <p>
        Il compenso e' comprensivo di tutte le ritenute fiscali, previdenziali e assistenziali
        previste dalla normativa vigente. Il reddito derivante dal presente contratto e' assimilato
        a reddito di lavoro dipendente ai sensi dell'art. 50, comma 1, lett. c-bis) del TUIR
        (D.P.R. 917/1986), con applicazione delle relative ritenute IRPEF e addizionali.
    </p>
    <p>
        Sono a carico del Committente gli oneri assicurativi INAIL. Il Collaboratore e' iscritto
        alla Gestione Separata INPS ai sensi della Legge n. 335/1995, con ripartizione degli
        oneri contributivi come previsto dalla normativa vigente.
    </p>
    <p>
        La prestazione e' esente IVA ai sensi dell'art. 5, comma 2, del D.P.R. 26/10/1972 n. 633.
    </p>
    <p>
        Il pagamento sara' effettuato al termine dell'incarico, ovvero secondo le modalita' concordate,
        previa verifica della regolare esecuzione dell'attivita' e acquisizione della documentazione
        prevista dalla normativa PNRR.
    </p>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 7 - Trattamento dati personali</div>
    <p>
        I dati personali forniti dal Collaboratore saranno trattati dal Committente in conformita'
        al Regolamento (UE) 2016/679 (GDPR) e al D.Lgs. 196/2003 e s.m.i., esclusivamente per
        le finalita' connesse all'esecuzione del presente contratto e agli obblighi di rendicontazione
        previsti dalla normativa PNRR. Il Collaboratore dichiara di aver preso visione dell'informativa
        privacy resa ai sensi dell'art. 13 del GDPR.
    </p>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 8 - Recesso</div>
    <p>
        Ciascuna delle parti potra' recedere dal presente contratto con un preavviso scritto di
        almeno 15 (quindici) giorni, da comunicarsi a mezzo PEC o raccomandata A/R.
        In caso di recesso del Collaboratore, allo stesso spettera' il compenso proporzionale
        alle ore di docenza effettivamente svolte. In caso di inadempimento grave, il Committente
        potra' risolvere il contratto con effetto immediato.
    </p>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 9 - Controversie</div>
    <p>
        Per qualsiasi controversia derivante dall'interpretazione o dall'esecuzione del presente
        contratto, le parti si impegnano a tentare una composizione bonaria. In caso di mancato
        accordo, sara' competente in via esclusiva il Foro di Messina.
    </p>
</div>

<div class="articolo">
    <div class="articolo-titolo">Art. 10 - Rinvio</div>
    <p>
        Per quanto non espressamente previsto dal presente contratto, si fa riferimento alle
        disposizioni del Codice Civile, alla normativa vigente in materia di collaborazioni
        coordinate e continuative, al Regolamento (UE) 2021/241 che istituisce il dispositivo
        per la ripresa e la resilienza, nonche' alle disposizioni del PNRR e dell'Unita'
        di Missione del MIM.
    </p>
</div>

<div class="allegati">
    <div class="articolo-titolo">Allegati</div>
    <ul>
        <li>Curriculum Vitae dell'Incaricato</li>
        <li>Dichiarazione di insussistenza cause di incompatibilita'</li>
        <li>Eventuale autorizzazione dell'Amministrazione di appartenenza</li>
    </ul>
</div>

<div class="firme">
    <table>
        <tr>
            <td>
                <div>Per La <?= Html::encode($nomeFondazione) ?></div>
                <div class="firma-linea">
                    Prof.ssa Antonina Sidoti
                </div>
            </td>
            <td style="width: 10%;">&nbsp;</td>
            <td>
                <div>Il Collaboratore</div>
                <div class="firma-linea">
                    <?= Html::encode($docente->cognome . ' ' . $docente->nome) ?>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="accettazione">
    Per espressa accettazione degli articoli da 1 a 10 del presente contratto.
</div>

<div class="firme">
    <table>
        <tr>
            <td>&nbsp;</td>
            <td style="width: 10%;">&nbsp;</td>
            <td>
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
