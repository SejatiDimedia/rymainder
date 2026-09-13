# Rymainder — Platform Otomasi Pengingat Donatur Rutin & Retensi Komitmen Donasi Terpadu

**Rymainder** (dengan identitas *Pledge Cloud*) adalah platform manajemen dan otomasi pengingat donasi berkala (*Recurring Pledge & Donor Retention Platform*) berbasis web yang dirancang khusus untuk membantu yayasan, lembaga amil zakat, panti asuhan, dan organisasi nirlaba mengelola komitmen donatur rutin secara profesional, tepat waktu, dan amanah.

Sistem ini menghubungkan pengelola yayasan (**Super Admin & Pengurus**) dengan para **Donatur Rutin (Orang Tua Asuh / Sponsor Program)** melalui alur komunikasi otomatis lintas saluran (*Multichannel*: **Telegram Bot Resmi**, **WhatsApp**, dan **Email**). 

Rymainder bekerja layaknya asisten retensi donasi cerdas: sistem memantau tanggal komitmen donasi masing-masing donatur (bulanan, triwulan, semesteran, hingga tahunan), kemudian mengirimkan pesan pengingat yang ramah dan personal secara otomatis sesuai jadwal gelombang yang telah ditentukan (seperti H-7, H-3, dan hari H jatuh tempo). Donatur dapat mengaktifkan pengingat bot Telegram hanya dengan sekali klik tanpa perlu registrasi yang rumit. Di sisi lain, tim pengurus yayasan memiliki kendali penuh atas dasbor pemantauan komitmen, impor data donatur massal dari Excel, riwayat log pengiriman pesan secara *real-time*, hingga mesin pengiriman ulang (*retry*) yang tangguh jika terjadi gangguan jaringan pada penyedia pesan.

Proyek ini berfokus pada ketepatan waktu pengiriman notifikasi tanpa risiko duplikasi pesan, kemudahan integrasi bot interaktif, ketahanan sistem terhadap kegagalan jaringan, fleksibilitas impor data dari berbagai format spreadsheet, serta antarmuka yang modern, bersih, dan menumbuhkan rasa percaya.

---

## Ringkasan Proyek

- **Kategori:** Non-Profit Tech, Donor Management System (DMS), Automated Notification & Retention Platform.
- **Peran:** Full-Stack Web & Automation Engineer (Solo Developer).
- **Fokus Utama:** Otomasi pengingat jatuh tempo berjadwal (*Scheduled Reminder Waves*), integrasi bot Telegram interaktif dua arah, pengiriman pesan lintas saluran (Telegram, WhatsApp, Email), impor donatur massal via Excel/CSV yang fleksibel, mesin *resend & retry* anti-gagal, dasbor pemantauan komitmen donasi, dan personalisasi pesan dengan variabel dinamis.
- **Backend & Arsitektur:** Laravel 11 (PHP 8.3), Arsitektur Berbasis Domain (*Domain-Driven Pattern*: Sponsor, Reminder, Communication), Sistem Antrean Latar Belakang (*Background Queues & Jobs*) dengan *Exponential Backoff*, Kontrol Akses Berbasis Peran (*RBAC: Super Admin & Staff*).
- **Database & Integritas Pesan:** MySQL / Relational Database, Skema ACID Compliant dengan proteksi *atomic log lock* (mencegah pesan terkirim ganda saat scheduler berjalan berulang), audit trail lengkap (*status: pending, sent, failed, skipped*).
- **Antarmuka Web (Frontend):** Tailwind CSS, Alpine.js, Blade Components, Desain bertema modern Swiss minimalism (*Palet Slate/Dark Obsidian, aksen Emerald & Sky Blue, tipografi bersih, dan modal konfirmasi kustom tanpa pop-up browser bawaan*).
- **Integrasi API & Layanan:** Telegram Bot API (dukungan aktivasi kode 6-digit instan dan fallback otomatis ke plain text jika format markdown bermasalah), Standarisasi Nomor Telepon Internasional (E.164), SMTP Email Service, serta PhpSpreadsheet Engine untuk pemrosesan file `.xlsx` dan `.csv`.

---

## Latar Belakang & Motivasi

Bagi yayasan sosial, panti asuhan, dan lembaga filantropi, donatur rutin (seperti program Orang Tua Asuh atau donasi operasional bulanan) adalah urat nadi keberlangsungan program. Namun, dalam praktiknya, sebagian besar yayasan masih mengelola pengingat donasi secara manual.

