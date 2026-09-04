<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

$stmt = $pdo->query("
    SELECT
        students.id,
        students.nis,
        students.nama,
        students.barcode_token,
        classes.nama_kelas
    FROM students
    LEFT JOIN classes ON students.kelas_id = classes.id
    WHERE students.aktif = 1
    ORDER BY classes.nama_kelas ASC, students.nama ASC
");

$siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Barcode Siswa - AbsenKu</title>

    <link rel="stylesheet" href="../assets/style.css">

    <!-- Library barcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <style>
        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .barcode-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,.05);
        }

        .barcode-card h3 {
            margin: 10px 0 5px;
            font-size: 18px;
        }

        .barcode-card p {
            margin: 4px 0;
            color: #64748b;
            font-size: 14px;
        }

        .barcode-image {
            width: 100%;
            max-width: 250px;
            height: 90px;
            margin: 15px auto 5px;
        }

        .barcode-number {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
            color: #334155;
        }

        .print-header {
            margin-bottom: 25px;
        }

        .print-header h2 {
            margin-bottom: 5px;
        }

        .print-header p {
            color: #64748b;
        }

        @media print {

            @page {
                size: A4;
                margin: 10mm;
            }

            body {
                background: white !important;
            }

            .sidebar,
            .topbar,
            .no-print {
                display: none !important;
            }

            .app,
            .main,
            .content {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .barcode-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .barcode-card {
                border: 1px solid #999;
                border-radius: 8px;
                box-shadow: none;
                padding: 12px;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .barcode-card h3 {
                font-size: 14px;
            }

            .barcode-card p {
                font-size: 11px;
            }

            .barcode-image {
                height: 65px;
                margin: 8px auto 3px;
            }

            .barcode-number {
                font-size: 10px;
            }
        }
    </style>
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="logo">
            <div>📚</div>
            <span>AbsenKu</span>
        </div>

        <nav>

            <a href="../index.php" class="nav-link">
                🏠 Dashboard
            </a>

            <a href="students.php" class="nav-link">
                👨‍🎓 Data Siswa
            </a>

            <a href="classes.php" class="nav-link">
                🏫 Data Kelas
            </a>

            <a href="reports.php" class="nav-link">
                📊 Laporan
            </a>

            <a href="barcode_students.php" class="nav-link active">
                ▦ Barcode Siswa
            </a>

            <a href="../logout.php" class="nav-link">
                🚪 Logout
            </a>

        </nav>

    </aside>

    <!-- MAIN -->
    <main class="main">

        <header class="topbar no-print">

            <div>
                <h2>Barcode Siswa</h2>
                <p>Generate dan cetak barcode siswa</p>
            </div>

        </header>

        <section class="content">

            <div class="card">

                <div class="card-header print-header">

                    <div>
                        <h2>▦ Barcode Siswa</h2>
                        <p>
                            Total siswa aktif:
                            <strong><?= count($siswa) ?></strong>
                        </p>
                    </div>

                    <div class="no-print">

                        <button
                            onclick="window.print()"
                            class="btn btn-primary"
                        >
                            🖨️ Cetak Semua Barcode
                        </button>

                    </div>

                </div>

                <div class="card-body">

                    <?php if (count($siswa) === 0): ?>

                        <div class="empty-state">
                            <div style="font-size:48px;">📭</div>
                            <h3>Belum ada siswa</h3>
                            <p>
                                Tambahkan siswa terlebih dahulu.
                            </p>
                        </div>

                    <?php else: ?>

                        <div class="barcode-grid">

                            <?php foreach ($siswa as $index => $row): ?>

                                <div class="barcode-card">

                                    <div>
                                        <strong>ABSENKU</strong>
                                    </div>

                                    <h3>
                                        <?= htmlspecialchars($row['nama']) ?>
                                    </h3>

                                    <p>
                                        NIS:
                                        <?= htmlspecialchars($row['nis']) ?>
                                    </p>

                                    <p>
                                        Kelas:
                                        <?= htmlspecialchars($row['nama_kelas'] ?? '-') ?>
                                    </p>

                                    <svg
                                        class="barcode-image"
                                        id="barcode-<?= $index ?>"
                                    ></svg>

                                    <div class="barcode-number">
                                        <?= htmlspecialchars($row['barcode_token']) ?>
                                    </div>

                                </div>

                                <script>
                                    JsBarcode(
                                        "#barcode-<?= $index ?>",
                                        <?= json_encode($row['barcode_token']) ?>,
                                        {
                                            format: "CODE128",
                                            width: 2,
                                            height: 60,
                                            displayValue: false,
                                            margin: 5
                                        }
                                    );
                                </script>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </section>

    </main>

</div>

</body>
</html>