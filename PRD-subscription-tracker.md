# PRD: Sistem Pencatatan Keuangan, Progress, Invoice, dan Masa Aktif Klien

**Versi:** 1.4
**Tanggal:** 17 November 2026
**Stack:** Laravel 12, PHP 8.3+, MySQL, AdminLTE 3 (Bootstrap 4) untuk UI, Chart.js, dompdf untuk PDF
**Penulis:** Alfin

---

## Bagian 0: Instruksi untuk AI Agent

Baca bagian ini dulu sebelum menulis kode apa pun.

### Konteks
Aplikasi internal yang dipakai satu orang (pemilik) untuk mencatat pekerjaan dari dua sumber: platform Fastwork dan klien langsung di luar Fastwork. Sistem mencatat potongan Fastwork, membuat invoice, menyimpan invoice yang diunggah manual, membuat draft perjanjian kerjasama, dan mengelola masa aktif layanan klien yang membayar per tahun.

### Aturan kerja
1. Kerjakan **per fase** sesuai bagian 12. Jangan membangun fitur fase berikutnya sebelum fase saat ini berjalan.
2. Tulis **kode lengkap dan bisa dijalankan**, bukan potongan atau pseudo-code.
3. Pilih implementasi **sederhana dan mudah dibaca**. Hindari repository pattern, service layer berlapis, atau event sourcing kecuali diminta.
4. Aplikasi ini **single user**. Tidak ada role, tidak ada Policy kompleks. Cukup middleware `auth` dan satu akun admin dari seeder.
5. Gunakan fitur bawaan Laravel 12: Form Request untuk validasi, Scheduler untuk tugas harian, Notification untuk email dan notifikasi database, Storage untuk file.
6. **UI memakai AdminLTE 3** (Bootstrap 4 + AdminLTE 3). Jangan memasang Tailwind, Livewire, atau Filament. Gunakan komponen bawaan AdminLTE: `small-box` untuk kartu ringkasan, `card` dan `table` untuk daftar, `modal` untuk konfirmasi, `select2` untuk pilihan klien atau order yang banyak. Tema dasar **putih (light)**. Minimalkan elemen tambahan: dashboard cukup kartu ringkasan, tabel, dan satu grafik Chart.js.
7. Semua teks antarmuka dan pesan validasi dalam **Bahasa Indonesia**. Kode dan nama kolom dalam **Bahasa Inggris** sesuai bagian 8.
8. Jika ada pertanyaan terbuka (bagian 13) yang memengaruhi desain, pakai nilai default, dan catat asumsinya di README.

### Konvensi teknis
- **Layout:** satu layout Blade `resources/views/layouts/admin.blade.php` yang meng-extend AdminLTE 3. Sidebar dibuat dari satu file konfigurasi menu.
- **Aset AdminLTE 3:** sudah tersedia di `public/assets/`. Panggil dengan `asset('assets/...')`, misalnya `asset('assets/css/adminlte.min.css')` dan `asset('assets/js/adminlte.min.js')`. Jangan memasang ulang AdminLTE lewat npm atau CDN. Pastikan path di layout sesuai dengan struktur folder yang sebenarnya sebelum menulis halaman lain.
- **Form dan tabel:** Blade biasa dengan Form Request untuk validasi. Tabel memakai DataTables (server-side untuk daftar order dan invoice) atau paginasi Laravel biasa untuk daftar kecil.
- **Uang:** simpan sebagai `unsignedBigInteger` dalam Rupiah tanpa desimal. Persentase komisi boleh `decimal(5,2)` karena bukan nilai uang. Format tampilan `Rp 1.500.000`.
- **Perhitungan potongan:** `platform_fee = round(gross_amount * commission_percent / 100) + fixed_fee`. Hasil dibulatkan ke Rupiah terdekat.
- **Tanggal:** simpan UTC, tampilkan `Asia/Jakarta`. Set `APP_TIMEZONE=Asia/Jakarta`.
- **Status langganan:** hitung dari `end_date` lewat accessor. Kolom status hanya cache yang diperbarui scheduler.
- **Penomoran invoice:** `INV/YYYY/MM/0001`, reset per bulan. Nomor perjanjian: `PKS/YYYY/0001`.
- **Soft delete** untuk `clients`, `orders`, `subscriptions`, `projects`. Invoice dan pembayaran tidak boleh dihapus, hanya dibatalkan.
- **Audit:** perubahan pada order, invoice, pembayaran, dan langganan tercatat di `activity_logs`.
- **File:** bukti dan invoice upload disimpan di disk privat dan dilayani lewat route yang terautentikasi.

