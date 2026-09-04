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

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas_id = $_GET['kelas_id'] ?? '';
$search = trim($_GET['search'] ?? '');


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
| STATISTIK
|--------------------------------------------------------------------------
*/

$sqlStats = "
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
    WHERE attendance.tanggal = ?
";

$paramsStats = [$tanggal];

if ($kelas_id !== '') {
    $sqlStats .= " AND students.kelas_id = ?";
    $paramsStats[] = $kelas_id;
}

$stmt = $pdo->prepare($sqlStats);
$stmt->execute($paramsStats);

$stats = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| DATA ABSENSI
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        attendance.id,
        attendance.student_id,
        attendance.tanggal,
        attendance.jam,
        attendance.status,
        attendance.keterangan,
        students.nis,
        students.nama,
        classes.nama_kelas
    FROM attendance

    JOIN students
        ON attendance.student_id = students.id

    JOIN classes
        ON students.kelas_id = classes.id

    WHERE attendance.tanggal = ?
";

$params = [$tanggal];


/*
|--------------------------------------------------------------------------
| FILTER KELAS
|--------------------------------------------------------------------------
*/

if ($kelas_id !== '') {

    $sql .= "
        AND students.kelas_id = ?
    ";

    $params[] = $kelas_id;
}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $sql .= "
        AND (
            students.nama LIKE ?
            OR students.nis LIKE ?
        )
    ";

    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}


$sql .= "
    ORDER BY attendance.id DESC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HELPER STATUS
|--------------------------------------------------------------------------
*/

