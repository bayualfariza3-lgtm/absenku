<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

$today = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM attendance 
    WHERE tanggal = ?
");
$stmt->execute([$today]);
$total_hari_ini = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Barcode - AbsenKu</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .scanner-box {
            max-width: 650px;
            margin: 0 auto;
        }

        #reader {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            background: #f8fafc;
        }

        .scan-status {
            margin-top: 20px;
            padding: 16px;
            border-radius: 12px;
            background: #f1f5f9;
            text-align: center;
            font-weight: 600;
        }

        .scan-status.success {
            background: #dcfce7;
            color: #166534;
        }

        .scan-status.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .scan-status.loading {
            background: #fef3c7;
            color: #92400e;
        }

        .counter {
            text-align: center;
            margin-bottom: 20px;
            font-size: 15px;
            color: #64748b;
        }
    </style>
</head>

<body>

<div class="app">

    <aside class="sidebar">
        <div class="logo">
            <div>📚</div>
            <span>AbsenKu</span>
        </div>

        <nav>
            <a href="../index.php" class="nav-link">
                🏠 Dashboard
            </a>

            <a href="scan.php" class="nav-link">
                📱 Scan QR
            </a>

            <a href="barcode_scan.php" class="nav-link active">
                ▦ Scan Barcode
            </a>

            <a href="attendance.php" class="nav-link">
                📋 Data Absensi
            </a>

            <a href="../logout.php" class="nav-link">
                🚪 Logout
            </a>
        </nav>
    </aside>

    <main class="main">

        <header class="topbar">
            <div>
                <h2>Scan Barcode</h2>
                <p>Scan barcode siswa untuk mencatat kehadiran</p>
            </div>
        </header>

        <section class="content">

            <div class="card scanner-box">

                <div class="card-header">
                    <div>
                        <h3>▦ Scanner Barcode</h3>
                        <p>Arahkan kamera ke barcode siswa</p>
                    </div>
                </div>

                <div class="card-body">

                    <div class="counter">
                        Absensi hari ini:
                        <strong><?= $total_hari_ini ?></strong> siswa
                    </div>

                    <div id="reader"></div>

                    <div id="status" class="scan-status">
                        Menunggu barcode...
                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
let sudahScan = false;

const statusBox = document.getElementById("status");

function setStatus(message, type = "") {
    statusBox.className = "scan-status " + type;
    statusBox.innerHTML = message;
}

function onScanSuccess(decodedText) {

    if (sudahScan) return;

    sudahScan = true;

    setStatus("⏳ Memproses absensi...", "loading");

    fetch("barcode_process.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "barcode_token=" + encodeURIComponent(decodedText)
    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {

            setStatus(
                "✅ " + data.message,
                "success"
            );

            setTimeout(() => {
                location.reload();
            }, 1500);

        } else {

            setStatus(
                "❌ " + data.message,
                "error"
            );

            setTimeout(() => {
                sudahScan = false;
                setStatus("Menunggu barcode...");
            }, 2000);
        }

    })
    .catch(error => {

        console.error(error);

        setStatus(
            "❌ Terjadi kesalahan koneksi.",
            "error"
        );

        setTimeout(() => {
            sudahScan = false;
            setStatus("Menunggu barcode...");
        }, 2000);
    });
}

function onScanFailure(error) {
    // Abaikan error scan biasa
}

const scanner = new Html5QrcodeScanner(
    "reader",
    {
        fps: 10,
        qrbox: {
            width: 300,
            height: 150
        },
        rememberLastUsedCamera: true
    },
    false
);

scanner.render(onScanSuccess, onScanFailure);
</script>

</body>
</html>