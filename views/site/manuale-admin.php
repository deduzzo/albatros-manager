<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Manuale Amministratore';
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
    border-left-color: var(--tblr-primary);
}
.manuale-sidebar .nav-link.active {
    color: var(--tblr-primary);
    font-weight: 600;
    border-left-color: var(--tblr-primary);
    background: rgba(var(--tblr-primary-rgb), 0.04);
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
    color: var(--tblr-body-color);
}
.manuale-content h4 {
    font-size: 0.875rem;
    font-weight: 600;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}
.manuale-header {
    background: linear-gradient(135deg, #206bc4, #1a56a0);
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
.back-to-top:hover {
    color: var(--tblr-primary);
}
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

/* Print styles */
@media print {
    .manuale-sidebar, .manuale-header .btn, .back-to-top {
        display: none !important;
    }
    .manuale-content {
        padding: 0;
        max-width: 100%;
    }
    .manuale-wrapper {
        display: block;
    }
    .manuale-header {
        background: #206bc4 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    section { page-break-inside: avoid; }
}

/* Mobile responsive */
@media (max-width: 768px) {
    .manuale-sidebar {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        width: 280px;
        z-index: 1050;
        box-shadow: 0 0 30px rgba(0,0,0,0.15);
    }
    .manuale-sidebar.show {
        display: block;
    }
    .manuale-content {
        padding: 1rem;
    }
    .manuale-header {
        padding: 1rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
}
</style>

<!-- Header -->
<div class="manuale-header">
    <div class="d-flex align-items-center gap-3">
        <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-badge-small.png" alt="Albatros" height="40" style="filter: brightness(10);">
        <div>
            <h1><i class="ti ti-book me-2"></i>Manuale Amministratore</h1>
            <div style="opacity: 0.8; font-size: 0.875rem;">Albatros Manager - Gestione Docenti FSE/PNRR</div>
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
    <!-- Sidebar Navigation -->
    <nav class="manuale-sidebar" id="manuale-nav">
        <div class="sidebar-heading">Indice del manuale</div>
        <div class="nav flex-column">
            <a class="nav-link" href="#introduzione"><i class="ti ti-info-circle"></i> Introduzione</a>
            <a class="nav-link" href="#dashboard"><i class="ti ti-home"></i> Dashboard</a>
            <a class="nav-link" href="#configurazione"><i class="ti ti-settings"></i> Configurazione Base</a>
            <a class="nav-link" href="#progetti"><i class="ti ti-folder"></i> Gestione Progetti</a>
            <a class="nav-link" href="#avvisi"><i class="ti ti-file-text"></i> Corsi</a>
            <a class="nav-link" href="#moduli"><i class="ti ti-book"></i> Moduli Formativi</a>
            <a class="nav-link" href="#docenti"><i class="ti ti-users"></i> Gestione Docenti</a>
            <a class="nav-link" href="#selezione"><i class="ti ti-clipboard-check"></i> Processo di Selezione</a>
            <a class="nav-link" href="#contratti"><i class="ti ti-file-text"></i> Contratti</a>
            <a class="nav-link" href="#pagamenti"><i class="ti ti-currency-euro"></i> Pagamenti</a>
            <a class="nav-link" href="#rendicontazione"><i class="ti ti-clock"></i> Rendicontazione Ore</a>
            <a class="nav-link" href="#allievi"><i class="ti ti-school"></i> Gestione Allievi</a>
            <a class="nav-link" href="#report"><i class="ti ti-chart-bar"></i> Report e Strumenti</a>
            <a class="nav-link" href="#import"><i class="ti ti-upload"></i> Import Dati</a>
            <a class="nav-link" href="#area-docenti"><i class="ti ti-door-enter"></i> Area Docenti</a>
            <a class="nav-link" href="#bilancio"><i class="ti ti-report-money"></i> Contabilita e Bilancio</a>
            <a class="nav-link" href="#fatturazione"><i class="ti ti-file-invoice"></i> Fatturazione e Cedolini</a>
            <a class="nav-link" href="#rimborsi"><i class="ti ti-receipt-refund"></i> Rimborsi Spese</a>
            <a class="nav-link" href="#albo-docenti"><i class="ti ti-address-book"></i> Albo Docenti</a>
            <a class="nav-link" href="#borse-studio"><i class="ti ti-school"></i> Borse di Studio</a>
            <a class="nav-link" href="#firma-digitale"><i class="ti ti-certificate"></i> Verifica Firma Digitale</a>
            <a class="nav-link" href="#contratti-pdf"><i class="ti ti-file-type-pdf"></i> Generazione Contratti PDF</a>
            <a class="nav-link" href="#coordinatori-tutor"><i class="ti ti-user-star"></i> Coordinatori e Tutor</a>
            <a class="nav-link" href="#gestione-semplificata"><i class="ti ti-layout-grid"></i> Gestione Semplificata</a>
            <a class="nav-link" href="#candidatura-multimodulo"><i class="ti ti-users-plus"></i> Candidatura Multi-modulo</a>
            <a class="nav-link" href="#metti-a-bando"><i class="ti ti-speakerphone"></i> Metti a Bando</a>
            <a class="nav-link" href="#alert-dashboard"><i class="ti ti-bell-exclamation"></i> Alert Dashboard</a>
            <a class="nav-link" href="#allegati-contratto"><i class="ti ti-paperclip"></i> Allegati Contratto</a>
            <a class="nav-link" href="#borsa-bloccata"><i class="ti ti-lock"></i> Erogazione Bloccata (Borsa)</a>
            <a class="nav-link" href="#export-sepa"><i class="ti ti-file-export"></i> Export SEPA XML</a>
            <a class="nav-link" href="#ridenominazione"><i class="ti ti-tag"></i> Ridenominazione Termini</a>
        </div>

        <div class="sidebar-heading mt-4">Altri manuali</div>
        <div class="nav flex-column">
            <a class="nav-link" href="<?= Url::to(['site/manuale-docente']) ?>"><i class="ti ti-school"></i> Manuale Docente</a>
            <a class="nav-link" href="<?= Url::to(['site/manuale-studente']) ?>"><i class="ti ti-users"></i> Manuale Studente</a>
        </div>
    </nav>

    <!-- Content -->
    <div class="manuale-content">

        <!-- 1. INTRODUZIONE -->
        <section id="introduzione">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-info-circle"></i> 1. Introduzione
                </div>
                <div class="section-card-body">
                    <h3>Cos'e Albatros Manager</h3>
                    <p>Albatros Manager e il sistema informatico per la gestione completa dei docenti e dei corsi finanziati da FSE (Fondo Sociale Europeo), PNRR (Piano Nazionale di Ripresa e Resilienza) e MIM (Ministero dell'Istruzione e del Merito).</p>
                    <p>Il sistema consente di gestire l'intero ciclo di vita di un progetto formativo: dalla configurazione iniziale alla selezione docenti, dalla contrattualizzazione ai pagamenti, fino alla rendicontazione finale.</p>

                    <h3>Panoramica funzionalita</h3>
                    <ul>
                        <li>Gestione progetti formativi multi-fonte (FSE, PNRR, MIM)</li>
                        <li>Pubblicazione bandi e raccolta candidature docenti</li>
                        <li>Processo di selezione con Commissione Tecnico Scientifica (CTS)</li>
                        <li>Contrattualizzazione e gestione pagamenti</li>
                        <li>Rendicontazione ore docenti con workflow di approvazione</li>
                        <li>Gestione allievi, iscrizioni, presenze e calendario</li>
                        <li>Report, statistiche e export per consulente del lavoro</li>
                        <li>Audit log completo di tutte le operazioni</li>
                    </ul>

                    <h3>Come accedere</h3>
                    <p>Accedi al sistema tramite il browser all'indirizzo fornito dall'amministratore di sistema. Nella pagina di login, seleziona la card "Amministratore" e inserisci le tue credenziali.</p>
                    <table class="table campo-table">
                        <tr><th>Campo</th><th>Valore predefinito</th></tr>
                        <tr><td>Username</td><td><code>admin</code></td></tr>
                        <tr><td>Password</td><td><code>admin</code></td></tr>
                    </table>
                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Importante</strong>: Cambia immediatamente la password al primo accesso per garantire la sicurezza del sistema.</div>
                    </div>

                    <h3>Struttura del menu</h3>
                    <p>Dopo il login, la barra di navigazione superiore presenta le seguenti voci:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Voce Menu</th><th>Contenuto</th></tr></thead>
                        <tbody>
                            <tr><td><i class="ti ti-home me-1"></i> Dashboard</td><td>Panoramica con statistiche, grafici e attivita recenti</td></tr>
                            <tr><td><i class="ti ti-settings me-1"></i> Configurazione</td><td>Fonti di finanziamento, Conti correnti, Sedi, Tipi documento</td></tr>
                            <tr><td><i class="ti ti-folder me-1"></i> Progetti</td><td>Progetti, Corsi, Moduli/Insegnamenti</td></tr>
                            <tr><td><i class="ti ti-users me-1"></i> Personale</td><td>Docenti, Allievi, Documentazione, Tag curriculum</td></tr>
                            <tr><td><i class="ti ti-clipboard me-1"></i> Gestione</td><td>Istanze, Assegnazioni, Contratti, Ore, Calendario, Presenze, Valutazioni</td></tr>
                            <tr><td><i class="ti ti-currency-euro me-1"></i> Pagamenti</td><td>Elenco pagamenti e liquidazione massiva</td></tr>
                            <tr><td><i class="ti ti-chart-bar me-1"></i> Report</td><td>Statistiche, Export, Audit Log</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 2. DASHBOARD -->
        <section id="dashboard">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-home"></i> 2. Dashboard
                </div>
                <div class="section-card-body">
                    <p>La Dashboard e la pagina principale del sistema, raggiungibile dal menu <span class="menu-path">Dashboard</span> o cliccando sul logo Albatros.</p>

                    <h3>Card finanziarie</h3>
                    <p>Nella parte superiore trovi 4 card con i dati finanziari aggregati:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Card</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Totale Liquidato</strong></td><td>Somma di tutti i pagamenti con stato "pagato", con barra di avanzamento sul budget totale</td></tr>
                            <tr><td><strong>Da Liquidare</strong></td><td>Pagamenti in stato "in attesa" di approvazione, con numero pratiche</td></tr>
                            <tr><td><strong>Budget Totale</strong></td><td>Somma dei budget di tutti i progetti, con numero progetti attivi</td></tr>
                            <tr><td><strong>In Attesa Rendiconto</strong></td><td>Pagamenti programmati ma non ancora elaborati</td></tr>
                        </tbody>
                    </table>

                    <h3>Contatori</h3>
                    <p>Sotto le card finanziarie, 6 riquadri mostrano i conteggi principali: Progetti attivi, Docenti registrati, Allievi iscritti, Ore erogate, Fonti di finanziamento e Sedi. Ogni riquadro e cliccabile e porta alla relativa sezione di gestione.</p>

                    <h3>Grafici</h3>
                    <ul>
                        <li><strong>Andamento Liquidazioni</strong>: Grafico ad area che mostra l'andamento mensile dei pagamenti effettuati negli ultimi 12 mesi</li>
                        <li><strong>Distribuzione per Fonte</strong>: Grafico a ciambella che mostra la ripartizione del budget tra le diverse fonti di finanziamento (FSE, PNRR, MIM)</li>
                    </ul>

                    <h3>Azioni Rapide</h3>
                    <p>La sezione "Azioni Rapide" offre 4 scorciatoie per le operazioni piu frequenti: Nuova Fonte di finanziamento, Nuova Sede, Nuovo Progetto, Nuovo Docente.</p>

                    <h3>Attivita Recenti</h3>
                    <p>La timeline "Attivita Recenti" mostra le ultime 8 operazioni eseguite nel sistema (creazioni, modifiche, eliminazioni) con indicazione dell'utente, del tipo di record e del tempo trascorso. I dati provengono dal sistema di Audit Log automatico.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 3. CONFIGURAZIONE BASE -->
        <section id="configurazione">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-settings"></i> 3. Configurazione Base
                </div>
                <div class="section-card-body">
                    <p>Prima di creare progetti e bandi, e necessario configurare le entita di base del sistema. Accedi tramite il menu <span class="menu-path">Configurazione</span>.</p>

                    <h3>Fonti di Finanziamento</h3>
                    <p>Le fonti di finanziamento identificano l'origine dei fondi per i progetti. Per creare una nuova fonte:</p>
                    <ol>
                        <li>Vai su <span class="menu-path">Configurazione > Fonti di Finanziamento</span></li>
                        <li>Clicca il pulsante <strong>"Nuova Fonte"</strong> in alto a destra</li>
                        <li>Compila i campi: codice identificativo, descrizione, note eventuali</li>
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>
                    <p>Le fonti tipiche sono: <strong>FSE</strong> (Fondo Sociale Europeo), <strong>PNRR</strong> (Piano Nazionale di Ripresa e Resilienza), <strong>MIM</strong> (Ministero dell'Istruzione e del Merito).</p>

                    <h3>Conti Correnti</h3>
                    <p>Ogni progetto puo essere associato a un conto corrente bancario dedicato. I conti correnti sono utili per la riconciliazione dei pagamenti.</p>
                    <ol>
                        <li>Vai su <span class="menu-path">Configurazione > Conti Correnti</span></li>
                        <li>Clicca <strong>"Nuovo Conto"</strong></li>
                        <li>Inserisci: denominazione, IBAN, banca, filiale, note</li>
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>

                    <h3>Sedi Operative</h3>
                    <p>Le sedi sono i luoghi fisici dove si svolgono le lezioni dei moduli formativi.</p>
                    <ol>
                        <li>Vai su <span class="menu-path">Configurazione > Sedi</span></li>
                        <li>Clicca <strong>"Nuova Sede"</strong></li>
                        <li>Compila: nome, indirizzo, citta, provincia, CAP, telefono, email, note</li>
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>

                    <h3>Tipi Documento</h3>
                    <p>Definiscono le categorie di documenti che possono essere richiesti ai docenti nelle candidature (es: curriculum vitae, diploma, attestato, documento d'identita).</p>
                    <ol>
                        <li>Vai su <span class="menu-path">Configurazione > Tipi Documento</span></li>
                        <li>Clicca <strong>"Nuovo Tipo"</strong></li>
                        <li>Inserisci: denominazione, descrizione, obbligatorio (si/no)</li>
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 4. GESTIONE PROGETTI -->
        <section id="progetti">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-folder"></i> 4. Gestione Progetti
                </div>
                <div class="section-card-body">
                    <p>I Progetti sono il contenitore principale del sistema. Ogni progetto corrisponde a un percorso formativo finanziato.</p>
                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Terminologia</strong>: Un "Progetto" nel sistema corrisponde a un "Corso" dal punto di vista degli studenti. Ad esempio, il progetto "Health Food" finanziato dal PNRR e un corso di formazione nel settore alimentare.</div>
                    </div>

                    <h3>Creare un nuovo progetto</h3>
                    <ol>
                        <li>Vai su <span class="menu-path">Progetti > Progetti</span></li>
                        <li>Clicca <strong>"Nuovo Progetto"</strong></li>
                        <li>Compila i campi obbligatori:</li>
                    </ol>
                    <table class="table campo-table">
                        <thead><tr><th>Campo</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td>Titolo</td><td>Nome completo del progetto (es: "Agrifood Avviso 2 Messina")</td></tr>
                            <tr><td>Codice Progetto</td><td>Codice identificativo ufficiale del progetto</td></tr>
                            <tr><td>Fonte Finanziamento</td><td>Seleziona FSE, PNRR o MIM dal menu a tendina</td></tr>
                            <tr><td>Conto Corrente</td><td>Conto bancario associato (opzionale)</td></tr>
                            <tr><td>Budget Totale</td><td>Importo complessivo del finanziamento in euro</td></tr>
                            <tr><td>Data Inizio / Fine</td><td>Periodo di validita del progetto</td></tr>
                            <tr><td>Numero Studenti</td><td>Posti disponibili per gli allievi (default: 20)</td></tr>
                            <tr><td>Status</td><td>Stato del progetto: pianificato, attivo, concluso</td></tr>
                        </tbody>
                    </table>

                    <h3>Dettaglio progetto</h3>
                    <p>La pagina di dettaglio di un progetto mostra:</p>
                    <ul>
                        <li>Dati anagrafici e configurazione del progetto</li>
                        <li>Elenco degli avvisi/bandi collegati</li>
                        <li>Elenco dei moduli formativi</li>
                        <li>Rendicontazione finanziaria: importo contrattualizzato, pagato e in attesa con barre di avanzamento</li>
                        <li>Dettaglio per modulo con docente, compenso, percentuale di erogazione</li>
                    </ul>

                    <h3>Progetti biennali</h3>
                    <p>Per i progetti che si estendono su piu annualita, il sistema supporta la gestione di consuntivo (primo anno) e preventivo (secondo anno) attraverso i campi budget suddivisi per annualita.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 5. CORSI -->
        <section id="avvisi">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-file-text"></i> 5. Corsi
                </div>
                <div class="section-card-body">
                    <p>I Corsi sono le procedure di selezione pubblica per il reclutamento dei docenti. Ogni corso e collegato a un progetto e contiene uno o piu moduli.</p>

                    <h3>Creare un corso</h3>
                    <ol>
                        <li>Vai su <span class="menu-path">Progetti > Corsi</span></li>
                        <li>Clicca <strong>"Nuovo Corso"</strong></li>
                        <li>Seleziona il progetto di riferimento</li>
                        <li>Compila: titolo, numero protocollo, data pubblicazione, data scadenza, descrizione</li>
                        <li>Imposta lo stato iniziale (generalmente "bozza")</li>
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>

                    <h3>Stati del corso</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Stato</th><th>Significato</th></tr></thead>
                        <tbody>
                            <tr><td><span class="badge bg-default text-default-fg">Bozza</span></td><td>Corso in preparazione, non visibile ai docenti</td></tr>
                            <tr><td><span class="badge bg-green text-green-fg">Pubblicato</span></td><td>Corso visibile nell'area docenti, candidature aperte</td></tr>
                            <tr><td><span class="badge bg-red text-red-fg">Chiuso</span></td><td>Scadenza superata, candidature chiuse</td></tr>
                        </tbody>
                    </table>

                    <h3>Documenti richiesti</h3>
                    <p>Per ogni corso puoi configurare quali documenti sono necessari per la candidatura. Nella pagina di dettaglio del corso, nella sezione "Documenti Richiesti", puoi aggiungere i tipi di documento necessari (es: CV, Diploma, Documento d'identita). I docenti dovranno caricare tali documenti al momento dell'iscrizione.</p>

                    <h3>Collegamento gerarchico</h3>
                    <p>La gerarchia e: <strong>Progetto > Corso > Moduli</strong>. Un progetto puo avere piu corsi (ad esempio corsi distinti per diverse figure professionali), e ogni corso contiene i moduli per cui si ricercano docenti.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 6. MODULI FORMATIVI -->
        <section id="moduli">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-book"></i> 6. Moduli Formativi
                </div>
                <div class="section-card-body">
                    <p>I Moduli (o Materie/Insegnamenti) sono le singole discipline all'interno di un corso. Ad esempio, in un progetto "Health Food" i moduli possono essere "Chimica degli Alimenti", "Sicurezza Alimentare", "Marketing Agroalimentare", ecc.</p>

                    <h3>Creare un modulo</h3>
                    <ol>
                        <li>Vai su <span class="menu-path">Progetti > Moduli/Insegnamenti</span></li>
                        <li>Clicca <strong>"Nuovo Modulo"</strong></li>
                        <li>Seleziona l'avviso di appartenenza</li>
                        <li>Compila i campi:</li>
                    </ol>
                    <table class="table campo-table">
                        <thead><tr><th>Campo</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td>Titolo</td><td>Nome del modulo/materia</td></tr>
                            <tr><td>Codice</td><td>Codice identificativo (es: MOD-001)</td></tr>
                            <tr><td>Ore Totali</td><td>Numero di ore previste per il modulo</td></tr>
                            <tr><td>Compenso Orario</td><td>Importo orario in euro (variabile per provenienza docente)</td></tr>
                            <tr><td>Sede</td><td>Sede dove si svolgeranno le lezioni</td></tr>
                            <tr><td>Filiera</td><td>Filiera formativa di appartenenza (opzionale)</td></tr>
                        </tbody>
                    </table>

                    <h3>Compenso orario</h3>
                    <p>Il compenso orario puo variare in base alla provenienza del docente e al tipo di finanziamento:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Provenienza</th><th>Significato</th><th>Compenso PNRR</th></tr></thead>
                        <tbody>
                            <tr><td><strong>TP</strong></td><td>Tipo Professionista (dal mondo del lavoro)</td><td>60,00 EUR/ora</td></tr>
                            <tr><td><strong>RU</strong></td><td>Ricercatore Universitario</td><td>70,00 EUR/ora</td></tr>
                        </tbody>
                    </table>
                    <p>Per progetti FSE e MIM, il compenso puo variare in base alla configurazione specifica.</p>

                    <h3>Rendicontazione modulo</h3>
                    <p>Nella pagina di dettaglio di ogni modulo, la sezione rendicontazione mostra: compenso contrattualizzato, totale pagato, percentuale di completamento e dettaglio dei pagamenti effettuati.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 7. GESTIONE DOCENTI -->
        <section id="docenti">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-users"></i> 7. Gestione Docenti
                </div>
                <div class="section-card-body">
                    <p>L'anagrafica docenti e il registro centrale di tutti i professionisti e ricercatori che collaborano o hanno collaborato con i progetti formativi. Accedi da <span class="menu-path">Personale > Docenti</span>.</p>

                    <h3>Anagrafica</h3>
                    <p>Ogni docente e identificato univocamente dal <strong>codice fiscale</strong> (chiave primaria). I campi principali sono:</p>
                    <ul>
                        <li>Dati anagrafici: cognome, nome, data e luogo di nascita</li>
                        <li>Contatti: email, telefono, indirizzo completo</li>
                        <li>Dati professionali: titolo di studio, provenienza (TP/RU)</li>
                        <li>Note aggiuntive</li>
                    </ul>

                    <h3>Provenienza: TP vs RU</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Codice</th><th>Significato</th><th>Caratteristiche</th></tr></thead>
                        <tbody>
                            <tr><td><strong>TP</strong></td><td>Tipo Professionista</td><td>Proviene dal mondo del lavoro, compenso PNRR: 60 EUR/ora</td></tr>
                            <tr><td><strong>RU</strong></td><td>Ricercatore Universitario</td><td>Proviene dall'universita, compenso PNRR: 70 EUR/ora</td></tr>
                        </tbody>
                    </table>

                    <h3>Tag e Competenze</h3>
                    <p>Il sistema di tag consente di classificare le competenze dei docenti. I tag sono organizzati in categorie (es: "Area disciplinare", "Certificazioni", "Lingue") e possono essere associati sia al profilo docente che alle singole candidature.</p>
                    <p>Per gestire le categorie e i tag: <span class="menu-path">Personale > Categorie Tag</span> e <span class="menu-path">Personale > Gestione Tag</span>.</p>

                    <h3>Alert sovra-impegnato</h3>
                    <p>Il sistema mostra un avviso visuale quando un docente ha un numero elevato di moduli/contratti attivi contemporaneamente. Nella lista docenti, la colonna "Moduli Attivi" consente di individuare rapidamente situazioni di sovraccarico. Nella scheda del docente, un badge rosso "Sovra-impegnato" appare se il docente supera la soglia configurata.</p>

                    <h3>Long list</h3>
                    <p>I docenti con esito CTS "idoneo" (ma non assegnati) entrano automaticamente nella long list, ovvero l'elenco di professionisti disponibili per future assegnazioni. La lista docenti offre filtri avanzati per competenze, valutazione media e numero di contratti pregressi, utili per identificare rapidamente il candidato piu adatto.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 8. PROCESSO DI SELEZIONE -->
        <section id="selezione">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-clipboard-check"></i> 8. Processo di Selezione
                </div>
                <div class="section-card-body">
                    <p>Il processo di selezione segue un workflow definito che coinvolge candidature (istanze), valutazione CTS e assegnazioni.</p>

                    <h3>Workflow CTS</h3>
                    <ol>
                        <li><strong>Candidatura</strong>: Il docente presenta un'istanza per uno o piu moduli di un avviso, caricando i documenti richiesti e dichiarando le proprie competenze tramite tag</li>
                        <li><strong>Valutazione</strong>: La Commissione Tecnico Scientifica (CTS) esamina le candidature, valuta curriculum e titoli</li>
                        <li><strong>Esito CTS</strong>: Per ogni candidatura, la CTS delibera uno dei seguenti esiti:</li>
                    </ol>
                    <table class="table campo-table">
                        <thead><tr><th>Esito</th><th>Significato</th><th>Azione sistema</th></tr></thead>
                        <tbody>
                            <tr><td><span class="badge bg-green text-green-fg">Assegnato</span></td><td>Docente selezionato per l'incarico</td><td>Genera Assegnazione e Contratto</td></tr>
                            <tr><td><span class="badge bg-azure text-azure-fg">Idoneo</span></td><td>Valutato positivamente ma non assegnato</td><td>Entra nella long list</td></tr>
                            <tr><td><span class="badge bg-red text-red-fg">Non idoneo</span></td><td>Valutazione negativa</td><td>Tracciato nello storico</td></tr>
                        </tbody>
                    </table>

                    <h3>Gestire le istanze</h3>
                    <p>Vai su <span class="menu-path">Gestione > Istanze</span> per visualizzare tutte le candidature. Puoi filtrare per avviso, modulo, docente e stato. Per ogni istanza puoi:</p>
                    <ul>
                        <li>Visualizzare i documenti caricati dal docente</li>
                        <li>Consultare i tag/competenze dichiarate</li>
                        <li>Impostare l'esito CTS</li>
                        <li>Consultare lo storico degli esiti precedenti</li>
                    </ul>

                    <h3>Snapshot dati</h3>
                    <p>Al momento dell'assegnazione, il sistema crea automaticamente uno snapshot dei dati del docente (dati anagrafici, titoli, competenze). Questo consente di conservare la "fotografia" del profilo al momento della selezione, anche se il docente aggiornera successivamente i propri dati.</p>

                    <h3>Promozione tag</h3>
                    <p>I tag dichiarati in una candidatura possono essere "promossi" al profilo permanente del docente. Nella scheda dell'istanza, il pulsante "Promuovi Tag" aggiunge i tag della candidatura al profilo, rendendoli disponibili per future ricerche.</p>

                    <h3>Assegnazioni</h3>
                    <p>Le assegnazioni sono il risultato formale della selezione. Vai su <span class="menu-path">Gestione > Assegnazioni</span> per gestirle. Ogni assegnazione collega un docente a un modulo specifico e genera la base per il contratto.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 9. CONTRATTI -->
        <section id="contratti">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-file-text"></i> 9. Contratti
                </div>
                <div class="section-card-body">
                    <p>I Contratti formalizzano il rapporto tra il progetto e il docente assegnato. Accedi da <span class="menu-path">Gestione > Contratti</span>.</p>

                    <h3>Tipi di contratto</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Tipo</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>CoCoCo</strong></td><td>Collaborazione Coordinata e Continuativa</td></tr>
                            <tr><td><strong>Prestazione Professionale</strong></td><td>Incarico a titolari di Partita IVA</td></tr>
                        </tbody>
                    </table>

                    <h3>Campi del contratto</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Campo</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td>Data Inizio / Fine (contrattuale)</td><td>Periodo di validita giuridica del contratto</td></tr>
                            <tr><td>Data Inizio / Fine Lezioni</td><td>Periodo effettivo delle lezioni (puo differire dalle date contrattuali)</td></tr>
                            <tr><td>Ore Contrattualizzate</td><td>Numero di ore previste nel contratto</td></tr>
                            <tr><td>Compenso Orario</td><td>Importo orario (precompilato in base a provenienza TP/RU)</td></tr>
                            <tr><td>Compenso Totale</td><td>Calcolato automaticamente: ore x compenso orario</td></tr>
                        </tbody>
                    </table>
                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: Le date delle lezioni sono distinte dalle date contrattuali. Un contratto puo avere validita annuale, ma le lezioni si svolgono solo in un periodo specifico. Questa distinzione e importante per alert di scadenza e per la riassegnazione docenti.</div>
                    </div>

                    <h3>Compenso suggerito</h3>
                    <p>Nel form di creazione del contratto, il sistema mostra un suggerimento sul compenso orario in base alla provenienza del docente (TP/RU) e al tipo di finanziamento del progetto. Puoi accettare il suggerimento o inserire un importo personalizzato.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 10. PAGAMENTI -->
        <section id="pagamenti">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-currency-euro"></i> 10. Pagamenti
                </div>
                <div class="section-card-body">
                    <p>La sezione Pagamenti gestisce tutte le liquidazioni ai docenti. Accedi da <span class="menu-path">Pagamenti</span> nel menu principale.</p>

                    <h3>Creare un pagamento singolo</h3>
                    <ol>
                        <li>Vai su <span class="menu-path">Pagamenti</span></li>
                        <li>Clicca <strong>"Nuovo Pagamento"</strong></li>
                        <li>Seleziona il contratto di riferimento</li>
                        <li>Compila: importo netto, ritenute (IRPEF, INPS, ecc.), data pagamento</li>
                        <li>Imposta il tipo: saldo, acconto o rata</li>
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>

                    <h3>Stati del pagamento</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Stato</th><th>Significato</th></tr></thead>
                        <tbody>
                            <tr><td><span class="badge bg-default text-default-fg">Programmato</span></td><td>Pagamento pianificato ma non ancora elaborato</td></tr>
                            <tr><td><span class="badge bg-orange text-orange-fg">In attesa</span></td><td>In fase di elaborazione/approvazione</td></tr>
                            <tr><td><span class="badge bg-green text-green-fg">Pagato</span></td><td>Pagamento effettuato con successo</td></tr>
                        </tbody>
                    </table>

                    <h3>Liquidazione Massiva</h3>
                    <p>La funzione di liquidazione massiva permette di creare pagamenti in blocco per tutti i contratti con saldo residuo positivo:</p>
                    <ol>
                        <li>Nella pagina Pagamenti, clicca <strong>"Liquidazione Massiva"</strong> nella toolbar</li>
                        <li>Si apre un pannello con l'elenco dei contratti che hanno ancora un saldo da liquidare</li>
                        <li>Seleziona i contratti da liquidare usando le checkbox (o "Seleziona tutti")</li>
                        <li>Verifica il riepilogo: numero contratti selezionati e totale saldo</li>
                        <li>Scegli il tipo di pagamento (saldo/acconto/rata)</li>
                        <li>Clicca <strong>"Liquida Selezionati"</strong></li>
                    </ol>
                    <p>Il sistema crea automaticamente un pagamento per ogni contratto selezionato, calcolando le ritenute in modo proporzionale.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 11. RENDICONTAZIONE ORE -->
        <section id="rendicontazione">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-clock"></i> 11. Rendicontazione Ore
                </div>
                <div class="section-card-body">
                    <p>La rendicontazione ore gestisce il tracciamento delle ore erogate dai docenti, con un workflow di approvazione. Accedi da <span class="menu-path">Gestione > Rendicontazione Ore</span>.</p>

                    <h3>Dashboard Ore</h3>
                    <p>La dashboard mostra una panoramica di tutte le assegnazioni con: ore contrattualizzate, ore rendicontate, ore approvate e percentuale di completamento per ciascuna.</p>

                    <h3>Workflow di validazione</h3>
                    <ol>
                        <li>Il docente inserisce le ore giornaliere dall'area riservata (data, ore lavorate, ora inizio/fine, note, firma digitale)</li>
                        <li>Le ore vengono salvate con stato <span class="badge bg-orange text-orange-fg">Inserito</span> (da validare)</li>
                        <li>L'amministratore accede alla lista ore per assegnazione</li>
                        <li>Per ogni registrazione puo:</li>
                    </ol>
                    <table class="table campo-table">
                        <thead><tr><th>Azione</th><th>Risultato</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Approva</strong></td><td>Le ore passano a stato <span class="badge bg-green text-green-fg">Approvato</span></td></tr>
                            <tr><td><strong>Rifiuta</strong></td><td>Le ore passano a stato <span class="badge bg-red text-red-fg">Rifiutato</span> con motivazione obbligatoria</td></tr>
                            <tr><td><strong>Approva Tutto</strong></td><td>Approva in blocco tutte le ore "da validare" per l'assegnazione</td></tr>
                        </tbody>
                    </table>

                    <h3>Ore extra</h3>
                    <p>Se le ore approvate superano le ore contrattualizzate, il sistema attiva il workflow di approvazione ore extra. Le ore in eccedenza devono essere esplicitamente autorizzate e vengono tracciate nel relativo pagamento.</p>

                    <h3>Riepilogo mensile</h3>
                    <p>Il sistema genera automaticamente un riepilogo mensile delle ore per assegnazione, utile per la rendicontazione periodica e per il controllo di gestione.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 12. GESTIONE ALLIEVI -->
        <section id="allievi">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-school"></i> 12. Gestione Allievi
                </div>
                <div class="section-card-body">
                    <p>La sezione allievi gestisce gli studenti iscritti ai corsi formativi.</p>

                    <h3>Anagrafica allievi</h3>
                    <p>Come per i docenti, ogni allievo e identificato dal <strong>codice fiscale</strong>. Vai su <span class="menu-path">Personale > Allievi</span> per gestire l'anagrafica. I campi includono: cognome, nome, codice fiscale, data di nascita, contatti, indirizzo.</p>

                    <h3>Iscrizioni</h3>
                    <p>Le iscrizioni collegano gli allievi ai moduli formativi. Vai su <span class="menu-path">Gestione > Iscrizioni</span> per creare o gestire le iscrizioni. Seleziona il modulo e l'allievo, imposta la data di iscrizione e lo stato.</p>

                    <h3>Calendario lezioni</h3>
                    <p>Il calendario registra le singole lezioni programmate per ogni modulo. Vai su <span class="menu-path">Gestione > Calendario</span> per inserire: data, ora inizio/fine, modulo, docente, sede, note.</p>

                    <h3>Presenze allievi</h3>
                    <p>Per ogni lezione del calendario puoi registrare le presenze. Vai su <span class="menu-path">Gestione > Presenze</span>. Il sistema mostra l'elenco degli allievi iscritti al modulo e consente di segnare presente/assente per ciascuno.</p>

                    <h3>Valutazione docenti</h3>
                    <p>Il sistema supporta la valutazione anonima dei docenti da parte degli studenti. Il workflow prevede:</p>
                    <ol>
                        <li>L'amministratore genera codici di accesso univoci per un modulo (batch)</li>
                        <li>I codici vengono stampati e distribuiti agli studenti</li>
                        <li>Ogni studente accede alla pagina di valutazione pubblica (senza login) inserendo il codice</li>
                        <li>Compila il questionario con punteggi da 1 a 5 e un commento libero</li>
                        <li>Dopo l'invio, il codice viene invalidato (uso singolo)</li>
                    </ol>
                    <p>Per generare i codici: nella scheda del modulo, clicca "Genera Codici Valutazione" e specifica la quantita desiderata.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 13. REPORT E STRUMENTI -->
        <section id="report">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-chart-bar"></i> 13. Report e Strumenti
                </div>
                <div class="section-card-body">
                    <p>La sezione Report offre strumenti di analisi, statistiche e funzionalita di export. Accedi da <span class="menu-path">Report</span> nel menu principale.</p>

                    <h3>Statistiche</h3>
                    <p>La pagina <span class="menu-path">Report > Statistiche</span> presenta grafici interattivi (ApexCharts) su:</p>
                    <ul>
                        <li>Distribuzione budget per fonte di finanziamento</li>
                        <li>Andamento temporale delle liquidazioni</li>
                        <li>Numero docenti per provenienza (TP/RU)</li>
                        <li>Stato dei progetti (attivi, conclusi, pianificati)</li>
                        <li>Ore erogate vs ore pianificate per progetto</li>
                    </ul>

                    <h3>Export dati</h3>
                    <p>La pagina <span class="menu-path">Report > Export</span> consente di scaricare i dati in formato CSV o Excel per le principali entita del sistema (progetti, docenti, contratti, pagamenti, ecc.).</p>

                    <h3>Export per consulente del lavoro</h3>
                    <p>Un export specializzato genera un file Excel con i dati necessari al consulente del lavoro per predisporre i contratti CoCoCo. I campi includono: dati anagrafici completi del docente, progetto, modulo, sede, ore, compenso, date lezioni.</p>
                    <p>Per generare l'export: nella scheda del progetto, clicca <strong>"Esporta per Consulente"</strong>.</p>

                    <h3>Audit Log</h3>
                    <p>Il sistema registra automaticamente ogni operazione (creazione, modifica, eliminazione) in un log dettagliato. Vai su <span class="menu-path">Report > Audit Log</span> per consultare la cronologia completa. Per ogni registrazione puoi vedere:</p>
                    <ul>
                        <li>Tipo di operazione (INSERT, UPDATE, SOFT_DELETE, RESTORE)</li>
                        <li>Tabella e ID del record modificato</li>
                        <li>Utente che ha eseguito l'operazione</li>
                        <li>Data e ora</li>
                        <li>Valori prima e dopo la modifica (per gli UPDATE)</li>
                        <li>Campi specifici modificati</li>
                    </ul>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 14. IMPORT DATI -->
        <section id="import">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-upload"></i> 14. Import Dati
                </div>
                <div class="section-card-body">
                    <p>Il sistema di importazione consente di caricare massivamente progetti, moduli e docenti da file Excel standardizzato.</p>

                    <h3>Template Excel</h3>
                    <p>Il template e composto da 4 fogli:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Foglio</th><th>Contenuto</th></tr></thead>
                        <tbody>
                            <tr><td><strong>ISTRUZIONI</strong></td><td>Guida completa alla compilazione</td></tr>
                            <tr><td><strong>INFO PROGETTO</strong></td><td>Dati del progetto, avviso e sede</td></tr>
                            <tr><td><strong>MODULI</strong></td><td>Elenco moduli formativi con codice, titolo, ore, compenso, filiera</td></tr>
                            <tr><td><strong>DOCENTI & ESITI</strong></td><td>Assegnazioni, idonei, non idonei, esiti CTS</td></tr>
                        </tbody>
                    </table>

                    <h3>Procedura di import</h3>
                    <ol>
                        <li>Scarica il template dalla pagina Import (oppure usa l'URL diretto)</li>
                        <li>Compila il template seguendo le istruzioni nel primo foglio</li>
                        <li>Accedi alla pagina di Import</li>
                        <li>Carica il file Excel compilato</li>
                        <li>Il sistema effettua una <strong>validazione preliminare</strong> (dry-run): controlla campi obbligatori, formati date, provenienza docente, riferimenti moduli</li>
                        <li>Se la validazione e superata, conferma l'importazione</li>
                        <li>Il sistema crea in un'unica transazione: fonte, sede, progetto, avviso, moduli, docenti, istanze, assegnazioni, contratti</li>
                    </ol>

                    <h3>Casi speciali</h3>
                    <ul>
                        <li><strong>Rinunce</strong>: Aggiungi "(Rinuncia)" dopo il nome dell'assegnatario. Il sistema crea l'istanza ma non l'assegnazione/contratto</li>
                        <li><strong>Compensi variabili</strong>: Specifica un importo orario personalizzato per sovrascrivere il default TP/RU</li>
                        <li><strong>Moduli senza assegnatario</strong>: Lascia vuoto il campo assegnatario; verranno create solo le istanze per gli idonei</li>
                        <li><strong>Piu docenti per modulo</strong>: Ripeti il codice modulo su piu righe con docenti diversi</li>
                    </ul>

                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Attenzione</strong>: L'import crea record in modo transazionale. Se uno qualsiasi dei record non supera la validazione, l'intera operazione viene annullata. Correggi gli errori segnalati e riprova.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 15. AREA DOCENTI -->
        <section id="area-docenti">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-door-enter"></i> 15. Area Docenti
                </div>
                <div class="section-card-body">
                    <p>L'Area Docenti e un portale self-service separato dall'area amministrativa, dedicato ai docenti per gestire candidature, ore e profilo personale.</p>

                    <h3>Accesso</h3>
                    <p>I docenti accedono dall'area di login selezionando la card "Docente" e inserendo il proprio codice fiscale e la password. La registrazione e disponibile per i nuovi docenti.</p>

                    <h3>Funzionalita per i docenti</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Sezione</th><th>Funzionalita</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Dashboard</strong></td><td>Statistiche personali: iscrizioni, bandi aperti, assegnazioni attive, ore da validare</td></tr>
                            <tr><td><strong>Corsi Aperti</strong></td><td>Lista dei corsi pubblicati con moduli disponibili</td></tr>
                            <tr><td><strong>Iscrizione Modulo</strong></td><td>Wizard guidato: dati, tag competenze, documenti, riepilogo</td></tr>
                            <tr><td><strong>Le Mie Iscrizioni</strong></td><td>Storico delle candidature con stato aggiornato</td></tr>
                            <tr><td><strong>Le Mie Assegnazioni</strong></td><td>Moduli assegnati con dettaglio ore contrattualizzate</td></tr>
                            <tr><td><strong>Inserisci Ore</strong></td><td>Form per inserimento ore giornaliere con firma digitale</td></tr>
                            <tr><td><strong>Le Mie Ore</strong></td><td>Elenco ore inserite con stato di validazione</td></tr>
                            <tr><td><strong>Profilo</strong></td><td>Modifica dati anagrafici personali</td></tr>
                        </tbody>
                    </table>

                    <h3>Autenticazione separata</h3>
                    <p>L'area docenti utilizza un sistema di autenticazione completamente separato dagli utenti amministratori. Questo significa che un docente non puo accedere all'area amministrativa e viceversa. Le sessioni sono indipendenti.</p>

                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Per approfondire</strong>: Consulta il <a href="<?= Url::to(['site/manuale-docente']) ?>">Manuale Docente</a> per le istruzioni dettagliate destinate ai docenti.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 16. CONTABILITA E BILANCIO -->
        <section id="bilancio">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-report-money"></i> 16. Contabilita e Bilancio
                </div>
                <div class="section-card-body">
                    <p>Il modulo di contabilita consente la gestione completa del bilancio dei progetti, dalle voci di spesa alle tranche di finanziamento, fino al quadro economico in formato Regione.</p>

                    <h3>Dashboard Bilancio</h3>
                    <p>Accedi alla dashboard bilancio dal menu <span class="menu-path">Contabilita > Bilancio</span> oppure direttamente all'indirizzo <code>/bilancio</code>.</p>
                    <p>La dashboard mostra una panoramica finanziaria con:</p>
                    <ul>
                        <li>Totale finanziamenti ricevuti (tranche accreditate)</li>
                        <li>Totale spese registrate (movimenti di spesa)</li>
                        <li>Saldo disponibile con indicatore visivo</li>
                        <li>Grafici di ripartizione per categoria di spesa</li>
                        <li>Andamento temporale entrate/uscite</li>
                    </ul>

                    <h3>Voci di Spesa</h3>
                    <p>Accedi da <span class="menu-path">Contabilita > Voci di Spesa</span> o da <code>/voce-spesa</code>.</p>
                    <p>Le voci di spesa definiscono le categorie in cui si articola il bilancio del progetto. Il sistema supporta una struttura gerarchica:</p>
                    <ul>
                        <li><strong>Categorie principali</strong>: macro-aree di spesa (es. Personale, Attrezzature, Gestione)</li>
                        <li><strong>Sotto-voci</strong>: dettaglio all'interno di ogni categoria (es. Docenti, Tutor, Coordinamento)</li>
                    </ul>
                    <p>Per ogni voce puoi configurare:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Campo</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Codice</strong></td><td>Codice identificativo della voce (es. A1, B2.1)</td></tr>
                            <tr><td><strong>Descrizione</strong></td><td>Nome della voce di spesa</td></tr>
                            <tr><td><strong>Categoria padre</strong></td><td>La macro-categoria di appartenenza (vuoto se e una categoria principale)</td></tr>
                            <tr><td><strong>Importo previsto</strong></td><td>Budget preventivato per questa voce</td></tr>
                            <tr><td><strong>Progetto</strong></td><td>Progetto di riferimento</td></tr>
                        </tbody>
                    </table>

                    <h3>Movimenti di Spesa</h3>
                    <p>Accedi da <span class="menu-path">Contabilita > Movimenti Spesa</span> o da <code>/movimento-spesa</code>.</p>
                    <p>I movimenti registrano le singole spese sostenute. Possono essere:</p>
                    <ul>
                        <li><strong>Manuali</strong>: inseriti direttamente dall'amministratore per spese generali, forniture, servizi</li>
                        <li><strong>Automatici</strong>: generati dal sistema quando viene registrato un pagamento docente o un'erogazione borsa di studio</li>
                    </ul>
                    <p>Per inserire un nuovo movimento manuale:</p>
                    <ol>
                        <li>Clicca <strong>"Nuovo Movimento"</strong></li>
                        <li>Seleziona il progetto e la voce di spesa</li>
                        <li>Inserisci data, importo, descrizione e numero documento</li>
                        <li>Opzionalmente allega il giustificativo (PDF/immagine)</li>
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>

                    <h3>Tranche di Finanziamento</h3>
                    <p>Accedi da <span class="menu-path">Contabilita > Tranche Finanziamento</span> o da <code>/tranche-finanziamento</code>.</p>
                    <p>Le tranche rappresentano gli accrediti ricevuti dall'ente finanziatore. Per ogni tranche registra:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Campo</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Progetto</strong></td><td>Progetto finanziato</td></tr>
                            <tr><td><strong>Numero tranche</strong></td><td>Progressivo (1a, 2a, 3a, ecc.)</td></tr>
                            <tr><td><strong>Importo</strong></td><td>Importo accreditato</td></tr>
                            <tr><td><strong>Data accredito</strong></td><td>Data di ricezione del finanziamento</td></tr>
                            <tr><td><strong>Note</strong></td><td>Riferimento del mandato o estremi bonifico</td></tr>
                        </tbody>
                    </table>

                    <h3>Quadro Economico</h3>
                    <p>Accedi da <span class="menu-path">Contabilita > Quadro Economico</span> o da <code>/bilancio/quadro-economico</code>.</p>
                    <p>Il quadro economico presenta il riepilogo finanziario del progetto nel formato richiesto dalla Regione, con:</p>
                    <ul>
                        <li>Voci di spesa con importo previsto, impegnato e liquidato</li>
                        <li>Percentuali di utilizzo per voce</li>
                        <li>Totali per macro-categoria</li>
                        <li>Saldo complessivo e disponibilita residua</li>
                    </ul>

                    <h3>Export Excel</h3>
                    <p>Tutte le sezioni contabili supportano l'export in formato Excel. Clicca il pulsante <strong><i class="ti ti-file-spreadsheet"></i> Export Excel</strong> presente nelle pagine di elenco per scaricare i dati filtrati in formato XLSX.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 17. FATTURAZIONE E CEDOLINI -->
        <section id="fatturazione">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-file-invoice"></i> 17. Fatturazione e Cedolini
                </div>
                <div class="section-card-body">
                    <p>Questa sezione gestisce la richiesta e la raccolta dei documenti fiscali (fatture e cedolini) necessari per la liquidazione dei compensi docenti.</p>

                    <h3>Richiesta Fatturazione (Docenti con P.IVA)</h3>
                    <p>Accedi da <span class="menu-path">Gestione > Richieste Fatturazione</span> o da <code>/richiesta-fatturazione</code>.</p>
                    <p>Per i docenti con tipologia <strong>TP</strong> (Professionista), il compenso viene liquidato tramite fattura. Il workflow e il seguente:</p>
                    <ol>
                        <li>L'amministratore crea una richiesta di fatturazione selezionando il contratto e l'importo da fatturare</li>
                        <li>Il sistema invia automaticamente un'email al docente con i dati per l'emissione della fattura (importo, CIG, CUP, dati intestatario)</li>
                        <li>Il docente emette la fattura e la invia</li>
                        <li>L'amministratore carica il documento ricevuto nel sistema (upload PDF)</li>
                        <li>La richiesta passa dallo stato <span class="badge bg-orange text-orange-fg">in attesa</span> a <span class="badge bg-green text-green-fg">ricevuta</span></li>
                    </ol>

                    <h3>Richiesta Cedolini (Docenti CoCoCo)</h3>
                    <p>Per i docenti con tipologia <strong>RU</strong> (Ricercatore/CoCoCo), il compenso viene liquidato tramite cedolino paga elaborato dal consulente del lavoro. Il workflow e analogo:</p>
                    <ol>
                        <li>Crea la richiesta di cedolino selezionando il contratto</li>
                        <li>Il sistema invia email al consulente del lavoro con i dati necessari per l'elaborazione</li>
                        <li>Il consulente elabora il cedolino e lo invia</li>
                        <li>L'amministratore carica il cedolino nel sistema</li>
                    </ol>

                    <h3>Invio Email</h3>
                    <p>Per ogni richiesta puoi inviare (o reinviare) l'email al destinatario cliccando il pulsante <strong><i class="ti ti-mail"></i> Invia Email</strong>. L'email contiene:</p>
                    <ul>
                        <li>Dati del contratto e del progetto</li>
                        <li>Importo da fatturare/cedolinare</li>
                        <li>Riferimenti CIG, CUP e codici progetto</li>
                        <li>Dati dell'ente per l'intestazione</li>
                    </ul>

                    <h3>Upload Documento Ricevuto</h3>
                    <p>Quando ricevi la fattura o il cedolino dal docente/consulente:</p>
                    <ol>
                        <li>Apri il dettaglio della richiesta</li>
                        <li>Clicca <strong>"Carica Documento"</strong></li>
                        <li>Seleziona il file PDF ricevuto</li>
                        <li>Il sistema aggiorna automaticamente lo stato a "Ricevuta"</li>
                    </ol>

                    <h3>Invio Massivo</h3>
                    <p>Per inviare piu richieste contemporaneamente:</p>
                    <ol>
                        <li>Dall'elenco delle richieste, seleziona le righe desiderate tramite le checkbox</li>
                        <li>Clicca il pulsante <strong><i class="ti ti-mail-forward"></i> Invio Massivo</strong></li>
                        <li>Conferma l'operazione: il sistema inviera un'email per ogni richiesta selezionata</li>
                    </ol>
                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: I documenti caricati (fatture e cedolini) sono visibili anche dal docente nella propria area personale, nella sezione "Documenti".</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 18. RIMBORSI SPESE -->
        <section id="rimborsi">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-receipt-refund"></i> 18. Rimborsi Spese
                </div>
                <div class="section-card-body">
                    <p>Il modulo rimborsi consente ai docenti di richiedere il rimborso di spese sostenute (viaggi, materiali, ecc.) e all'amministratore di approvarle o rifiutarle.</p>

                    <h3>Panoramica</h3>
                    <p>Accedi da <span class="menu-path">Gestione > Rimborsi Spese</span> o da <code>/rimborso-spesa</code>.</p>
                    <p>L'elenco mostra tutti i rimborsi con filtri per stato, docente e progetto.</p>

                    <h3>Workflow del rimborso</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Stato</th><th>Descrizione</th><th>Azione</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-orange text-orange-fg">Richiesto</span></td>
                                <td>Il docente ha presentato la richiesta di rimborso dall'area personale</td>
                                <td>L'admin deve valutare</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-green text-green-fg">Approvato</span></td>
                                <td>Il rimborso e stato approvato e sara liquidato</td>
                                <td>Procedi con il pagamento</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-red text-red-fg">Rifiutato</span></td>
                                <td>Il rimborso e stato rifiutato con motivazione</td>
                                <td>Il docente viene notificato</td>
                            </tr>
                        </tbody>
                    </table>

                    <h3>Approvare o rifiutare un rimborso</h3>
                    <ol>
                        <li>Clicca sulla richiesta per aprire il dettaglio</li>
                        <li>Verifica i dati: tipo di spesa, importo, descrizione e allegato (ricevuta/scontrino)</li>
                        <li>Clicca <strong><i class="ti ti-check"></i> Approva</strong> per approvare il rimborso</li>
                        <li>Oppure clicca <strong><i class="ti ti-x"></i> Rifiuta</strong>: si apre un campo dove devi inserire obbligatoriamente il <strong>motivo del rifiuto</strong></li>
                    </ol>
                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Importante</strong>: Il motivo del rifiuto e obbligatorio e verra comunicato al docente. Inserisci una motivazione chiara e completa.</div>
                    </div>

                    <h3>Tipologie di rimborso</h3>
                    <ul>
                        <li><strong>Viaggio</strong>: spostamenti per raggiungere la sede del corso (km, pedaggi, trasporti pubblici)</li>
                        <li><strong>Vitto e alloggio</strong>: pasti e pernottamento per trasferte</li>
                        <li><strong>Materiale didattico</strong>: acquisto di materiali per le lezioni</li>
                        <li><strong>Altro</strong>: altre spese documentate e giustificate</li>
                    </ul>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 19. ALBO DOCENTI -->
        <section id="albo-docenti">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-address-book"></i> 19. Albo Docenti
                </div>
                <div class="section-card-body">
                    <p>L'Albo Docenti e un registro consultabile di tutti i docenti presenti nel sistema, con i loro profili professionali, competenze e storico incarichi.</p>

                    <h3>Accesso</h3>
                    <p>Accedi da <span class="menu-path">Personale > Albo Docenti</span> o da <code>/docente/albo</code>.</p>

                    <h3>Filtri avanzati</h3>
                    <p>L'albo dispone di filtri avanzati per trovare rapidamente il docente adatto:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Filtro</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Nome/Cognome</strong></td><td>Ricerca testuale per nome o cognome</td></tr>
                            <tr><td><strong>Codice Fiscale</strong></td><td>Ricerca esatta per codice fiscale</td></tr>
                            <tr><td><strong>Provenienza</strong></td><td>TP (Professionista) o RU (Ricercatore)</td></tr>
                            <tr><td><strong>Competenze (Tag)</strong></td><td>Filtra per tag curriculum associati al profilo</td></tr>
                            <tr><td><strong>Disponibilita</strong></td><td>Mostra solo docenti senza incarichi attivi</td></tr>
                            <tr><td><strong>Provincia</strong></td><td>Filtra per zona geografica</td></tr>
                        </tbody>
                    </table>

                    <h3>Assegnazione diretta da Albo</h3>
                    <p>Dall'albo e possibile assegnare direttamente un docente a un modulo aperto:</p>
                    <ol>
                        <li>Trova il docente desiderato usando i filtri</li>
                        <li>Clicca sul pulsante <strong><i class="ti ti-user-plus"></i> Assegna a Modulo</strong> nella riga del docente</li>
                        <li>Seleziona il modulo tra quelli disponibili (non ancora assegnati)</li>
                        <li>Conferma l'assegnazione</li>
                    </ol>
                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: L'assegnazione diretta da albo crea automaticamente l'istanza di candidatura con esito "assegnato" e l'assegnazione corrispondente. Utilizzala per incarichi diretti che non richiedono bando pubblico.</div>
                    </div>

                    <h3>Scheda docente</h3>
                    <p>Cliccando sul nome del docente si apre la scheda completa con: dati anagrafici, competenze (tag), storico incarichi, contratti attivi e valutazioni ricevute.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 20. BORSE DI STUDIO -->
        <section id="borse-studio">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-school"></i> 20. Borse di Studio
                </div>
                <div class="section-card-body">
                    <p>Il modulo Borse di Studio gestisce l'erogazione delle borse previste dai progetti PNRR per gli studenti frequentanti.</p>

                    <h3>Accesso</h3>
                    <p>Accedi da <span class="menu-path">Gestione > Borse di Studio</span> o da <code>/borsa-studio</code>.</p>

                    <h3>Calcolo importi PNRR</h3>
                    <p>Il sistema calcola automaticamente l'importo della borsa in base alle regole PNRR:</p>
                    <ul>
                        <li><strong>Importo base</strong>: calcolato in base alle ore del corso e alla tipologia di intervento</li>
                        <li><strong>Maggiorazione disabilita</strong>: incremento percentuale per studenti con disabilita certificata</li>
                        <li><strong>Maggiorazione residenza</strong>: incremento per studenti residenti fuori regione o in aree svantaggiate</li>
                        <li><strong>Maggiorazione ISEE</strong>: incremento in base alla fascia ISEE</li>
                    </ul>
                    <p>L'importo finale viene calcolato sommando la base e le maggiorazioni applicabili.</p>

                    <h3>Erogazione trimestrale</h3>
                    <p>La borsa viene erogata in rate trimestrali proporzionali alla frequenza:</p>
                    <ol>
                        <li>Seleziona il progetto e il trimestre di riferimento</li>
                        <li>Il sistema calcola automaticamente l'importo per ogni studente in base alle presenze registrate</li>
                        <li>Verifica gli importi nella tabella riepilogativa</li>
                        <li>Clicca <strong>"Genera Erogazioni"</strong> per creare le erogazioni trimestrali</li>
                    </ol>
                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Requisito</strong>: Per l'erogazione della borsa, lo studente deve aver frequentato almeno il 70% delle ore previste nel trimestre.</div>
                    </div>

                    <h3>Revoca borsa</h3>
                    <p>E possibile revocare una borsa gia assegnata nei seguenti casi:</p>
                    <ul>
                        <li>Ritiro dello studente dal corso</li>
                        <li>Frequenza inferiore alla soglia minima</li>
                        <li>Mancato rispetto delle condizioni previste dal bando</li>
                    </ul>
                    <p>Per revocare: apri il dettaglio della borsa, clicca <strong><i class="ti ti-ban"></i> Revoca</strong> e inserisci la motivazione. Le erogazioni future vengono annullate.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 21. VERIFICA FIRMA DIGITALE -->
        <section id="firma-digitale">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-certificate"></i> 21. Verifica Firma Digitale
                </div>
                <div class="section-card-body">
                    <p>Questa funzionalita consente di verificare la validita della firma digitale su documenti ricevuti, senza dover utilizzare software esterno.</p>

                    <h3>Accesso</h3>
                    <p>Accedi da <span class="menu-path">Strumenti > Verifica Firma Digitale</span> o da <code>/firma-digitale</code>.</p>

                    <h3>Formati supportati</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Formato</th><th>Estensione</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>CAdES</strong></td><td>.p7m</td><td>Busta crittografica (il formato piu comune per documenti firmati digitalmente)</td></tr>
                            <tr><td><strong>PAdES</strong></td><td>.pdf</td><td>PDF con firma digitale incorporata</td></tr>
                        </tbody>
                    </table>

                    <h3>Come verificare un documento</h3>
                    <ol>
                        <li>Clicca <strong>"Carica Documento"</strong> o trascina il file nell'area di upload</li>
                        <li>Seleziona il file .p7m o .pdf firmato</li>
                        <li>Il sistema analizza il documento e mostra il risultato della verifica</li>
                    </ol>

                    <h3>Risultato della verifica</h3>
                    <p>Per ogni firma trovata nel documento, il sistema mostra:</p>
                    <ul>
                        <li>Nome e cognome del firmatario</li>
                        <li>Codice fiscale del firmatario</li>
                        <li>Ente certificatore</li>
                        <li>Data e ora della firma</li>
                        <li>Esito: <span class="badge bg-green text-green-fg">Valida</span>, <span class="badge bg-orange text-orange-fg">Scaduta</span> o <span class="badge bg-red text-red-fg">Non valida</span></li>
                    </ul>
                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Attenzione</strong>: La verifica e indicativa e basata sui certificati disponibili. Per documenti con valore legale, si consiglia di utilizzare anche il servizio ufficiale di verifica dell'AgID.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 22. GENERAZIONE CONTRATTI PDF -->
        <section id="contratti-pdf">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-file-type-pdf"></i> 22. Generazione Contratti PDF
                </div>
                <div class="section-card-body">
                    <p>Il sistema consente di generare automaticamente i contratti in formato PDF a partire dai template configurati, precompilati con i dati del docente e del progetto.</p>

                    <h3>Generare un contratto PDF</h3>
                    <p>Dalla vista dettaglio di un contratto (<span class="menu-path">Gestione > Contratti > Dettaglio</span>):</p>
                    <ol>
                        <li>Clicca il pulsante <strong><i class="ti ti-file-type-pdf"></i> Genera PDF</strong></li>
                        <li>Il sistema genera il contratto utilizzando il template appropriato</li>
                        <li>Il PDF si scarica automaticamente</li>
                    </ol>

                    <h3>Template disponibili</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Template</th><th>Fonte</th><th>Contenuto</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Contratto FSE</strong></td><td>FSE</td><td>Incarico professionale con riferimenti al PO FSE, CIG, CUP</td></tr>
                            <tr><td><strong>Contratto PNRR</strong></td><td>PNRR</td><td>Incarico con riferimenti al PNRR, Missione, Componente, Investimento</td></tr>
                        </tbody>
                    </table>
                    <p>I template includono automaticamente:</p>
                    <ul>
                        <li>Dati anagrafici del docente (nome, CF, indirizzo, P.IVA/dati fiscali)</li>
                        <li>Dati del progetto (titolo, codice, fonte di finanziamento, CIG, CUP)</li>
                        <li>Dettaglio del modulo (titolo, ore, sede)</li>
                        <li>Compenso orario e totale contrattualizzato</li>
                        <li>Date di inizio e fine lezioni</li>
                        <li>Clausole contrattuali standard</li>
                        <li>Dati della fondazione (da Configurazione)</li>
                    </ul>

                    <h3>Upload contratto firmato</h3>
                    <p>Dopo che il docente ha firmato il contratto e lo ha restituito:</p>
                    <ol>
                        <li>Apri il dettaglio del contratto</li>
                        <li>Clicca <strong><i class="ti ti-upload"></i> Carica Firmato</strong></li>
                        <li>Seleziona il file PDF firmato (o .p7m se firmato digitalmente)</li>
                        <li>Il contratto passa allo stato <span class="badge bg-green text-green-fg">Firmato</span></li>
                    </ol>
                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Suggerimento</strong>: Puoi verificare la firma digitale del contratto ricevuto utilizzando la funzione <a href="#firma-digitale">Verifica Firma Digitale</a> (Sez. 21).</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 23. COORDINATORI E TUTOR -->
        <section id="coordinatori-tutor">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-user-star"></i> 23. Coordinatori e Tutor
                </div>
                <div class="section-card-body">
                    <p>Oltre ai docenti incaricati per i singoli moduli formativi, il sistema gestisce anche le figure di <strong>Coordinatore</strong> e <strong>Tutor</strong> di progetto.</p>

                    <h3>Tipi di docente</h3>
                    <p>Il campo <strong>Tipo</strong> nell'anagrafica docenti distingue le diverse figure professionali:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Tipo</th><th>Ruolo</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Docente</strong></td><td>Insegnamento</td><td>Svolge le lezioni nei moduli formativi assegnati</td></tr>
                            <tr><td><strong>Coordinatore</strong></td><td>Coordinamento didattico</td><td>Coordina le attivita formative del progetto, supervisiona i docenti</td></tr>
                            <tr><td><strong>Tutor</strong></td><td>Supporto formativo</td><td>Affianca i docenti, supporta gli studenti, gestisce le presenze</td></tr>
                        </tbody>
                    </table>

                    <h3>Gestione dall'anagrafica</h3>
                    <p>Per impostare il tipo di un docente:</p>
                    <ol>
                        <li>Vai su <span class="menu-path">Personale > Docenti</span></li>
                        <li>Apri la scheda del docente</li>
                        <li>Clicca <strong>"Modifica"</strong></li>
                        <li>Seleziona il <strong>Tipo</strong> appropriato dal menu a tendina</li>
                        <li>Salva le modifiche</li>
                    </ol>

                    <h3>Utilizzo nei progetti</h3>
                    <p>Coordinatori e tutor possono essere:</p>
                    <ul>
                        <li>Assegnati a uno o piu progetti come figure di supporto</li>
                        <li>Contrattualizzati con le stesse modalita dei docenti</li>
                        <li>Inclusi nella rendicontazione ore con il medesimo workflow</li>
                        <li>Filtrati nelle viste tramite il campo Tipo</li>
                    </ul>
                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: Coordinatori e tutor accedono all'Area Docenti con le stesse credenziali e funzionalita dei docenti. La distinzione del tipo serve principalmente per la categorizzazione amministrativa e la rendicontazione.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 24. GESTIONE SEMPLIFICATA -->
        <section id="gestione-semplificata">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-layout-grid"></i> 24. Gestione Semplificata
                </div>
                <div class="section-card-body">
                    <p>La <strong>Gestione Semplificata</strong> e una nuova area progettata per gestire i corsi in modo rapido e intuitivo, senza dover navigare tra le voci avanzate del menu. E accessibile direttamente dal menu principale.</p>

                    <h3>Schermata iniziale</h3>
                    <p>All'accesso viene mostrata una griglia di card, una per ogni corso attivo nel sistema. Ogni card riporta:</p>
                    <ul>
                        <li>Titolo del corso e fonte di finanziamento</li>
                        <li>Numero di moduli, docenti assegnati e studenti iscritti</li>
                        <li>Stato del corso (attivo, pianificato, concluso)</li>
                    </ul>

                    <h3>Dashboard del corso</h3>
                    <p>Cliccando su una card si apre la dashboard dedicata al corso. La navigazione interna avviene tramite una <strong>sidebar</strong> con le seguenti sezioni:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Sezione</th><th>Contenuto</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Panoramica</strong></td><td>Statistiche riassuntive e alert attivi per il corso</td></tr>
                            <tr><td><strong>Piano Formativo</strong></td><td>Elenco moduli con docente assegnato, ore e stato</td></tr>
                            <tr><td><strong>Personale</strong></td><td>Docenti, coordinatori e tutor collegati al corso</td></tr>
                            <tr><td><strong>Studenti</strong></td><td>Elenco iscritti con stato frequenza e graduatoria</td></tr>
                            <tr><td><strong>Calendario</strong></td><td>Lezioni programmate con gestione presenze rapida</td></tr>
                            <tr><td><strong>Contratti</strong></td><td>Contratti docenti con stato e avanzamento pagamenti</td></tr>
                            <tr><td><strong>Borse di Studio</strong></td><td>Borse degli studenti con frequenza e stato erogazione</td></tr>
                            <tr><td><strong>Import/Export</strong></td><td>Import dati Excel e export per reportistica</td></tr>
                        </tbody>
                    </table>

                    <h3>Selettore rapido corso</h3>
                    <p>In cima alla dashboard e presente un selettore a tendina che permette di passare da un corso all'altro senza tornare alla schermata iniziale. Questo velocizza notevolmente il lavoro quando si gestiscono piu corsi in parallelo.</p>

                    <h3>Modalita avanzata</h3>
                    <p>La Gestione Semplificata non sostituisce l'area amministrativa completa. Il link <strong>"Modalita avanzata"</strong> presente nella dashboard di ogni corso riporta direttamente alla vista dettaglio avanzata del progetto/avviso, con tutte le funzionalita standard.</p>

                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Suggerimento</strong>: Usa la Gestione Semplificata per le operazioni quotidiane (verificare presenze, controllare contratti, monitorare borse). Usa la modalita avanzata per configurazioni e operazioni straordinarie.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 25. CANDIDATURA MULTI-MODULO -->
        <section id="candidatura-multimodulo">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-users-plus"></i> 25. Candidatura Multi-modulo
                </div>
                <div class="section-card-body">
                    <p>La funzione <strong>Candidatura Multi-modulo</strong> consente di iscrivere un docente a piu moduli contemporaneamente, eliminando la necessita di creare istanze una alla volta.</p>

                    <h3>Come accedere</h3>
                    <p>La candidatura multi-modulo e accessibile da due punti:</p>
                    <ul>
                        <li><strong>Gestione Semplificata</strong>: Sezione "Piano Formativo" del corso → pulsante <strong>"Aggiungi Docente"</strong></li>
                        <li><strong>Vista avanzata</strong>: Dettaglio avviso → pulsante <strong>"Candidatura Multi-modulo"</strong></li>
                    </ul>

                    <h3>Procedura</h3>
                    <ol>
                        <li>Seleziona il <strong>docente</strong> dall'albo (ricerca per nome, cognome o codice fiscale)</li>
                        <li>Vengono mostrati tutti i <strong>moduli disponibili</strong> del corso/avviso</li>
                        <li>Spunta i moduli per cui si desidera creare la candidatura</li>
                        <li>Clicca <strong>"Conferma Candidatura"</strong></li>
                    </ol>

                    <h3>Risultato</h3>
                    <p>Il sistema crea automaticamente un'<strong>istanza</strong> (candidatura) per ogni modulo selezionato. Le istanze vengono create in stato "in valutazione" e potranno essere valutate dalla CTS come di consueto.</p>

                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Attenzione</strong>: Se per un modulo esiste gia un'istanza del docente selezionato, il sistema lo segnalera e saltara automaticamente quel modulo per evitare duplicati.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 26. METTI A BANDO -->
        <section id="metti-a-bando">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-speakerphone"></i> 26. Metti a Bando
                </div>
                <div class="section-card-body">
                    <p>La funzione <strong>Metti a Bando</strong> consente di pubblicare rapidamente piu moduli nell'area docenti direttamente dalla Gestione Semplificata, senza dover configurare manualmente ogni avviso.</p>

                    <h3>Come usarla</h3>
                    <ol>
                        <li>Accedi alla <strong>Gestione Semplificata</strong> e apri il corso di interesse</li>
                        <li>Vai alla sezione <strong>"Piano Formativo"</strong></li>
                        <li>I moduli <strong>scoperti</strong> (senza docente assegnato) mostrano una <strong>checkbox</strong> sulla sinistra</li>
                        <li>Seleziona i moduli che vuoi mettere a bando</li>
                        <li>Clicca il pulsante <strong>"Metti a Bando"</strong></li>
                        <li>Conferma l'operazione</li>
                    </ol>

                    <h3>Effetto dell'operazione</h3>
                    <p>I moduli selezionati vengono pubblicati nell'area docenti e diventano visibili come bandi aperti. I docenti registrati potranno candidarsi normalmente attraverso il loro portale.</p>

                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: I moduli che hanno gia un docente assegnato non mostrano la checkbox e non possono essere messi a bando. Per pubblicare comunque un modulo gia assegnato, usa la gestione avvisi nell'area avanzata.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 27. ALERT DASHBOARD -->
        <section id="alert-dashboard">
            <div class="section-card">
                <div class="section-card-header bg-orange-lt">
                    <i class="ti ti-bell-exclamation"></i> 27. Alert Dashboard
                </div>
                <div class="section-card-body">
                    <p>Il sistema genera automaticamente <strong>alert</strong> per segnalare situazioni che richiedono attenzione. Gli alert sono visibili nella <strong>dashboard principale</strong> e nella sezione <strong>Panoramica</strong> di ogni corso nella Gestione Semplificata.</p>

                    <h3>Tipi di alert</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Alert</th><th>Colore</th><th>Condizione</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><strong>Docenti non contrattualizzati</strong></td>
                                <td><span class="badge bg-orange-lt text-orange">Arancione</span></td>
                                <td>Docenti con assegnazione approvata ma contratto non ancora emesso</td>
                            </tr>
                            <tr>
                                <td><strong>Presenze non acquisite</strong></td>
                                <td><span class="badge bg-red-lt text-red">Rosso</span></td>
                                <td>Lezioni gia avvenute (data passata) prive di registro presenze</td>
                            </tr>
                            <tr>
                                <td><strong>Frequenza borsa insufficiente</strong></td>
                                <td><span class="badge bg-yellow-lt text-yellow">Giallo</span></td>
                                <td>Studenti con borsa approvata ma frequenza inferiore all'80%</td>
                            </tr>
                        </tbody>
                    </table>

                    <h3>Come gestire gli alert</h3>
                    <p>Ogni alert e cliccabile e porta direttamente alla sezione pertinente del sistema per risolvere la problematica segnalata:</p>
                    <ul>
                        <li>Alert docenti non contrattualizzati → lista assegnazioni senza contratto</li>
                        <li>Alert presenze non acquisite → calendario con lezioni incomplete</li>
                        <li>Alert frequenza borsa → lista borse con frequenza sotto soglia</li>
                    </ul>

                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: Gli alert vengono aggiornati automaticamente a ogni caricamento della pagina. Non e necessario alcun aggiornamento manuale.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 28. ALLEGATI CONTRATTO -->
        <section id="allegati-contratto">
            <div class="section-card">
                <div class="section-card-header bg-blue-lt">
                    <i class="ti ti-paperclip"></i> 28. Allegati Contratto
                </div>
                <div class="section-card-body">
                    <p>La sezione <strong>Allegati</strong> nel dettaglio contratto consente di archiviare digitalmente le scansioni dei contratti firmati a mano, tenendole associate al contratto nel sistema.</p>

                    <h3>Come caricare un allegato</h3>
                    <ol>
                        <li>Apri il dettaglio di un contratto (<span class="menu-path">Gestione > Contratti > Dettaglio</span>)</li>
                        <li>Individua la sezione <strong>"Allegati"</strong> nella pagina</li>
                        <li>Clicca <strong>"Carica Allegato"</strong></li>
                        <li>Seleziona il file dal tuo dispositivo</li>
                        <li>Clicca <strong>"Carica"</strong></li>
                    </ol>

                    <h3>Formati supportati</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Formato</th><th>Utilizzo tipico</th></tr></thead>
                        <tbody>
                            <tr><td><strong>PDF</strong></td><td>Scansione del contratto firmato</td></tr>
                            <tr><td><strong>JPG / PNG</strong></td><td>Foto del contratto firmato (da smartphone)</td></tr>
                        </tbody>
                    </table>

                    <h3>Gestione allegati</h3>
                    <p>Gli allegati caricati sono elencati nella sezione con data di caricamento e nome file. Per ogni allegato sono disponibili le azioni:</p>
                    <ul>
                        <li><strong><i class="ti ti-download"></i> Scarica</strong>: scarica il file originale</li>
                        <li><strong><i class="ti ti-trash"></i> Elimina</strong>: rimuove l'allegato dal sistema</li>
                    </ul>

                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota</strong>: Gli allegati sono visibili solo agli amministratori e non all'area docenti. Per il contratto firmato visibile al docente, usa la funzione "Carica Firmato" nella sezione <a href="#contratti-pdf">Generazione Contratti PDF</a> (Sez. 22).</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 29. EROGAZIONE BLOCCATA (BORSA DI STUDIO) -->
        <section id="borsa-bloccata">
            <div class="section-card">
                <div class="section-card-header bg-red-lt">
                    <i class="ti ti-lock"></i> 29. Borse di Studio — Erogazione Bloccata
                </div>
                <div class="section-card-body">
                    <p>Le borse di studio possono trovarsi nello stato <strong>Erogazione Bloccata</strong> quando lo studente ha una frequenza inferiore all'80% delle ore del corso. La borsa rimane approvata ma l'erogazione e sospesa fino al recupero della frequenza.</p>

                    <h3>Come funziona il calcolo automatico</h3>
                    <p>Il sistema incrocia i dati di:</p>
                    <ul>
                        <li>Ore totali previste dal corso</li>
                        <li>Presenze registrate nel calendario lezioni</li>
                    </ul>
                    <p>Se la percentuale di frequenza calcolata scende sotto l'<strong>80%</strong>, la borsa passa automaticamente allo stato <span class="badge bg-red text-red-fg">Erogazione Bloccata</span>.</p>

                    <h3>Ricalcolo manuale</h3>
                    <p>In caso di aggiornamento delle presenze, usa il pulsante <strong>"Ricalcola Frequenze"</strong> disponibile nella lista borse di studio per forzare un aggiornamento immediato di tutti i calcoli:</p>
                    <ol>
                        <li>Vai su <span class="menu-path">Gestione > Borse di Studio</span></li>
                        <li>Clicca <strong><i class="ti ti-refresh"></i> Ricalcola Frequenze</strong></li>
                        <li>Il sistema aggiorna le percentuali e gli stati per tutti gli studenti del progetto selezionato</li>
                    </ol>

                    <h3>Sblocco manuale</h3>
                    <p>Se la situazione si risolve (es. assenze giustificate, aggiornamento presenze) e la frequenza torna sopra l'80%, il sistema sblocca automaticamente l'erogazione al prossimo ricalcolo. E possibile anche sbloccare manualmente una singola borsa aprendo il dettaglio e cliccando <strong>"Sblocca Erogazione"</strong>, specificando la motivazione.</p>

                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Importante</strong>: Lo stato "Erogazione Bloccata" genera anche un alert nella Dashboard e nella Panoramica del corso nella Gestione Semplificata. Questi alert sono visibili nella sezione <a href="#alert-dashboard">Alert Dashboard</a> (Sez. 27).</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 30. EXPORT SEPA XML -->
        <section id="export-sepa">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-file-export"></i> 30. Export SEPA XML
                </div>
                <div class="section-card-body">
                    <p>La funzione <strong>Export SEPA XML</strong> genera un file in formato <code>pain.001.001.03</code> compatibile con i principali sistemi bancari italiani ed europei, per il pagamento massivo delle borse di studio tramite bonifico.</p>

                    <h3>Prerequisiti</h3>
                    <p>Prima di utilizzare la funzione, assicurati di aver configurato i dati bancari della fondazione:</p>
                    <ol>
                        <li>Vai su <span class="menu-path">Configurazione > Dati Fondazione</span></li>
                        <li>Nella sezione <strong>"Dati Bancari"</strong> (gruppo <code>banca</code>), inserisci:
                            <ul>
                                <li><strong>IBAN</strong> della fondazione (conto di addebito)</li>
                                <li><strong>BIC/SWIFT</strong> della banca della fondazione</li>
                            </ul>
                        </li>
                        <li>Salva le modifiche</li>
                    </ol>

                    <h3>Generare il file SEPA</h3>
                    <ol>
                        <li>Vai su <span class="menu-path">Gestione > Borse di Studio</span></li>
                        <li>Seleziona le borse da includere nel pagamento (usa i filtri per progetto e stato)</li>
                        <li>Clicca il pulsante <strong><i class="ti ti-file-export"></i> Export SEPA XML</strong></li>
                        <li>Il file <code>pain.001.001.03</code> viene scaricato automaticamente</li>
                    </ol>

                    <h3>Utilizzo del file</h3>
                    <p>Il file XML generato puo essere importato nel portale di home banking della fondazione per eseguire i bonifici in modo massivo. Le procedure di importazione variano a seconda della banca; fare riferimento al proprio sportello bancario per le istruzioni specifiche.</p>

                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Attenzione</strong>: Verifica sempre che gli IBAN degli studenti siano aggiornati prima di generare il file SEPA. IBAN errati causano il rigetto del bonifico da parte della banca.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 31. RIDENOMINAZIONE TERMINI -->
        <section id="ridenominazione">
            <div class="section-card">
                <div class="section-card-header bg-default-lt">
                    <i class="ti ti-tag"></i> 31. Ridenominazione Termini
                </div>
                <div class="section-card-body">
                    <p>A partire dalla quarta riunione operativa, la terminologia del sistema e stata aggiornata per allinearsi al linguaggio utilizzato dai referenti istituzionali. Di seguito la tabella di corrispondenza:</p>

                    <table class="table campo-table">
                        <thead><tr><th>Termine precedente</th><th>Nuovo termine</th><th>Contesto</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><strong>Corso</strong></td>
                                <td><strong>Corso</strong></td>
                                <td>Riferimento all'intero percorso formativo nella comunicazione con docenti e studenti</td>
                            </tr>
                            <tr>
                                <td><strong>Moduli / Corsi</strong></td>
                                <td><strong>Moduli / Insegnamenti</strong></td>
                                <td>Singola disciplina/materia all'interno di un corso</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="alert-manuale bg-azure-lt">
                        <i class="ti ti-info-circle text-azure"></i>
                        <div><strong>Nota tecnica</strong>: La terminologia interna al database (tabelle <code>avviso</code>, <code>modulo</code>) e le URL del sistema non cambiano. La ridenominazione riguarda esclusivamente la comunicazione verso l'utente nell'interfaccia e nei manuali.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- Footer -->
        <div class="text-center text-secondary py-4" style="border-top: 1px solid var(--tblr-border-color); margin-top: 2rem;">
            <p class="mb-1">Albatros Manager - Manuale Amministratore</p>
            <p class="mb-0 small">Versione 1.2 - Aprile 2026</p>
        </div>
    </div>
</div>

<script>
// Scrollspy manuale
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

    // Smooth scroll
    navLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            // Chiudi sidebar mobile
            document.getElementById('manuale-nav').classList.remove('show');
        });
    });
});
</script>
