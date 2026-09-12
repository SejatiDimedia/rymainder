# Product Requirements Document (PRD)
## Rymainder — Automated Sponsor Reminder System

| | |
|---|---|
| **Versi Dokumen** | 1.2 |
| **Tanggal** | 12 September 2026 |
| **Status** | Draft — siap direview klien/tim |
| **Penulis** | — |
| **Target Rilis** | Production-ready, v1.0 |

---

## 1. Ringkasan Eksekutif

Automated Sponsor Reminder System adalah aplikasi web berbasis **Laravel** yang mengotomatiskan pengingat pembayaran donasi kepada sponsor/donatur (mis. program sponsor anak asuh), berdasarkan tanggal donasi terakhir dan frekuensi pembayaran (tahunan atau 6 bulanan). Sistem mengirim pengingat melalui **Email, WhatsApp, dan Telegram**, mencatat seluruh riwayat pengiriman, dan menyediakan dashboard admin untuk mengelola data sponsor serta memantau status jatuh tempo.

Ketiga channel ini dipilih karena kombinasinya menekan biaya operasional mendekati nol: Email gratis (SMTP), Telegram Bot API 100% gratis tanpa batas, dan WhatsApp — meski ada biaya per pesan dari Meta — jauh lebih murah dan lebih sering dibuka orang Indonesia dibanding SMS.

Tujuan utama: menghilangkan pekerjaan manual staf yayasan dalam mengingatkan donatur, mengurangi keterlambatan/hilangnya donasi berulang, dan memberi visibilitas penuh kepada admin atas siapa yang sudah/belum dihubungi.

---

## 2. Latar Belakang & Masalah

- Staf yayasan saat ini mengingatkan sponsor secara manual (WhatsApp/telepon satu per satu), rawan lupa dan tidak konsisten.
- Tidak ada catatan terpusat kapan seorang sponsor terakhir diingatkan atau kapan mereka jatuh tempo berikutnya.
- Volume sponsor terus bertambah sehingga proses manual tidak scalable.

---

## 3. Tujuan Produk (Goals)

1. Mengirim reminder otomatis tanpa intervensi manual harian.
2. Mendukung tiga channel notifikasi: Email, WhatsApp, dan Telegram — masing-masing bisa diaktif/nonaktifkan per wave reminder.
3. Mencegah pengiriman ganda (duplicate-safe) meski job dijalankan berkali-kali.
4. Memberi admin dashboard untuk CRUD data sponsor dan audit trail pengiriman.
5. Sistem dapat di-deploy di hosting/VPS standar dan berjalan stabil tanpa pengawasan harian.

### Non-Goals (di luar scope v1.0)
- Payment gateway / pemrosesan pembayaran langsung.
- Aplikasi mobile terpisah.
- Multi-bahasa (v1.0 hanya Bahasa Indonesia).
- Multi-tenant (v1.0 untuk satu organisasi/yayasan saja).

---

## 4. Target Pengguna & Peran (Roles)

| Role | Deskripsi | Akses |
|---|---|---|
| **Super Admin** | Pemilik sistem/IT yayasan | Full akses: kelola user, pengaturan reminder, kredensial integrasi |
| **Admin/Staf Program** | Staf yang input & pantau data sponsor | CRUD sponsor, lihat log pengiriman, tidak bisa ubah kredensial sistem |
| **Sistem (Scheduler)** | Proses otomatis (bukan user manusia) | Menjalankan job reminder harian |

---

## 5. User Stories

1. **Sebagai staf program**, saya ingin menambahkan data sponsor baru (nama, kontak, tanggal donasi terakhir, frekuensi) agar sistem bisa menghitung jadwal reminder mereka.
2. **Sebagai staf program**, saya ingin melihat daftar sponsor yang akan jatuh tempo dalam 7 hari ke depan agar bisa follow-up manual jika perlu.
3. **Sebagai staf program**, saya ingin melihat riwayat pengiriman reminder per sponsor (berhasil/gagal) agar bisa menindaklanjuti kegagalan pengiriman.
4. **Sebagai super admin**, saya ingin mengatur kapan reminder dikirim (H-7, H-3, H-0, dst.) tanpa mengubah kode.
5. **Sebagai sponsor**, saya menerima pengingat lewat Email, WhatsApp, dan/atau Telegram sebelum jatuh tempo donasi saya, sehingga saya tidak lupa membayar.
7. **Sebagai sponsor yang sudah connect bot Telegram yayasan**, saya ingin bisa membalas pesan reminder untuk konfirmasi pembayaran langsung dari chat (fitur lanjutan, lihat roadmap).
6. **Sebagai super admin**, saya ingin sistem otomatis retry jika pengiriman gagal (mis. SMTP down), tanpa mengirim dobel ke sponsor yang sudah berhasil menerima.