Sebelum adanya sistem ini, operasional yayasan sering kali menghadapi kendala nyata di lapangan:

1. **Penagihan Manual yang Melelahkan dan Memakan Waktu:** Setiap akhir atau awal bulan, staf yayasan harus membuka lembaran spreadsheet, mencari siapa saja donatur yang sudah jatuh tempo, lalu menyalin nomor telepon dan mengetik pesan WhatsApp satu per satu ke ratusan donatur.
2. **Keterlambatan Donasi Bukan Karena Enggan, Melainkan Lupa:** Sebagian besar donatur yang telat menyalurkan donasi rutinnya bukan karena tidak ingin berdonasi lagi, melainkan karena kesibukan harian dan tidak adanya pengingat yang ramah dan tepat waktu.
3. **Kekhawatiran Pesan Ganda (*Double Reminder*):** Saat penagihan dikerjakan oleh lebih dari satu staf, sering terjadi kebingungan mengenai donatur mana yang sudah diingatkan dan mana yang belum. Hal ini memicu risiko pengiriman pesan ganda yang membuat donatur merasa terganggu.
4. **Saluran Komunikasi Terfragmentasi:** Sebagian donatur lebih aktif membaca pesan di Telegram, sebagian di WhatsApp, dan sebagian di Email. Mengelola preferensi saluran yang berbeda ini secara manual sangat rawan salah kirim.
5. **Pesan Gagal Tanpa Pemberitahuan (*Silent Failures*):** Ketika email masuk ke kotak spam atau koneksi bot mengalami kendala, pengurus tidak pernah tahu bahwa pesan tersebut gagal sampai donatur akhirnya berhenti berdonasi.
6. **Data Donatur yang Tercecer di Berbagai Format Excel:** Data donatur lama sering tersimpan dalam format file spreadsheet yang tidak seragam, penulisan nomor telepon yang acak (pakai spasi, strip, atau tanpa kode negara), serta format tanggal donasi yang membingungkan.

Rymainder dibangun untuk mengatasi seluruh permasalahan tersebut secara tuntas melalui pendekatan teknologi yang andal dan ramah pengguna:

1. **Otomasi Terjadwal Tanpa Sentuhan Manual:** Sistem bekerja otomatis di latar belakang setiap hari pada jam yang ditentukan, memindai data komitmen, dan mengirimkan pesan ramah kepada donatur yang tepat pada hari yang tepat.
2. **Jaminan Anti-Kirim Ganda (*Zero Duplicate Delivery*):** Menggunakan penguncian log status yang ketat sehingga pemindaian otomatis maupun tindakan manual staf tidak akan pernah mengirimkan notifikasi ganda untuk siklus jatuh tempo yang sama.
3. **Koneksi Telegram Bot yang Sangat Mudah:** Donatur cukup mengklik satu tautan atau mengirim kode aktivasi singkat ke bot Telegram yayasan, dan sistem otomatis terhubung tanpa meminta donatur membuat akun atau password baru.
4. **Impor Spreadsheet yang Cerdas & Toleran:** Pengurus yayasan dapat mengunggah ratusan data donatur dari file Excel/CSV sekaligus. Sistem otomatis merapikan format nomor telepon ke standar internasional (+62), membaca berbagai variasi format tanggal, dan mendeteksi data duplikat secara cerdas.
5. **Pusat Audit Pengiriman & Tombol Pengiriman Ulang (*Resend Engine*):** Setiap pesan memiliki rekaman jejak audit yang transparan. Jika ada pesan yang gagal karena kendala jaringan pihak ketiga, pengurus dapat mengirim ulang hanya dengan satu klik menggunakan modal konfirmasi interaktif.

---

## Kontribusi Utama Saya

**Arsitektur Penjadwalan Gelombang Pengingat Otomatis (*Multi-Wave Engine*)**
- Merancang dan membangun mesin penjadwal cerdas yang secara otomatis menghitung siklus jatuh tempo komitmen donatur berdasarkan periode yang dipilih (bulanan, 3 bulanan, 6 bulanan, atau tahunan).
- Menerapkan sistem gelombang bertahap (*Waves*): misalnya pengingat awal H-7 (ramah & informatif), pengingat H-3, pengingat hari H jatuh tempo, hingga sapaan pasca-jatuh tempo, lengkap dengan pengaturan jam pengiriman harian yang disesuaikan dengan zona waktu lokal (WIB, WITA, WIT).

