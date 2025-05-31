<?php
header('Content-Type: application/json');

// --- Fungsi untuk memanggil Gemini API ---
function call_gemini_api(string $apiKey, string $promptText, float $temperature, int $maxOutputTokens): array {
    if (empty($apiKey)) {
        return ['error' => ['message' => 'API Key Gemini tidak disediakan atau kosong.']];
    }
    $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey; // Menggunakan model flash terbaru
    // Atau jika Anda ingin tetap dengan gemini-2.0-flash:
    // $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;
    
    $payload = [
        'contents' => [['parts' => [['text' => $promptText]]]],
        'generationConfig' => ['temperature' => (float)$temperature, 'maxOutputTokens' => (int)$maxOutputTokens]
    ];
    $ch = curl_init($geminiApiUrl);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_TIMEOUT => 180 // Timeout sedikit lebih lama
    ]);
    $apiResponse = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); $curlError = curl_error($ch); curl_close($ch);
    if ($curlError) { error_log("cURL Error (Gemini): " . $curlError); return ['error' => ['message' => 'Kesalahan cURL Gemini: ' . $curlError]]; }
    $decodedResponse = json_decode($apiResponse, true);
    if ($httpCode !== 200 || isset($decodedResponse['error'])) {
        error_log("API Error (Gemini HTTP {$httpCode}): " . $apiResponse);
        return ['error' => $decodedResponse['error'] ?? ['message' => 'Error API Gemini HTTP: ' . $httpCode, 'details' => $apiResponse]];
    }
    if (!isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
        if (isset($decodedResponse['candidates'][0]['finishReason']) && $decodedResponse['candidates'][0]['finishReason'] !== 'STOP') {
            error_log("Konten Gemini diblokir/tidak selesai. Alasan: " . $decodedResponse['candidates'][0]['finishReason'] . ". Respons: " . $apiResponse);
            return ['error' => ['message' => 'Konten Gemini mungkin diblokir atau tidak selesai. Alasan: ' . $decodedResponse['candidates'][0]['finishReason']]];
        }
        error_log("Struktur respons API Gemini tidak diharapkan: " . $apiResponse);
        return ['error' => ['message' => 'Struktur respons API Gemini tidak diharapkan.', 'details' => $apiResponse]];
    }
    return $decodedResponse;
}

// --- Fungsi untuk memanggil Google Custom Search API (jika masih digunakan) ---
function call_google_custom_search_api(string $apiKey, string $cxId, string $query): array {
    if (empty($apiKey) || empty($cxId)) {
        return ['error' => true, 'message' => 'API Key atau CX ID Google Custom Search belum diatur.'];
    }
    $params = ['key' => $apiKey, 'cx' => $cxId, 'q' => $query, 'searchType' => 'image', 'num' => 1, 'safe' => 'active', 'imgSize' => 'large'];
    $url = 'https://www.googleapis.com/customsearch/v1?' . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_SSL_VERIFYPEER => true]);
    $response = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); $curlError = curl_error($ch); curl_close($ch);

    if ($curlError || $httpCode !== 200) {
        error_log("Google Search API Error: " . ($curlError ?: "HTTP {$httpCode}") . " URL: " . $url);
        return ['error' => true, 'message' => 'Gagal mengambil data dari Google Search API.', 'details' => $curlError ?: "HTTP {$httpCode}"];
    }
    $data = json_decode($response, true);
    if (empty($data['items'][0]['link'])) {
        return ['error' => true, 'message' => 'Tidak ada hasil gambar ditemukan dari Google Search untuk query: ' . htmlspecialchars($query)];
    }
    return ['error' => false, 'image_url' => $data['items'][0]['link'], 'title' => $data['items'][0]['title'] ?? 'Gambar terkait'];
}