### Yang TIDAK boleh dilakukan
- Jangan menghapus atau mengubah nilai invoice yang sudah lunas.
- Jangan menyimpan nilai uang sebagai `float`.
- Jangan membuat langganan kembali aktif setelah kedaluwarsa tanpa perpanjangan.
- Jangan mengirim notifikasi ganda. Gunakan kolom penanda seperti `reminder_30_sent_at`.
- Jangan menghitung ulang potongan Fastwork secara diam-diam saat rule berubah. Order lama tetap memakai nilai yang tersimpan.
- Jangan menambahkan integrasi payment gateway atau WhatsApp API di versi 1.

### Urutan kerja yang disarankan
1. Setup proyek, auth sederhana, seeder admin, pengaturan bisnis.
2. Master: platform, klien, paket layanan, kategori pengeluaran. Tests untuk perhitungan potongan.
3. Order dan pencatatan potongan Fastwork, lalu penarikan saldo.
4. Invoice generate PDF dan upload invoice.
5. Langganan dan masa aktif, lalu tests aturan bisnis bagian 10.
6. Perjanjian kerjasama dari template.
7. Lanjut ke fase berikutnya.

---

## 1. Ringkasan

Aplikasi pribadi untuk mencatat seluruh pekerjaan freelance: order dari Fastwork dan klien langsung, potongan platform, invoice, perjanjian kerjasama, progress proyek, dan masa aktif layanan tahunan klien.

## 2. Masalah

- Pendapatan Fastwork sulit dihitung bersih karena dipotong komisi dan biaya penarikan. Angka yang masuk ke rekening selalu lebih kecil dari harga order.
- Invoice untuk klien langsung dibuat manual dan sering tidak konsisten.
- Perjanjian kerjasama belum punya template yang selalu dipakai.
- Tanggal perpanjangan layanan tahunan sering terlewat.
- Progress proyek tidak terhubung dengan keuangan.

## 3. Tujuan

1. Mencatat harga kotor, potongan, dan nilai bersih setiap order, baik Fastwork maupun langsung.
2. Membuat invoice PDF otomatis dari order atau langganan, dan menyimpan invoice yang diunggah manual.
3. Membuat draft perjanjian kerjasama otomatis dari template.
4. Memantau masa aktif layanan tahunan klien dan memberi peringatan sebelum habis.
5. Menampilkan posisi keuangan bersih, termasuk saldo Fastwork yang belum ditarik.

## 4. Pengguna

**Satu pengguna (admin / pemilik).** Tidak ada role, tidak ada akun staff, tidak ada portal klien di versi 1. Login cukup email dan password.

## 5. Ruang Lingkup

**Termasuk versi 1:** master platform dan klien, order dengan potongan, penarikan saldo, invoice generate dan upload, template dan draft perjanjian kerjasama, langganan dan masa aktif, pembayaran, pengeluaran, proyek dan progress, notifikasi, dashboard, laporan dasar, ekspor Excel.

**Tidak termasuk versi 1:** API otomatis ke Fastwork (tidak tersedia secara publik, input manual atau CSV), payment gateway, portal klien, tanda tangan elektronik tersertifikasi, e-Faktur.

## 6. Kebutuhan Fungsional