**Integrasi Telegram Bot Interaktif Dua Arah & Fallback Cerdas**
- Membangun pengemudi bot Telegram resmi yang terhubung dengan akun donatur melalui tautan aktivasi unik atau perintah kode 6-digit (`/start KODE`).
- Mengimplementasikan mekanisme *fail-safe*: jika pesan yang dikirimkan mengalami kegagalan penguraian format (*Markdown entity error*), sistem otomatis melakukan pengiriman ulang instan dalam bentuk teks polos (*plain text fallback*) agar pesan tetap sampai ke ponsel donatur.
- Menyediakan alat pengujian koneksi instan (*Test Ping Tool*) di halaman pengaturan agar admin dapat memverifikasi status bot langsung ke chat ID mereka.

**Mesin Pengiriman Ulang Tahan Gangguan (*Resilient Retry & Resend System*)**
- Mengembangkan modul pemulihan pesan gagal (*Failed Reminder Recovery*) yang dapat menangani seluruh jenis pengingat (gelombang otomatis, broadcast khusus, maupun pesan langsung personal).
- Membangun fitur **"Retry All Failed Reminders"** untuk mengirim ulang seluruh antrean gagal sekaligus di latar belakang, serta tombol **"Retry"** mandiri pada baris riwayat donatur dengan modal konfirmasi kustom yang informatif dan aman.
- Menyediakan perintah otomatisasi terminal (`php artisan reminders:retry-failed`) lengkap dengan mode pratinjau (*dry-run*) untuk kebutuhan pemeliharaan terjadwal.

**Modul Impor Donatur Massal Berkemampuan Tinggi (*Smart Spreadsheet Importer*)**
- Membangun mesin pembaca file Excel (`.xlsx`, `.xls`) dan CSV menggunakan PhpSpreadsheet yang mampu mengenali berbagai variasi nama kolom secara fleksibel (*case-insensitive auto-mapping*).
- Mengatasi masalah umum pada file spreadsheet: secara otomatis mengonversi nomor serial tanggal bawaan Excel (misal angka serial `46280`) menjadi format tanggal yang valid, membersihkan penulisan mata uang (seperti `Rp 500.000`), dan menormalisasi nomor telepon lokal (`0812...`) menjadi format internasional E.164 (`+62812...`).
- Menyediakan pilihan penanganan duplikat: lewati data yang sudah ada (*skip*) atau perbarui data yang sudah ada (*update*), serta menyediakan template spreadsheet resmi yang dapat diunduh langsung oleh staf yayasan.

**Kampanye Pengingat Siaran Khusus (*Standalone Custom Reminders*)**
- Merancang fitur pengiriman pesan pengumuman atau kampanye tematik (seperti ajakan sedekah Ramadan, santunan khusus hari raya, atau renovasi asrama yatim) yang dapat ditujukan ke seluruh donatur aktif atau daftar donatur tertentu yang dipilih secara fleksibel.
- Menyediakan pilihan eksekusi satu kali (*one-time*) atau berulang secara harian, lengkap dengan tombol eksekusi instan (*Run Now*).

**Penyempurnaan Pengalaman Pengguna (Custom UI Modal & Desain Elegan)**
- Menggantikan semua pop-up konfirmasi bawaan browser (`confirm()`) yang kaku dengan modal konfirmasi interaktif berbasis Alpine.js yang elegan, responsif, dan ramah pengguna.
- Modal menyajikan rincian lengkap sebelum konfirmasi: nama penerima, saluran pengiriman, jadwal gelombang, tanggal jatuh tempo, kotak catatan kesalahan sebelumnya, serta indikator *loading spinner* untuk mencegah klik ganda saat pengiriman diproses.
- Menata ulang seluruh ikon antarmuka menggunakan ikon vektor SVG Heroicons yang bersih tanpa menggunakan emoticon karakter yang tidak formal.

---

## Fitur Produk Inti

