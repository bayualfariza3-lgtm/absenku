<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

$nis = $_POST['nis'];
$nama = $_POST['nama'];
$kelas_id = $_POST['kelas_id'];

$qr_token = bin2hex(random_bytes(16));
$barcode_token = $nis;

$stmt = $pdo->prepare("
    INSERT INTO students
    (nis, nama, kelas_id, qr_token, barcode_token)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([
    $nis,
    $nama,
    $kelas_id,
    $qr_token,
    $barcode_token
]);

header("Location: students.php");
exit;