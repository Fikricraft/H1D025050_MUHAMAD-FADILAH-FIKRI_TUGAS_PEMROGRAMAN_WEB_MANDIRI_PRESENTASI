<?php

function e($teks): string
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function redirect(string $tujuan): void
{
    header('Location: ' . $tujuan);
    exit;
}

function pesan_gagal(string $objek): string
{
    return 'Data ' . $objek . ' gagal disimpan. Periksa kembali isian, lalu coba lagi.';
}

function set_flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function take_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function nama_hari(string $tanggal): string
{
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    return $hari[(int)date('w', strtotime($tanggal))];
}

function nama_bulan(int $bulan): string
{
    $bulan_indonesia = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    return $bulan_indonesia[$bulan] ?? '';
}

function tanggal_indonesia(string $tanggal): string
{
    $ts = strtotime($tanggal);

    return date('j', $ts) . ' ' . nama_bulan((int)date('n', $ts)) . ' ' . date('Y', $ts);
}

function selisih_hari(string $tanggal): int
{
    $awal  = new DateTime(date('Y-m-d'));
    $akhir = new DateTime(date('Y-m-d', strtotime($tanggal)));

    return (int)$awal->diff($akhir)->format('%r%a');
}

function jam_ringkas(string $waktu): string
{
    return date('H:i', strtotime($waktu));
}

function demo_habits(): array
{
    return [
        ['id' => 1, 'nama_habit' => 'Beri pakan pelet berprotein tinggi', 'kategori' => 'PAKAN', 'waktu' => '07:30 WIB', 'emblem' => '🐟', 'target_harian' => 1],
        ['id' => 2, 'nama_habit' => 'Cek suhu air (25°C - 28°C) & heater', 'kategori' => 'SUHU & LAMPU', 'waktu' => '09:00 WIB', 'emblem' => '🌡️', 'target_harian' => 1],
        ['id' => 3, 'nama_habit' => 'Siphon kotoran dasar & cuci busa filter', 'kategori' => 'SANITASI', 'waktu' => '14:00 WIB', 'emblem' => '🧽', 'target_harian' => 1],
        ['id' => 4, 'nama_habit' => 'Inspeksi sirip, sisik & keaktifan renang', 'kategori' => 'KESEHATAN', 'waktu' => '16:00 WIB', 'emblem' => '🐠', 'target_harian' => 1],
        ['id' => 5, 'nama_habit' => 'Pakan cacing sutra / artemia sore', 'kategori' => 'PAKAN', 'waktu' => '17:30 WIB', 'emblem' => '🐡', 'target_harian' => 1],
        ['id' => 6, 'nama_habit' => 'Water change 20% air endapan anti-klorin', 'kategori' => 'KUALITAS AIR', 'waktu' => 'Setiap 2 Hari', 'emblem' => '💧', 'target_harian' => 1],
    ];
}

function demo_fishes(): array
{
    return [
        ['id' => 1, 'nama' => 'Alpha Striker', 'jenis' => 'Channa Marulioides', 'ukuran_cm' => 32.5, 'umur_bulan' => 14, 'kondisi' => 'SEHAT', 'catatan' => 'Spesimen utama, agresif saat makan.', 'emblem' => '🐟'],
        ['id' => 2, 'nama' => 'Beta Nebula', 'jenis' => 'Betta splendens', 'ukuran_cm' => 6.0, 'umur_bulan' => 8, 'kondisi' => 'SEHAT', 'catatan' => 'Sirip lebar, perlu air tenang.', 'emblem' => '🐠'],
        ['id' => 3, 'nama' => 'Gamma Abyss', 'jenis' => 'Paracheirodon innesi', 'ukuran_cm' => 3.2, 'umur_bulan' => 5, 'kondisi' => 'PERHATIAN', 'catatan' => 'Nafsu makan menurun dua hari terakhir.', 'emblem' => '🐡'],
    ];
}

