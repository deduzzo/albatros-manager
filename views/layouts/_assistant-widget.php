<?php
/**
 * Widget chat Assistente AI - Partial da includere nei layout.
 * Richiede: css/assistant.css (caricato via WebLlmAsset o manualmente)
 */

use yii\helpers\Url;

?>

<!-- FAB Button (nascosto di default, JS lo mostra se WebGPU disponibile) -->
<button id="assistantFab" class="assistant-fab" style="display:none" title="Assistente AI">
    <i class="ti ti-robot"></i>
    <span id="assistantStatusBadge" class="assistant-fab-badge status-loading loading"></span>
</button>

<!-- Chat Panel -->
<div id="assistantPanel" class="assistant-panel">
    <!-- Header -->
    <div class="assistant-header">
        <div class="assistant-header-left">
            <div class="assistant-header-avatar">
                <i class="ti ti-robot"></i>
            </div>
            <div class="assistant-header-info">
                <h4>Assistente AI</h4>
                <span id="assistantStatusText" class="assistant-header-status">Inizializzazione...</span>
            </div>
        </div>
        <div class="assistant-header-actions">
            <button id="assistantRefresh" class="assistant-header-btn" title="Ricarica modello">
                <i class="ti ti-refresh"></i>
            </button>
            <button id="assistantExpand" class="assistant-header-btn" title="Espandi">
                <i class="ti ti-arrows-maximize"></i>
            </button>
            <button id="assistantSettings" class="assistant-header-btn" title="Impostazioni">
                <i class="ti ti-settings"></i>
            </button>
            <button id="assistantClear" class="assistant-header-btn" title="Cancella chat">
                <i class="ti ti-trash"></i>
            </button>
            <button id="assistantClose" class="assistant-header-btn" title="Chiudi">
                <i class="ti ti-x"></i>
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
    <div id="assistantMessages" class="assistant-messages"></div>

    <!-- Typing Indicator -->
    <div id="assistantTyping" class="assistant-typing" style="display:none">
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
    </div>

    <!-- Input Area -->
    <div class="assistant-input-area">
        <input id="assistantInput" type="text" class="assistant-input" placeholder="Scrivi un messaggio..." disabled>
        <button id="assistantSend" class="assistant-send-btn" disabled title="Invia">
            <i class="ti ti-send"></i>
        </button>
    </div>
</div>

<!-- Unsupported Browser Banner -->
<div id="assistantUnsupported" class="assistant-unsupported" style="display:none">
    <i class="ti ti-browser-off"></i>
    <p>Il tuo browser non supporta WebGPU, necessario per l'assistente AI locale. Usa <strong>Chrome 113+</strong> o <strong>Edge 113+</strong> per questa funzionalita.</p>
</div>

<!-- Settings Modal -->
<div class="modal modal-blur fade" id="assistantSettingsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content" style="background: var(--assistant-bg); border: 1px solid var(--assistant-border); color: var(--assistant-text);">
            <div class="modal-header" style="border-bottom-color: var(--assistant-border);">
                <h5 class="modal-title">
                    <i class="ti ti-settings me-2"></i>Impostazioni Assistente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" style="color: var(--assistant-text-muted);">Modello AI</label>
                    <select id="assistantModelSelect" class="form-select" style="background: var(--assistant-bg-light); border-color: var(--assistant-border); color: var(--assistant-text);">
                        <optgroup label="Standard (keyword routing)">
                            <option value="Qwen2.5-3B-Instruct-q4f16_1-MLC" selected>Qwen2.5 3B (~2GB) - Veloce</option>
                            <option value="Qwen2.5-7B-Instruct-q4f16_1-MLC">Qwen2.5 7B (~5GB) - Migliore italiano</option>
                        </optgroup>
                        <optgroup label="Avanzato (function calling nativo)">
                            <option value="Hermes-3-Llama-3.1-8B-q4f16_1-MLC">Hermes-3 8B (~5GB) - Function calling</option>
                            <option value="Hermes-2-Pro-Mistral-7B-q4f16_1-MLC">Hermes-2 Pro 7B (~5GB) - Function calling</option>
                        </optgroup>
                    </select>
                    <small class="form-hint mt-1" style="color: var(--assistant-text-muted);">
                        I modelli "Standard" usano keyword matching per le query. I modelli "Avanzato" chiamano le API direttamente.
                    </small>
                </div>
            </div>
            <div class="modal-footer" style="border-top-color: var(--assistant-border);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" id="assistantModelApply" class="btn btn-primary">
                    <i class="ti ti-check me-1"></i>Applica
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Assistant Engine (ES Module) -->
<script type="module" src="/js/assistant/assistant-engine.js"></script>