---

## 6. Ruang Lingkup Fungsional

### 6.1 Manajemen Sponsor
- CRUD sponsor: nama, email, no. HP/WhatsApp (format E.164), Telegram chat ID atau username (opsional, diisi setelah sponsor `/start` ke bot), nama anak asuh (opsional), tanggal donasi terakhir, frekuensi (`annual` / `6_months`), nominal, status (`active`/`paused`/`cancelled`), catatan.
- Import massal via CSV (v1.1 — lihat roadmap).
- Validasi: email valid, no. HP/WhatsApp valid (regex E.164), tanggal tidak boleh di masa depan.
- Preferensi channel per sponsor: admin bisa pilih channel mana saja yang aktif untuk sponsor tsb (misal sponsor tanpa smartphone hanya diaktifkan Email).

### 6.2 Mesin Perhitungan Jatuh Tempo
- Hitung tanggal jatuh tempo berikutnya = `last_donation_date + frequency`.
- Jika tanggal hasil hitung sudah lewat (sponsor terlambat beberapa siklus), sistem otomatis "roll forward" ke siklus berikutnya yang akan datang.

### 6.3 Reminder Rules Engine
- Tabel `reminder_settings` berisi beberapa "wave" (gelombang) reminder, dikonfigurasi oleh admin, contoh default:
  | Label | Hari sebelum jatuh tempo | Channel |
  |---|---|---|
  | Reminder pertama | H-7 | Email + Telegram |
  | Reminder kedua | H-3 | Email + WhatsApp + Telegram |
  | Reminder hari-H | H-0 | Email + WhatsApp + Telegram |
  | Follow-up telat | H+7 (overdue) | Email + WhatsApp + Telegram |
- Admin dapat menambah/menonaktifkan wave tanpa deploy ulang kode.

### 6.4 Pengiriman Notifikasi

| Channel | Cara Kirim | Biaya | Catatan |
|---|---|---|---|
| **Email** | Laravel `Notification` + `Mailable` via SMTP (Mailgun/SES/Brevo) | Gratis s/d kuota tertentu | Fallback paling stabil, hampir semua sponsor punya email |
| **Telegram** | Telegram Bot API (`sendMessage`), 1 HTTP call, tanpa SDK berbayar | 100% gratis, tanpa batas | Sponsor wajib `/start` ke bot yayasan dulu agar `chat_id` tercatat; onboarding perlu link/QR code yang dibagikan ke sponsor |
| **WhatsApp** | WhatsApp Cloud API (Meta) langsung, atau provider lokal (Watzap.id/Zenziva) sebagai alternatif lebih simpel setup | Berbayar per pesan (kategori "utility" relatif murah) | Channel paling sering dibuka sponsor Indonesia; dipakai untuk wave kritis saja agar biaya terkontrol |
| **SMS** *(disiapkan, belum aktif di v1.0)* | Twilio/Vonage/provider lokal, via custom Notification Channel | Berbayar per SMS (biasanya paling mahal dari semua channel) | Arsitektur sudah siap menambahkan channel ini kapan pun tanpa migrasi database — lihat 6.4.1 |

- Setiap pengiriman dicatat ke tabel `reminder_logs` dengan status `sent`/`failed` + pesan error (jika ada), per channel.
- **Idempotency**: constraint unik pada kombinasi (`sponsor_id`, `due_date`, `reminder_setting_id`, `channel`) mencegah pengiriman dobel; percobaan gagal dapat di-retry pada eksekusi berikutnya tanpa mengganggu yang sudah sukses.
- **Fallback logic (opsional, disepakati dengan klien)**: jika WhatsApp gagal terkirim (nomor tidak terdaftar WhatsApp, dsb.), sistem otomatis coba kirim ulang lewat Telegram atau Email di hari yang sama, supaya sponsor tetap dapat pengingat lewat channel lain.
- **Onboarding Telegram**: karena Telegram butuh sponsor memulai chat dengan bot terlebih dahulu (keterbatasan platform Telegram, bukan pilihan desain kita), dashboard menyediakan link `t.me/nama_bot_yayasan` + kode unik per sponsor untuk auto-matching `chat_id` ke data sponsor saat mereka `/start`.

