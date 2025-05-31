<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Content Generator Pro (Auto Tag)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #eef2f7; 
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }
        .container-main { max-width: 860px; }
        .app-card {
            background-color: #ffffff;
            border-radius: 0.75rem; 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08); 
            padding: 2rem; 
            transition: all 0.3s ease-in-out;
        }
        .app-card-header { border-bottom: 1px solid #dee2e6; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .app-title { font-weight: 700; color: #333; }
        .nav-tabs .nav-link { color: #525f7f; font-weight: 500; border: 0; border-bottom: 3px solid transparent; padding: 0.75rem 1.25rem; }
        .nav-tabs .nav-link.active { color: #5e72e4; border-bottom-color: #5e72e4; font-weight: 600; }
        .nav-tabs .nav-link:hover { border-bottom-color: #adb5bd; }
        .btn-primary-gradient { background-image: linear-gradient(to right, #6777ef, #8965e0); border: none; color: white; }
        .btn-primary-gradient:hover { background-image: linear-gradient(to right, #525ddc, #7149c7); color: white; }
        .btn-secondary-outline { border-color: #6777ef; color: #6777ef; }
        .btn-secondary-outline:hover { background-color: #6777ef; color: white; }
        .form-label { font-weight: 500; color: #344767; }
        .form-control:focus, .form-select:focus { border-color: #a3b7ff; box-shadow: 0 0 0 0.25rem rgba(103, 119, 239, 0.25); }
        .note-editor.note-frame { border-radius: 0.375rem; border: 1px solid #cad1d7; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .note-editable { min-height: 250px; padding: 1rem; background-color: #fff; }
        .input-group-text { cursor: pointer; background-color: #f8f9fa; }
        .input-group-text:hover { background-color: #e9ecef; }
        .toast-container { z-index: 1090; }
        .tag-badge {
            background-color: #e9ecef; 
            color: #495057;
            padding: 0.3em 0.75em;
            border-radius: 0.25rem;
            font-size: 0.875em;
            transition: background-color 0.2s ease;
            margin-bottom: 0.25rem; /* Tambahkan margin bawah untuk tag */
        }
        .tag-badge:hover {
            background-color: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="container container-main">
        <div id="app-content" class="app-card">
            <header class="app-card-header text-center">
                <h1 class="app-title h2">
                    <i class="fas fa-magic-wand-sparkles me-2"></i>AI Content Generator Pro
                </h1>
                <p class="text-muted small mt-1">Buat konten berkualitas tinggi dengan mudah dan cepat!</p>
            </header>

            <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="generator-tab-btn" data-bs-toggle="tab" data-bs-target="#generatorTabContent" type="button" role="tab" aria-controls="generatorTabContent" aria-selected="true">
                        <i class="fas fa-lightbulb me-2"></i>Generator
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="settings-tab-btn" data-bs-toggle="tab" data-bs-target="#settingsTabContent" type="button" role="tab" aria-controls="settingsTabContent" aria-selected="false">
                        <i class="fas fa-cog me-2"></i>Pengaturan
                    </button>
                </li>
        
            </ul>

            <div class="tab-content" id="mainTabsContent">
                <div class="tab-pane fade show active" id="generatorTabContent" role="tabpanel" aria-labelledby="generator-tab-btn">
                    <div class="mb-3">
                        <label for="keywords" class="form-label">Kata Kunci Utama</label>
                        <input type="text" class="form-control form-control-lg" id="keywords" placeholder="cth: Manfaat teknologi AI dalam pendidikan">
                        <div class="form-text">Masukkan topik atau kata kunci utama untuk konten Anda.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="inputTargetAudience" class="form-label">Target Audiens</label>
                            <input type="text" class="form-control" id="inputTargetAudience" placeholder="cth: Mahasiswa, Profesional Muda">
                        </div>
                        <div class="col-md-6">
                            <label for="inputToneStyle" class="form-label">Gaya Bahasa</label>
                            <select class="form-select" id="inputToneStyle"></select>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-sm-flex justify-content-start mb-3">
                        <button class="btn btn-primary-gradient btn-lg px-4" id="generateBtn" type="button">
                            <span id="buttonText"><i class="fas fa-rocket me-2"></i>Generate Konten</span>
                            <span class="spinner spinner-border spinner-border-sm ms-2" id="spinner" role="status" aria-hidden="true" style="display: none;"></span>
                        </button>
                        <button class="btn btn-outline-secondary btn-lg px-4" id="resetFormBtn" type="button">
                            <i class="fas fa-eraser me-2"></i>Konten Baru
                        </button>
                    </div>
                    <div id="statusMessage" class="alert alert-info mt-3" role="alert" style="display: none;"></div>
                
                    <div id="resultArea" class="mt-4 pt-4 border-top" style="display: none;">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="generatedTitle" class="form-label h5">Judul Hasil Generate</label>
                                <button class="btn btn-sm btn-outline-secondary" id="copyTitleBtn" title="Salin Judul">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <input type="text" class="form-control form-control-lg bg-light" id="generatedTitle">
                        </div>
                        <div id="autoTagsArea" class="mb-3" style="display: none;">
                            <h3 class="h5 form-label">Saran Tag Otomatis:</h3>
                            <div id="autoTagsDisplay" class="d-flex flex-wrap gap-2 mt-1">
                                {/* Tags akan diisi oleh JavaScript */}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                             <div class="d-flex justify-content-between align-items-center mb-1">
                                 <label for="summernoteEditor" class="form-label h5">Konten Hasil Generate</label>
                                <button class="btn btn-sm btn-outline-secondary" id="copyContentBtn" title="Salin Konten">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div id="summernoteEditorContainer" class="mt-1">
                                <div id="summernoteEditor"></div>
                            </div>
                        </div>
                        <div id="autoScrollTarget" class="py-1"></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="settingsTabContent" role="tabpanel" aria-labelledby="settings-tab-btn">
                    <div class="settings-scroll-area" style="max-height: 70vh; overflow-y: auto; padding-right: 1rem;">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><i class="fas fa-key me-2"></i>Pengaturan API Gemini</h5>
                                <div class="mb-3">
                                    <label for="settingsApiKey" class="form-label">Gemini API Key</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="settingsApiKey" placeholder="Masukkan API Key Gemini Anda">
                                        <span class="input-group-text" id="toggleApiKeyVisibility"><i class="fas fa-eye" id="apiKeyIcon"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                             <div class="card-body">
                                <h5 class="card-title mb-3"><i class="fas fa-robot me-2"></i>Pengaturan AI Konten</h5>
                                <div class="mb-3">
                                    <label for="settingsBaseInstructions" class="form-label">Instruksi Dasar AI (Persona)</label>
                                    <textarea class="form-control" id="settingsBaseInstructions" rows="3" placeholder="Contoh: Anda adalah seorang ahli marketing..."></textarea>
                                    <div class="form-text">Instruksi ini akan menjadi dasar setiap pembuatan konten.</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="settingsTemperature" class="form-label">Temperature (0.0 - 1.0)</label>
                                        <input type="number" class="form-control" id="settingsTemperature" step="0.1" min="0" max="1" placeholder="cth: 0.7">
                                        <div class="form-text">Kontrol kreativitas AI.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="settingsMaxTokensContent" class="form-label">Max Tokens Konten</label>
                                        <input type="number" class="form-control" id="settingsMaxTokensContent" step="1" min="50" placeholder="cth: 2048">
                                        <div class="form-text">Panjang maksimum konten.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><i class="fas fa-image me-2"></i>Pengaturan Pencarian Gambar</h5>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="settingsEnableImageSearch">
                                    <label class="form-check-label" for="settingsEnableImageSearch">Aktifkan Pencarian Gambar Otomatis</label>
                                </div>
                                <div id="googleSearchSettings" class="space-y-3" style="display:none;">
                                    <div class="mb-3">
                                        <label for="settingsGoogleSearchApiKey" class="form-label">Google Custom Search API Key</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="settingsGoogleSearchApiKey" placeholder="API Key Google Search">
                                            <span class="input-group-text" id="toggleGoogleSearchApiKeyVisibility"><i class="fas fa-eye" id="googleSearchApiKeyIcon"></i></span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="settingsGoogleSearchCxId" class="form-label">Google Custom Search CX ID</label>
                                        <input type="text" class="form-control" id="settingsGoogleSearchCxId" placeholder="CX ID Google Search">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><i class="fas fa-sliders-h me-2"></i>Pengaturan Konten Default</h5>
                                <div class="mb-3">
                                    <label for="settingsDefaultTargetAudience" class="form-label">Target Audiens Default</label>
                                    <input type="text" class="form-control" id="settingsDefaultTargetAudience" placeholder="cth: Masyarakat umum">
                                </div>
                                <div>
                                    <label for="settingsDefaultToneStyle" class="form-label">Gaya Bahasa Default</label>
                                    <select class="form-select" id="settingsDefaultToneStyle"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-top">
                        <button class="btn btn-success btn-lg w-100" id="saveSettingsBtn" type="button">
                             <span class="spinner-border spinner-border-sm me-2" id="saveSettingsSpinner" role="status" aria-hidden="true" style="display: none;"></span>
                            <span id="saveSettingsBtnText"><i class="fas fa-save me-2"></i>Simpan Pengaturan</span>
                        </button>
                        <div id="settingsStatusMessage" class="alert mt-3" role="alert" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto" id="toastTitle">Notifikasi</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body" id="toastBody">
                    Pesan toast.
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote.min.js"></script>
    <script src="script.js"></script>
</body>
</html>