<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Assistente AI';
$this->params['breadcrumbs'][] = $this->title;

\app\components\assets\WebLlmAsset::register($this);

?>

<div class="page-body">
    <div class="container-xl">
        <!-- Page Header -->
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="page-pretitle">Strumenti</div>
                    <h2 class="page-title">
                        <i class="ti ti-robot me-2"></i>Assistente AI
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <span id="fullscreenStatus" class="badge bg-orange-lt">
                        <span class="badge-dot"></span> Inizializzazione...
                    </span>
                </div>
            </div>
        </div>

        <!-- Assistant Fullscreen Container -->
        <div class="assistant-fullscreen">
            <div class="card" style="background: var(--assistant-bg); border-color: var(--assistant-border); color: var(--assistant-text);">
                <div class="card-body p-0 d-flex flex-column" style="height: 70vh;">

                    <!-- Download Progress -->
                    <div id="fullscreenDownload" class="assistant-download" style="display:none">
                        <div class="assistant-download-label">
                            <span id="fullscreenDownloadText">Download modello...</span>
                            <span id="fullscreenDownloadPercent">0%</span>
                        </div>
                        <div class="assistant-download-bar">
                            <div id="fullscreenDownloadBar" class="assistant-download-fill" style="width:0%"></div>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div id="fullscreenMessages" class="assistant-messages" style="flex:1; overflow-y:auto;"></div>

                    <!-- Typing Indicator -->
                    <div id="fullscreenTyping" class="assistant-typing" style="display:none; margin: 0 16px 8px;">
                        <span class="assistant-typing-dot"></span>
                        <span class="assistant-typing-dot"></span>
                        <span class="assistant-typing-dot"></span>
                    </div>

                    <!-- Quick Suggestions -->
                    <div id="fullscreenSuggestions" class="assistant-suggestions">
                        <button class="assistant-suggestion-btn" data-query="Fammi un quadro generale della situazione: progetti attivi, budget, docenti e studenti">Quadro generale</button>
                        <button class="assistant-suggestion-btn" data-query="Quali docenti hanno pagamenti in attesa da liquidare?">Docenti da liquidare</button>
                        <button class="assistant-suggestion-btn" data-query="Ci sono moduli senza docente assegnato?">Moduli scoperti</button>
                        <button class="assistant-suggestion-btn" data-query="Quali sono le prossime scadenze di bandi e contratti?">Scadenze</button>
                        <button class="assistant-suggestion-btn" data-query="Quali sono i docenti con il punteggio piu alto nelle graduatorie?">Top docenti</button>
                        <button class="assistant-suggestion-btn" data-query="Quante ore sono state rendicontate e quante approvate questo mese?">Ore rendicontate</button>
                    </div>

                    <!-- Input Area -->
                    <div class="assistant-input-area">
                        <input id="fullscreenInput" type="text" class="assistant-input" placeholder="Scrivi un messaggio..." disabled>
                        <button id="fullscreenSend" class="assistant-send-btn" disabled title="Invia">
                            <i class="ti ti-send"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Assistant Engine (ES Module) -->
<script type="module" src="/js/assistant/assistant-engine.js"></script>
