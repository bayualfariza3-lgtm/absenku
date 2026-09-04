<?php

session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['user'])) {
    echo json_encode([
        "success" => false,
        "message" => "Session tidak valid."
    ]);
    exit;
}

require_once "../config/database.php";

$barcode_token = trim($_POST['barcode_token'] ?? '');

if ($barcode_token === '') {
    echo json_encode([
        "success" => false,
        "message" => "Barcode kosong."
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Cari siswa berdasarkan barcode
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        students.id,
        students.nis,
        students.nama,
        students.aktif,
        classes.nama_kelas
    FROM students
    JOIN classes ON students.kelas_id = classes.id
    WHERE students.barcode_token = ?
    LIMIT 1
");

$stmt->execute([$barcode_token]);

$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$siswa) {
    echo json_encode([
        "success" => false,
        "message" => "Barcode siswa tidak ditemukan."
    ]);
    exit;
}

if ((int)$siswa['aktif'] !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Siswa sedang tidak aktif."
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Cek apakah sudah absen hari ini
|--------------------------------------------------------------------------
*/

$today = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT id, jam, status
    FROM attendance
    WHERE student_id = ?
      AND tanggal = ?
    LIMIT 1
");

$stmt->execute([
    $siswa['id'],
    $today
]);

$sudah_absen = $stmt->fetch(PDO::FETCH_ASSOC);

if ($sudah_absen) {

    echo json_encode([
        "success" => false,
        "message" =>
            $siswa['nama'] .
            " sudah absen hari ini pada " .
            $sudah_absen['jam'] .
            " (" .
            strtoupper($sudah_absen['status']) .
            ")."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Simpan absensi
|--------------------------------------------------------------------------
*/

$jam = date('H:i:s');

/*
| Aturan sederhana:
| sebelum 07:00 = hadir
| 07:00 ke atas = terlambat
|
| Kalau jam masuk sekolah kamu berbeda,
| tinggal ubah bagian ini.
*/

$batas_masuk = "07:00:00";

$status = ($jam <= $batas_masuk)
    ? "hadir"
    : "terlambat";

/*
|--------------------------------------------------------------------------
| recorded_by
|--------------------------------------------------------------------------
*/

$recorded_by = $_SESSION['user']['id'] ?? null;

$stmt = $pdo->prepare("
    INSERT INTO attendance
    (
        student_id,
        tanggal,
        jam,
        status,
        keterangan,
        recorded_by
    )
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $siswa['id'],
    $today,
    $jam,
    $status,
    "Absen melalui barcode",
    $recorded_by
]);

echo json_encode([
    "success" => true,
    "message" =>
        "Absensi berhasil: " .
        $siswa['nama'] .
        " - " .
        $siswa['nama_kelas'] .
        " (" .
        strtoupper($status) .
        ")"
]);