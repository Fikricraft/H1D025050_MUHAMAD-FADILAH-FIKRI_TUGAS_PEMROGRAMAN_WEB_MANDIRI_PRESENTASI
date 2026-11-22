# IKAN// PROTOCOL — Fish Habit Tracker

Aplikasi pemantau disiplin perawatan ikan: checklist pakan, kualitas air,
sanitasi, dan kesehatan sirip. Dibangun bertahap mengikuti materi praktikum
Pemrograman Web (Modul 1–7): HTML, HTML lanjut, CSS, JavaScript, PHP dasar,
PHP CRUD, dan PHP Session.

---

## 1. Cara Menjalankan

### Cara cepat (tanpa XAMPP)
1. Klik dua kali **`JALANKAN_WEBSITE.bat`**.
2. Browser terbuka otomatis ke `http://localhost:8000/login.php`.
3. Login: **admin / password123**.

### Cara XAMPP (disarankan, agar data tersimpan)
1. Salin seluruh folder ini ke `C:\xampp\htdocs\`.
2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin` → tab **SQL** → tempel isi
   `database.sql` → **Go**.
4. Buka `http://localhost/TUGAS_PEMROGRAMAN_WEB_MANDIRI_PRESENTASI/login.php`.
5. Login: **admin / password123**.

Tanpa MySQL aplikasi tetap jalan dalam **mode demo** (data contoh dari
`includes/functions.php`, tanpa penyimpanan permanen).

---

## 2. Peta Materi Modul → Kode

| Modul | Materi | Diimplementasikan pada |
||---|---|---|
|| 1 | HTML dasar (struktur, heading, teks, gambar, list) | seluruh halaman; `ikan/detail.php` (list & heading), `login.php` |
|| 2 | HTML lanjut (layout, link, form, tabel) | `login.php`, `register.php`, `profil.php`, semua form CRUD, seluruh `<table class="table-cyber">` |
|| 3 | CSS (inline/internal/external, selector, box model, background) | `assets/css/style.css` (tema dashboard), `assets/css/pages.css` (halaman dalam + grafik air), variabel CSS, flex/grid |
|| 4 | JavaScript (variabel, loop, event, validasi, DOM) | `assets/js/script.js` (dashboard interaktif), `assets/js/pages.js` (konfirmasi, counter, filter), kalender dashboard, fetch parameter air |
|| 5 | PHP dasar (variabel, array, include, function, if/foreach) | `includes/functions.php`, `includes/header.php`, `includes/footer.php`, `laporan.php` |
|| 6 | PHP CRUD (koneksi, create, read, update, delete) | `ikan/*` (tabel `fishes`), `habit/*` (tabel `habits`), `air/*` (tabel `water_params`), `pakan/*` (tabel `feedings`), `api.php` (CRUD via JSON) |
|| 7 | PHP Session (login, register, logout, cookie, guard) | `includes/auth.php`, `login.php`, `register.php`, `logout.php`, `profil.php` |

---

## 3. Struktur Folder

```
TUGAS_PEMROGRAMAN_WEB_MANDIRI_PRESENTASI/
├── assets/
│   ├── css/
│   │   ├── style.css          # tema utama dashboard (deep abyss)
│   │   └── pages.css          # gaya halaman dalam + auth
│   ├── js/
│   │   ├── script.js          # logika dashboard (DOM, fetch API, localStorage)
│   │   └── pages.js           # helper halaman: konfirmasi, counter, filter
│   ├── img/
│   │   ├── favicon.svg
│   │   └── ikan-placeholder.svg
│   └── models/
│       └── alien_fish_animated.glb   # model 3D ikan
│
├── includes/
│   ├── config.php             # konstanta, session, BASE_URL, timezone
│   ├── koneksi.php            # koneksi mysqli + helper db_query/db_row/db_all
│   ├── auth.php               # login, logout, guard, cookie "ingat saya"
│   ├── functions.php          # escape, flash, tanggal Indonesia, statistik
│   ├── header.php             # kerangka atas halaman dalam + navigasi
│   └── footer.php             # kerangka bawah halaman dalam
│
├── air/                       # CRUD parameter air
│   ├── index.php              # grafik tren 7 hari, status, riwayat + aksi
│   ├── tambah.php             # CREATE data tes air
│   ├── edit.php               # UPDATE
│   ├── hapus.php              # DELETE (khusus POST)
│   └── form_fields.php        # potongan form tambah & edit
│
├── pakan/                     # CRUD catatan pakan
│   ├── index.php              # riwayat pakan, ringkasan, daftar jenis pakan
│   ├── tambah.php             # CREATE catatan pakan
│   ├── edit.php               # UPDATE
│   ├── hapus.php              # DELETE (khusus POST)
│   └── form_fields.php        # form jenis pakan custom + satuan
│
├── ikan/                      # CRUD data spesimen (tabel fishes)
│   ├── index.php              # daftar + pencarian + statistik
│   ├── tambah.php             # CREATE
│   ├── detail.php             # READ satu data + grafik 7 hari
│   ├── edit.php               # UPDATE
│   └── hapus.php              # DELETE (khusus POST)
│
├── habit/                     # CRUD master habit (tabel habits)
│   ├── index.php
│   ├── tambah.php
│   ├── edit.php
│   └── hapus.php
│
├── log/
│   ├── index.php              # riwayat checklist per tanggal
│   └── toggle.php             # centang / batalkan centang (tanpa AJAX)
│
├── index.php                  # dashboard (checklist harian + modal CRUD + 3D + kartu air)
├── login.php                  # autentikasi (session)
├── register.php               # pendaftaran user (password_hash)
├── logout.php                 # hapus session + cookie
├── profil.php                 # data akun, ganti password, tema (cookie)
├── laporan.php                # statistik, streak, matriks 7 hari, ekspor CSV
├── api.php                    # endpoint JSON untuk dashboard (CRUD habit, log, air)
├── database.sql               # satu file: skema + data awal 7 tabel
├── test_orientation.html      # utilitas kalibrasi rotasi model 3D
├── JALANKAN_WEBSITE.bat       # server lokal satu klik (port 8000)
└── README.md
```