function ambil_habits(int $user_id): array
{
    global $db_connected;

    if (!$db_connected) {
        return demo_habits();
    }

    return db_all(
        "SELECT id, nama_habit, kategori, waktu, emblem, target_harian
         FROM habits WHERE user_id = ? ORDER BY id ASC",
        'i',
        [$user_id]
    );
}

function statistik_harian(array $habits, array $completed_ids): array
{
    $total   = count($habits);
    $selesai = 0;
    $id_selesai = array_map('intval', $completed_ids);

    foreach ($habits as $h) {
        if (in_array((int)$h['id'], $id_selesai, true)) {
            $selesai++;
        }
    }

    $persen = $total > 0 ? (int)round($selesai / $total * 100) : 0;

    return [
        'total'   => $total,
        'selesai' => $selesai,
        'persen'  => $persen,
    ];
}

function ambil_fishes(int $user_id): array
{
    global $db_connected;

    if (!$db_connected) {
        return demo_fishes();
    }

    return db_all(
        "SELECT id, nama, jenis, ukuran_cm, umur_bulan, kondisi, catatan, emblem
         FROM fishes WHERE user_id = ? ORDER BY id ASC",
        'i',
        [$user_id]
    );
}

function ambil_fish(int $id, int $user_id): ?array
{
    global $db_connected;

    if (!$db_connected) {
        return null;
    }

    return db_row(
        "SELECT id, nama, jenis, ukuran_cm, umur_bulan, kondisi, catatan, emblem, created_at
         FROM fishes WHERE id = ? AND user_id = ? LIMIT 1",
        'ii',
        [$id, $user_id]
    );
}

function kelas_kondisi(string $kondisi): string
{
    return match (strtoupper($kondisi)) {
        'SEHAT'     => 'badge-ok',
        'PERHATIAN' => 'badge-warn',
        'SAKIT'     => 'badge-danger',
        default     => 'badge-muted',
    };
}

function rupiah($angka): string
{
    return 'Rp' . number_format((float)$angka, 0, ',', '.');
}

function ambil_water_params(int $user_id, int $limit = 30): array
{
    global $db_connected;

    if (!$db_connected) {
        return [];
    }

    return db_all(
        "SELECT id, tanggal, ph, suhu_c, ammonia, nitrit, nitrat, catatan
         FROM water_params
         WHERE user_id = ?
         ORDER BY tanggal DESC, id DESC
         LIMIT " . max(1, $limit),
        'i',
        [$user_id]
    );
}

function ambil_water_param(int $id, int $user_id): ?array
{
    global $db_connected;

    if (!$db_connected) {
        return null;
    }

    return db_row(
        "SELECT id, tanggal, ph, suhu_c, ammonia, nitrit, nitrat, catatan
         FROM water_params WHERE id = ? AND user_id = ? LIMIT 1",
        'ii',
        [$id, $user_id]
    );
}

function ambil_water_params_terbaru(int $user_id): ?array
{
    $semua = ambil_water_params($user_id, 1);

    return $semua[0] ?? null;
}

function ambil_grafik_air(int $user_id): array
{
    global $db_connected;

    if (!$db_connected) {
        return [];
    }

    return db_all(
        "SELECT tanggal, ph, suhu_c, ammonia, nitrit, nitrat
         FROM water_params
         WHERE user_id = ?
           AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
         ORDER BY tanggal ASC",
        'i',
        [$user_id]
    );
}

function status_parameter_air(array $params): array
{
    $ph    = (float)$params['ph'];
    $suhu  = (float)$params['suhu_c'];
    $amonia = (float)$params['ammonia'];
    $nitrit = (float)$params['nitrit'];
    $nitrat = (float)$params['nitrat'];

    return [
        'ph'     => ($ph >= 6.5 && $ph <= 8.0) ? 'AMAN' : 'PERHATIAN',
        'suhu'   => ($suhu >= 24.0 && $suhu <= 30.0) ? 'AMAN' : 'PERHATIAN',
        'amonia' => ($amonia <= 0.05) ? 'AMAN' : 'BAHAYA',
        'nitrit' => ($nitrit <= 0.10) ? 'AMAN' : 'BAHAYA',
        'nitrat' => ($nitrat <= 40.0) ? 'AMAN' : 'PERHATIAN',
    ];
}

