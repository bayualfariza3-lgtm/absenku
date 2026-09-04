<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

$classes = $pdo->query("
    SELECT * FROM classes
    ORDER BY nama_kelas
")->fetchAll(PDO::FETCH_ASSOC);

$students = $pdo->query("
    SELECT students.*, classes.nama_kelas
    FROM students
    JOIN classes ON students.kelas_id = classes.id
    ORDER BY students.nama
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

```
<title>Data Siswa - AbsenKu</title>

<link rel="stylesheet" href="../assets/style.css">
```

</head>

<body>

<div class="app">

```
<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="logo">
        <div class="logo-icon">A</div>

        <div>
            <div class="logo-title">AbsenKu</div>
            <div class="logo-subtitle">Sistem Absensi</div>
        </div>
    </div>

    <nav class="sidebar-nav">

        <a href="../index.php" class="nav-link">
            <span>📊</span>
            <span>Dashboard</span>
        </a>

        <a href="students.php" class="nav-link active">
            <span>👨‍🎓</span>
            <span>Data Siswa</span>
        </a>

        <a href="classes.php" class="nav-link">
            <span>🏫</span>
            <span>Data Kelas</span>
        </a>

        <a href="../guru/scan.php" class="nav-link">
            <span>📷</span>
            <span>Scan Absensi</span>
        </a>

        <a href="../guru/attendance.php" class="nav-link">
            <span>📝</span>
            <span>Data Absensi</span>
        </a>

        <a href="reports.php" class="nav-link">
            <span>📈</span>
            <span>Laporan</span>
        </a>

    </nav>

    <div class="sidebar-bottom">

        <a href="../logout.php" class="nav-link">
            <span>🚪</span>
            <span>Logout</span>
        </a>

    </div>

</aside>


<!-- MAIN -->
<main class="main">

    <!-- TOPBAR -->
    <header class="topbar">

        <div>
            <h1>Data Siswa</h1>
            <p>Kelola data siswa sekolah</p>
        </div>

        <div class="topbar-user">
            <div class="avatar">
                <?= strtoupper(substr($_SESSION['user']['nama'] ?? 'A', 0, 1)) ?>
            </div>

            <div>
                <strong>
                    <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Admin') ?>
                </strong>

                <small>
                    <?= htmlspecialchars($_SESSION['user']['role'] ?? 'Admin') ?>
                </small>
            </div>
        </div>

    </header>


    <!-- CONTENT -->
    <div class="content">

        <!-- FORM TAMBAH -->
        <div class="card">

            <div class="card-header">

                <div>
                    <h2>Tambah Siswa</h2>
                    <p>Masukkan data siswa baru</p>
                </div>

            </div>

            <div class="card-body">

                <form method="post" action="student_save.php">

                    <div class="form-row">

                        <div class="form-group">

                            <label class="form-label">
                                NIS
                            </label>

                            <input
                                type="text"
                                name="nis"
                                class="form-control"
                                placeholder="Contoh: 1001"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label class="form-label">
                                Nama Siswa
                            </label>

                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                placeholder="Nama lengkap siswa"
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
                                required
                            >

                                <option value="">
                                    -- Pilih Kelas --
                                </option>

                                <?php foreach ($classes as $class): ?>

                                    <option value="<?= $class['id'] ?>">
                                        <?= htmlspecialchars($class['nama_kelas']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>


                    <button type="submit" class="btn btn-primary">
                        ➕ Tambah Siswa
                    </button>

                </form>

            </div>

        </div>


        <!-- DAFTAR SISWA -->
        <div class="card">

            <div class="card-header">

                <div>
                    <h2>Daftar Siswa</h2>

                    <p>
                        <?= count($students) ?> siswa terdaftar
                    </p>
                </div>

            </div>


            <div class="card-body">

                <?php if (count($students) > 0): ?>

                    <div class="table-responsive">

                        <table class="table">

                            <thead>

                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($students as $i => $student): ?>

                                    <tr>

                                        <td>
                                            <?= $i + 1 ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($student['nis']) ?>
                                            </strong>
                                        </td>

                                        <td>

                                            <div class="student-name">

                                                <div class="avatar avatar-sm">
                                                    <?= strtoupper(
                                                        substr($student['nama'], 0, 1)
                                                    ) ?>
                                                </div>

                                                <span>
                                                    <?= htmlspecialchars($student['nama']) ?>
                                                </span>

                                            </div>

                                        </td>

                                        <td>

                                            <span class="badge badge-info">
                                                <?= htmlspecialchars($student['nama_kelas']) ?>
                                            </span>

                                        </td>

                                        <td>

                                            <?php if ($student['aktif']): ?>

                                                <span class="badge badge-active">
                                                    Aktif
                                                </span>

                                            <?php else: ?>

                                                <span class="badge badge-inactive">
                                                    Tidak Aktif
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
                            👨‍🎓
                        </div>

                        <h3>Belum Ada Siswa</h3>

                        <p>
                            Tambahkan siswa menggunakan form di atas.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>
```

</div>

</body>
</html>