#### 6.4.1 Desain "Channel-Ready" untuk SMS (dan channel masa depan lainnya)

Agar SMS (atau channel lain seperti push notification app mobile di masa depan) bisa ditambahkan **tanpa mengubah struktur database**, sistem dirancang dengan prinsip berikut sejak v1.0:

- Kolom channel di `reminder_settings` dan `reminder_logs` **tidak** menggunakan `ENUM` MySQL yang kaku (yang butuh migrasi tiap kali ada channel baru), melainkan `VARCHAR` dengan validasi di level aplikasi (Laravel Enum class / constant list). Menambah channel baru = tinggal tambah satu nilai di `App\Enums\ReminderChannel`, tanpa `ALTER TABLE`.
- Setiap channel adalah implementasi terpisah dari Laravel Notification Channel (`App\Notifications\Channels\*`) yang mengikuti interface yang sama (`send($notifiable, $notification)`). Menambah SMS = membuat satu class baru `SmsChannel.php`, tanpa menyentuh kode channel lain (Email/WhatsApp/Telegram).
- Preferensi channel per sponsor & per wave disimpan fleksibel (kolom `channels` berupa JSON array, mis. `["email","telegram"]`), bukan kolom boolean terpisah per channel (`channel_email`, `channel_sms`, dst.) — jadi menambah channel baru tidak perlu `ALTER TABLE reminder_settings ADD COLUMN`.
- Kredensial provider SMS (`SMS_PROVIDER`, `SMS_API_KEY`, dst.) sudah disediakan tempatnya di `.env.example` (lihat 14.1), tinggal diisi saat SMS diaktifkan — tidak perlu deploy ulang struktur aplikasi.
- **Kapan SMS masuk akal diaktifkan**: biasanya sebagai fallback terakhir untuk sponsor yang tidak punya WhatsApp/Telegram aktif dan gagal dihubungi email berkali-kali (overdue jangka panjang) — bukan channel utama, karena biaya per pesan paling mahal dibanding tiga channel lain.

### 6.5 Scheduler / Automasi
- Laravel Task Scheduling (`routes/console.php` atau `bootstrap/app.php` di Laravel 11) menjalankan command `reminders:send` setiap hari pada jam yang dikonfigurasi (default 08:00 waktu lokal).
- Dijalankan lewat satu baris cron server: `* * * * * php artisan schedule:run`.
- Job dijalankan lewat **Queue** (database atau Redis driver) agar pengiriman ratusan notifikasi tidak memblokir request/response dan bisa di-retry otomatis oleh Laravel Queue jika gagal (`tries`, `backoff`).

### 6.6 Dashboard Admin
- Login (Laravel Breeze/Fortify — session-based auth).
- Halaman daftar sponsor dengan indikator visual: hijau (>7 hari), kuning (≤7 hari), merah (telat).
- Filter & pencarian (nama, status, frekuensi).
- Halaman detail sponsor: riwayat reminder yang pernah dikirim.
- Halaman pengaturan reminder waves (CRUD `reminder_settings`).
- Halaman log global: semua pengiriman terbaru, dengan filter status.

### 6.7 Notifikasi Internal untuk Admin (tambahan value untuk versi produksi)
- Ringkasan harian ke admin (email) berisi: jumlah reminder terkirim, jumlah gagal, daftar sponsor yang gagal dihubungi — agar admin bisa follow-up manual.

---

## 7. Kebutuhan Non-Fungsional

| Kategori | Requirement |
|---|---|
| **Performance** | Mampu memproses ≥5.000 sponsor dalam satu run harian dalam <10 menit menggunakan queue worker |
| **Reliability** | Kegagalan pengiriman satu sponsor tidak boleh menghentikan proses sponsor lain (isolasi per-job) |
| **Security** | Kredensial (SMTP, WhatsApp Cloud API token, Telegram Bot token) disimpan di `.env`, tidak pernah di-commit; akses dashboard wajib login; CSRF protection aktif (default Laravel) |
| **Auditability** | Semua pengiriman & perubahan data sponsor tercatat (log + timestamps) |
| **Availability** | Target uptime 99% (bergantung hosting); scheduler harus auto-recover jika server restart (cron tetap jalan) |
| **Scalability** | Struktur queue memungkinkan scale ke banyak sponsor tanpa refactor besar |
| **Maintainability** | Kode mengikuti konvensi Laravel standar (PSR-12, service/action classes terpisah dari controller) |
| **Localization** | UI dan isi pesan dalam Bahasa Indonesia |
| **Browser Support** | Dashboard responsif, mendukung Chrome/Firefox/Safari/Edge versi 2 tahun terakhir, mobile-friendly |