function kelas_status_air(string $status): string
{
    return match (strtoupper($status)) {
        'AMAN'      => 'badge-ok',
        'PERHATIAN' => 'badge-warn',
        'BAHAYA'    => 'badge-danger',
        default     => 'badge-muted',
    };
}

function ambil_feedings(int $user_id, int $limit = 50): array
{
    global $db_connected;

    if (!$db_connected) {
        return [];
    }

    return db_all(
        "SELECT f.id, f.tanggal, f.waktu, f.jenis_pakan, f.jumlah, f.satuan,
                f.habis, f.catatan, f.fish_id, i.nama AS nama_ikan
         FROM feedings f
         LEFT JOIN fishes i ON i.id = f.fish_id
         WHERE f.user_id = ?
         ORDER BY f.tanggal DESC, f.waktu DESC, f.id DESC
         LIMIT " . max(1, $limit),
        'i',
        [$user_id]
    );
}

function ambil_feeding(int $id, int $user_id): ?array
{
    global $db_connected;

    if (!$db_connected) {
        return null;
    }

    return db_row(
        "SELECT id, fish_id, tanggal, waktu, jenis_pakan, jumlah, satuan, habis, catatan
         FROM feedings WHERE id = ? AND user_id = ? LIMIT 1",
        'ii',
        [$id, $user_id]
    );
}

function jenis_pakan_bawaan(): array
{
    return [
        'Pelet protein tinggi',
        'Pelet kecil',
        'Pelet apung',
        'Pelet tenggelam',
        'Cacing sutra',
        'Artemia',
        'Kutu air',
        'Udang beku',
        'Jangkrik kecil',
        'Daun ketapang kering',
    ];
}

function daftar_jenis_pakan(int $user_id): array
{
    global $db_connected;

    $daftar = jenis_pakan_bawaan();

    if ($db_connected) {
        foreach (db_all("SELECT nama FROM pakan_jenis WHERE user_id = ? ORDER BY nama ASC", 'i', [$user_id]) as $row) {
            $daftar[] = $row['nama'];
        }
    }

    $daftar = array_values(array_unique(array_filter(array_map('trim', $daftar)), SORT_STRING));

    sort($daftar);

    return $daftar;
}

function simpan_jenis_pakan(int $user_id, string $nama): void
{
    global $db_connected;

    $nama = trim($nama);

    if (!$db_connected || $nama === '') {
        return;
    }

    if (in_array($nama, jenis_pakan_bawaan(), true)) {
        return;
    }

    db_exec(
        "INSERT INTO pakan_jenis (user_id, nama) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE nama = VALUES(nama)",
        [$user_id, $nama]
    );
}

function daftar_satuan(): array
{
    return ['butir', 'gram', 'sendok', 'ml', 'ekor', 'keping'];
}

function ringkasan_pakan(array $feedings): array
{
    $total     = 0.0;
    $habis     = 0;
    $sisa      = 0;
    $hari_ini  = 0.0;
    $tanggal   = date('Y-m-d');
    $ikan      = [];

    foreach ($feedings as $f) {
        $jumlah = (float)$f['jumlah'];
        $total += $jumlah;

        if ((int)$f['habis'] === 1) {
            $habis++;
        } else {
            $sisa++;
        }

        if ($f['tanggal'] === $tanggal) {
            $hari_ini += $jumlah;
        }

        $nama = $f['nama_ikan'] ?: 'Umum';
        $ikan[$nama] = ($ikan[$nama] ?? 0) + 1;
    }

    return [
        'total'    => $total,
        'habis'    => $habis,
        'sisa'     => $sisa,
        'hari_ini' => $hari_ini,
        'per_ikan' => $ikan,
    ];
}