---

## 4. Skema Database (`fish_habit_tracker`)

| Tabel | Kolom penting | Keterangan |
|---|---|---|
| `users` | id, username, password (hash), nama_ikan, jenis_ikan | akun untuk login |
| `habits` | id, user_id, nama_habit, kategori, waktu, emblem, target_harian | master protokol |
| `habit_logs` | id, habit_id, tanggal, status, completed_at | riwayat centang; `UNIQUE(habit_id, tanggal)` |
| `fishes` | id, user_id, nama, jenis, ukuran_cm, umur_bulan, kondisi, catatan | data spesimen |
| `water_params` | id, user_id, tanggal, ph, suhu_c, ammonia, nitrit, nitrat, catatan | parameter kualitas air harian |
| `feedings` | id, user_id, fish_id, tanggal, waktu, jenis_pakan, jumlah, satuan, habis, catatan | riwayat pemberian pakan |
| `pakan_jenis` | id, user_id, nama, `UNIQUE(user_id, nama)` | jenis pakan buatan pengguna |

Relasi: `users` 1—N `habits`, `habits` 1—N `habit_logs`,
`users` 1—N `fishes`, `users` 1—N `water_params`, `users` 1—N `feedings`,
`fishes` 1—N `feedings`, `users` 1—N `pakan_jenis`. Semua memakai
`ON DELETE CASCADE`, kecuali `feedings.fish_id` memakai `ON DELETE SET NULL`.

Semua tabel berada dalam **satu database**: `fish_habit_tracker`.
Cukup impor `database.sql` sekali; file itu aman dijalankan berulang kali
tanpa membuat tabel atau data ganda.

---

## 5. Fitur Baru Versi 2.1

| Fitur | Halaman | Keterangan |
|---|---|---|
| Parameter air | `air/index.php`, `air/tambah.php`, `air/edit.php`, `air/hapus.php` | CRUD penuh: catat, ubah, hapus pH, suhu, ammonia, nitrit, nitrat. Grafik tren 7 hari dan status otomatis. |
| Data pakan | `pakan/index.php`, `pakan/tambah.php`, `pakan/edit.php`, `pakan/hapus.php` | CRUD penuh: catat, ubah, hapus jenis pakan, jumlah, waktu, status habis atau sisa. |
| Jenis pakan custom | `pakan/*` + tabel `pakan_jenis` | Pilih dari daftar bawaan atau tulis jenis sendiri; jenis baru tersimpan untuk pemakaian berikutnya. |
| Kartu air dashboard | `index.php` | Menampilkan pH terbaru dan jumlah parameter aman via `api.php?action=get_water_params`. |

---

## 6. Alur Autentikasi

1. `register.php` menyimpan user baru dengan `password_hash()`.
2. `login.php` memanggil `attempt_login()` di `includes/auth.php` →
   mencocokkan dengan `password_verify()`.
3. Berhasil → `session_regenerate_id(true)` lalu data user masuk `$_SESSION`.
4. Setiap halaman memanggil `require_login()`; bila belum login, user
   dilempar ke `login.php?next=...`.
5. Opsi **"ingat saya"** menyimpan cookie `ikan_remember` berisi
   `username|HMAC`, sehingga tidak bisa dipalsukan.
6. `logout.php` menghapus session dan cookie.

Akun demo bila MySQL mati: `admin / password123`.

---

## 7. Catatan Keamanan

- Semua query yang menerima input memakai **prepared statement**.
- Output dinamis dilewatkan `htmlspecialchars()` (helper `e()`).
- Hapus data hanya menerima **POST**, bukan link GET.
- Setiap operasi data difilter dengan `user_id` dari session, sehingga user
  tidak bisa mengubah data user lain.
- `APP_SECRET` di `includes/config.php` masih hardcoded; pada aplikasi
  produksi pindahkan ke environment variable.

---

## 8. Jika Ada Masalah

| Gejala | Penyebab & solusi |
|---|---|
| "Koneksi database gagal" | MySQL belum Start, atau `database.sql` belum diimpor |
| Halaman kosong / error 500 | Cek `includes/config.php`, `display_errors` sudah aktif |
| Login selalu gagal padahal password benar | Password di database bukan hash bcrypt; jalankan ulang `database.sql` |
| Model 3D tidak muncul | Folder `assets/models/` belum tersalin, atau browser memblokir WebGL |
| Data tidak tersimpan | Berjalan di mode demo (MySQL mati); nyalakan MySQL |
| Sempat muncul pesan foreign key | Session lama menunjuk akun yang sudah tidak ada. Sejak v2.1 aplikasi menyinkronkan session dengan tabel `users` otomatis; cukup muat ulang halaman |