---

## 8. Arsitektur Teknis

### 8.1 Tech Stack
| Layer | Teknologi |
|---|---|
| Framework | Laravel 11.x |
| Database | MySQL 8.x (produksi) |
| Queue | Database driver (v1.0) → upgrade ke Redis jika volume tinggi |
| Email | Laravel Mail + SMTP provider (Mailgun/SES/Brevo) |
| WhatsApp | WhatsApp Cloud API (Meta) via custom Notification Channel, atau provider lokal (Watzap.id/Zenziva) sebagai opsi lebih simpel |
| Telegram | Telegram Bot API (HTTP request murni via Guzzle, tanpa SDK) via custom Notification Channel |
| Frontend Dashboard | Blade + Bootstrap 5 (konsisten dengan keahlian tim), atau Livewire jika butuh interaktivitas tanpa JS terpisah |
| Auth | Laravel Breeze |
| Scheduler | Laravel Task Scheduling + server cron |
| Hosting Target | VPS (Ubuntu) dengan PHP-FPM + Nginx, atau shared hosting yang mendukung Laravel (butuh SSH untuk composer & queue worker) |

### 8.2 Model Data (ERD Ringkas)

```
sponsors
 ├─ id, name, email, phone (WhatsApp, E.164), telegram_chat_id, telegram_onboard_code
 ├─ orphan_name, last_donation_date, frequency (enum), amount
 ├─ status (enum), notes, timestamps

reminder_settings
 ├─ id, days_before_due, channels (JSON array, mis. ["email","whatsapp","telegram"])
 ├─ label, is_active

reminder_logs
 ├─ id, sponsor_id (FK), due_date, reminder_setting_id (FK)
 ├─ channel (VARCHAR, bukan ENUM — divalidasi via App\Enums\ReminderChannel), status (enum), error_message, sent_at
 ├─ UNIQUE(sponsor_id, due_date, reminder_setting_id, channel)

users  (admin dashboard)
 ├─ id, name, email, password, role, timestamps
```

> Catatan: `telegram_chat_id` diisi otomatis oleh webhook Telegram saat sponsor mengetik `/start <telegram_onboard_code>` ke bot yayasan. Sebelum itu terjadi, channel Telegram untuk sponsor tersebut dianggap belum aktif (skip saat pengiriman, tidak dihitung `failed`).
>
> Kolom `channels` (JSON) dan `channel` (VARCHAR) dipilih secara sengaja alih-alih kolom boolean/ENUM per channel, agar penambahan channel baru (SMS, push notification, dst.) di masa depan tidak memerlukan `ALTER TABLE` — cukup tambah nilai baru di `App\Enums\ReminderChannel` dan satu Notification Channel class baru. Lihat 6.4.1.

### 8.3 Alur Proses Reminder (Sequence)

1. Server cron memicu `php artisan schedule:run` setiap menit.
2. Laravel Scheduler mengeksekusi command `reminders:send` sesuai jadwal (mis. 08:00 setiap hari).
3. Command mengambil seluruh sponsor `status = active`, menghitung jatuh tempo masing-masing.
4. Untuk setiap sponsor, dicocokkan dengan `reminder_settings` yang aktif.
5. Jika cocok dan belum pernah sukses terkirim (cek `reminder_logs`), job pengiriman **di-dispatch ke queue** (satu job per sponsor per channel yang aktif untuk wave tsb).
6. Queue worker memproses job: memanggil Notification (Mail/WhatsApp/Telegram) sesuai channel, mencatat hasil ke `reminder_logs` (upsert, retry-safe). Sponsor yang belum onboarding Telegram otomatis dilewati untuk channel tsb (bukan dihitung gagal).
7. Setelah semua job selesai, command mengirim ringkasan harian ke admin.