### 6.1 Master Platform
Sumber pekerjaan. Default: **Fastwork** dan **Langsung** (klien di luar platform).

- Field: nama, kode, `commission_percent` (persen, boleh 0), `fixed_fee` (Rupiah, boleh 0), `withdrawal_fee` (Rupiah per penarikan), catatan, aktif/nonaktif.
- Rule potongan disimpan di master. Perubahan rule hanya berlaku untuk order baru.
- Order boleh menimpa angka potongan secara manual jika platform mengenakan biaya di luar rule (misalnya promo atau biaya tambahan). Nilai override harus diberi alasan.

### 6.2 Master Klien
- Field: nama, nama kontak/PIC, email, telepon, alamat, NPWP (opsional), nama perusahaan (opsional), catatan.
- **Sumber klien:** `platform_id` (Fastwork atau Langsung). Satu klien bisa punya order dari beberapa platform, sehingga platform juga bisa ditentukan per order.
- Username atau ID klien di Fastwork disimpan sebagai `external_ref` (opsional) untuk pencarian.
- Halaman detail klien: daftar order, invoice, perjanjian, langganan, pembayaran, total bersih yang sudah diterima.

### 6.3 Master Paket Layanan
- Field: nama, deskripsi, harga default, durasi default (dalam bulan), aktif/nonaktif.
- Dipakai untuk layanan berlangganan tahunan. Order satu kali juga boleh tanpa paket.

### 6.4 Master Template Dokumen
Template untuk invoice dan perjanjian kerjasama.

- Field: jenis (`invoice` | `agreement`), nama, isi (HTML dengan placeholder), default (ya/tidak).
- Placeholder dipakai dengan format `{{ nama_variabel }}`, contoh `{{ client.name }}`, `{{ order.gross_amount }}`.
- Template bisa diedit langsung dari aplikasi. Perubahan template tidak mengubah dokumen yang sudah dibuat.
- Pengaturan bisnis (nama usaha, alamat, rekening bank, nama penandatangan, logo) tersimpan di tabel `settings` dan tersedia sebagai variabel.

### 6.5 Master Kategori Pengeluaran
- Default: Server dan Hosting, Domain, Lisensi dan Software, Operasional, Lainnya.
- Admin bisa menambah kategori.

### 6.6 Order (Pekerjaan)
Setiap pekerjaan dari platform manapun.

- Field: klien, platform, judul pekerjaan, paket (opsional), `external_order_ref` (nomor order di Fastwork, opsional), tanggal order, deadline, tanggal selesai, status (`draft`, `in_progress`, `delivered`, `completed`, `cancelled`), jumlah revisi (untuk Fastwork), catatan.
- **Nilai keuangan:**
  - `gross_amount`: harga yang tercantum di order.
  - `platform_fee`: total potongan platform. Otomatis dari rule platform, bisa diubah dengan alasan.
  - `other_fee`: biaya lain (misalnya biaya tambahan extra service) dengan keterangan.
  - `net_amount`: `gross_amount - platform_fee - other_fee`. Dihitung otomatis.
- **Rincian potongan:** tampilkan breakdown dari rule (komisi persen, biaya tetap) agar mudah dicek saat membandingkan dengan laporan Fastwork.
- **Status pencairan:** `pending` (belum masuk saldo), `in_balance` (sudah masuk saldo Fastwork), `withdrawn` (sudah ditarik), atau `received` (untuk order langsung, sudah masuk rekening).
- Order langsung tidak punya potongan platform, kecuali biaya transfer jika ingin dicatat di `other_fee`.
- Order bisa dikaitkan ke satu proyek (opsional) dan satu invoice atau lebih.

### 6.7 Penarikan Saldo (Withdrawal)
Khusus platform yang punya saldo, dalam hal ini Fastwork.