// --- Logika Utama ---
$response = ['success' => false, 'message' => '', 'data' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = isset($_POST['apiKey']) ? trim(filter_var($_POST['apiKey'], FILTER_SANITIZE_STRING)) : '';
    $baseInstructions = isset($_POST['baseInstructions']) ? trim(filter_var($_POST['baseInstructions'], FILTER_SANITIZE_STRING)) : 'Anda adalah seorang penulis AI.';
    $keywords = isset($_POST['keywords']) ? trim(filter_var($_POST['keywords'], FILTER_SANITIZE_STRING)) : '';
    $temperature = isset($_POST['temperature']) ? (float)$_POST['temperature'] : 0.7;
    $maxTokensContent = isset($_POST['maxTokensContent']) ? (int)$_POST['maxTokensContent'] : 2048;
    $maxTokensTitle = isset($_POST['maxTokensTitle']) ? (int)$_POST['maxTokensTitle'] : 100; 

    $enableImageSearch = isset($_POST['enableImageSearch']) && filter_var($_POST['enableImageSearch'], FILTER_VALIDATE_BOOLEAN);
    $googleSearchApiKey = isset($_POST['googleSearchApiKey']) ? trim(filter_var($_POST['googleSearchApiKey'], FILTER_SANITIZE_STRING)) : '';
    $googleSearchCxId = isset($_POST['googleSearchCxId']) ? trim(filter_var($_POST['googleSearchCxId'], FILTER_SANITIZE_STRING)) : '';
    $targetAudience = isset($_POST['targetAudience']) ? trim(filter_var($_POST['targetAudience'], FILTER_SANITIZE_STRING)) : '';
    $toneStyle = isset($_POST['toneStyle']) ? trim(filter_var($_POST['toneStyle'], FILTER_SANITIZE_STRING)) : '';
    $generateAutoTags = isset($_POST['generateAutoTags']) && filter_var($_POST['generateAutoTags'], FILTER_VALIDATE_BOOLEAN);


    if (empty($apiKey)) { $response['message'] = 'API Key Gemini kosong.'; echo json_encode($response); exit; }
    if (empty($keywords)) { $response['message'] = 'Kata kunci kosong.'; echo json_encode($response); exit; }

    // --- Generate Judul ---
    $promptJudul = "Anda adalah seorang redaktur berita. Berdasarkan kata kunci: \"{$keywords}\". Buat SATU judul berita (40-70 karakter), menarik, faktual. Langsung berikan judulnya.\nContoh (kata kunci 'irigasi desa'): Warga Bersihkan Irigasi Demi Pertanian Lancar\nJudul untuk \"{$keywords}\":";
    $apiResponseJudul = call_gemini_api($apiKey, $promptJudul, $temperature, $maxTokensTitle);
    $generatedTitle = '';
    if (isset($apiResponseJudul['error'])) {
        $response['message'] = 'Judul: ' . ($apiResponseJudul['error']['message'] ?? 'Error.');
    } elseif (isset($apiResponseJudul['candidates'][0]['content']['parts'][0]['text'])) {
        $generatedTitle = trim(preg_replace(['/^```[a-zA-Z]*\s*\n?/', '/\s*\n?```$/'], '', $apiResponseJudul['candidates'][0]['content']['parts'][0]['text']));
        $response['data']['judul'] = $generatedTitle;
    } else { $response['message'] = 'Judul: Format API tidak sesuai.'; }

    // --- Generate Isi Berita ---
    $contentCustomizations = "";
    if (!empty($targetAudience)) $contentCustomizations .= " Target audiens: " . htmlspecialchars($targetAudience) . ".";
    if (!empty($toneStyle) && $toneStyle !== 'Netral' && $toneStyle !== '') $contentCustomizations .= " Gunakan gaya bahasa dan nada " . htmlspecialchars($toneStyle) . ".";
    
    $promptIsiBerita = $baseInstructions . "\n\nKata kunci: \"{$keywords}\"." . $contentCustomizations . "\nTulis artikel berita lengkap (sekitar 300-500 kata, beberapa paragraf), faktual, objektif. Ikuti struktur jurnalistik jika relevan (Lead 5W+1H, Body, Penutup). Format output HTML sederhana (<p>,<strong>,<em>,<ul><li>). Jangan gunakan tag heading (h1,h2) dalam konten utama artikel. Jangan sertakan kalimat pembuka seperti 'Berikut adalah artikel...' atau 'Tentu, ini dia...'. Langsung ke konten utama.";
    $apiResponseIsi = call_gemini_api($apiKey, $promptIsiBerita, $temperature, $maxTokensContent);
    $cleanedContentTextForTags = ''; // Untuk prompt tags
    if (isset($apiResponseIsi['error'])) {
        $response['message'] .= (empty($response['message']) ? '' : ' | ') . 'Isi: ' . ($apiResponseIsi['error']['message'] ?? 'Error.');
    } elseif (isset($apiResponseIsi['candidates'][0]['content']['parts'][0]['text'])) {
        $rawContentText = $apiResponseIsi['candidates'][0]['content']['parts'][0]['text'];
        // Pembersihan sudah diinstruksikan di prompt, tapi jaga-jaga
        $cleanedContentText = trim(preg_replace(['/^```html\s*\n?/', '/\s*\n?```$/', '/^```\s*\n?/', '/\s*\n?```$/'], '', $rawContentText));
        $cleanedContentTextForTags = strip_tags($cleanedContentText); // Versi teks murni untuk prompt tags
        $response['data']['isi_berita'] = $cleanedContentText;
    } else { $response['message'] .= (empty($response['message']) ? '' : ' | ') . 'Isi: Format API tidak sesuai.'; }

    // --- Generate Auto Tags (jika diminta dan konten ada) ---
    $response['data']['autoTags'] = [];
    if ($generateAutoTags && !empty($cleanedContentTextForTags)) {
        $promptTags = "Berdasarkan teks berikut, berikan maksimal 5 tag yang paling relevan. Setiap tag idealnya terdiri dari SATU kata (pilih kata kunci inti dari frasa jika perlu). Pisahkan setiap tag dengan koma. Jangan gunakan nomor atau bullet point. Hanya daftar tag yang dipisahkan koma.\n\nTeks (potongan awal):\n\"" . mb_substr($cleanedContentTextForTags, 0, 700) . "...\"\n\nTags:";
        $apiResponseTags = call_gemini_api($apiKey, $promptTags, $temperature, 60); // Max token lebih kecil untuk tags

        if (isset($apiResponseTags['error'])) {
            error_log("Gemini AutoTags API Error: " . ($apiResponseTags['error']['message'] ?? 'Unknown error'));
            $response['data']['autoTagsError'] = 'Gagal generate tags: ' . ($apiResponseTags['error']['message'] ?? 'Error.');
        } elseif (isset($apiResponseTags['candidates'][0]['content']['parts'][0]['text'])) {
            $rawTagsText = $apiResponseTags['candidates'][0]['content']['parts'][0]['text'];
            $cleanedTagsText = trim(preg_replace(['/^```[a-zA-Z]*\s*\n?/', '/\s*\n?```$/', '/^(Tags:|Saran tags:|Berikut adalah tags:)\s*/i'], '', $rawTagsText));
            $tagsArray = array_filter(array_map('trim', explode(',', $cleanedTagsText)));
            
            $singleWordTags = [];
            foreach ($tagsArray as $tag) {
                // Ambil hanya kata pertama jika tag adalah frasa, atau biarkan jika sudah satu kata
                $words = explode(' ', $tag);
                if (!empty($words[0])) {
                    $singleWordTags[] = $words[0];
                }
            }
            $response['data']['autoTags'] = array_slice(array_unique($singleWordTags), 0, 5);
        } else {
            $response['data']['autoTagsError'] = 'Gagal memproses respons tags dari API.';
        }
    }


    // --- Pencarian Gambar (jika aktif dan judul ada) ---
    $response['data']['imageUrl'] = 'NO_IMAGE_FOUND'; 
    $response['data']['imageSearchError'] = '';
    if ($enableImageSearch && !empty($generatedTitle) && !empty($googleSearchApiKey) && !empty($googleSearchCxId)) {
        $imageQueryResult = call_google_custom_search_api($googleSearchApiKey, $googleSearchCxId, $generatedTitle);
        if (!$imageQueryResult['error'] && !empty($imageQueryResult['image_url'])) {
            $response['data']['imageUrl'] = $imageQueryResult['image_url'];
        } else {
            $response['data']['imageSearchError'] = $imageQueryResult['message'] ?? 'Gagal mencari gambar.';
        }
    } elseif ($enableImageSearch) {
        $response['data']['imageSearchError'] = 'Pencarian gambar aktif tapi API Key/CX ID Google Search tidak lengkap atau judul tidak ada.';
    }

    if (!empty($response['data']['judul']) || !empty($response['data']['isi_berita'])) {
        $response['success'] = true;
        if (empty($response['message'])) { $response['message'] = 'Konten berhasil dibuat.';}
    } else {
        if (empty($response['message'])) { $response['message'] = 'Gagal menghasilkan konten.';}
    }

} else { $response['message'] = 'Metode request tidak valid.'; }
echo json_encode($response);
exit;
?>