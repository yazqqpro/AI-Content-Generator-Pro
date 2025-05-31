<?php
header('Content-Type: application/json');
$configFile = 'config.json'; // Pastikan nama file ini konsisten

// Default settings structure tanpa field Blogger
$defaultSettings = [
    'apiKey' => '', // Gemini API Key
    'baseInstructions' => "Anda adalah seorang jurnalis desa yang berpengalaman dan ahli SEO.",
    'temperature' => 0.7,
    'maxTokensContent' => 2048,
    'maxTokensTitle' => 100, 
    'enableImageSearch' => false,
    'googleSearchApiKey' => '',
    'googleSearchCxId' => '',
    'defaultTargetAudience' => 'Masyarakat umum',
    'defaultToneStyle' => 'Informatif',
    'toneStyleOptions' => [
        "Netral", "Jurnalistik / Berita", "Formal / Akademis", "Santai / Percakapan",
        "Informatif / Edukatif", "Persuasif / Marketing", "Naratif / Cerita",
        "Deskriptif", "Optimis", "Humoris"
    ]
];

if (file_exists($configFile)) {
    $jsonContent = file_get_contents($configFile);
    if ($jsonContent === false) {
        // Gagal membaca file, mungkin karena izin
        error_log("Failed to read config.json. Check permissions. Using default settings.");
        echo json_encode(['success' => true, 'data' => $defaultSettings, 'message' => 'Gagal membaca file konfigurasi, menggunakan pengaturan default.']);
        exit;
    }
    
    $settings = json_decode($jsonContent, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($settings)) {
        // Merge dengan defaults untuk memastikan semua key yang relevan ada
        // dan untuk menambahkan default baru jika config.json lebih lama atau kurang lengkap.
        // Ini juga akan menghapus key yang tidak ada di $defaultSettings dari $settings yang dikembalikan,
        // jika $settings memiliki key ekstra yang tidak diinginkan.
        $loadedSettings = array_merge($defaultSettings, $settings);
        
        // Pastikan hanya key yang ada di $defaultSettings yang dikembalikan untuk konsistensi
        $finalSettings = [];
        foreach ($defaultSettings as $key => $defaultValue) {
            $finalSettings[$key] = isset($loadedSettings[$key]) ? $loadedSettings[$key] : $defaultValue;
        }
        
        // Khusus untuk toneStyleOptions, pastikan itu array yang valid
        if (!isset($finalSettings['toneStyleOptions']) || !is_array($finalSettings['toneStyleOptions']) || empty($finalSettings['toneStyleOptions'])) {
            $finalSettings['toneStyleOptions'] = $defaultSettings['toneStyleOptions'];
        }

        echo json_encode(['success' => true, 'data' => $finalSettings]);
    } else {
        // Jika JSON rusak atau tidak valid
        error_log("Error decoding config.json: " . json_last_error_msg() . ". Using default settings.");
        echo json_encode(['success' => true, 'data' => $defaultSettings, 'message' => 'File konfigurasi rusak atau tidak valid, menggunakan pengaturan default.']);
    }
} else {
    // Jika file config.json tidak ada, coba buat dengan default settings
    if (file_put_contents($configFile, json_encode($defaultSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        // Cobalah untuk mengatur izin agar bisa ditulis oleh web server di masa depan (jika PHP memiliki izin untuk chmod)
        // Ini mungkin tidak selalu berhasil tergantung pada konfigurasi server.
        @chmod($configFile, 0664); 
        echo json_encode(['success' => true, 'data' => $defaultSettings, 'message' => 'File konfigurasi berhasil dibuat dengan pengaturan default.']);
    } else {
        // Gagal membuat file, mungkin karena izin direktori
        error_log("Failed to create config.json. Check directory permissions. Using default settings for this session.");
        // Kembalikan default settings tapi tandai sebagai gagal membuat file
        echo json_encode(['success' => false, 'message' => 'Gagal membuat file konfigurasi. Periksa izin direktori. Menggunakan pengaturan default sementara.', 'data' => $defaultSettings]);
    }
}
?>