- Field: platform, tanggal tarik, jumlah diterima di rekening, biaya penarikan, nama bank, referensi, catatan.
- Saat penarikan dicatat, order yang berstatus `in_balance` dan termasuk dalam penarikan diubah menjadi `withdrawn`. Admin memilih order mana saja yang termasuk.
- Dashboard menampilkan saldo Fastwork yang belum ditarik: total `net_amount` order berstatus `in_balance`.

### 6.8 Invoice
Dua jenis invoice:

**A. Invoice generate.** Dibuat dari order atau langganan.
- Pilih order atau langganan, pilih template, sistem mengisi nomor, tanggal terbit, jatuh tempo (default 14 hari), item, subtotal, pajak (opsional, default nol), total, dan data rekening bank dari pengaturan.
- Hasil berupa PDF yang bisa diunduh dan dikirim.
- Invoice generate tidak boleh diubah setelah dikirim. Jika perlu koreksi, batalkan dan buat baru.

**B. Invoice upload.** Invoice yang dibuat di luar sistem (misalnya dari aplikasi lain atau sudah ditandatangani).
- Upload file PDF atau gambar, maksimal 5 MB.
- Wajib dikaitkan ke order atau langganan, dan diisi nomor, tanggal, dan total.
- Ditandai `source = uploaded` agar mudah dibedakan.

Status invoice: `draft`, `sent`, `unpaid`, `partial`, `paid`, `cancelled`.

**Catatan order Fastwork:** invoice untuk order Fastwork biasanya tidak diperlukan karena pembayaran lewat platform. Sistem tetap mengizinkan pembuatan invoice jika diminta, dan invoice tersebut menampilkan harga kotor, bukan nilai bersih.

### 6.9 Perjanjian Kerjasama (Draft Otomatis)
- Pilih klien, pilih template perjanjian, dan (opsional) pilih order atau langganan terkait.
- Sistem mengisi variabel otomatis: identitas kedua pihak, deskripsi pekerjaan, nilai kontrak, termin pembayaran, jangka waktu, masa revisi, tanggal mulai dan berakhir, nomor perjanjian, tanggal pembuatan.
- Variabel yang belum terisi ditandai kuning dan tidak boleh dicetak sebelum lengkap.
- Hasil berupa draft yang bisa diedit di aplikasi dan diunduh sebagai PDF atau DOCX.
- Status: `draft` → `sent` → `signed` (setelah upload file yang sudah ditandatangani) atau `cancelled`.
- Setiap perubahan isi membuat versi baru. Versi lama tetap bisa dilihat.
- Klausul standar yang disarankan di template: lingkup pekerjaan, nilai dan termin pembayaran, masa revisi, hak kekayaan intelektual, kerahasiaan, pembatalan, penyelesaian sengketa, dan tanda tangan kedua pihak.

Catatan: template dan klausul hukum harus dibaca dan disesuaikan sendiri. Sistem hanya membantu mengisi dan menyusun dokumen.

### 6.10 Langganan dan Masa Aktif (Layanan Tahunan)
- Field: klien, paket, tanggal mulai, tanggal berakhir, nilai kontrak, status, langganan sebelumnya, catatan, alasan pembekuan.
- Tanggal berakhir otomatis = tanggal mulai + 1 tahun - 1 hari. Bisa diubah manual.
- Status dihitung otomatis:
  - **Aktif:** lebih dari 30 hari sebelum berakhir.
  - **Akan berakhir:** 30 hari atau kurang.
  - **Kedaluwarsa:** lewat tanggal berakhir.
  - **Dibekukan:** diset manual dengan alasan.
  - **Diperpanjang:** sudah ada langganan lanjutan.
- Perpanjangan membuat langganan baru. Tanggal mulai baru = tanggal berakhir lama + 1 hari. Tagihan perpanjangan dibuat otomatis.