**1. Dasbor Retensi & Manajemen Donatur (Sponsor Management)**
- Pencatatan lengkap data donatur: nama, email, nomor kontak, anak asuh binaan yang didukung, nominal komitmen donasi, periode pembayaran, dan preferensi saluran notifikasi.
- Status donatur yang jelas (*Active*, *Paused*, *Cancelled*) untuk memastikan yayasan hanya menghubungi donatur yang berstatus aktif.
- Halaman profil donatur terpadu yang menampilkan riwayat seluruh pengingat yang pernah dikirimkan beserta status pengirimannya.

**2. Otomasi Pengingat Berjadwal Lintas Saluran (Multichannel Reminder Waves)**
- Pengiriman pesan otomatis melalui Telegram, WhatsApp, dan Email.
- Template pesan yang dapat disesuaikan untuk masing-masing gelombang dengan dukungan variabel dinamis: `{sponsor_name}`, `{orphan_name}`, `{amount}`, `{due_date}`, dan `{platform_name}`.
- Pengaturan jam pengiriman harian dan penyesuaian zona waktu yayasan (WIB, WITA, WIT).

**3. Kampanye Siaran Khusus (Custom Broadcast Campaigns)**
- Fasilitas untuk membuat pesan pengingat atau pengumuman khusus di luar jadwal rutin donasi.
- Fleksibilitas memilih target penerima: seluruh donatur yayasan atau kelompok donatur terpilih.
- Penjadwalan tanggal dan jam pengiriman yang presisi atau pengiriman langsung seketika (*Run Now*).

**4. Pusat Audit Pengiriman & Log Notifikasi (Delivery Audit Logs)**
- Catatan audit terpusat untuk setiap pesan keluar yang memuat status pengiriman (*Pending, Sent, Failed, Skipped*), waktu kirim, saluran yang digunakan, dan isi pesan riil.
- Callout diagnostik otomatis jika terjadi kegagalan dari pihak penyedia saluran (misal: penolakan server email atau chat ID Telegram belum terhubung).
- Aksi pengiriman ulang satu donatur maupun massal (*Bulk Resend*) dengan jaminan keamanan *zero duplicate*.

**5. Pengiriman Pesan Manual Personal Langsung dari Profil Donatur**
- Tombol aksi cepat bagi staf untuk mengirimkan pesan pengingat personal langsung dari halaman detail donatur.
- Dilengkapi kotak pratinjau pesan dinamis yang langsung mengisi nama donatur, nominal komitmen, dan nama anak asuh secara otomatis.

**6. Modul Impor Massal & Unduh Template (Excel & CSV)**
- Fitur unggah file Excel/CSV untuk memasukkan ratusan data donatur baru dalam hitungan detik.
- Deteksi error baris per baris yang informatif sehingga staf tahu persis jika ada baris data yang perlu diperbaiki.
- Unduh template starter berformat `.xlsx` dan `.csv` yang sudah dirancang rapi dengan contoh data riil.

**7. Personalisasi Identitas Lembaga & Pengaturan Bot Telegram**
- Kustomisasi nama yayasan, slogan lembaga, zona waktu, dan logo resmi organisasi.
- Pengaturan token dan nama pengguna Bot Telegram dengan verifikasi status koneksi langsung.
- Pengatur template pesan bot Telegram (pesan aktivasi berhasil, ucapan selamat datang `/start`, balasan otomatis default, dan pesan penanganan kode kedaluwarsa).

---

## Teknologi & Arsitektur Sistem (Tech Stack)

Rymainder dibangun menggunakan fondasi teknologi modern yang memprioritaskan kestabilan pengiriman pesan, kecepatan proses latar belakang, dan kemudahan pemeliharaan:

- **Framework Web & API:** Laravel 11 (PHP 8.3)
- **Arsitektur Aplikasi:** Domain-Driven Design (Domain Sponsor, Reminder, Communication, dan Admin)
- **Antarmuka & Desain:** Blade Templates, Tailwind CSS, Alpine.js, Heroicons Vektor
- **Basis Data:** MySQL / Relational Database dengan Eloquent ORM & Migrasi Skema Berversi
- **Proses Latar Belakang & Antrean:** Laravel Queue Worker & Scheduler (`schedule:work`), mekanisme *Retry & Exponential Backoff*
- **Pemrosesan Dokumen & Spreadsheet:** PhpSpreadsheet (`phpoffice/phpspreadsheet` v5.9) untuk berkas `.xlsx`, `.xls`, dan `.csv`
- **Integrasi Pihak Ketiga:** Telegram Bot API (HTTP Client), SMTP Mail Drivers, E.164 Phone Normalization Helper
- **Keamanan & Otorisasi:** Proteksi CSRF, Session Cookie Terenkripsi, Role-Based Access Control (*Super Admin & Staff*), Database Credential Fallback
- **Jaminan Kualitas & Pengujian:** PHPUnit & Pest Test Suite Terintegrasi (99 Automated Tests, 373 assertions dengan cakupan fitur 100%)

