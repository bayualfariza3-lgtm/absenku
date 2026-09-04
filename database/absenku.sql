CREATE DATABASE IF NOT EXISTS absenku
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE absenku;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin','guru','kepala_sekolah') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL,
    wali_kelas VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(30) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    kelas_id INT NOT NULL,
    qr_token VARCHAR(100) NOT NULL UNIQUE,
    barcode_token VARCHAR(100) NOT NULL UNIQUE,
    aktif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (kelas_id)
        REFERENCES classes(id)
        ON DELETE CASCADE
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    status ENUM(
        'hadir',
        'terlambat',
        'izin',
        'sakit',
        'alpa'
    ) NOT NULL,
    keterangan TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_student_date(student_id, tanggal),

    FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    FOREIGN KEY (recorded_by)
        REFERENCES users(id)
        ON DELETE SET NULL
);

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
);

INSERT INTO classes
(nama_kelas, wali_kelas)
VALUES
('kon', 'Wali Kelas bangsat'),
('pler', 'Wali Kelas anjing'),
('mmk', 'Wali Kelas bau');