### 6.11 Pembayaran Masuk
- Field: invoice (atau order untuk pembayaran langsung), tanggal bayar, jumlah, metode (transfer, tunai, lainnya), nomor referensi, bukti (opsional).
- Pembayaran melebihi sisa tagihan ditolak.
- Pembayaran yang melunasi tagihan mengubah status invoice menjadi `paid`, dan jika terkait langganan, langganan menjadi aktif.

### 6.12 Pengeluaran
- Field: kategori, tanggal, jumlah, deskripsi, proyek (opsional), klien (opsional), bukti (opsional).

### 6.13 Proyek dan Progress
- Proyek terhubung ke klien dan opsional ke order. Field: nama, deskripsi, tanggal mulai, target selesai, status (`planning`, `running`, `done`, `on_hold`).
- Tahapan default: Analisis, Desain, Development, Testing, Deploy, Maintenance. Bisa diubah per proyek.
- Setiap tahapan punya bobot (total 100), status (`todo`, `doing`, `done`), tanggal target, tanggal aktual, dan `progress_percent` (0–100) untuk tahapan yang sedang berjalan.
- Persentase proyek = jumlah (bobot × progress tahapan) / 100.

### 6.14 Notifikasi
Kanal: notifikasi di aplikasi dan email. Dikirim lewat scheduler harian pukul 08:00 WIB.
- Masa aktif akan berakhir dalam 30, 14, dan 7 hari.
- Masa aktif sudah berakhir.
- Invoice melewati jatuh tempo.
- Deadline order dalam 3 hari dan belum selesai.
- Tahapan proyek melewati target.
- Setiap pemicu hanya terkirim sekali per objek.

### 6.15 Dashboard
Halaman utama setelah login.

1. **Kartu ringkasan:**
   - Pendapatan bersih bulan ini (dari `net_amount` order yang selesai dan pembayaran masuk).
   - Total potongan platform bulan ini.
   - Saldo Fastwork belum ditarik.
   - Tagihan belum lunas.
   - Klien dengan masa aktif habis dalam 30 hari.
2. **Perlu tindakan:** deadline order terdekat, invoice jatuh tempo, langganan akan berakhir.
3. **Grafik:** pendapatan kotor vs bersih per bulan, 12 bulan terakhir. Satu grafik.
4. **Proyek aktif:** daftar dengan bar progress.

Prinsip tampilan:
- Mengikuti AdminLTE 3 standar: sidebar, navbar, dan card. Tidak ada elemen dekoratif tambahan.
- Responsif untuk desktop dan tablet. Mobile cukup bisa dibaca.
- Tema dasar putih (light). Latar konten putih, sidebar dan navbar mengikuti tema light bawaan AdminLTE 3. Mode gelap tidak dibuat di versi 1.

### 6.16 Laporan
- **Rekap per platform:** jumlah order, total kotor, total potongan, total bersih, per periode.
- **Rekap potongan:** daftar semua potongan Fastwork per bulan, untuk dicocokkan dengan laporan dari Fastwork dan untuk keperluan pencatatan pajak.
- **Rekap penarikan:** riwayat penarikan dan biayanya.
- **Pendapatan dan pengeluaran** per periode.
- **Piutang:** invoice belum lunas per klien.
- Ekspor semua laporan ke Excel.

### 6.17 Pengaturan
- Data usaha: nama, alamat, NPWP (opsional), rekening bank, nama penandatangan, logo.
- Default jatuh tempo invoice, default masa revisi, format nomor dokumen.
- Zona waktu dan mata uang (IDR).

## 7. Alur Utama

**A. Order Fastwork**
1. Buat order, pilih platform Fastwork, isi harga kotor.
2. Sistem menghitung potongan dari rule, lalu menampilkan nilai bersih.
3. Saat status di Fastwork sudah selesai, ubah status order menjadi `completed` dan pencairan menjadi `in_balance`.
4. Saat menarik saldo, buat penarikan dan pilih order yang termasuk. Order menjadi `withdrawn`.