function statusBadge($status)
{
    switch ($status) {

        case 'hadir':
            return '<span class="badge badge-hadir">Hadir</span>';

        case 'terlambat':
            return '<span class="badge badge-terlambat">Terlambat</span>';

        case 'izin':
            return '<span class="badge badge-izin">Izin</span>';

        case 'sakit':
            return '<span class="badge badge-sakit">Sakit</span>';

        case 'alpa':
            return '<span class="badge badge-alpa">Alpa</span>';

        default:
            return '<span class="badge">-</span>';
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

    <title>Data Absensi - AbsenKu</title>

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

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="../admin/students.php"
                class="nav-link"
            >

                <span>👨‍🎓</span>

                <span>
                    Data Siswa
                </span>

            </a>


            <a
                href="../admin/classes.php"
                class="nav-link"
            >

                <span>🏫</span>

                <span>
                    Data Kelas
                </span>

            </a>


            <a
                href="scan.php"
                class="nav-link"
            >

                <span>📷</span>

                <span>
                    Scan Absensi
                </span>

            </a>


            <a
                href="attendance.php"
                class="nav-link active"
            >

                <span>📝</span>

                <span>
                    Data Absensi
                </span>

            </a>


            <a
                href="../admin/reports.php"
                class="nav-link"
            >

                <span>📈</span>

                <span>
                    Laporan
                </span>

            </a>


        </nav>


        <div class="sidebar-bottom">


            <a
                href="../logout.php"
                class="nav-link"
            >

                <span>🚪</span>

                <span>
                    Logout
                </span>

            </a>


        </div>


    </aside>



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="main">


        <!-- =================================================
             TOPBAR
        ================================================== -->

        <header class="topbar">


            <div>

                <h1>
                    Data Absensi
                </h1>

                <p>
                    Lihat dan pantau absensi siswa
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



        <!-- =================================================
             CONTENT
        ================================================== -->

        <div class="content">


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
                            Total
                        </div>

                        <div class="stat-number">
                            <?= (int)($stats['total'] ?? 0) ?>
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
                            <?= (int)($stats['hadir'] ?? 0) ?>
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
                            <?= (int)($stats['terlambat'] ?? 0) ?>
                        </div>

                    </div>

                </div>



                <div class="stat-card">

                    <div class="stat-icon info">
                        ℹ
                    </div>

                    <div>

                        <div class="stat-label">
                            Izin / Sakit
                        </div>

                        <div class="stat-number">

                            <?= (int)($stats['izin'] ?? 0)
                                + (int)($stats['sakit'] ?? 0) ?>

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
                            <?= (int)($stats['alpa'] ?? 0) ?>
                        </div>

                    </div>

                </div>


            </div>



            <!-- =================================================
                 FILTER
            ================================================== -->

            <div class="card">


                <div class="card-header">


                    <div>

                        <h2>
                            Filter Absensi
                        </h2>

                        <p>
                            Pilih tanggal, kelas, atau cari siswa
                        </p>

                    </div>


                </div>


                <div class="card-body">


                    <form
                        method="get"
                        class="attendance-filter"
                    >


                        <div class="form-group">

                            <label class="form-label">
                                Tanggal
                            </label>

                            <input
                                type="date"
                                name="tanggal"
                                value="<?= htmlspecialchars($tanggal) ?>"
                                class="form-control"
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



                        <div class="form-group">

                            <label class="form-label">
                                Cari Siswa
                            </label>

                            <input
                                type="text"
                                name="search"
                                value="<?= htmlspecialchars($search) ?>"
                                class="form-control"
                                placeholder="Nama atau NIS..."
                            >

                        </div>



                        <div class="filter-buttons">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                🔎 Cari
                            </button>


                            <a
                                href="attendance.php"
                                class="btn btn-light"
                            >
                                Reset
                            </a>

                        </div>


                    </form>


                </div>


            </div>



            <!-- =================================================
                 TABEL
            ================================================== -->

            <div class="card">


                <div class="card-header">


                    <div>

                        <h2>
                            Daftar Absensi
                        </h2>

                        <p>

                            <?= date(
                                'd F Y',
                                strtotime($tanggal)
                            ) ?>

                            ·

                            <?= count($attendance) ?>
                            data

                        </p>

                    </div>


                    <a
                        href="../admin/reports.php"
                        class="btn btn-outline btn-sm"
                    >
                        📊 Laporan
                    </a>


                </div>



                <div class="card-body">


                    <?php if (count($attendance) > 0): ?>


                        <div class="table-responsive">


                            <table class="table">


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
                                            Jam
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Keterangan
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php foreach (
                                        $attendance
                                        as $i => $row
                                    ): ?>


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

                                                <strong>

                                                    <?= date(
                                                        'H:i',
                                                        strtotime(
                                                            $row['jam']
                                                        )
                                                    ) ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <?= statusBadge(
                                                    $row['status']
                                                ) ?>

                                            </td>


                                            <td>

                                                <?php if (
                                                    !empty(
                                                        $row['keterangan']
                                                    )
                                                ): ?>

                                                    <span class="text-muted">

                                                        <?= htmlspecialchars(
                                                            $row['keterangan']
                                                        ) ?>

                                                    </span>

                                                <?php else: ?>

                                                    <span class="text-muted">
                                                        -
                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                </tbody>


                            </table>


                        </div>


                    <?php else: ?>


                        <div class="empty-state">


                            <div class="empty-icon">
                                📝
                            </div>


                            <h3>
                                Belum Ada Absensi
                            </h3>


                            <p>

                                Tidak ada data absensi untuk filter
                                yang dipilih.

                            </p>


                            <a
                                href="scan.php"
                                class="btn btn-primary"
                            >
                                📷 Mulai Scan
                            </a>


                        </div>


                    <?php endif; ?>


                </div>


            </div>


        </div>


    </main>


</div>



<style>

/* =========================================================
   FILTER
========================================================= */

.attendance-filter {

    display: grid;

    grid-template-columns:
        1fr
        1fr
        1.5fr
        auto;

    gap: 16px;

    align-items: end;

}


.filter-buttons {

    display: flex;

    gap: 8px;

}


/* =========================================================
   TEXT
========================================================= */

.text-muted {

    color: #64748b;

    font-size: 13px;

}


/* =========================================================
   TABLE
========================================================= */

.table-responsive {

    width: 100%;

    overflow-x: auto;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {

    .attendance-filter {

        grid-template-columns:
            1fr
            1fr;

    }

}


@media (max-width: 600px) {

    .attendance-filter {

        grid-template-columns: 1fr;

    }


    .filter-buttons {

        width: 100%;

    }


    .filter-buttons .btn {

        flex: 1;

    }

}

</style>


</body>

</html> 