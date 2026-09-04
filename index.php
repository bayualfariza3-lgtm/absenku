<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once "config/database.php";

/* =========================
   DATA DASHBOARD
   ========================= */

$nama_user = $_SESSION['user']['nama'];
$role_user = $_SESSION['user']['role'];

/* Total siswa */
$total_siswa = $pdo->query("
    SELECT COUNT(*) 
    FROM students 
    WHERE aktif = 1
")->fetchColumn();

/* Total kelas */
$total_kelas = $pdo->query("
    SELECT COUNT(*) 
    FROM classes
")->fetchColumn();

/* Absensi hari ini */
$hari_ini = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM attendance 
    WHERE tanggal = ?
    AND status = 'hadir'
");
$stmt->execute([$hari_ini]);
$total_hadir = $stmt->fetchColumn();

/* Terlambat */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM attendance 
    WHERE tanggal = ?
    AND status = 'terlambat'
");
$stmt->execute([$hari_ini]);
$total_terlambat = $stmt->fetchColumn();

/* Izin */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM attendance 
    WHERE tanggal = ?
    AND status = 'izin'
");
$stmt->execute([$hari_ini]);
$total_izin = $stmt->fetchColumn();

/* Sakit */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM attendance 
    WHERE tanggal = ?
    AND status = 'sakit'
");
$stmt->execute([$hari_ini]);
$total_sakit = $stmt->fetchColumn();

/* Alpa */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM attendance 
    WHERE tanggal = ?
    AND status = 'alpa'
");
$stmt->execute([$hari_ini]);
$total_alpa = $stmt->fetchColumn();

/* Absensi terbaru */
$stmt = $pdo->prepare("
    SELECT 
        attendance.*,
        students.nama,
        students.nis,
        classes.nama_kelas
    FROM attendance
    JOIN students ON attendance.student_id = students.id
    JOIN classes ON students.kelas_id = classes.id
    WHERE attendance.tanggal = ?
    ORDER BY attendance.id DESC
    LIMIT 10
");

$stmt->execute([$hari_ini]);
$absensi_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Persentase kehadiran */
$persentase_hadir = 0;

if ($total_siswa > 0) {
    $persentase_hadir = round(
        (($total_hadir + $total_terlambat) / $total_siswa) * 100
    );
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - AbsenKu</title>

    <link rel="stylesheet"
          href="assets/style.css">

</head>

<body>

<div class="app">

    <!-- ================= SIDEBAR ================= -->

    <aside class="sidebar" id="sidebar">

        <div class="logo">

            <div class="logo-icon">
                A
            </div>

            <div class="logo-text">
                Absen<span>Ku</span>
            </div>

        </div>


        <div class="sidebar-menu">

            <div class="menu-title">
                Menu Utama
            </div>


            <a href="index.php"
               class="nav-link active">

                <span class="nav-icon">▣</span>

                Dashboard

            </a>


            <a href="admin/students.php"
               class="nav-link">

                <span class="nav-icon">👨‍🎓</span>

                Data Siswa

            </a>


            <a href="guru/scan.php"
               class="nav-link">

                <span class="nav-icon">📷</span>

                Scan QR

            </a>


            <a href="guru/attendance.php"
               class="nav-link">

                <span class="nav-icon">🕐</span>

                Riwayat Absensi

            </a>


            <a href="admin/reports.php"
               class="nav-link">

                <span class="nav-icon">📊</span>

                Laporan

            </a>


            <div class="menu-title">
                Sistem
            </div>


            <a href="logout.php"
               class="nav-link">

                <span class="nav-icon">🚪</span>

                Keluar

            </a>

        </div>


        <div class="sidebar-bottom">

            <div class="profile-card">

                <div class="profile-avatar">

                    <?= strtoupper(substr($nama_user, 0, 1)) ?>

                </div>

                <div>

                    <div class="profile-name">
                        <?= htmlspecialchars($nama_user) ?>
                    </div>

                    <div class="profile-info">
                        <?= htmlspecialchars($role_user) ?>
                    </div>

                </div>

            </div>

        </div>

    </aside>


    <!-- OVERLAY MOBILE -->

    <div class="sidebar-overlay"
         id="sidebarOverlay"></div>


    <!-- ================= MAIN ================= -->

    <main class="main">


        <!-- TOPBAR -->

        <header class="topbar">

            <div style="display:flex; align-items:center; gap:12px;">

                <button class="mobile-menu"
                        id="mobileMenu">

                    ☰

                </button>


                <div>

                    <div class="page-title">
                        Dashboard
                    </div>

                    <div class="page-subtitle">
                        Sistem Absensi Sekolah
                    </div>

                </div>

            </div>


            <div class="user-area">

                <div>

                    <div class="user-name">
                        <?= htmlspecialchars($nama_user) ?>
                    </div>

                    <div class="user-role">
                        <?= htmlspecialchars($role_user) ?>
                    </div>

                </div>


                <div class="user-avatar">

                    <?= strtoupper(substr($nama_user, 0, 1)) ?>

                </div>

            </div>

        </header>


        <!-- ================= CONTENT ================= -->

        <section class="content">


            <!-- HEADER -->

            <div class="content-header">

                <div>

                    <h1>
                        Selamat datang, <?= htmlspecialchars($nama_user) ?> 👋
                    </h1>

                    <p>
                        Berikut ringkasan absensi sekolah hari ini.
                    </p>

                </div>


                <div class="header-actions">

                    <a href="guru/scan.php"
                       class="btn btn-primary">

                        📷 Scan Absensi

                    </a>

                    <a href="admin/students.php"
                       class="btn btn-outline">

                        + Tambah Siswa

                    </a>

                </div>

            </div>


            <!-- ================= STATISTICS ================= -->

            <div class="stats-grid">


                <!-- SISWA -->

                <div class="stat-card">

                    <div class="stat-top">

                        <div>

                            <div class="stat-label">
                                Total Siswa
                            </div>

                            <div class="stat-number">
                                <?= $total_siswa ?>
                            </div>

                        </div>


                        <div class="stat-icon primary">
                            👨‍🎓
                        </div>

                    </div>

                    <div class="stat-description">
                        Siswa aktif
                    </div>

                </div>


                <!-- KELAS -->

                <div class="stat-card">

                    <div class="stat-top">

                        <div>

                            <div class="stat-label">
                                Total Kelas
                            </div>

                            <div class="stat-number">
                                <?= $total_kelas ?>
                            </div>

                        </div>


                        <div class="stat-icon info">
                            🏫
                        </div>

                    </div>

                    <div class="stat-description">
                        Kelas terdaftar
                    </div>

                </div>


                <!-- HADIR -->

                <div class="stat-card">

                    <div class="stat-top">

                        <div>

                            <div class="stat-label">
                                Hadir Hari Ini
                            </div>

                            <div class="stat-number">
                                <?= $total_hadir ?>
                            </div>

                        </div>


                        <div class="stat-icon success">
                            ✓
                        </div>

                    </div>

                    <div class="stat-description">
                        Siswa hadir
                    </div>

                </div>


                <!-- ALPA -->

                <div class="stat-card">

                    <div class="stat-top">

                        <div>

                            <div class="stat-label">
                                Tidak Hadir
                            </div>

                            <div class="stat-number">
                                <?= $total_alpa ?>
                            </div>

                        </div>


                        <div class="stat-icon danger">
                            !
                        </div>

                    </div>

                    <div class="stat-description">
                        Siswa tanpa keterangan
                    </div>

                </div>

            </div>


            <!-- ================= GRID ================= -->

            <div class="grid-2">


                <!-- ABSENSI -->

                <div class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Statistik Hari Ini
                            </div>

                            <div class="card-subtitle">
                                <?= date('d F Y') ?>
                            </div>

                        </div>

                    </div>


                    <div class="card-body">


                        <div style="
                            display:grid;
                            grid-template-columns:repeat(2,1fr);
                            gap:12px;
                        ">


                            <div class="quick-action">

                                <div class="quick-action-icon">
                                    ✓
                                </div>

                                <strong>
                                    <?= $total_hadir ?> Hadir
                                </strong>

                                <span>
                                    Siswa hadir
                                </span>

                            </div>


                            <div class="quick-action">

                                <div class="quick-action-icon">
                                    ⏰
                                </div>

                                <strong>
                                    <?= $total_terlambat ?> Terlambat
                                </strong>

                                <span>
                                    Datang terlambat
                                </span>

                            </div>


                            <div class="quick-action">

                                <div class="quick-action-icon">
                                    📄
                                </div>

                                <strong>
                                    <?= $total_izin ?> Izin
                                </strong>

                                <span>
                                    Siswa izin
                                </span>

                            </div>


                            <div class="quick-action">

                                <div class="quick-action-icon">
                                    🏥
                                </div>

                                <strong>
                                    <?= $total_sakit ?> Sakit
                                </strong>

                                <span>
                                    Siswa sakit
                                </span>

                            </div>


                        </div>


                        <div style="margin-top:22px;">

                            <div style="
                                display:flex;
                                justify-content:space-between;
                                margin-bottom:8px;
                            ">

                                <span class="text-muted"
                                      style="font-size:13px;">

                                    Tingkat kehadiran

                                </span>

                                <strong>
                                    <?= $persentase_hadir ?>%
                                </strong>

                            </div>


                            <div class="progress">

                                <div class="progress-bar"
                                     style="width:<?= min($persentase_hadir, 100) ?>%;">
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- QUICK ACTION -->

                <div class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Aksi Cepat
                            </div>

                            <div class="card-subtitle">
                                Kelola sistem absensi
                            </div>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="quick-actions">


                            <a href="guru/scan.php"
                               class="quick-action">

                                <div class="quick-action-icon">
                                    📷
                                </div>

                                <strong>
                                    Scan QR
                                </strong>

                                <span>
                                    Absen siswa
                                </span>

                            </a>


                            <a href="admin/students.php"
                               class="quick-action">

                                <div class="quick-action-icon">
                                    👨‍🎓
                                </div>

                                <strong>
                                    Data Siswa
                                </strong>

                                <span>
                                    Kelola siswa
                                </span>

                            </a>


                            <a href="admin/reports.php"
                               class="quick-action">

                                <div class="quick-action-icon">
                                    📊
                                </div>

                                <strong>
                                    Laporan
                                </strong>

                                <span>
                                    Lihat laporan
                                </span>

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================= RECENT ATTENDANCE ================= -->

            <div class="card">

                <div class="card-header">

                    <div>

                        <div class="card-title">
                            Absensi Terbaru
                        </div>

                        <div class="card-subtitle">
                            Aktivitas absensi hari ini
                        </div>

                    </div>


                    <a href="guru/attendance.php"
                       class="btn btn-light btn-sm">

                        Lihat Semua →

                    </a>

                </div>


                <div class="card-body"
                     style="padding:0;">

                    <?php if (count($absensi_terbaru) > 0): ?>

                        <div class="table-container">

                            <table class="table">

                                <thead>

                                <tr>

                                    <th>
                                        Siswa
                                    </th>

                                    <th>
                                        Kelas
                                    </th>

                                    <th>
                                        Jam
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                                </thead>


                                <tbody>

                                <?php foreach ($absensi_terbaru as $row): ?>

                                    <tr>

                                        <td>

                                            <div class="student-name">
                                                <?= htmlspecialchars($row['nama']) ?>
                                            </div>

                                            <div class="student-nis">
                                                NIS: <?= htmlspecialchars($row['nis']) ?>
                                            </div>

                                        </td>


                                        <td>
                                            <?= htmlspecialchars($row['nama_kelas']) ?>
                                        </td>


                                        <td>

                                            <?= isset($row['jam'])
                                                ? htmlspecialchars($row['jam'])
                                                : '-'
                                            ?>

                                        </td>


                                        <td>

                                            <?php

                                            $status = strtolower($row['status']);

                                            $class_status = 'badge-alpa';

                                            if ($status === 'hadir') {
                                                $class_status = 'badge-hadir';
                                            } elseif ($status === 'terlambat') {
                                                $class_status = 'badge-terlambat';
                                            } elseif ($status === 'izin') {
                                                $class_status = 'badge-izin';
                                            } elseif ($status === 'sakit') {
                                                $class_status = 'badge-sakit';
                                            }

                                            ?>

                                            <span class="badge <?= $class_status ?>">

                                                <?= ucfirst($status) ?>

                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="empty-state">

                            <div class="empty-icon">
                                📋
                            </div>

                            <h3>
                                Belum ada absensi
                            </h3>

                            <p>
                                Belum ada data absensi untuk hari ini.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


        </section>

    </main>

</div>


<!-- ================= MOBILE SCRIPT ================= -->

<script>

const mobileMenu =
    document.getElementById('mobileMenu');

const sidebar =
    document.getElementById('sidebar');

const overlay =
    document.getElementById('sidebarOverlay');


mobileMenu.addEventListener('click', function () {

    sidebar.classList.toggle('open');

    overlay.classList.toggle('show');

});


overlay.addEventListener('click', function () {

    sidebar.classList.remove('open');

    overlay.classList.remove('show');

});

</script>

</body>

</html>