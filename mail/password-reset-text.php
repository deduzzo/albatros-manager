<?php

use app\models\Configurazione;

/* @var $this yii\web\View */
/* @var $nome string Nome completo dell'utente */
/* @var $resetLink string Link completo per il reset */
/* @var $expiry string Tempo di scadenza (default: "24 ore") */

$fondazione = Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
$expiry = $expiry ?? '24 ore';
?>
<?= $fondazione ?> - Reimposta la tua password
========================================

Gentile <?= $nome ?>,

abbiamo ricevuto una richiesta per reimpostare la password del tuo account sulla piattaforma <?= $fondazione ?>.

Per procedere, copia e incolla il seguente link nel tuo browser:

<?= $resetLink ?>


INFORMAZIONI IMPORTANTI:
- Questo link e' valido per <?= $expiry ?>.
- Se non hai richiesto tu il reset della password, ignora questa email.
- Non condividere questo link con nessuno.

---
Cordiali saluti,
<?= $fondazione ?>

Questa email e' stata generata automaticamente. Non rispondere a questa email.
