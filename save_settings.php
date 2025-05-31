<?php
header('Content-Type: application/json');
$configFile = 'config.json'; // Pastikan nama file ini konsisten

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data JSON dari body request
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE); // Konversi ke array asosiatif

    // Sanitize dan validate data yang diterima (hanya yang relevan)
    $apiKey = isset($input['apiKey']) ? trim(filter_var($input['apiKey'], FILTER_SANITIZE_STRING)) : '';
    $baseInstructions = isset($input['baseInstructions']) ? trim(filter_var($input['baseInstructions'], FILTER_SANITIZE_STRING)) : '';
    
    $temperature = isset($input['temperature']) ? filter_var($input['temperature'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.0, 'max_range' => 1.0]]) : null;
    $maxTokensContent = isset($input['maxTokensContent']) ? filter_var($input['maxTokensContent'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 50]]) : null;
    // maxTokensTitle bisa ditambahkan jika ada inputnya di modal settings
    // $maxTokensTitle = isset($input['maxTokensTitle']) ? filter_var($input['maxTokensTitle'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 10]]) : 100; 

    $enableImageSearch = isset($input['enableImageSearch']) ? filter_var($input['enableImageSearch'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : false;
    $googleSearchApiKey = isset($input['googleSearchApiKey']) ? trim(filter_var($input['googleSearchApiKey'], FILTER_SANITIZE_STRING)) : '';
    $googleSearchCxId = isset($input['googleSearchCxId']) ? trim(filter_var($input['googleSearchCxId'], FILTER_SANITIZE_STRING)) : '';
    
    $defaultTargetAudience = isset($input['defaultTargetAudience']) ? trim(filter_var($input['defaultTargetAudience'], FILTER_SANITIZE_STRING)) : '';
    $defaultToneStyle = isset($input['defaultToneStyle']) ? trim(filter_var($input['defaultToneStyle'], FILTER_SANITIZE_STRING)) : '';
    
    $currentConfigData = [];
    $defaultToneOptions = [
        "Netral", "Jurnalistik / Berita", "Formal / Akademis", "Santai / Percakapan",
        "Informatif / Edukatif", "Persuasif / Marketing", "Naratif / Cerita",
        "Deskriptif", "Optimis", "Humoris"
    ];

    if (file_exists($configFile)) {
        $jsonContent = file_get_contents($configFile);
        $decoded = json_decode($jsonContent, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $currentConfigData = $decoded;
        }
    }
    // toneStyleOptions diambil dari config yang ada atau default, tidak diubah dari frontend saat ini
    $toneStyleOptions = $currentConfigData['toneStyleOptions'] ?? $defaultToneOptions;
    if (!is_array($toneStyleOptions) || empty($toneStyleOptions)) { 
        $toneStyleOptions = $defaultToneOptions;
    }
    // maxTokensTitle juga bisa diambil dari config yang ada jika tidak dikirim dari frontend
    $maxTokensTitle = $currentConfigData['maxTokensTitle'] ?? 100;
    if (isset($input['maxTokensTitle'])) { // Jika dikirim dari frontend, gunakan itu
         $validatedMaxTokensTitle = filter_var($input['maxTokensTitle'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 10]]);
         if ($validatedMaxTokensTitle !== false && $validatedMaxTokensTitle !== null) {
            $maxTokensTitle = $validatedMaxTokensTitle;
         }
    }


    // Validations
    if (empty($apiKey)) { echo json_encode(['success' => false, 'message' => 'API Key Gemini tidak boleh kosong.']); exit; }
    if ($temperature === null || $temperature === false) { echo json_encode(['success' => false, 'message' => 'Temperature tidak valid (harus antara 0.0 dan 1.0).']); exit; }
    if ($maxTokensContent === null || $maxTokensContent === false) { echo json_encode(['success' => false, 'message' => 'Max Tokens Konten tidak valid (harus angka positif minimal 50).']); exit; }
    
    if ($enableImageSearch && (empty($googleSearchApiKey) || empty($googleSearchCxId))) {
        echo json_encode(['success' => false, 'message' => 'Jika pencarian gambar otomatis aktif, API Key & CX ID Google Custom Search harus diisi.']);
        exit;
    }
    
    $newSettings = [
        'apiKey' => $apiKey,
        'baseInstructions' => $baseInstructions,
        'temperature' => (float)$temperature,
        'maxTokensContent' => (int)$maxTokensContent,
        'maxTokensTitle' => (int)$maxTokensTitle, // Pastikan ini ada
        'enableImageSearch' => (bool)$enableImageSearch, 
        'googleSearchApiKey' => $googleSearchApiKey,
        'googleSearchCxId' => $googleSearchCxId,
        'defaultTargetAudience' => $defaultTargetAudience,
        'defaultToneStyle' => $defaultToneStyle,
        'toneStyleOptions' => $toneStyleOptions 
    ];

    // Gabungkan dengan $currentConfigData untuk mempertahankan field yang mungkin tidak dikirim dari frontend
    // (misalnya jika ada field lain di config.json yang tidak dihandle oleh form settings ini)
    $finalSettings = array_merge($currentConfigData, $newSettings);


    if (file_put_contents($configFile, json_encode($finalSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        chmod($configFile, 0664); 
        echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan.']);
    } else {
        error_log("Failed to write to config.json. Check directory permissions or if file is writable.");
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengaturan ke file. Periksa izin file/direktori.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid. Hanya POST yang diizinkan.']);
}
?>
