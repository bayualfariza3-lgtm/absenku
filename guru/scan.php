<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

$tanggal = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM attendance
    WHERE tanggal = ?
");
$stmt->execute([$tanggal]);

$total_scan = (int) $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Scan Absensi - AbsenKu</title>

    <link rel="stylesheet" href="../assets/style.css">

    <script src="https://unpkg.com/html5-qrcode"></script>
</head>

<body>

<div class="app">

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

            <a href="../admin/students.php" class="nav-link">
                <span>👨‍🎓</span>
                <span>Data Siswa</span>
            </a>

            <a href="../admin/classes.php" class="nav-link">
                <span>🏫</span>
                <span>Data Kelas</span>
            </a>

            <a href="scan.php" class="nav-link active">
                <span>📷</span>
                <span>Scan Absensi</span>
            </a>

            <a href="attendance.php" class="nav-link">
                <span>📝</span>
                <span>Data Absensi</span>
            </a>

            <a href="../admin/reports.php" class="nav-link">
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
                <h1>Scan Absensi</h1>
                <p>Scan QR Code siswa untuk mencatat kehadiran</p>
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

            <div class="scan-layout">


                <!-- SCANNER -->
                <div class="card">

                    <div class="card-header">

                        <div>
                            <h2>📷 Scanner QR Code</h2>
                            <p>Arahkan kamera ke QR Code siswa</p>
                        </div>

                    </div>


                    <div class="card-body">

                        <div id="reader"></div>

                        <div id="scan-status" class="scan-status">
                            Tekan tombol di bawah untuk mengaktifkan kamera.
                        </div>

                        <button
                            type="button"
                            id="start-button"
                            class="btn btn-primary btn-lg btn-block"
                        >
                            📷 Mulai Kamera
                        </button>

                    </div>

                </div>


                <!-- PANEL KANAN -->
                <div>


                    <!-- TOTAL -->
                    <div class="card">

                        <div class="card-header">

                            <div>
                                <h2>Absensi Hari Ini</h2>

                                <p>
                                    <?= date('d-m-Y') ?>
                                </p>
                            </div>

                        </div>

                        <div class="card-body">

                            <div class="scan-total">

                                <div class="stat-icon success">
                                    ✓
                                </div>

                                <div>

                                    <div class="stat-label">
                                        Total Absensi
                                    </div>

                                    <div class="stat-number">
                                        <?= $total_scan ?>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- PETUNJUK -->
                    <div class="card">

                        <div class="card-header">

                            <h2>📌 Cara Scan</h2>

                        </div>

                        <div class="card-body">

                            <div class="scan-instruction">

                                <div class="instruction-number">
                                    1
                                </div>

                                <div>
                                    Klik <strong>Mulai Kamera</strong>
                                </div>

                            </div>


                            <div class="scan-instruction">

                                <div class="instruction-number">
                                    2
                                </div>

                                <div>
                                    Izinkan akses kamera
                                </div>

                            </div>


                            <div class="scan-instruction">

                                <div class="instruction-number">
                                    3
                                </div>

                                <div>
                                    Arahkan kamera ke QR siswa
                                </div>

                            </div>


                            <div class="scan-instruction">

                                <div class="instruction-number">
                                    4
                                </div>

                                <div>
                                    Absensi tersimpan otomatis
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>


<style>

.scan-layout {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
    gap: 24px;
}

#reader {
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
    border-radius: 16px;
    overflow: hidden;
}

#reader video {
    width: 100% !important;
    border-radius: 16px;
}

#reader__dashboard {
    margin-top: 10px;
}

.scan-status {
    max-width: 520px;
    margin: 18px auto;
    padding: 14px;
    text-align: center;
    border-radius: 12px;
    background: #f1f5f9;
    color: #475569;
    font-weight: 600;
}

.scan-total {
    display: flex;
    align-items: center;
    gap: 15px;
}

.scan-instruction {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}

.scan-instruction:last-child {
    border-bottom: none;
}

.instruction-number {
    width: 32px;
    height: 32px;
    min-width: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #eef2ff;
    color: #4f46e5;
    font-weight: 700;
}

@media (max-width: 900px) {

    .scan-layout {
        grid-template-columns: 1fr;
    }

}

</style>


<script>

let scanner = null;
let sudahScan = false;


/*
|--------------------------------------------------------------------------
| Ubah status
|--------------------------------------------------------------------------
*/

function statusScan(teks) {

    document.getElementById("scan-status").innerHTML = teks;

}


/*
|--------------------------------------------------------------------------
| Mulai kamera
|--------------------------------------------------------------------------
*/

document
    .getElementById("start-button")
    .addEventListener("click", function () {

        if (scanner !== null) {
            return;
        }

        statusScan("⏳ Membuka kamera...");

        scanner = new Html5Qrcode("reader");


        Html5Qrcode.getCameras()

            .then(function (devices) {

                if (!devices || devices.length === 0) {

                    statusScan(
                        "❌ Kamera tidak ditemukan."
                    );

                    scanner = null;

                    return;
                }


                /*
                 * Pilih kamera belakang jika tersedia.
                 */

                let cameraId = devices[0].id;

                for (let i = 0; i < devices.length; i++) {

                    const label =
                        (devices[i].label || "").toLowerCase();

                    if (
                        label.includes("back") ||
                        label.includes("rear") ||
                        label.includes("environment")
                    ) {

                        cameraId = devices[i].id;

                        break;
                    }
                }


                scanner.start(

                    cameraId,

                    {
                        fps: 10,

                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },

                    function(decodedText) {

                        if (sudahScan) {
                            return;
                        }

                        sudahScan = true;

                        statusScan(
                            "⏳ QR ditemukan, memproses..."
                        );


                        scanner.stop()

                            .then(function () {

                                prosesScan(decodedText);

                            })

                            .catch(function () {

                                prosesScan(decodedText);

                            });

                    },

                    function(errorMessage) {

                        // Error pencarian QR diabaikan.

                    }

                )

                .then(function () {

                    statusScan(
                        "📷 Kamera aktif — arahkan ke QR Code siswa."
                    );

                })

                .catch(function(error) {

                    console.error(error);

                    statusScan(
                        "❌ Kamera tidak dapat digunakan."
                    );

                    scanner = null;

                });

            })

            .catch(function(error) {

                console.error(error);

                statusScan(
                    "❌ Browser tidak memberikan izin kamera."
                );

                scanner = null;

            });

    });


/*
|--------------------------------------------------------------------------
| Kirim QR ke server
|--------------------------------------------------------------------------
*/

function prosesScan(qrToken) {

    const data = new URLSearchParams();

    data.append("qr_token", qrToken);


    fetch("scan_process.php", {

        method: "POST",

        headers: {
            "Content-Type":
                "application/x-www-form-urlencoded"
        },

        body: data.toString()

    })

    .then(function(response) {

        return response.json();

    })

    .then(function(data) {

        if (data.success) {

            statusScan(
                "✅ " + data.message
            );


            setTimeout(function () {

                window.location.reload();

            }, 1800);

        } else {

            statusScan(
                "⚠️ " + data.message
            );


            setTimeout(function () {

                location.reload();

            }, 1800);

        }

    })

    .catch(function(error) {

        console.error(error);

        statusScan(
            "❌ Gagal menghubungi server."
        );

    });

}

</script>

</body>
</html>