**B. Order langsung dengan invoice generate**
1. Buat order, platform Langsung.
2. Buat draft perjanjian kerjasama dari template, lalu kirim ke klien dan unggah versi yang sudah ditandatangani.
3. Buat invoice generate dari order, unduh PDF, kirim.
4. Catat pembayaran saat transfer masuk. Invoice menjadi `paid`.

**C. Invoice upload**
1. Buka order atau langganan, pilih "Unggah invoice".
2. Isi nomor, tanggal, total, lalu upload file.
3. Invoice tampil di daftar dengan tanda `uploaded`.

**D. Perpanjangan layanan tahunan**
1. Notifikasi 30 hari sebelum berakhir.
2. Klik "Perpanjang". Sistem membuat langganan dan tagihan baru.
3. Catat pembayaran. Langganan baru aktif, lama berstatus `renewed`.

## 8. Model Data

Gunakan nama tabel dan kolom ini persis.

- `users`: id, name, email, password, timestamps
- `settings`: id, key (unique), value (text), timestamps
- `platforms`: id, name, code (unique), commission_percent decimal(5,2), fixed_fee, withdrawal_fee, notes, is_active, timestamps
- `clients`: id, platform_id (nullable, sumber utama), name, company_name, contact_name, email, phone, address, npwp, external_ref, notes, soft deletes, timestamps
- `service_packages`: id, name, description, default_price, default_duration_months, is_active, timestamps
- `document_templates`: id, type (`invoice`|`agreement`), name, body (longtext), is_default, timestamps
- `orders`: id, client_id, platform_id, service_package_id (nullable), title, external_order_ref (nullable), ordered_at, deadline, completed_at, status (`draft`|`in_progress`|`delivered`|`completed`|`cancelled`), revision_count, gross_amount, platform_fee, platform_fee_override_reason (nullable), other_fee, other_fee_note (nullable), net_amount, payout_status (`pending`|`in_balance`|`withdrawn`|`received`), project_id (nullable), notes, soft deletes, timestamps
- `withdrawals`: id, platform_id, withdrawn_at, amount_received, fee, bank_name, reference_no (nullable), notes, timestamps
- `withdrawal_orders`: id, withdrawal_id, order_id (pivot)
- `subscriptions`: id, client_id, service_package_id, previous_subscription_id (nullable), start_date, end_date, contract_value, status (`active`|`expiring`|`expired`|`frozen`|`renewed`), freeze_reason (nullable), reminder_30_sent_at, reminder_14_sent_at, reminder_7_sent_at, expired_notified_at, notes, soft deletes, timestamps
- `invoices`: id, number (unique), source (`generated`|`uploaded`), order_id (nullable), subscription_id (nullable), template_id (nullable), issued_at, due_date, subtotal, tax_amount, total, status (`draft`|`sent`|`unpaid`|`partial`|`paid`|`cancelled`), pdf_path (nullable), file_path (nullable, untuk upload), overdue_notified_at, timestamps
- `payments`: id, invoice_id, paid_at, amount, method (`transfer`|`cash`|`other`), reference_no (nullable), proof_path (nullable), timestamps
- `agreements`: id, client_id, order_id (nullable), template_id, number (unique), title, status (`draft`|`sent`|`signed`|`cancelled`), current_version, signed_file_path (nullable), signed_at (nullable), timestamps
- `agreement_versions`: id, agreement_id, version_number, variables (json), body_rendered (longtext), pdf_path (nullable), docx_path (nullable), created_at
- `expense_categories`: id, name, timestamps
- `expenses`: id, expense_category_id, project_id (nullable), client_id (nullable), spent_at, amount, description, proof_path (nullable), timestamps
- `projects`: id, client_id, name, description, start_date, target_date, status (`planning`|`running`|`done`|`on_hold`), soft deletes, timestamps
- `project_stages`: id, project_id, name, weight, progress_percent (0–100), status (`todo`|`doing`|`done`), target_date, actual_date, position, timestamps
- `activity_logs`: id, user_id, subject_type, subject_id, action, changes (json), created_at