### 8.4 Modularitas Kode (best practice, agar mudah maintain)
- `App\Models`: Sponsor, ReminderSetting, ReminderLog
- `App\Enums\ReminderChannel`: daftar channel yang didukung (`Email`, `WhatsApp`, `Telegram`, dan `Sms` yang sudah didefinisikan tapi non-aktif — tinggal diaktifkan tanpa migrasi)
- `App\Services\DueDateCalculator`: logika perhitungan tanggal jatuh tempo (unit-testable, terpisah dari controller/command)
- `App\Notifications\SponsorPaymentReminder`: Notification class multi-channel (mail + WhatsApp + Telegram, siap tambah SMS)
- `App\Notifications\Channels\WhatsAppChannel`: custom channel untuk kirim WhatsApp (Cloud API/provider lokal)
- `App\Notifications\Channels\TelegramChannel`: custom channel untuk kirim Telegram Bot API
- `App\Notifications\Channels\SmsChannel`: *(placeholder, dibuat kerangkanya di v1.0 tapi tidak dipanggil sampai channel SMS diaktifkan admin)* — begitu diisi kredensial provider dan diaktifkan di `reminder_settings.channels`, langsung berfungsi tanpa deploy ulang
- `App\Http\Controllers\TelegramWebhookController`: menerima webhook `/start <code>` dari Telegram, mencocokkan `telegram_onboard_code` ke sponsor, menyimpan `telegram_chat_id`
- `App\Console\Commands\SendReminders`: entry point scheduler
- `App\Jobs\SendSponsorReminderJob`: job queue per sponsor/channel
- `App\Http\Controllers\Admin\*`: controller dashboard (SponsorController, ReminderSettingController, ReminderLogController)

---

## 9. Rencana Testing

| Jenis Test | Cakupan |
|---|---|
| **Unit Test** | `DueDateCalculator` (berbagai skenario: tepat waktu, telat beberapa siklus, edge case tanggal 29 Feb, dsb.) |
| **Feature Test** | CRUD sponsor via dashboard, validasi form |
| **Integration Test** | Command `reminders:send` dengan mock Notification (pastikan tidak double-send saat dijalankan 2x pada hari yang sama) |
| **Manual QA** | Uji kirim Email, WhatsApp, dan Telegram sungguhan ke akun test sebelum go-live |
| **Load Test** | Simulasi 5.000 sponsor dummy untuk memastikan queue tidak bottleneck |

---

## 10. Rencana Deployment (Production Checklist)

- [ ] Server VPS/hosting dengan PHP 8.2+, MySQL 8, Composer, akses SSH
- [ ] `.env` produksi terisi: `DB_*`, `MAIL_*`, `WHATSAPP_*`, `TELEGRAM_*`, `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Nomor WhatsApp Business terdaftar & Meta Business Account terverifikasi (atau akun provider lokal aktif)
- [ ] Bot Telegram dibuat via @BotFather, token disimpan, webhook URL didaftarkan ke Telegram (`setWebhook`)
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Queue worker dijalankan permanen via **Supervisor** (`php artisan queue:work --tries=3 --backoff=60`)
- [ ] Cron server: `* * * * * php /path/artisan schedule:run >> /dev/null 2>&1`
- [ ] SSL/HTTPS aktif (Let's Encrypt)
- [ ] Backup database otomatis harian
- [ ] Monitoring error (Laravel log + opsional Sentry/Flare)
- [ ] Uji kirim reminder end-to-end di environment staging sebelum go-live

---

## 11. Metrik Keberhasilan (Success Metrics)

- ≥95% reminder terjadwal berhasil terkirim tanpa intervensi manual dalam 30 hari pertama.
- Penurunan keterlambatan pembayaran sponsor sebesar target tertentu (diukur setelah 3 bulan berjalan, dibandingkan baseline manual).
- 0 insiden pengiriman dobel ke sponsor yang sama.
- Waktu admin yang dihabiskan untuk reminder manual berkurang signifikan (diukur via survei staf).

---

## 12. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Nomor HP/email sponsor tidak valid/lama | Reminder gagal terkirim | Validasi input ketat + laporan gagal kirim ke admin untuk follow-up manual |
| Biaya WhatsApp membengkak seiring jumlah sponsor | Cost overrun | Batasi WhatsApp hanya di wave kritis (H-3, H-0, overdue); Telegram (gratis) dipakai di wave awal |
| Sponsor belum onboarding Telegram (belum `/start` ke bot) | Reminder Telegram tidak terkirim ke sebagian sponsor | Dashboard menampilkan status onboarding per sponsor; kirim link onboarding lewat Email/WhatsApp saat sponsor baru didaftarkan |
| Nomor WhatsApp Business gagal verifikasi/proses lama | Go-live tertunda | Mulai proses verifikasi Meta Business di awal proyek (paralel dengan development), siapkan rencana B pakai provider lokal jika mepet deadline |
| Provider SMTP/WhatsApp/Telegram down | Reminder tertunda | Retry otomatis via Queue; fallback antar-channel bila salah satu gagal |
| Data sponsor di-input staf secara tidak konsisten (format tanggal/HP) | Perhitungan jatuh tempo salah | Validasi form ketat + format terstandar (E.164, ISO date) |
| Vendor lock-in ke satu provider WhatsApp | Sulit pindah provider | Gunakan Notification Channel abstraction Laravel agar provider WhatsApp mudah diganti tanpa ubah kode inti |

---

## 13. Roadmap Setelah v1.0

| Versi | Fitur |
|---|---|
| v1.1 | Import sponsor massal via CSV/Excel |
| v1.2 | Konfirmasi pembayaran dua-arah via Telegram (sponsor balas chat untuk update status donasi) |
| v1.3 | Halaman konfirmasi pembayaran (sponsor klik link di email untuk update status donasi) |
| v1.4 | Aktivasi channel **SMS** sebagai fallback terakhir untuk sponsor overdue yang tidak responsif di channel lain (kerangka kode sudah ada sejak v1.0, lihat 6.4.1) |
| v1.5 | Laporan analitik (tren keterlambatan, retensi sponsor per periode) |
| v2.0 | Multi-tenant (mendukung lebih dari satu yayasan dalam satu instalasi) |

---

## 14. Lampiran

### 14.1 Contoh Variabel `.env`
```
APP_NAME="Sponsor Reminder System"
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=sponsor_reminder
DB_USERNAME=
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="reminders@yayasan.org"
MAIL_FROM_NAME="Yayasan Peduli Anak Yatim"

