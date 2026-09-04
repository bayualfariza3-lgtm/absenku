<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');
$kelas_id = $_GET['kelas_id'] ?? '';


/*
|--------------------------------------------------------------------------
| DATA KELAS
|--------------------------------------------------------------------------
*/

$classes = $pdo->query("
    SELECT *
    FROM classes
    ORDER BY nama_kelas
")->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| REKAP KESELURUHAN
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        COUNT(*) AS total,
        SUM(status = 'hadir') AS hadir,
        SUM(status = 'terlambat') AS terlambat,
        SUM(status = 'izin') AS izin,
        SUM(status = 'sakit') AS sakit,
        SUM(status = 'alpa') AS alpa
    FROM attendance
    JOIN students
        ON attendance.student_id = students.id
    WHERE attendance.tanggal BETWEEN ? AND ?
";

$params = [
    $tanggal_awal,
    $tanggal_akhir
];

if ($kelas_id !== '') {

    $sql .= "
        AND students.kelas_id = ?
    ";

    $params[] = $kelas_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rekap = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| REKAP PER SISWA
|--------------------------------------------------------------------------
*/

$sqlSiswa = "
    SELECT
        students.id,
        students.nis,
        students.nama,
        classes.nama_kelas,

        SUM(attendance.status = 'hadir') AS hadir,
        SUM(attendance.status = 'terlambat') AS terlambat,
        SUM(attendance.status = 'izin') AS izin,
        SUM(attendance.status = 'sakit') AS sakit,
        SUM(attendance.status = 'alpa') AS alpa,

        COUNT(attendance.id) AS total

    FROM students

    JOIN classes
        ON students.kelas_id = classes.id

    LEFT JOIN attendance
        ON attendance.student_id = students.id
        AND attendance.tanggal BETWEEN ? AND ?

    WHERE students.aktif = 1
";

$paramsSiswa = [
    $tanggal_awal,
    $tanggal_akhir
];

if ($kelas_id !== '') {

    $sqlSiswa .= "
        AND students.kelas_id = ?
    ";

    $paramsSiswa[] = $kelas_id;
}

$sqlSiswa .= "
    GROUP BY
        students.id,
        students.nis,
        students.nama,
        classes.nama_kelas

    ORDER BY students.nama
";

$stmt = $pdo->prepare($sqlSiswa);
$stmt->execute($paramsSiswa);

$rekapSiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| NAMA KELAS TERPILIH
|--------------------------------------------------------------------------
*/

$namaKelas = 'Semua Kelas';

if ($kelas_id !== '') {

    foreach ($classes as $class) {

        if ((string)$class['id'] === (string)$kelas_id) {

            $namaKelas = $class['nama_kelas'];

            break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Laporan Absensi - AbsenKu</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>


<body>


<div class="app">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="sidebar">

        <div class="logo">

            <div class="logo-icon">
                A
            </div>

            <div>

                <div class="logo-title">
                    AbsenKu
                </div>

                <div class="logo-subtitle">
                    Sistem Absensi
                </div>

            </div>

        </div>


        <nav class="sidebar-nav">


            <a
                href="../index.php"
                class="nav-link"
            >
                <span>📊</span>
                <span>Dashboard</span>
            </a>


            <a
                href="students.php"
                class="nav-link"
            >
                <span>👨‍🎓</span>
                <span>Data Siswa</span>
            </a>


            <a
                href="classes.php"
                class="nav-link"
            >
                <span>🏫</span>
                <span>Data Kelas</span>
            </a>


            <a
                href="../guru/scan.php"
                class="nav-link"
            >
                <span>📷</span>
                <span>Scan Absensi</span>
            </a>


            <a
                href="../guru/attendance.php"
                class="nav-link"
            >
                <span>📝</span>
                <span>Data Absensi</span>
            </a>


            <a
                href="reports.php"
                class="nav-link active"
            >
                <span>📈</span>
                <span>Laporan</span>
            </a>


        </nav>


        <div class="sidebar-bottom">

            <a
                href="../logout.php"
                class="nav-link"
            >
                <span>🚪</span>
                <span>Logout</span>
            </a>

        </div>

    </aside>



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="main">


        <!-- TOPBAR -->

        <header class="topbar">

            <div>

                <h1>
                    Laporan Absensi
                </h1>

                <p>
                    Rekap kehadiran siswa
                </p>

            </div>


            <div class="topbar-user">

                <div class="avatar">

                    <?= strtoupper(
                        substr(
                            $_SESSION['user']['nama'] ?? 'A',
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div>

                    <strong>

                        <?= htmlspecialchars(
                            $_SESSION['user']['nama'] ?? 'User'
                        ) ?>

                    </strong>

                    <small>

                        <?= htmlspecialchars(
                            $_SESSION['user']['role'] ?? 'User'
                        ) ?>

                    </small>

                </div>

            </div>

        </header>



        <!-- CONTENT -->

        <div class="content">


            <!-- =================================================
                 FILTER
            ================================================== -->

            <div class="card no-print">

                <div class="card-header">

                    <div>

                        <h2>
                            📅 Filter Laporan
                        </h2>

                        <p>
                            Tentukan periode dan kelas
                        </p>

                    </div>

                </div>


                <div class="card-body">


                    <form
                        method="get"
                        class="report-filter"
                    >


                        <div class="form-group">

                            <label class="form-label">
                                Dari Tanggal
                            </label>

                            <input
                                type="date"
                                name="tanggal_awal"
                                class="form-control"
                                value="<?= htmlspecialchars($tanggal_awal) ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label class="form-label">
                                Sampai Tanggal
                            </label>

                            <input
                                type="date"
                                name="tanggal_akhir"
                                class="form-control"
                                value="<?= htmlspecialchars($tanggal_akhir) ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label class="form-label">
                                Kelas
                            </label>

                            <select
                                name="kelas_id"
                                class="form-control form-select"
                            >

                                <option value="">
                                    Semua Kelas
                                </option>


                                <?php foreach ($classes as $class): ?>

                                    <option
                                        value="<?= $class['id'] ?>"
                                        <?= $kelas_id == $class['id']
                                            ? 'selected'
                                            : '' ?>
                                    >

                                        <?= htmlspecialchars(
                                            $class['nama_kelas']
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="report-buttons">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                🔎 Tampilkan
                            </button>


                            <button
                                type="button"
                                onclick="window.print()"
                                class="btn btn-light"
                            >
                                🖨️ Cetak
                            </button>

                        </div>


                    </form>

                </div>

            </div>



            <!-- =================================================
                 JUDUL LAPORAN
            ================================================== -->

            <div class="report-title">

                <div>

                    <h2>
                        Rekap Absensi
                    </h2>

                    <p>

                        <?= date(
                            'd M Y',
                            strtotime($tanggal_awal)
                        ) ?>

                        s/d

                        <?= date(
                            'd M Y',
                            strtotime($tanggal_akhir)
                        ) ?>

                        ·

                        <?= htmlspecialchars($namaKelas) ?>

                    </p>

                </div>

            </div>



            <!-- =================================================
                 STATISTIK
            ================================================== -->

            <div class="stats-grid">


                <div class="stat-card">

                    <div class="stat-icon primary">
                        📝
                    </div>

                    <div>

                        <div class="stat-label">
                            Total Absensi
                        </div>

                        <div class="stat-number">
                            <?= (int)($rekap['total'] ?? 0) ?>
                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon success">
                        ✓
                    </div>

                    <div>

                        <div class="stat-label">
                            Hadir
                        </div>

                        <div class="stat-number">
                            <?= (int)($rekap['hadir'] ?? 0) ?>
                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon warning">
                        ⏰
                    </div>

                    <div>

                        <div class="stat-label">
                            Terlambat
                        </div>

                        <div class="stat-number">
                            <?= (int)($rekap['terlambat'] ?? 0) ?>
                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon info">
                        ℹ
                    </div>

                    <div>

                        <div class="stat-label">
                            Izin + Sakit
                        </div>

                        <div class="stat-number">

                            <?= (int)($rekap['izin'] ?? 0)
                                + (int)($rekap['sakit'] ?? 0) ?>

                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon danger">
                        !
                    </div>

                    <div>

                        <div class="stat-label">
                            Alpa
                        </div>

                        <div class="stat-number">
                            <?= (int)($rekap['alpa'] ?? 0) ?>
                        </div>

                    </div>

                </div>


            </div>



            <!-- =================================================
                 TABEL REKAP SISWA
            ================================================== -->

            <div class="card">


                <div class="card-header">

                    <div>

                        <h2>
                            👨‍🎓 Rekap Per Siswa
                        </h2>

                        <p>
                            Detail kehadiran setiap siswa
                        </p>

                    </div>

                </div>


                <div class="card-body">


                    <?php if (count($rekapSiswa) > 0): ?>


                        <div class="table-responsive">


                            <table class="table report-table">


                                <thead>

                                    <tr>

                                        <th>
                                            No
                                        </th>

                                        <th>
                                            Siswa
                                        </th>

                                        <th>
                                            NIS
                                        </th>

                                        <th>
                                            Kelas
                                        </th>

                                        <th>
                                            Hadir
                                        </th>

                                        <th>
                                            Terlambat
                                        </th>

                                        <th>
                                            Izin
                                        </th>

                                        <th>
                                            Sakit
                                        </th>

                                        <th>
                                            Alpa
                                        </th>

                                        <th>
                                            Persentase
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php foreach (
                                        $rekapSiswa
                                        as $i => $row
                                    ): ?>


                                        <?php

                                        $totalHadir =
                                            (int)$row['hadir']
                                            +
                                            (int)$row['terlambat'];

                                        $totalData =
                                            (int)$row['total'];

                                        $persentase = $totalData > 0
                                            ? round(
                                                (
                                                    $totalHadir
                                                    /
                                                    $totalData
                                                ) * 100
                                            )
                                            : 0;

                                        ?>


                                        <tr>


                                            <td>
                                                <?= $i + 1 ?>
                                            </td>


                                            <td>

                                                <div class="student-name">

                                                    <div
                                                        class="avatar avatar-sm"
                                                    >

                                                        <?= strtoupper(
                                                            substr(
                                                                $row['nama'],
                                                                0,
                                                                1
                                                            )
                                                        ) ?>

                                                    </div>


                                                    <strong>

                                                        <?= htmlspecialchars(
                                                            $row['nama']
                                                        ) ?>

                                                    </strong>

                                                </div>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $row['nis']
                                                ) ?>

                                            </td>


                                            <td>

                                                <span
                                                    class="badge badge-info"
                                                >

                                                    <?= htmlspecialchars(
                                                        $row['nama_kelas']
                                                    ) ?>

                                                </span>

                                            </td>


                                            <td>

                                                <span class="status-number hadir">
                                                    <?= (int)$row['hadir'] ?>
                                                </span>

                                            </td>


                                            <td>

                                                <span class="status-number terlambat">
                                                    <?= (int)$row['terlambat'] ?>
                                                </span>

                                            </td>


                                            <td>

                                                <span class="status-number izin">
                                                    <?= (int)$row['izin'] ?>
                                                </span>

                                            </td>


                                            <td>

                                                <span class="status-number sakit">
                                                    <?= (int)$row['sakit'] ?>
                                                </span>

                                            </td>


                                            <td>

                                                <span class="status-number alpa">
                                                    <?= (int)$row['alpa'] ?>
                                                </span>

                                            </td>


                                            <td>

                                                <div class="percentage">

                                                    <strong>
                                                        <?= $persentase ?>%
                                                    </strong>

                                                    <div
                                                        class="progress"
                                                    >

                                                        <div
                                                            class="progress-bar"
                                                            style="width: <?= $persentase ?>%"
                                                        ></div>

                                                    </div>

                                                </div>

                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                </tbody>


                            </table>


                        </div>


                    <?php else: ?>


                        <div class="empty-state">

                            <div class="empty-icon">
                                📊
                            </div>

                            <h3>
                                Tidak Ada Data
                            </h3>

                            <p>
                                Belum ada data absensi pada periode
                                yang dipilih.
                            </p>

                        </div>


                    <?php endif; ?>


                </div>

            </div>


        </div>

    </main>

</div>


<style>

.report-filter {

    display: grid;

    grid-template-columns:
        1fr
        1fr
        1fr
        auto;

    gap: 16px;

    align-items: end;

}


.report-buttons {

    display: flex;

    gap: 8px;

}


.report-title {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin: 10px 0 20px;

}


.report-title h2 {

    margin: 0 0 5px;

}


.report-title p {

    margin: 0;

    color: #64748b;

}


.status-number {

    font-weight: 700;

}


.status-number.hadir {
    color: #16a34a;
}


.status-number.terlambat {
    color: #d97706;
}


.status-number.izin {
    color: #2563eb;
}


.status-number.sakit {
    color: #7c3aed;
}


.status-number.alpa {
    color: #dc2626;
}


.percentage {

    min-width: 100px;

}


.percentage strong {

    display: block;

    margin-bottom: 6px;

}


.progress {

    width: 100%;

    height: 6px;

    background: #e5e7eb;

    border-radius: 20px;

    overflow: hidden;

}


.progress-bar {

    height: 100%;

    background: #4f46e5;

    border-radius: 20px;

}


@media (max-width: 1000px) {

    .report-filter {

        grid-template-columns: 1fr 1fr;

    }

}


@media (max-width: 600px) {

    .report-filter {

        grid-template-columns: 1fr;

    }

    .report-buttons {

        width: 100%;

    }

    .report-buttons .btn {

        flex: 1;

    }

}


/* =========================================================
   PRINT
========================================================= */

@media print {

    body {

        background: white !important;

    }

    .sidebar,
    .topbar,
    .no-print {

        display: none !important;

    }

    .main {

        margin: 0 !important;

        width: 100% !important;

    }

    .content {

        padding: 0 !important;

    }

    .card {

        box-shadow: none !important;

        border: 1px solid #ddd !important;

    }

    .report-title {

        margin-top: 0;

    }

}

</style>


</body>
</html>