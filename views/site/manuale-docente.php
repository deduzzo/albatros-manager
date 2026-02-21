<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Manuale Docente';
?>

<style>
/* Manuale styles */
.manuale-wrapper {
    display: flex;
    min-height: 100vh;
}
.manuale-sidebar {
    width: 280px;
    min-width: 280px;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    border-right: 1px solid var(--tblr-border-color);
    background: var(--tblr-bg-surface);
    padding: 1.5rem 0;
    z-index: 10;
}
.manuale-sidebar .nav-link {
    padding: 0.4rem 1.25rem;
    color: var(--tblr-body-color);
    font-size: 0.8125rem;
    border-left: 2px solid transparent;
    transition: all 0.15s;
}
.manuale-sidebar .nav-link:hover {
    background: var(--tblr-bg-surface-secondary);
    border-left-color: var(--tblr-green);
}
.manuale-sidebar .nav-link.active {
    color: var(--tblr-green);
    font-weight: 600;
    border-left-color: var(--tblr-green);
    background: rgba(var(--tblr-green-rgb), 0.04);
}
.manuale-sidebar .nav-link .ti {
    margin-right: 0.5rem;
    width: 1.25rem;
    text-align: center;
}
.manuale-sidebar .sidebar-heading {
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
    color: var(--tblr-secondary);
    padding: 0.75rem 1.25rem 0.25rem;
}
.manuale-content {
    flex: 1;
    min-width: 0;
    padding: 2rem 3rem;
    max-width: 960px;
}
.manuale-content section {
    margin-bottom: 3rem;
    scroll-margin-top: 1rem;
}
.manuale-content .section-card {
    border: 1px solid var(--tblr-border-color);
    border-radius: var(--tblr-border-radius);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.manuale-content .section-card-header {
    padding: 1rem 1.25rem;
    font-weight: 600;
    font-size: 1.125rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.manuale-content .section-card-body {
    padding: 1.25rem;
}
.manuale-content h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}
.manuale-content h4 {
    font-size: 0.875rem;
    font-weight: 600;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}
.manuale-header {
    background: linear-gradient(135deg, #2fb344, #1f8c33);
    color: #fff;
    padding: 2rem 3rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.manuale-header h1 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}
.back-to-top {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.8125rem;
    color: var(--tblr-secondary);
    text-decoration: none;
    margin-top: 1rem;
}
.back-to-top:hover { color: var(--tblr-green); }
.alert-manuale {
    border-radius: var(--tblr-border-radius);
    padding: 0.75rem 1rem;
    margin: 0.75rem 0;
    font-size: 0.875rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}
.alert-manuale .ti {
    margin-top: 0.125rem;
    flex-shrink: 0;
}
.campo-table td, .campo-table th {
    font-size: 0.8125rem;
    padding: 0.4rem 0.75rem;
}
.menu-path {
    font-family: var(--tblr-font-monospace);
    font-size: 0.8125rem;
    background: var(--tblr-bg-surface-secondary);
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    white-space: nowrap;
}
.step-box {
    background: var(--tblr-bg-surface-secondary);
    border-radius: var(--tblr-border-radius);
    padding: 1rem;
    margin: 0.75rem 0;
}
.step-box strong {
    color: var(--tblr-green);
}

@media print {
    .manuale-sidebar, .manuale-header .btn, .back-to-top { display: none !important; }
    .manuale-content { padding: 0; max-width: 100%; }
    .manuale-wrapper { display: block; }
    .manuale-header { background: #2fb344 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    section { page-break-inside: avoid; }
}
@media (max-width: 768px) {
    .manuale-sidebar { display: none; position: fixed; left: 0; top: 0; width: 280px; z-index: 1050; box-shadow: 0 0 30px rgba(0,0,0,0.15); }
    .manuale-sidebar.show { display: block; }
    .manuale-content { padding: 1rem; }
    .manuale-header { padding: 1rem; flex-wrap: wrap; gap: 0.75rem; }
}
</style>

<!-- Header -->
<div class="manuale-header">
    <div class="d-flex align-items-center gap-3">
        <i class="ti ti-school" style="font-size: 2.5rem; opacity: 0.8;"></i>
        <div>
            <h1><i class="ti ti-book me-2"></i>Manuale Docente</h1>
            <div style="opacity: 0.8; font-size: 0.875rem;">Area Docenti - Albatros Manager</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-ghost-light btn-sm">
            <i class="ti ti-printer me-1"></i> Stampa / PDF
        </button>
        <button class="btn btn-ghost-light btn-sm d-md-none" onclick="document.getElementById('manuale-nav').classList.toggle('show')">
            <i class="ti ti-menu-2 me-1"></i> Indice
        </button>
        <a href="<?= Url::to(['/site/login']) ?>" class="btn btn-ghost-light btn-sm">
            <i class="ti ti-arrow-left me-1"></i> Torna al sito
        </a>
    </div>
</div>

<div class="manuale-wrapper">
    <!-- Sidebar -->
    <nav class="manuale-sidebar" id="manuale-nav">
        <div class="sidebar-heading">Indice del manuale</div>
        <div class="nav flex-column">
            <a class="nav-link" href="#introduzione"><i class="ti ti-info-circle"></i> Introduzione</a>
            <a class="nav-link" href="#registrazione"><i class="ti ti-user-plus"></i> Registrazione e Login</a>
            <a class="nav-link" href="#profilo"><i class="ti ti-user"></i> Il Tuo Profilo</a>
            <a class="nav-link" href="#bandi"><i class="ti ti-file-text"></i> Bandi Aperti</a>
            <a class="nav-link" href="#iscrizione"><i class="ti ti-send"></i> Iscriversi a un Modulo</a>
            <a class="nav-link" href="#le-mie-iscrizioni"><i class="ti ti-list-check"></i> Le Tue Iscrizioni</a>
            <a class="nav-link" href="#assegnazioni"><i class="ti ti-circle-check"></i> Le Tue Assegnazioni</a>
            <a class="nav-link" href="#inserire-ore"><i class="ti ti-clock"></i> Inserire le Ore</a>
            <a class="nav-link" href="#le-mie-ore"><i class="ti ti-clock-check"></i> Le Tue Ore</a>
            <a class="nav-link" href="#pagamenti"><i class="ti ti-currency-euro"></i> Pagamenti</a>
        </div>
        <div class="sidebar-heading mt-4">Altri manuali</div>
        <div class="nav flex-column">
            <a class="nav-link" href="<?= Url::to(['site/manuale-admin']) ?>"><i class="ti ti-shield-lock"></i> Manuale Amministratore</a>
            <a class="nav-link" href="<?= Url::to(['site/manuale-studente']) ?>"><i class="ti ti-users"></i> Manuale Studente</a>
        </div>
    </nav>

    <!-- Content -->
    <div class="manuale-content">

        <!-- 1. INTRODUZIONE -->
        <section id="introduzione">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-info-circle"></i> 1. Introduzione
                </div>
                <div class="section-card-body">
                    <h3>Cos'e l'Area Docenti</h3>
                    <p>L'Area Docenti e il portale riservato ai professionisti e ricercatori che collaborano o desiderano collaborare con i progetti formativi gestiti da Albatros Manager. Attraverso questa area puoi:</p>
                    <ul>
                        <li>Consultare i bandi aperti e candidarti per moduli di tuo interesse</li>
                        <li>Monitorare lo stato delle tue candidature</li>
                        <li>Gestire le assegnazioni ricevute e inserire le ore lavorate</li>
                        <li>Aggiornare il tuo profilo professionale</li>
                    </ul>

                    <h3>Come accedere</h3>
                    <p>L'accesso avviene dalla pagina di login principale del sistema. Seleziona la card <strong>"Docente"</strong> (con icona verde) e inserisci le tue credenziali:</p>
                    <table class="table campo-table">
                        <tr><th>Campo</th><th>Valore</th></tr>
                        <tr><td>Codice Fiscale</td><td>Il tuo codice fiscale (16 caratteri)</td></tr>
                        <tr><td>Password</td><td>La password scelta in fase di registrazione</td></tr>
                    </table>
                    <p>Se non hai ancora un account, puoi registrarti dalla stessa pagina. Se hai dimenticato la password, usa il link "Password dimenticata".</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 2. REGISTRAZIONE E LOGIN -->
        <section id="registrazione">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-user-plus"></i> 2. Registrazione e Login
                </div>
                <div class="section-card-body">
                    <h3>Registrazione</h3>
                    <p>Per registrarti come docente:</p>
                    <ol>
                        <li>Dalla pagina di login, clicca sulla card "Docente" e poi su <strong>"Registrati"</strong></li>
                        <li>Compila il modulo con i tuoi dati: codice fiscale, nome, cognome, email, telefono</li>
                        <li>Scegli una password sicura (minimo 6 caratteri)</li>
                        <li>Clicca <strong>"Registrati"</strong></li>
                    </ol>
                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Importante</strong>: Il codice fiscale inserito deve corrispondere esattamente a quello presente nell'anagrafica del sistema. Se non e presente, contatta la segreteria per farti inserire.</div>
                    </div>

                    <h3>Login</h3>
                    <ol>
                        <li>Accedi alla pagina di login</li>
                        <li>Seleziona la card <strong>"Docente"</strong></li>
                        <li>Inserisci il tuo <strong>codice fiscale</strong> (viene convertito automaticamente in maiuscolo)</li>
                        <li>Inserisci la tua <strong>password</strong></li>
                        <li>Opzionalmente seleziona "Ricordami su questo dispositivo"</li>
                        <li>Clicca <strong>"Accedi"</strong></li>
                    </ol>

                    <h3>Password dimenticata</h3>
                    <p>Se hai dimenticato la password:</p>
                    <ol>
                        <li>Clicca su "Password dimenticata" sotto il form di login docente</li>
                        <li>Inserisci il tuo codice fiscale</li>
                        <li>Riceverai un'email con le istruzioni per reimpostare la password</li>
                    </ol>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 3. IL TUO PROFILO -->
        <section id="profilo">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-user"></i> 3. Il Tuo Profilo
                </div>
                <div class="section-card-body">
                    <p>La pagina Profilo mostra i tuoi dati anagrafici e professionali registrati nel sistema. Accedi cliccando su <strong>"Profilo"</strong> nel menu di navigazione o dal dropdown del tuo nome in alto a destra.</p>

                    <h3>Dati visualizzati</h3>
                    <ul>
                        <li>Nome completo e codice fiscale</li>
                        <li>Email e telefono</li>
                        <li>Indirizzo, citta e provincia</li>
                        <li>Titolo di studio</li>
                        <li>Provenienza: TP (Professionista) o RU (Ricercatore Universitario)</li>
                    </ul>

                    <h3>Modificare i dati</h3>
                    <p>Per aggiornare i tuoi dati anagrafici:</p>
                    <ol>
                        <li>Vai alla pagina Profilo</li>
                        <li>Clicca <strong>"Modifica profilo"</strong></li>
                        <li>Aggiorna i campi desiderati (email, telefono, indirizzo, titolo di studio, ecc.)</li>
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>
                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: Il codice fiscale non e modificabile in quanto rappresenta la tua identita nel sistema. Per correzioni al codice fiscale, contatta la segreteria.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 4. BANDI APERTI -->
        <section id="bandi">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-file-text"></i> 4. Bandi Aperti
                </div>
                <div class="section-card-body">
                    <p>La sezione "Bandi Aperti" mostra tutti gli avvisi/bandi pubblicati per la selezione docenti. Accedi dal menu <strong>"Bandi Aperti"</strong>.</p>

                    <h3>Cosa vedi</h3>
                    <p>Per ogni bando aperto vengono mostrati:</p>
                    <ul>
                        <li>Titolo dell'avviso e progetto di riferimento</li>
                        <li>Data di pubblicazione e data di scadenza</li>
                        <li>Numero di moduli (materie) disponibili</li>
                        <li>Descrizione sintetica</li>
                    </ul>

                    <h3>Dettaglio bando</h3>
                    <p>Cliccando su un bando si apre la pagina di dettaglio con:</p>
                    <ul>
                        <li>Informazioni complete sull'avviso</li>
                        <li>Elenco dei moduli con: titolo, ore previste, compenso orario, sede</li>
                        <li>Documenti richiesti per la candidatura</li>
                        <li>Pulsante <strong>"Iscriviti"</strong> per ogni modulo di tuo interesse</li>
                    </ul>

                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Attenzione</strong>: Puoi candidarti solo per bandi la cui data di scadenza non sia ancora stata superata. Dopo la scadenza il pulsante "Iscriviti" non sara piu disponibile.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 5. ISCRIVERSI A UN MODULO -->
        <section id="iscrizione">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-send"></i> 5. Iscriversi a un Modulo
                </div>
                <div class="section-card-body">
                    <p>L'iscrizione a un modulo avviene tramite un wizard guidato in piu passaggi. Per avviare l'iscrizione, clicca <strong>"Iscriviti"</strong> dal dettaglio del bando.</p>

                    <h3>Passaggi del wizard</h3>

                    <div class="step-box">
                        <strong>Passaggio 1 - Dati personali</strong>
                        <p class="mb-0 mt-1">Verifica e conferma i tuoi dati anagrafici. Se necessario, aggiornali prima di procedere. Questi dati saranno allegati alla tua candidatura.</p>
                    </div>

                    <div class="step-box">
                        <strong>Passaggio 2 - Competenze (Tag)</strong>
                        <p class="mb-0 mt-1">Seleziona i tag che descrivono le tue competenze pertinenti al modulo. I tag sono organizzati per categoria (es: area disciplinare, certificazioni, lingue). Seleziona quelli che ritieni rilevanti per la tua candidatura.</p>
                    </div>

                    <div class="step-box">
                        <strong>Passaggio 3 - Documenti</strong>
                        <p class="mb-0 mt-1">Carica i documenti richiesti dal bando (es: curriculum vitae, documento d'identita, diplomi, attestati). Per ogni tipo di documento vedrai se e obbligatorio o facoltativo. I formati accettati sono PDF, DOC, DOCX, JPG, PNG.</p>
                    </div>

                    <div class="step-box">
                        <strong>Passaggio 4 - Riepilogo e Conferma</strong>
                        <p class="mb-0 mt-1">Rivedi tutti i dati inseriti, le competenze selezionate e i documenti caricati. Se tutto e corretto, clicca <strong>"Conferma Iscrizione"</strong> per inviare la candidatura.</p>
                    </div>

                    <div class="alert-manuale bg-green-lt">
                        <i class="ti ti-check text-green"></i>
                        <div><strong>Dopo l'invio</strong>: La tua candidatura verra esaminata dalla Commissione Tecnico Scientifica (CTS). Potrai seguire lo stato dalla sezione "Le Mie Iscrizioni".</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 6. LE TUE ISCRIZIONI -->
        <section id="le-mie-iscrizioni">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-list-check"></i> 6. Le Tue Iscrizioni
                </div>
                <div class="section-card-body">
                    <p>La sezione "Le Mie Iscrizioni" mostra lo storico completo delle tue candidature. Accedi dal menu <strong>"Le Mie Iscrizioni"</strong>.</p>

                    <h3>Informazioni per ogni candidatura</h3>
                    <ul>
                        <li>Avviso e modulo per cui ti sei candidato</li>
                        <li>Data di presentazione della candidatura</li>
                        <li>Stato della candidatura</li>
                        <li>Esito CTS (quando disponibile)</li>
                    </ul>

                    <h3>Esiti possibili</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Esito</th><th>Significato</th></tr></thead>
                        <tbody>
                            <tr><td><span class="badge bg-green text-green-fg">Assegnato</span></td><td>Sei stato selezionato per il modulo. Riceverai informazioni sul contratto.</td></tr>
                            <tr><td><span class="badge bg-azure text-azure-fg">Idoneo</span></td><td>La tua candidatura e stata valutata positivamente ma sei in lista d'attesa. Potresti essere contattato in futuro.</td></tr>
                            <tr><td><span class="badge bg-red text-red-fg">Non idoneo</span></td><td>La tua candidatura non ha superato la valutazione per questo specifico modulo.</td></tr>
                            <tr><td><span class="badge bg-default text-default-fg">In valutazione</span></td><td>La Commissione non ha ancora esaminato la tua candidatura.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 7. LE TUE ASSEGNAZIONI -->
        <section id="assegnazioni">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-circle-check"></i> 7. Le Tue Assegnazioni
                </div>
                <div class="section-card-body">
                    <p>Quando la CTS ti assegna un modulo, questo compare nella sezione "Le Mie Assegnazioni" (raggiungibile anche dal menu <strong>"Le Mie Ore"</strong>).</p>

                    <h3>Cosa vedi per ogni assegnazione</h3>
                    <ul>
                        <li>Nome del modulo e del progetto</li>
                        <li>Sede delle lezioni</li>
                        <li>Ore contrattualizzate (totale previsto)</li>
                        <li>Ore gia rendicontate e stato di approvazione</li>
                        <li>Percentuale di completamento</li>
                    </ul>

                    <h3>Azioni disponibili</h3>
                    <ul>
                        <li><strong>"Inserisci Ore"</strong>: Apre il form per inserire le ore giornaliere lavorate</li>
                        <li><strong>"Le Mie Ore"</strong>: Mostra l'elenco di tutte le ore inserite per questa assegnazione</li>
                    </ul>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 8. INSERIRE LE ORE -->
        <section id="inserire-ore">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-clock"></i> 8. Inserire le Ore
                </div>
                <div class="section-card-body">
                    <p>Per rendicontare le ore lavorate per un modulo assegnato:</p>

                    <ol>
                        <li>Vai su <strong>"Le Mie Ore"</strong> nel menu</li>
                        <li>Seleziona l'assegnazione</li>
                        <li>Clicca <strong>"Inserisci Ore"</strong></li>
                        <li>Compila il form:</li>
                    </ol>
                    <table class="table campo-table">
                        <thead><tr><th>Campo</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Data</strong></td><td>La data in cui hai svolto le ore (seleziona dal calendario)</td></tr>
                            <tr><td><strong>Ore</strong></td><td>Il numero di ore lavorate (es: 4, 2.5)</td></tr>
                            <tr><td><strong>Ora Inizio</strong></td><td>Orario di inizio attivita (es: 09:00)</td></tr>
                            <tr><td><strong>Ora Fine</strong></td><td>Orario di fine attivita (es: 13:00)</td></tr>
                            <tr><td><strong>Note</strong></td><td>Eventuali annotazioni sull'attivita svolta (opzionale)</td></tr>
                            <tr><td><strong>Firma digitale</strong></td><td>Conferma della veridicita dei dati inseriti</td></tr>
                        </tbody>
                    </table>

                    <ol start="5">
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>

                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: Le ore inserite vengono salvate con stato "Da validare". L'amministrazione provvedera ad approvarle o rifiutarle. Puoi modificare o eliminare le ore solo finche lo stato e "Da validare".</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 9. LE TUE ORE -->
        <section id="le-mie-ore">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-clock-check"></i> 9. Le Tue Ore
                </div>
                <div class="section-card-body">
                    <p>La pagina "Le Mie Ore" mostra l'elenco completo delle ore inserite per un'assegnazione specifica.</p>

                    <h3>Informazioni visualizzate</h3>
                    <p>Per ogni registrazione di ore vedi:</p>
                    <ul>
                        <li>Data dell'attivita</li>
                        <li>Ore lavorate</li>
                        <li>Orario inizio/fine</li>
                        <li>Note inserite</li>
                        <li>Stato di validazione</li>
                    </ul>

                    <h3>Stati delle ore</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Stato</th><th>Significato</th><th>Azioni possibili</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-orange text-orange-fg">Da validare</span></td>
                                <td>Ore inserite, in attesa di approvazione dall'amministrazione</td>
                                <td>Puoi <strong>modificare</strong> o <strong>eliminare</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-green text-green-fg">Approvato</span></td>
                                <td>Ore approvate dall'amministrazione</td>
                                <td>Nessuna modifica possibile</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-red text-red-fg">Rifiutato</span></td>
                                <td>Ore rifiutate con motivazione</td>
                                <td>Leggi il motivo del rifiuto e inserisci nuovamente se necessario</td>
                            </tr>
                        </tbody>
                    </table>

                    <h3>Riepilogo</h3>
                    <p>In cima alla pagina trovi un riepilogo con: ore totali contrattualizzate, ore inserite, ore approvate, ore rifiutate e percentuale di completamento.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 10. PAGAMENTI -->
        <section id="pagamenti">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-currency-euro"></i> 10. Pagamenti
                </div>
                <div class="section-card-body">
                    <p>La sezione pagamenti ti consente di monitorare lo stato delle liquidazioni relative ai tuoi contratti.</p>

                    <h3>Informazioni disponibili</h3>
                    <p>Per ogni contratto attivo puoi visualizzare:</p>
                    <ul>
                        <li>Compenso totale previsto dal contratto</li>
                        <li>Importo gia liquidato (pagamenti con stato "pagato")</li>
                        <li>Importo in attesa di liquidazione</li>
                        <li>Saldo residuo</li>
                    </ul>

                    <h3>Dettaglio pagamenti</h3>
                    <p>Per ogni pagamento effettuato vedi: data, importo netto, tipo (saldo/acconto/rata) e stato. I pagamenti passano attraverso gli stati: programmato, in attesa, pagato.</p>

                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: Per eventuali discrepanze sugli importi o per richiedere informazioni sui pagamenti, contatta direttamente la segreteria o l'amministrazione del progetto.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- Footer -->
        <div class="text-center text-secondary py-4" style="border-top: 1px solid var(--tblr-border-color); margin-top: 2rem;">
            <p class="mb-1">Albatros Manager - Manuale Docente</p>
            <p class="mb-0 small">Versione 1.0 - Febbraio 2026</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.manuale-sidebar .nav-link[href^="#"]');
    const sections = document.querySelectorAll('.manuale-content section[id]');

    function updateActiveNav() {
        let current = '';
        sections.forEach(function(section) {
            const sectionTop = section.offsetTop - 100;
            if (window.pageYOffset >= sectionTop) {
                current = section.getAttribute('id');
            }
        });
        navLinks.forEach(function(link) {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', updateActiveNav);
    updateActiveNav();

    navLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            document.getElementById('manuale-nav').classList.remove('show');
        });
    });
});
</script>