WHATSAPP_CLOUD_API_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
# Alternatif jika pakai provider lokal (Watzap.id/Zenziva) alih-alih Cloud API langsung:
# WHATSAPP_PROVIDER_API_KEY=
# WHATSAPP_PROVIDER_ENDPOINT=

TELEGRAM_BOT_TOKEN=
TELEGRAM_BOT_USERNAME=
TELEGRAM_WEBHOOK_SECRET=

# --- SMS (disiapkan untuk v1.x berikutnya, non-aktif secara default) ---
# Isi & set SMS_ENABLED=true kapan pun channel ini ingin diaktifkan,
# tanpa perlu migrasi database atau deploy ulang kode inti.
SMS_ENABLED=false
SMS_PROVIDER=twilio
SMS_API_KEY=
SMS_API_SECRET=
SMS_FROM=

QUEUE_CONNECTION=database
```

### 14.2 Estimasi Biaya Operasional Bulanan (Perkiraan Kasar)

| Item | Estimasi | Catatan |
|---|---|---|
| Hosting (shared/VPS kecil) | Rp20.000 – Rp150.000/bln | Tergantung jumlah sponsor & traffic dashboard |
| Email (SMTP provider) | Gratis – Rp0 | Kebanyakan provider (Brevo dll) free tier cukup untuk skala yayasan kecil-menengah |
| Telegram Bot API | Rp0 | Selamanya gratis, tanpa batas kuota pesan |
| WhatsApp Cloud API | Bervariasi per pesan (kategori "utility" relatif murah) | Biaya real baru muncul setelah free tier awal Meta habis; kontrol dengan membatasi wave WhatsApp hanya di reminder kritis |
| Domain | ~Rp150.000/tahun (opsional) | Bisa pakai subdomain gratis di awal |

Estimasi ini bersifat kasar dan wajib dikonfirmasi ulang dengan harga real Meta/provider serta jumlah sponsor aktual milik klien sebelum dituangkan ke penawaran/kontrak.

### 14.3 Definisi Status
- `active`: sponsor masih berjalan, akan menerima reminder.
- `paused`: sementara tidak menerima reminder (mis. sedang cuti donasi).
- `cancelled`: berhenti permanen, tidak dihitung dalam job apa pun.

---

**Catatan:** Dokumen ini adalah dasar untuk pengembangan; detail teknis final (skema tabel persis, nama route, wireframe UI) akan dituangkan di dokumen technical design terpisah sebelum development dimulai.