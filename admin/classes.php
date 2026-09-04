    <?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Ambil Data Kelas
|--------------------------------------------------------------------------
*/

$classes = $pdo->query("
    SELECT 
        classes.id,
        classes.nama_kelas,
        COUNT(students.id) AS jumlah_siswa
    FROM classes
    LEFT JOIN students 
        ON students.kelas_id = classes.id
        AND students.aktif = 1
    GROUP BY classes.id, classes.nama_kelas
    ORDER BY classes.nama_kelas
")->fetchAll(PDO::FETCH_ASSOC);

$total_kelas = count($classes);

$total_siswa = $pdo->query("
    SELECT COUNT(*)
    FROM students
    WHERE aktif = 1
")->fetchColumn();

?>

<!DOCTYPE html>

<html lang="id">

<head>

```
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Kelas - AbsenKu</title>

<link rel="stylesheet" href="../assets/style.css">
```

</head>

<body>

<div class="app">

```
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


        <a href="../index.php" class="nav-link">

            <span>📊</span>

            <span>
                Dashboard
            </span>

        </a>


        <a href="students.php" class="nav-link">

            <span>👨‍🎓</span>

            <span>
                Data Siswa
            </span>

        </a>


        <a href="classes.php" class="nav-link active">

            <span>🏫</span>

            <span>
                Data Kelas
            </span>

        </a>


        <a href="../guru/scan.php" class="nav-link">

            <span>📷</span>

            <span>
                Scan Absensi
            </span>

        </a>


        <a href="../guru/attendance.php" class="nav-link">

            <span>📝</span>

            <span>
                Data Absensi
            </span>

        </a>


        <a href="reports.php" class="nav-link">

            <span>📈</span>

            <span>
                Laporan
            </span>

        </a>


    </nav>


    <div class="sidebar-bottom">


        <a href="../logout.php" class="nav-link">

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


    <!-- TOPBAR -->

    <header class="topbar">


        <div>

            <h1>
                Data Kelas
            </h1>

            <p>
                Kelola kelas dan jumlah siswa
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
                        $_SESSION['user']['nama'] ?? 'Admin'
                    ) ?>

                </strong>


                <small>

                    <?= htmlspecialchars(
                        $_SESSION['user']['role'] ?? 'Admin'
                    ) ?>

                </small>

            </div>


        </div>


    </header>



    <!-- CONTENT -->

    <div class="content">


        <!-- =================================================
             STATISTIK
        ================================================== -->

        <div class="stats-grid">


            <div class="stat-card">


                <div class="stat-icon primary">
                    🏫
                </div>


                <div>

                    <div class="stat-label">
                        Total Kelas
                    </div>

                    <div class="stat-number">
                        <?= $total_kelas ?>
                    </div>

                </div>


            </div>



            <div class="stat-card">


                <div class="stat-icon success">
                    👨‍🎓
                </div>


                <div>

                    <div class="stat-label">
                        Total Siswa
                    </div>

                    <div class="stat-number">
                        <?= $total_siswa ?>
                    </div>

                </div>


            </div>


        </div>



        <!-- =================================================
             TAMBAH KELAS
        ================================================== -->

        <div class="card">


            <div class="card-header">


                <div>

                    <h2>
                        Tambah Kelas
                    </h2>

                    <p>
                        Tambahkan kelas baru ke sistem
                    </p>

                </div>


            </div>



            <div class="card-body">


                <form
                    method="post"
                    action="class_save.php"
                >


                    <div class="form-row">


                        <div class="form-group">


                            <label class="form-label">

                                Nama Kelas

                            </label>


                            <input
                                type="text"
                                name="nama_kelas"
                                class="form-control"
                                placeholder="Contoh: X IPA 1"
                                required
                            >


                        </div>


                        <div
                            class="form-group"
                            style="display:flex;align-items:flex-end;"
                        >


                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                ➕ Tambah Kelas

                            </button>


                        </div>


                    </div>


                </form>


            </div>


        </div>



        <!-- =================================================
             DAFTAR KELAS
        ================================================== -->

        <div class="card">


            <div class="card-header">


                <div>

                    <h2>
                        Daftar Kelas
                    </h2>

                    <p>
                        <?= $total_kelas ?>
                        kelas terdaftar
                    </p>

                </div>


            </div>



            <div class="card-body">


                <?php if ($total_kelas > 0): ?>


                    <div class="class-grid">


                        <?php foreach ($classes as $index => $class): ?>


                            <div class="class-card">


                                <!-- ICON -->

                                <div class="class-card-icon">

                                    🏫

                                </div>



                                <!-- INFO -->

                                <div class="class-card-content">


                                    <div class="class-number">

                                        KELAS
                                        <?= $index + 1 ?>

                                    </div>


                                    <h3>

                                        <?= htmlspecialchars(
                                            $class['nama_kelas']
                                        ) ?>

                                    </h3>


                                    <p>

                                        <?= (int)$class['jumlah_siswa'] ?>

                                        siswa

                                    </p>


                                </div>



                                <!-- STATUS -->

                                <div class="class-card-footer">


                                    <span class="badge badge-active">

                                        Aktif

                                    </span>


                                    <span class="class-student-count">

                                        👨‍🎓
                                        <?= (int)$class['jumlah_siswa'] ?>

                                    </span>


                                </div>


                            </div>


                        <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <div class="empty-state">


                        <div class="empty-icon">
                            🏫
                        </div>


                        <h3>
                            Belum Ada Kelas
                        </h3>


                        <p>
                            Tambahkan kelas menggunakan form di atas.
                        </p>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </div>


</main>
```

</div>

<style>

/* =========================================================
   CLASS GRID
========================================================= */

.class-grid {

    display: grid;

    grid-template-columns:
        repeat(auto-fill, minmax(240px, 1fr));

    gap: 20px;

}


/* =========================================================
   CLASS CARD
========================================================= */

.class-card {

    position: relative;

    padding: 22px;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    background: #ffffff;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;

}


.class-card:hover {

    transform: translateY(-3px);

    box-shadow:
        0 10px 25px rgba(15, 23, 42, 0.08);

}


/* =========================================================
   ICON
========================================================= */

.class-card-icon {

    width: 50px;

    height: 50px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 14px;

    background: #eef2ff;

    font-size: 24px;

    margin-bottom: 16px;

}


/* =========================================================
   CONTENT
========================================================= */

.class-card-content {

    margin-bottom: 20px;

}


.class-number {

    font-size: 11px;

    font-weight: 700;

    color: #64748b;

    letter-spacing: 0.08em;

    margin-bottom: 5px;

}


.class-card h3 {

    margin: 0 0 6px;

    font-size: 20px;

    color: #0f172a;

}


.class-card p {

    margin: 0;

    color: #64748b;

    font-size: 14px;

}


/* =========================================================
   FOOTER
========================================================= */

.class-card-footer {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding-top: 15px;

    border-top: 1px solid #f1f5f9;

}


.class-student-count {

    font-size: 13px;

    font-weight: 600;

    color: #475569;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .class-grid {

        grid-template-columns:
            repeat(1, minmax(0, 1fr));

    }

}

</style>

</body>

</html>
