$(document).ready(function() {
    // Elemen UI Utama
    const generateBtn = $('#generateBtn');
    const generateBtnText = $('#generateBtn').find('span#buttonText'); 
    const generateSpinner = $('#generateBtn').find('#spinner'); 
    
    const statusMessage = $('#statusMessage'); 
    const resultArea = $('#resultArea');
    const generatedTitleInput = $('#generatedTitle'); 
    // const bloggerLabelsInput = $('#bloggerLabels'); // Dihapus sebagai input utama
    
    const inputTargetAudience = $('#inputTargetAudience');
    const inputToneStyle = $('#inputToneStyle');
    const keywordsInput = $('#keywords');

    const resetFormBtn = $('#resetFormBtn');
    const copyTitleBtn = $('#copyTitleBtn');
    const copyContentBtn = $('#copyContentBtn');
    const autoScrollTarget = $('#autoScrollTarget'); 

    // Elemen untuk Auto Tags
    const autoTagsArea = $('#autoTagsArea');
    const autoTagsDisplay = $('#autoTagsDisplay');

    // Elemen Tab Pengaturan
    const saveSettingsBtn = $('#saveSettingsBtn');
    const saveSettingsBtnText = $('#saveSettingsBtn').find('#saveSettingsBtnText'); 
    const saveSettingsSpinner = $('#saveSettingsBtn').find('#saveSettingsSpinner'); 
    const settingsApiKeyInput = $('#settingsApiKey');
    const toggleApiKeyVisibilityBtn = $('#toggleApiKeyVisibility'); 
    const apiKeyIcon = $('#apiKeyIcon'); 

    const settingsBaseInstructionsInput = $('#settingsBaseInstructions');
    const settingsTemperatureInput = $('#settingsTemperature');
    const settingsMaxTokensContentInput = $('#settingsMaxTokensContent');
    const settingsEnableImageSearchCheckbox = $('#settingsEnableImageSearch');
    const googleSearchSettingsDiv = $('#googleSearchSettings');
    const settingsGoogleSearchApiKeyInput = $('#settingsGoogleSearchApiKey');
    const toggleGoogleSearchApiKeyVisibilityBtn = $('#toggleGoogleSearchApiKeyVisibility'); 
    const googleSearchApiKeyIcon = $('#googleSearchApiKeyIcon'); 
    const settingsGoogleSearchCxIdInput = $('#settingsGoogleSearchCxId');
    const settingsDefaultTargetAudienceInput = $('#settingsDefaultTargetAudience');
    const settingsDefaultToneStyleSelect = $('#settingsDefaultToneStyle');
    const settingsStatusMessage = $('#settingsStatusMessage'); 

    // Notifikasi Toast
    const liveToastEl = document.getElementById('liveToast');
    const liveToast = liveToastEl ? new bootstrap.Toast(liveToastEl, { delay: 3500 }) : null;
    const toastTitle = $('#toastTitle');
    const toastBody = $('#toastBody');

    let currentSettings = { 
        apiKey: '',
        baseInstructions: "Anda adalah seorang penulis AI yang membantu membuat konten berkualitas.",
        temperature: 0.7,
        maxTokensContent: 2048,
        maxTokensTitle: 100,
        enableImageSearch: false,
        googleSearchApiKey: '',
        googleSearchCxId: '',
        defaultTargetAudience: 'Masyarakat umum',
        defaultToneStyle: 'Informatif',
        toneStyleOptions: ["Netral", "Jurnalistik / Berita", "Formal / Akademis", "Santai / Percakapan", "Informatif / Edukatif", "Persuasif / Marketing", "Naratif / Cerita", "Deskriptif", "Optimis", "Humoris"]
    };

    let summernoteEditor = $('#summernoteEditor'); 
    let editPostId = null; 
    let settingsReady = false; 

    function showMainStatusMessage(message, type = 'info') {
        statusMessage.text(message).removeClass('alert-success alert-danger alert-info alert-warning d-none').addClass('alert-' + type).slideDown();
    }
    
    function showSettingsStatusMessage(message, type = 'info') {
        settingsStatusMessage.text(message).removeClass('alert-success alert-danger alert-info alert-warning d-none').addClass('alert-' + type).slideDown();
    }

    function showToast(title, message, type = 'info') {
        if (!liveToast) {
            console.warn("Toast component not found, cannot show toast:", title, message);
            alert(title + ": " + message); 
            return;
        }
        toastTitle.text(title);
        toastBody.text(message);
        
        $(liveToastEl).removeClass (function (index, className) {
            return (className.match (/(^|\s)text-bg-\S+/g) || []).join(' ');
        });
        let toastHeaderBgClass = 'text-bg-secondary'; 
        if (type === 'success') toastHeaderBgClass = 'text-bg-success';
        else if (type === 'error') toastHeaderBgClass = 'text-bg-danger';
        else if (type === 'info') toastHeaderBgClass = 'text-bg-info';
        else if (type === 'warning') toastHeaderBgClass = 'text-bg-warning';
        $(liveToastEl).addClass(toastHeaderBgClass);
        liveToast.show();
    }
    
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    summernoteEditor.summernote({
        placeholder: 'Mulai menulis konten di sini...',
        tabsize: 2,
        height: 280, 
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']], 
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
    console.log('Summernote initialized.');

    function populateToneStyleDropdown(selectElement, options, selectedValue) {
        selectElement.empty();
        if (!Array.isArray(options) || options.length === 0) {
            options = ["Netral", "Informatif"]; 
        }
        selectElement.append($('<option>', { value: '', text: 'Pilih Gaya Bahasa...' }));
        options.forEach(function(option) {
            selectElement.append($('<option>', { value: option, text: option }));
        });
        if (selectedValue) { 
            selectElement.val(selectedValue);
        }
    }
    
    function loadPostForEditing(postId) { 
        console.warn("loadPostForEditing untuk ID:", postId, "tidak diimplementasikan karena tidak ada sumber data (Blogger dihapus).");
        showMainStatusMessage('Mode edit saat ini tidak dapat memuat data postingan lama.', 'warning');
        generatedTitleInput.val('Contoh Judul untuk Edit ID: ' + postId); 
        summernoteEditor.summernote('code', '<p>Konten contoh untuk postingan yang diedit dengan ID: ' + postId + '</p><p>Silakan ganti dengan konten sebenarnya atau generate ulang.</p>');
        // bloggerLabelsInput.val('contoh,label,edit'); // Input label manual sudah dihapus
        autoTagsDisplay.empty().append('<span class="tag-badge">contoh</span> <span class="tag-badge">edit</span>'); // Contoh tag
        autoTagsArea.show();
        resultArea.slideDown();
    }

    function fetchSettings() {
        console.log("Fetching settings...");
        statusMessage.text('Memuat pengaturan...').removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-info').show();
        $.ajax({
            url: 'get_settings.php', 
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("Settings fetched:", response);
                if (response && response.success && response.data) {
                    currentSettings = { ...currentSettings, ...response.data }; 
                    settingsApiKeyInput.val(currentSettings.apiKey);
                    settingsBaseInstructionsInput.val(currentSettings.baseInstructions);
                    settingsTemperatureInput.val(currentSettings.temperature);
                    settingsMaxTokensContentInput.val(currentSettings.maxTokensContent);
                    settingsEnableImageSearchCheckbox.prop('checked', currentSettings.enableImageSearch);
                    googleSearchSettingsDiv.toggle(currentSettings.enableImageSearch);
                    settingsGoogleSearchApiKeyInput.val(currentSettings.googleSearchApiKey);
                    settingsGoogleSearchCxIdInput.val(currentSettings.googleSearchCxId);
                    settingsDefaultTargetAudienceInput.val(currentSettings.defaultTargetAudience);
                    
                    populateToneStyleDropdown(settingsDefaultToneStyleSelect, currentSettings.toneStyleOptions, currentSettings.defaultToneStyle);
                    populateToneStyleDropdown(inputToneStyle, currentSettings.toneStyleOptions, currentSettings.defaultToneStyle); 
                    inputTargetAudience.val(currentSettings.defaultTargetAudience); 
                    statusMessage.text('Pengaturan berhasil dimuat.').removeClass('alert-info alert-danger alert-warning').addClass('alert-success').delay(2000).slideUp();
                } else { 
                    console.warn("Could not fetch settings or settings data invalid, using defaults. Response:", response);
                    showToast("Peringatan", response.message || "Gagal memuat pengaturan, menggunakan default.", "warning");
                    statusMessage.text(response.message || "Gagal memuat pengaturan, menggunakan default.").removeClass('alert-info alert-success alert-danger').addClass('alert-warning').delay(3000).slideUp();
                    populateToneStyleDropdown(settingsDefaultToneStyleSelect, currentSettings.toneStyleOptions, currentSettings.defaultToneStyle);
                    populateToneStyleDropdown(inputToneStyle, currentSettings.toneStyleOptions, currentSettings.defaultToneStyle);
                    inputTargetAudience.val(currentSettings.defaultTargetAudience);
                }
                settingsReady = true; 
                
                editPostId = getUrlParameter('postId'); 
                if (editPostId) {
                    generateBtnText.text('Generate Ulang (Opsional)'); 
                    loadPostForEditing(editPostId); 
                }
            },
            error: function(jqXHR, textStatus, errorThrown) { /* ... (error handling sama seperti sebelumnya) ... */ }
        });
    }
    fetchSettings(); 

    settingsEnableImageSearchCheckbox.on('change', function() { 
        googleSearchSettingsDiv.slideToggle(this.checked); 
    });

    function togglePasswordVisibility(inputElement, iconElement) { /* ... (fungsi tetap sama) ... */ }
    toggleApiKeyVisibilityBtn.on('click', function() { togglePasswordVisibility(settingsApiKeyInput, apiKeyIcon); });
    toggleGoogleSearchApiKeyVisibilityBtn.on('click', function() { togglePasswordVisibility(settingsGoogleSearchApiKeyInput, googleSearchApiKeyIcon); });
    
    saveSettingsBtn.on('click', function() { /* ... (fungsi saveSettingsBtn tetap sama seperti sebelumnya, pastikan saveSettingsBtnText dan saveSettingsSpinner ada di HTML atau hapus referensinya) ... */ });

    generateBtn.on('click', function() {
        const keywords = keywordsInput.val().trim();
        const targetAudience = inputTargetAudience.val().trim() || currentSettings.defaultTargetAudience;
        const toneStyle = inputToneStyle.val() || currentSettings.defaultToneStyle;

        if (!keywords) { /* ... (validasi keyword) ... */ return; }
        if (!currentSettings.apiKey) { /* ... (validasi API Key) ... */ return; }

        console.log("Generate button clicked. Keywords:", keywords);
        generateBtnText.hide(); 
        generateSpinner.show();
        generateBtn.prop('disabled', true);
        showMainStatusMessage('Sedang menghasilkan konten... Mohon tunggu.', 'info');
        
        resultArea.slideUp(200);
        autoTagsArea.hide(); 
        autoTagsDisplay.empty(); 
        
        $.ajax({
            url: 'generate_content.php', type: 'POST',
            data: { 
                apiKey: currentSettings.apiKey, 
                baseInstructions: currentSettings.baseInstructions,
                temperature: currentSettings.temperature, 
                maxTokensContent: currentSettings.maxTokensContent,
                maxTokensTitle: currentSettings.maxTokensTitle,
                enableImageSearch: currentSettings.enableImageSearch, 
                googleSearchApiKey: currentSettings.googleSearchApiKey, 
                googleSearchCxId: currentSettings.googleSearchCxId,     
                targetAudience: targetAudience,
                toneStyle: toneStyle,
                keywords: keywords,
                generateAutoTags: true // Kirim flag ini ke backend
            },
            dataType: 'json',
            success: function(response) {
                console.log("Full response from generate_content.php:", response); 
                if (response.success && response.data) {
                    generatedTitleInput.val(response.data.judul || ''); 
                    let contentHtml = response.data.isi_berita || '<p>Tidak ada konten diterima.</p>';
                    contentHtml = contentHtml.replace(/^```html\s*/i, '').replace(/\s*```$/, '');
                    contentHtml = contentHtml.replace(/^```\s*/i, '').replace(/\s*```$/, '');

                    if (response.data.imageUrl && response.data.imageUrl !== 'NO_IMAGE_FOUND' && currentSettings.enableImageSearch) {
                        const imageHtmlToInsert = `<p style="text-align:center;"><img src="${response.data.imageUrl}" alt="Saran Gambar untuk ${keywords}" style="max-width:100%; height:auto; border-radius:0.375rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></p>`;
                        const firstParagraphEndIndex = contentHtml.toLowerCase().indexOf('</p>');
                        if (firstParagraphEndIndex !== -1) {
                            contentHtml = contentHtml.substring(0, firstParagraphEndIndex + 4) + imageHtmlToInsert + contentHtml.substring(firstParagraphEndIndex + 4);
                        } else {
                            contentHtml = imageHtmlToInsert + contentHtml; 
                        }
                    } else if (currentSettings.enableImageSearch && response.data.imageSearchError) {
                         contentHtml += `<p class="text-sm text-muted"><em>[Info: Pencarian gambar otomatis gagal: ${response.data.imageSearchError}]</em></p>`;
                    }

                    summernoteEditor.summernote('code', contentHtml);
                    
                    // Tampilkan Auto Tags
                    if (response.data.autoTags && Array.isArray(response.data.autoTags) && response.data.autoTags.length > 0) {
                        response.data.autoTags.forEach(function(tag) {
                            autoTagsDisplay.append(`<span class="tag-badge me-1 mb-1">${tag}</span>`);
                        });
                        autoTagsArea.slideDown();
                    } else {
                        autoTagsArea.hide();
                    }
                    
                    resultArea.slideDown(300); 
                    showMainStatusMessage(response.message || 'Konten berhasil dibuat!', 'success');
                    showToast('Sukses!', 'Konten berhasil digenerate!', 'success');
                    
                    if (autoScrollTarget.length) {
                        $('html, body').animate({ scrollTop: autoScrollTarget.offset().top - 80 }, 500);
                    }
                    editPostId = null; 
                    keywordsInput.prop('disabled', false); 

                } else { /* ... (error handling) ... */ }
            },
            error: function(jqXHR, textStatus, errorThrown) { /* ... (error handling) ... */ },
            complete: function() { /* ... (complete handling) ... */ }
        });
    });

    resetFormBtn.on('click', function() {
        keywordsInput.val('').prop('disabled', false);
        inputTargetAudience.val(currentSettings.defaultTargetAudience); 
        inputToneStyle.val(currentSettings.defaultToneStyle); 
        generatedTitleInput.val('');
        summernoteEditor.summernote('reset'); 
        // bloggerLabelsInput.val(''); // Dihapus
        autoTagsDisplay.empty();    
        autoTagsArea.hide();        
        resultArea.slideUp(200);
        statusMessage.text('').removeClass('alert-success alert-danger alert-info alert-warning').hide();
        editPostId = null; 
        generateBtnText.text('Generate Konten'); 
        showToast('Info', 'Form telah direset.', 'info');
        console.log("Form reset.");
    });

    function copyToClipboard(text, type) { /* ... (fungsi tetap sama) ... */ }
    function fallbackCopyToClipboard(text, type) { /* ... (fungsi tetap sama) ... */ }
    copyTitleBtn.on('click', function() { /* ... (fungsi tetap sama) ... */ });
    copyContentBtn.on('click', function() { /* ... (fungsi tetap sama, pastikan mengambil dari Summernote) ... */ });
});