---

## Tantangan Nyata & Solusi yang Diterapkan

**1. Menghindari Pengiriman Pesan Ganda pada Sistem Scheduler Otomatis**
- *Tantangan:* Dalam sistem cron job yang berjalan setiap menit atau jam, terdapat risiko jadwal dieksekusi dua kali secara bersamaan (*race condition*). Jika tidak diantisipasi, donatur bisa menerima dua atau tiga pesan pengingat yang sama dalam waktu berdekatan, yang dapat menurunkan reputasi profesional yayasan.
- *Solusi:* Saya merancang pola transaksi database atomik dengan pencatatan log instan berstatus `pending` sebelum pesan dimasukkan ke dalam antrean *worker*. Kueri seleksi donatur selalu memeriksa keberadaan log pada siklus jatuh tempo yang sama. Jika log untuk tanggal jatuh tempo tersebut sudah ada, sistem secara otomatis melewatinya.

**2. Menangani Ketidakteraturan Format Data Donatur pada File Excel Lama**
- *Tantangan:* Banyak yayasan memiliki file Excel warisan dengan format data yang berantakan: nomor telepon diawali `08`, `62`, atau `+62` dengan tanda strip dan spasi; nominal donasi berisi teks `Rp` atau titik pemisah ribuan; serta format tanggal yang sering kali tersimpan sebagai angka serial bawaan Excel (misal angka integer `46280`).
- *Solusi:* Saya membangun lapisan pembersih data (*Data Sanitizer*) cerdas di dalam aksi impor. Sistem otomatis mengubah nomor serial Excel menjadi objek tanggal yang valid, membersihkan karakter non-numerik pada nominal donasi, menormalisasi seluruh variasi nomor telepon ke standar internasional E.164, dan memberikan laporan baris per baris yang jelas jika terdapat data yang tidak lengkap.

**3. Karakter Khusus yang Menggagalkan Notifikasi Bot Telegram**
- *Tantangan:* API Telegram Bot menggunakan format Markdown yang sangat ketat. Jika pesan pengingat memuat nama yayasan atau nama donatur dengan karakter tanda baca seperti garis bawah (`_`), tanda bintang (`*`), atau kurung siku (`[`), Telegram akan menolak pengiriman dengan status error `400 Bad Request`.
- *Solusi:* Saya menerapkan mekanisme pertahanan berlapis: selain fungsi pelolosan karakter (*escaping*), driver bot Telegram dilengkapi logika deteksi kegagalan parsing otomatis. Jika respons Telegram mengindikasikan kendala format, sistem secara instan mengirim ulang muatan pesan sebagai teks biasa (*plain text fallback*), memastikan donatur tetap menerima pengingat tanpa kendala teknis.

---

## Nilai Bisnis & Pembelajaran Kunci

- **Meningkatkan Arus Kas & Retensi Donatur Yayasan:** Menyadari bahwa keberlangsungan program sosial sangat bergantung pada konsistensi donatur. Sistem pengingat yang otomatis dan ramah secara signifikan mengurangi tingkat donatur lupa, sehingga arus kas program yayasan tetap stabil dan terencana.
- **Efisiensi Ratusan Jam Kerja Tim Operasional:** Mengubah proses penagihan manual yang tadinya memakan waktu berhari-hari setiap bulan menjadi proses otomatis yang berjalan sendiri di latar belakang, membebaskan staf yayasan untuk lebih fokus pada pelayanan sosial dan pembinaan anak asuh.
- **Pentingnya Ketahanan Sistem (*Resilience Engineering*):** Membangun aplikasi yang tidak hanya berfungsi saat koneksi lancar, melainkan memiliki sistem audit, pencatatan alasan kegagalan, dan tombol pemulihan instan saat terjadi kendala pada pihak ketiga.
