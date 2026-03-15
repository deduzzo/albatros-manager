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

                    <!-- Header con azioni -->
                    <div class="assistant-header">
                        <div class="assistant-header-left">
                            <div class="assistant-header-avatar">
                                <i class="ti ti-robot"></i>
                            </div>
                            <div class="assistant-header-info">
                                <h4>Assistente AI</h4>
                                <div class="assistant-header-status-row">
                                    <span id="assistantStatusText" class="assistant-header-status">Inizializzazione...</span>
                                    <span id="assistantModelLabel" class="assistant-model-label" style="display:none" title="Clicca per cambiare modello"></span>
                                </div>
                                <div id="assistantModelSwitcher" class="assistant-model-switcher" style="display:none">
                                    <select id="assistantModelQuickSelect" class="assistant-model-select">
                                        <optgroup label="Leggeri (~2GB)">
                                            <option value="Qwen2.5-3B-Instruct-q4f16_1-MLC">Qwen2.5 3B - Ottimo italiano</option>
                                            <option value="gemma-2-2b-it-q4f16_1-MLC">Gemma 2 2B - Veloce</option>
                                            <option value="Llama-3.2-3B-Instruct-q4f16_1-MLC">Llama 3.2 3B</option>
                                        </optgroup>
                                        <optgroup label="Potenti (~5GB)">
                                            <option value="Qwen2.5-7B-Instruct-q4f16_1-MLC">Qwen2.5 7B - Miglior italiano</option>
                                            <option value="DeepSeek-R1-Distill-Qwen-7B-q4f16_1-MLC">DeepSeek R1 7B - Ragionamento</option>
                                            <option value="Llama-3.1-8B-Instruct-q4f16_1-MLC">Llama 3.1 8B</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="assistant-header-actions">
                            <button id="assistantRefresh" class="assistant-header-btn" title="Aggiorna dati">
                                <i class="ti ti-refresh"></i>
                            </button>
                            <button id="assistantClear" class="assistant-header-btn" title="Cancella chat">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Download Progress -->
                    <div id="assistantDownload" class="assistant-download" style="display:none">
                        <div class="assistant-download-label">
                            <span id="assistantDownloadText">Download modello...</span>
                            <span id="assistantDownloadPercent">0%</span>
                        </div>
                        <div class="assistant-download-bar">
                            <div id="assistantDownloadFill" class="assistant-download-fill" style="width:0%"></div>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div id="assistantMessages" class="assistant-messages" style="flex:1; overflow-y:auto;"></div>

                    <!-- Typing Indicator -->
                    <div id="assistantTyping" class="assistant-typing" style="display:none; margin: 0 16px 8px;">
                        <span class="assistant-typing-dot"></span>
                        <span class="assistant-typing-dot"></span>
                        <span class="assistant-typing-dot"></span>
                    </div>

                    <!-- Quick Suggestions -->
                    <div id="assistantSuggestions" class="assistant-suggestions">
                        <button class="assistant-suggestion-btn" data-query="Fammi un quadro generale della situazione: progetti attivi, budget, docenti e studenti">Quadro generale</button>
                        <button class="assistant-suggestion-btn" data-query="Quali docenti hanno pagamenti in attesa da liquidare?">Docenti da liquidare</button>
                        <button class="assistant-suggestion-btn" data-query="Ci sono moduli senza docente assegnato?">Moduli scoperti</button>
                        <button class="assistant-suggestion-btn" data-query="Quali sono le prossime scadenze di bandi e contratti?">Scadenze</button>
                        <button class="assistant-suggestion-btn" data-query="Quali sono i docenti con il punteggio piu alto nelle graduatorie?">Top docenti</button>
                        <button class="assistant-suggestion-btn" data-query="Quante ore sono state rendicontate e quante approvate questo mese?">Ore rendicontate</button>
                    </div>

                    <!-- Input Area -->
                    <div class="assistant-input-area">
                        <input id="assistantInput" type="text" class="assistant-input" placeholder="Scrivi un messaggio..." disabled>
                        <button id="assistantSend" class="assistant-send-btn" disabled title="Invia">
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
