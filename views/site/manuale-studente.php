<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Manuale Studente';
?>

<style>
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
    border-left-color: var(--tblr-purple);
}
.manuale-sidebar .nav-link.active {
    color: var(--tblr-purple);
    font-weight: 600;
    border-left-color: var(--tblr-purple);
    background: rgba(var(--tblr-purple-rgb), 0.04);
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
.manuale-header {
    background: linear-gradient(135deg, #ae3ec9, #8c3bb0);
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
.back-to-top:hover { color: var(--tblr-purple); }
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

@media print {
    .manuale-sidebar, .manuale-header .btn, .back-to-top { display: none !important; }
    .manuale-content { padding: 0; max-width: 100%; }
    .manuale-wrapper { display: block; }
    .manuale-header { background: #ae3ec9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
        <i class="ti ti-users" style="font-size: 2.5rem; opacity: 0.8;"></i>
        <div>
            <h1><i class="ti ti-book me-2"></i>Manuale Studente</h1>
            <div style="opacity: 0.8; font-size: 0.875rem;">I Corsi di Formazione - Albatros Manager</div>
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
            <a class="nav-link" href="#consultare-corsi"><i class="ti ti-search"></i> Consultare i Corsi</a>
            <a class="nav-link" href="#iscriversi"><i class="ti ti-user-plus"></i> Iscriversi</a>
            <a class="nav-link" href="#percorso"><i class="ti ti-road"></i> Il Tuo Percorso</a>
        </div>
        <div class="sidebar-heading mt-4">Altri manuali</div>
        <div class="nav flex-column">
            <a class="nav-link" href="<?= Url::to(['site/manuale-admin']) ?>"><i class="ti ti-shield-lock"></i> Manuale Amministratore</a>
            <a class="nav-link" href="<?= Url::to(['site/manuale-docente']) ?>"><i class="ti ti-school"></i> Manuale Docente</a>
        </div>
    </nav>

    <!-- Content -->
    <div class="manuale-content">

        <!-- 1. INTRODUZIONE -->
        <section id="introduzione">
            <div class="section-card">
                <div class="section-card-header bg-purple-lt">
                    <i class="ti ti-info-circle"></i> 1. Introduzione
                </div>
                <div class="section-card-body">
                    <h3>I Corsi di Formazione</h3>
                    <p>Albatros Manager gestisce corsi di formazione professionale finanziati dal Fondo Sociale Europeo (FSE), dal Piano Nazionale di Ripresa e Resilienza (PNRR) e dal Ministero dell'Istruzione e del Merito (MIM).</p>
                    <p>Come studente, puoi:</p>
                    <ul>
                        <li>Consultare i corsi disponibili e le loro caratteristiche</li>
                        <li>Iscriverti ai corsi di tuo interesse</li>
                        <li>Seguire il tuo percorso formativo con presenze e certificazioni</li>
                        <li>Valutare i docenti in modo anonimo</li>
                    </ul>

                    <h3>Come funziona</h3>
                    <p>I corsi sono organizzati in <strong>progetti formativi</strong>, ciascuno composto da diversi <strong>moduli</strong> (materie). Ogni modulo e tenuto da un docente specializzato, in una sede specifica, per un determinato numero di ore.</p>

                    <p>Dalla pagina di login del sistema puoi consultare i corsi disponibili senza bisogno di registrarti. Per iscriverti a un corso e accedere all'area riservata studenti, dovrai creare un account.</p>

                    <div class="alert-manuale bg-purple-lt">
                        <i class="ti ti-info-circle text-purple"></i>
                        <div><strong>Nota</strong>: L'area studenti e in fase di attivazione progressiva. Alcune funzionalita saranno disponibili a breve. Per informazioni immediate, contatta la segreteria.</div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 2. CONSULTARE I CORSI -->
        <section id="consultare-corsi">
            <div class="section-card">
                <div class="section-card-header bg-azure-lt">
                    <i class="ti ti-search"></i> 2. Consultare i Corsi
                </div>
                <div class="section-card-body">
                    <h3>Dove trovare i corsi</h3>
                    <p>I corsi disponibili sono visibili direttamente nella pagina di accesso del sistema, nella sezione "Corsi Disponibili". Non e necessario effettuare il login per consultarli.</p>

                    <h3>Informazioni sui corsi</h3>
                    <p>Per ogni corso disponibile vengono mostrati:</p>
                    <table class="table campo-table">
                        <thead><tr><th>Informazione</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Titolo del corso</strong></td><td>Il nome del percorso formativo (es: "Health Food", "Agrifood Messina")</td></tr>
                            <tr><td><strong>Fonte di finanziamento</strong></td><td>Il fondo che finanzia il corso (FSE, PNRR, MIM)</td></tr>
                            <tr><td><strong>Posti disponibili</strong></td><td>Il numero di studenti che possono partecipare (tipicamente 20)</td></tr>
                            <tr><td><strong>Sede</strong></td><td>La localita dove si svolgono le lezioni</td></tr>
                            <tr><td><strong>Stato</strong></td><td>Attivo (iscrizioni aperte), Pianificato (in preparazione), Concluso</td></tr>
                        </tbody>
                    </table>

                    <h3>Dettaglio del corso</h3>
                    <p>Cliccando su un corso puoi visualizzare il dettaglio completo con:</p>
                    <ul>
                        <li>Descrizione completa del percorso formativo</li>
                        <li>Elenco dei moduli (materie) con titolo e numero di ore</li>
                        <li>Sedi operative</li>
                        <li>Date di inizio e fine previste</li>
                        <li>Requisiti di ammissione (se previsti)</li>
                    </ul>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 3. ISCRIVERSI -->
        <section id="iscriversi">
            <div class="section-card">
                <div class="section-card-header bg-green-lt">
                    <i class="ti ti-user-plus"></i> 3. Iscriversi
                </div>
                <div class="section-card-body">
                    <h3>Procedura di iscrizione</h3>
                    <p>Per iscriverti a un corso di formazione:</p>
                    <ol>
                        <li>Accedi alla pagina di login e seleziona la card <strong>"Studente"</strong></li>
                        <li>Se non hai un account, clicca <strong>"Registrati"</strong> e compila il modulo con i tuoi dati anagrafici</li>
                        <li>Dopo il login, naviga al corso di tuo interesse</li>
                        <li>Compila il modulo di iscrizione con:</li>
                    </ol>
                    <table class="table campo-table">
                        <thead><tr><th>Campo</th><th>Descrizione</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Dati anagrafici</strong></td><td>Nome, cognome, codice fiscale, data di nascita, indirizzo</td></tr>
                            <tr><td><strong>Contatti</strong></td><td>Email, telefono</td></tr>
                            <tr><td><strong>Documenti</strong></td><td>Documento d'identita, eventuali titoli di studio o attestati richiesti</td></tr>
                        </tbody>
                    </table>
                    <ol start="5">
                        <li>Conferma l'iscrizione</li>
                    </ol>

                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Posti limitati</strong>: I corsi hanno un numero massimo di partecipanti. Le iscrizioni vengono accettate in ordine di arrivo fino a esaurimento dei posti disponibili.</div>
                    </div>

                    <h3>Conferma iscrizione</h3>
                    <p>Dopo l'invio del modulo, riceverai una conferma con il riepilogo dei dati inseriti. La segreteria verifichera la documentazione e ti comunichera l'esito dell'iscrizione via email.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- 4. IL TUO PERCORSO -->
        <section id="percorso">
            <div class="section-card">
                <div class="section-card-header bg-purple-lt">
                    <i class="ti ti-road"></i> 4. Il Tuo Percorso
                </div>
                <div class="section-card-body">
                    <h3>Seguire il corso</h3>
                    <p>Una volta ammesso al corso, potrai accedere alla tua area riservata per:</p>
                    <ul>
                        <li><strong>Calendario lezioni</strong>: Consulta le date, gli orari e le sedi di ogni lezione programmata</li>
                        <li><strong>Le tue presenze</strong>: Visualizza il registro delle presenze. La frequenza minima e necessaria per ottenere la certificazione</li>
                        <li><strong>Materiali</strong>: Eventuali materiali didattici condivisi dai docenti</li>
                    </ul>

                    <h3>Presenze</h3>
                    <p>Le presenze vengono registrate dal personale amministrativo all'inizio di ogni lezione. E importante:</p>
                    <ul>
                        <li>Presentarsi puntuali all'orario indicato</li>
                        <li>Firmare il registro presenze (cartaceo o digitale)</li>
                        <li>Comunicare tempestivamente eventuali assenze alla segreteria</li>
                    </ul>
                    <div class="alert-manuale bg-orange-lt">
                        <i class="ti ti-alert-triangle text-orange"></i>
                        <div><strong>Frequenza obbligatoria</strong>: Per ottenere la certificazione finale e generalmente richiesta una frequenza minima del 70-80% delle ore totali. La percentuale esatta dipende dal corso.</div>
                    </div>

                    <h3>Valutazione dei docenti</h3>
                    <p>Al termine di ogni modulo, ti verra consegnato un codice di accesso per compilare un questionario di valutazione anonimo sul docente. Il questionario prevede:</p>
                    <ul>
                        <li>Punteggi da 1 a 5 su diversi criteri (chiarezza, competenza, disponibilita, ecc.)</li>
                        <li>Un campo per commenti e suggerimenti liberi</li>
                    </ul>
                    <p>La compilazione e anonima: il codice monouso garantisce che non sia possibile risalire alla tua identita. Le valutazioni sono importanti per migliorare la qualita della formazione.</p>

                    <h3>Certificazione</h3>
                    <p>Al completamento del percorso formativo, se hai raggiunto la frequenza minima richiesta, riceverai un attestato di partecipazione/certificazione relativo al corso frequentato. La segreteria ti contrattera per le modalita di rilascio.</p>
                </div>
            </div>
            <a href="#" class="back-to-top"><i class="ti ti-arrow-up"></i> Torna all'indice</a>
        </section>

        <!-- Footer -->
        <div class="text-center text-secondary py-4" style="border-top: 1px solid var(--tblr-border-color); margin-top: 2rem;">
            <p class="mb-1">Albatros Manager - Manuale Studente</p>
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
