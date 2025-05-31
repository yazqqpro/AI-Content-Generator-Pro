# AI Content Generator Pro
![Screenshot 2025-05-31 101942](https://github.com/user-attachments/assets/20909831-0e7d-4cbf-941d-558ceb2399e5)

Selamat datang di AI Content Generator Pro! Aplikasi web ini dirancang untuk membantu Anda membuat konten artikel berkualitas tinggi dengan bantuan kecerdasan buatan (AI) dari Google Gemini. Dilengkapi dengan antarmuka yang modern dan profesional menggunakan Bootstrap dan editor Summernote, aplikasi ini memudahkan proses pembuatan konten mulai dari ide hingga teks jadi.

## Deskripsi Singkat

AI Content Generator Pro adalah alat berbasis web yang memungkinkan pengguna memasukkan kata kunci, target audiens, dan gaya bahasa untuk menghasilkan judul artikel, konten lengkap, dan saran tag otomatis. Aplikasi ini juga memiliki fitur pencarian gambar terkait (jika diaktifkan) menggunakan Google Custom Search API dan menyediakan antarmuka pengaturan yang mudah digunakan untuk mengelola kunci API dan preferensi lainnya.

## Fitur Utama

* **Generator Konten AI**: Menghasilkan judul dan isi artikel berdasarkan input pengguna menggunakan Google Gemini API.
* **Saran Tag Otomatis**: AI akan memberikan saran hingga 5 tag relevan (masing-masing satu kata) berdasarkan konten yang dihasilkan.
* **Editor WYSIWYG**: Menggunakan Summernote untuk pengalaman mengedit konten yang kaya fitur dan intuitif.
* **Desain Modern & Profesional**: Antarmuka pengguna dibangun dengan Bootstrap 5, menampilkan skema warna yang harmonis, gradien, dan ikon yang konsisten.
* **Sistem Tab**: Navigasi mudah antara halaman Generator dan Pengaturan.
* **Pengaturan yang Dapat Dikonfigurasi**:
    * API Key untuk Google Gemini.
    * Pengaturan AI (Instruksi Dasar/Persona, Temperature, Max Tokens).
    * Opsi untuk mengaktifkan pencarian gambar otomatis via Google Custom Search API (memerlukan API Key & CX ID tambahan).
    * Pengaturan default untuk Target Audiens dan Gaya Bahasa.
* **Interaksi Pengguna yang Ditingkatkan**:
    * Tombol "Salin" untuk judul dan konten.
    * Tombol "Konten Baru" untuk mereset form dengan cepat.
    * Toggle untuk melihat/menyembunyikan API key di pengaturan.
    * Notifikasi toast untuk feedback aksi pengguna.
    * Animasi dan transisi halus untuk pengalaman pengguna yang lebih baik.
* **Responsif**: Tampilan optimal di berbagai ukuran layar (desktop, tablet, mobile).
* **Pengelolaan Pengaturan via File**: Pengaturan disimpan dalam file `config.json` di sisi server.

## Teknologi yang Digunakan

* **Frontend**:
    * HTML5
    * CSS3 (Bootstrap 5)
    * JavaScript (jQuery 3.7.1)
    * Summernote WYSIWYG Editor
    * Font Awesome (untuk ikon)
* **Backend**:
    * PHP (untuk menangani permintaan AJAX dan interaksi dengan API)
* **API Eksternal**:
    * Google Gemini API (untuk generasi teks)
    * Google Custom Search API (opsional, untuk pencarian gambar)

## Struktur File Utama
├── index.php               # Halaman utama aplikasi (Generator & Pengaturan)
├── script.js               # Logika JavaScript utama untuk index.php
├── generate_content.php    # Skrip PHP untuk memanggil Gemini API dan menghasilkan konten
├── get_settings.php        # Skrip PHP untuk mengambil pengaturan dari config.json
├── save_settings.php       # Skrip PHP untuk menyimpan pengaturan ke config.json
└── config.json             # File konfigurasi (akan dibuat otomatis jika belum ada)

## Cara Kerja Aplikasi

1.  **Input Pengguna**: Pengguna membuka `index.php` dan memasukkan kata kunci, target audiens (opsional), dan gaya bahasa (opsional) di tab "Generator".
2.  **Permintaan Generate**: Saat tombol "Generate Konten" diklik, `script.js` mengirimkan data input tersebut beserta pengaturan yang relevan (seperti API Key Gemini, instruksi dasar AI, dll., yang diambil dari `currentSettings` yang dimuat dari `config.json`) ke `generate_content.php` melalui permintaan AJAX (POST).
3.  **Proses Backend (`generate_content.php`)**:
    * Menerima data dari `script.js`.
    * Membuat prompt untuk judul dan mengirimkannya ke Google Gemini API.
    * Membuat prompt untuk isi konten (menggabungkan instruksi dasar, kata kunci, target audiens, gaya bahasa) dan mengirimkannya ke Google Gemini API.
    * Jika fitur pencarian gambar diaktifkan dan API Key Google Search tersedia, skrip akan menggunakan judul yang dihasilkan untuk mencari gambar melalui Google Custom Search API.
    * Setelah konten utama dihasilkan, skrip membuat prompt baru untuk meminta Gemini menghasilkan maksimal 5 tag otomatis berdasarkan konten tersebut.
    * Mengumpulkan semua hasil (judul, isi berita, URL gambar jika ada, tag otomatis, dan pesan status) ke dalam format JSON.
4.  **Tampilan Hasil**: `script.js` menerima respons JSON dari `generate_content.php`.
    * Jika berhasil, judul akan ditampilkan di input judul, konten akan dimuat ke editor Summernote, dan tag otomatis akan ditampilkan sebagai badge.
    * Pesan status dan notifikasi toast akan memberi tahu pengguna tentang hasil operasi.
5.  **Pengaturan**:
    * Saat tab "Pengaturan" diakses, `script.js` (melalui `fetchSettings()`) memanggil `get_settings.php` untuk memuat konfigurasi saat ini dari `config.json`.
    * Saat pengguna menyimpan perubahan di tab "Pengaturan", `script.js` mengirimkan data pengaturan baru ke `save_settings.php`, yang kemudian akan memperbarui file `config.json` di server.

## Instalasi dan Setup

1.  **Persyaratan Server**:
    * Web server yang mendukung PHP (misalnya Apache, Nginx).
    * Ekstensi PHP cURL harus aktif (untuk komunikasi dengan API eksternal).
2.  **Unggah File**: Unggah semua file proyek (`index.php`, `script.js`, `generate_content.php`, `get_settings.php`, `save_settings.php`) ke direktori yang dapat diakses melalui web di server Anda.
3.  **File Konfigurasi (`config.json`)**:
    * Saat pertama kali `get_settings.php` diakses (misalnya, saat halaman `index.php` dimuat), jika `config.json` belum ada di direktori yang sama, skrip akan mencoba membuatnya dengan nilai default.
    * Pastikan direktori tempat file-file PHP ini berada **dapat ditulis (writable)** oleh pengguna server web agar `config.json` bisa dibuat dan diperbarui. Anda mungkin perlu mengatur izin direktori (misalnya, `755` atau `775`) dan izin file `config.json` (misalnya, `664`) jika ada masalah.
4.  **API Keys**:
    * Buka aplikasi di browser Anda (`index.php`).
    * Navigasi ke tab **Pengaturan**.
    * Masukkan **Gemini API Key** Anda. Anda bisa mendapatkannya dari [Google AI Studio](https://aistudio.google.com/app/apikey).
    * Jika Anda ingin menggunakan fitur pencarian gambar otomatis:
        * Aktifkan opsi "Aktifkan Pencarian Gambar Otomatis".
        * Masukkan **Google Custom Search API Key** Anda.
        * Masukkan **Google Custom Search CX ID** Anda.
    * Klik "Simpan Pengaturan".

## Cara Menggunakan

1.  Buka file `index.php` di browser Anda.
2.  Pergi ke tab **Pengaturan** terlebih dahulu untuk memastikan API Key Gemini Anda sudah terisi dan konfigurasi lainnya sesuai keinginan. Klik "Simpan Pengaturan".
3.  Pindah ke tab **Generator**.
4.  Masukkan **Kata Kunci Utama** untuk konten yang ingin Anda buat.
5.  (Opsional) Isi **Target Audiens** dan pilih **Gaya Bahasa**.
6.  Klik tombol **"Generate Konten"**.
7.  Tunggu beberapa saat hingga AI memproses permintaan Anda.
8.  Hasilnya (judul, konten, dan saran tag) akan muncul di bawah form. Anda bisa mengedit judul dan konten langsung di field yang tersedia.
9.  Gunakan tombol "Salin Judul" atau "Salin Konten" untuk menyalin hasil ke clipboard.
10. Klik "Konten Baru" untuk mereset semua input dan hasil.

## Kontribusi
![image](https://github.com/user-attachments/assets/5c26980a-1283-4663-89f4-4a0a9580d039)

Jika Anda ingin berkontribusi pada proyek ini, silakan lakukan fork pada repositori, buat branch baru untuk fitur atau perbaikan Anda, dan ajukan Pull Request.