Catatan: `platform_fee` pada order disimpan hasil hitung saat order dibuat, supaya perubahan rule di masa depan tidak mengubah data lama.

## 9. Kebutuhan Non-Fungsional

- **Keamanan:** login dengan password hash, CSRF, validasi server-side, semua file privat dilayani lewat route terautentikasi.
- **Performa:** dashboard dimuat dalam 2 detik untuk data hingga 5.000 order. Index pada `orders.status`, `orders.payout_status`, `invoices.due_date`, `subscriptions.end_date`.
- **Backup:** database harian dan folder file privat mingguan.
- **Mata uang:** IDR integer.
- **Zona waktu:** Asia/Jakarta untuk tampilan dan perhitungan tanggal.
- **Ekspor:** semua tabel utama bisa diekspor ke Excel.
- **Aset UI:** AdminLTE 3 dan Bootstrap 4 dimuat dari `public/assets/`, bukan CDN, supaya aplikasi tetap jalan bila CDN bermasalah. Chart.js boleh dimuat dari `public/assets/` juga agar konsisten.

### Paket yang dipakai
- `laravel/framework` 12 dan `php` 8.3+
- `jeroennoten/laravel-adminlte` hanya jika ingin dipakai. Karena aset AdminLTE 3 sudah ada di `public/assets/`, layout Blade manual lebih sederhana dan tidak perlu package ini.
- `yajra/laravel-datatables` untuk tabel server-side
- `barryvdh/laravel-dompdf` untuk PDF invoice dan perjanjian
- `phpoffice/phpword` untuk draft perjanjian dalam DOCX (fase 4)
- `maatwebsite/excel` untuk ekspor Excel
- `chart.js` (npm) untuk grafik dashboard

## 10. Aturan Bisnis

1. Potongan platform = `round(gross × commission_percent / 100) + fixed_fee`. Net = gross − potongan − other_fee.
2. Order yang sudah `withdrawn` tidak bisa diubah nilainya.
3. Invoice yang sudah `paid` tidak bisa diubah nilainya. Koreksi dilakukan dengan pembatalan dan invoice baru.
4. Pembayaran tidak boleh melebihi sisa tagihan.
5. Perjanjian yang sudah `signed` tidak bisa diedit. Perubahan dibuat sebagai perjanjian baru atau versi baru yang statusnya kembali `draft`.
6. Perpanjangan layanan hanya bisa dilakukan dalam 60 hari sebelum berakhir, atau setelah kedaluwarsa. Admin bisa override.
7. Langganan kedaluwarsa tidak kembali aktif tanpa perpanjangan.
8. Langganan dibekukan tidak memicu notifikasi masa aktif.

Contoh: langganan mulai 1 Maret 2026, berakhir 28 Februari 2027. Notifikasi 30 hari dikirim 29 Januari 2027. Perpanjangan mulai 1 Maret 2027.

Contoh potongan: order Fastwork Rp 1.000.000 dengan komisi 20% dan biaya tetap Rp 0 maka potongan Rp 200.000, net Rp 800.000. Angka ini hanya contoh; isi rule sesuai yang berlaku di Fastwork.

## 11. Metrik Keberhasilan

- Selisih antara net yang dihitung sistem dan saldo yang ditarik dari Fastwork bisa dijelaskan dalam satu halaman rekap.
- Invoice generate selesai dalam kurang dari 1 menit dari pilih order sampai PDF siap.
- Tidak ada masa aktif yang berakhir tanpa notifikasi sebelumnya.
- Posisi keuangan bulan berjalan terlihat di dashboard tanpa membuka menu lain.

## 12. Rencana Fase

