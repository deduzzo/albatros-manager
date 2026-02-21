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
            <a class="nav-link" href="#avvisi"><i class="ti ti-file-text"></i> Avvisi e Bandi</a>
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
                            <tr><td><i class="ti ti-folder me-1"></i> Progetti</td><td>Progetti, Avvisi/Bandi, Moduli/Corsi</td></tr>
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

        <!-- 5. AVVISI E BANDI -->
        <section id="avvisi">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-file-text"></i> 5. Avvisi e Bandi
                </div>
                <div class="section-card-body">
                    <p>Gli Avvisi (o Bandi) sono le procedure di selezione pubblica per il reclutamento dei docenti. Ogni avviso e collegato a un progetto e contiene uno o piu moduli.</p>

                    <h3>Creare un avviso</h3>
                    <ol>
                        <li>Vai su <span class="menu-path">Progetti > Avvisi/Bandi</span></li>
                        <li>Clicca <strong>"Nuovo Avviso"</strong></li>
                        <li>Seleziona il progetto di riferimento</li>
                        <li>Compila: titolo, numero protocollo, data pubblicazione, data scadenza, descrizione</li>
                        <li>Imposta lo stato iniziale (generalmente "bozza")</li>
                        <li>Clicca <strong>"Salva"</strong></li>
                    </ol>

                    <h3>Stati dell'avviso</h3>
                    <table class="table campo-table">
                        <thead><tr><th>Stato</th><th>Significato</th></tr></thead>
                        <tbody>
                            <tr><td><span class="badge bg-default text-default-fg">Bozza</span></td><td>Avviso in preparazione, non visibile ai docenti</td></tr>
                            <tr><td><span class="badge bg-green text-green-fg">Pubblicato</span></td><td>Avviso visibile nell'area docenti, candidature aperte</td></tr>
                            <tr><td><span class="badge bg-red text-red-fg">Chiuso</span></td><td>Scadenza superata, candidature chiuse</td></tr>
                        </tbody>
                    </table>

                    <h3>Documenti richiesti</h3>
                    <p>Per ogni avviso puoi configurare quali documenti sono necessari per la candidatura. Nella pagina di dettaglio dell'avviso, nella sezione "Documenti Richiesti", puoi aggiungere i tipi di documento necessari (es: CV, Diploma, Documento d'identita). I docenti dovranno caricare tali documenti al momento dell'iscrizione.</p>

                    <h3>Collegamento gerarchico</h3>
                    <p>La gerarchia e: <strong>Progetto > Avviso > Moduli</strong>. Un progetto puo avere piu avvisi (ad esempio avvisi distinti per diverse figure professionali), e ogni avviso contiene i moduli per cui si ricercano docenti.</p>
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
                    <p>I Moduli (o Materie) sono le singole discipline all'interno di un avviso/bando. Ad esempio, in un progetto "Health Food" i moduli possono essere "Chimica degli Alimenti", "Sicurezza Alimentare", "Marketing Agroalimentare", ecc.</p>

                    <h3>Creare un modulo</h3>
                    <ol>
                        <li>Vai su <span class="menu-path">Progetti > Moduli/Corsi</span></li>
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
                            <tr><td><strong>Bandi Aperti</strong></td><td>Lista dei bandi pubblicati con moduli disponibili</td></tr>
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

        <!-- Footer -->
        <div class="text-center text-secondary py-4" style="border-top: 1px solid var(--tblr-border-color); margin-top: 2rem;">
            <p class="mb-1">Albatros Manager - Manuale Amministratore</p>
            <p class="mb-0 small">Versione 1.0 - Febbraio 2026</p>
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