| Fase | Isi | Estimasi |
|---|---|---|
| 1 | Setup, auth, settings, master platform dan klien, paket layanan, kategori pengeluaran, tests perhitungan potongan | 1,5 minggu |
| 2 | Order dengan potongan, penarikan saldo, pembayaran masuk | 1,5 minggu |
| 3 | Template dokumen, invoice generate PDF, invoice upload | 1,5 minggu |
| 4 | Perjanjian kerjasama dari template, versi, upload signed | 1 minggu |
| 5 | Langganan dan masa aktif, perpanjangan | 1 minggu |
| 6 | Proyek, tahapan, progress, activity log | 1 minggu |
| 7 | Dashboard AdminLTE, laporan, ekspor Excel, notifikasi, scheduler | 1,5 minggu |
| 8 | Testing, perbaikan, deploy | 1 minggu |

Estimasi total sekitar 9,5 minggu untuk satu developer.

## 13. Pertanyaan Terbuka

Gunakan nilai default jika belum ada jawaban.

1. Apakah pajak (PPh atau PPN) perlu dicatat dan ditampilkan di invoice? **Default: tidak, dicatat manual di `tax_amount` jika perlu.**
2. Apakah rule potongan Fastwork sama untuk semua kategori jasa? **Default: ya, satu rule per platform. Bisa diubah nanti.**
3. Apakah invoice untuk order Fastwork perlu dibuat? **Default: tidak wajib, tetap tersedia.**
4. Format nomor invoice dan perjanjian mengikuti kebiasaan yang sudah ada? **Default: ikuti format di bagian 4.**
5. Apakah perlu menyimpan data bank klien? **Default: tidak.**

## 14. Ide Penyempurnaan (Kandidat, Bukan Wajib)

Urut dari yang paling berguna untuk alur kerja kamu.

1. **Impor CSV dari Fastwork.** Kalau Fastwork menyediakan riwayat pesanan atau penarikan yang bisa diunduh, impor CSV lebih cepat daripada input manual dan mengurangi salah ketik. Cocokkan dengan `external_order_ref`.
2. **Kalkulator harga bersih.** Input nilai bersih yang diinginkan, sistem menghitung harga yang harus dipasang di Fastwork agar bersih sesuai target. Sangat berguna saat menentukan harga paket.
3. **Pengingat follow-up pembayaran.** Tombol "Kirim pengingat" di invoice yang membuka tautan WhatsApp (`wa.me`) dengan teks yang sudah terisi dari template. Tidak perlu integrasi API.
4. **Tautan invoice publik read-only.** Invoice bisa dibagikan lewat tautan dengan token acak dan masa berlaku. Klien tidak perlu login.
5. **Saldo tertahan Fastwork.** Pisahkan order yang sudah selesai tapi belum masuk saldo (`pending`) dari yang sudah masuk saldo (`in_balance`). Dashboard menampilkan keduanya agar tahu uang yang akan masuk.
6. **Pencatatan waktu per proyek.** Catat jam kerja per proyek. Laporan menampilkan pendapatan bersih per jam, sehingga terlihat order mana yang paling menguntungkan.
7. **Counter revisi dengan batas.** Setiap order punya batas revisi dari paket. Saat batas hampir habis, sistem memberi peringatan. Berguna untuk mencegah revisi tanpa batas.
8. **Versi template dokumen.** Simpan riwayat perubahan template invoice dan perjanjian, supaya tahu klausul mana yang dipakai pada dokumen lama.
9. **Ringkasan pajak tahunan.** Rekap omzet bruto dan bersih per tahun, plus total potongan, sebagai bahan untuk konsultan pajak. Tidak menghitung pajak otomatis kecuali diminta.
10. **Backup terenkripsi dan ekspor penuh.** Satu tombol untuk mengunduh seluruh database dan file dalam satu arsip terenkripsi.

---

## Catatan Penutup

Dokumen ini adalah perencanaan dan bukan nasihat hukum atau pajak. Template perjanjian dan perhitungan pajak perlu diperiksa oleh pihak yang berwenang sebelum dipakai untuk klien.
