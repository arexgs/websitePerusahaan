-- =============================================
-- DATABASE: PPemWeb-06-L0124086
-- Import file ini di phpMyAdmin
-- =============================================

CREATE DATABASE IF NOT EXISTS `PPemWeb-06-L0124086` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `PPemWeb-06-L0124086`;

-- =============================================
-- TABEL PERUSAHAAN
-- =============================================
CREATE TABLE perusahaan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    industri VARCHAR(100),
    website VARCHAR(200),
    ukuran VARCHAR(50),
    deskripsi TEXT,
    email VARCHAR(100),
    telepon VARCHAR(30),
    bergabung VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO perusahaan (nama, industri, website, ukuran, deskripsi, email, telepon, bergabung) VALUES (
    'AirCrops Indonesia',
    'Teknologi & Agrikultur',
    'https://aircrops.id',
    '50–200 karyawan',
    'AirCrops Indonesia adalah perusahaan teknologi agrikultur yang berfokus pada solusi digital untuk petani dan distributor hasil bumi. Kami menghubungkan rantai pasok pertanian dengan teknologi modern.',
    'admin@aircrops.id',
    '+62 21-1234-5678',
    'Maret 2022'
);

-- =============================================
-- TABEL LOWONGAN
-- =============================================
CREATE TABLE lowongan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(150) NOT NULL,
    ikon VARCHAR(50) DEFAULT 'bi-briefcase',
    warna_bg VARCHAR(10) DEFAULT '#EEF3FF',
    warna VARCHAR(10) DEFAULT '#1A56DB',
    lokasi VARCHAR(100),
    tipe VARCHAR(50),
    gaji VARCHAR(50),
    status ENUM('aktif','pending','ditutup') DEFAULT 'pending',
    deskripsi TEXT,
    pendidikan VARCHAR(50),
    ipk_min DECIMAL(3,2),
    keahlian VARCHAR(255),
    deadline DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO lowongan (judul, ikon, warna_bg, warna, lokasi, tipe, gaji, status, deskripsi, pendidikan, ipk_min, keahlian) VALUES
('Frontend Developer',    'bi-code-slash',      '#EEF3FF', '#1A56DB', 'Yogyakarta', 'Full-time', '8–12 jt',  'aktif',   'Membangun antarmuka web yang responsif dan modern.', 'S1 / Sederajat', 3.00, 'JavaScript, React, CSS'),
('Backend Developer',     'bi-server',           '#EDE9FE', '#6D28D9', 'Remote',     'Full-time', '10–15 jt', 'aktif',   'Mengembangkan API dan sistem server yang skalabel.',  'S1 / Sederajat', 3.00, 'Node.js, PHP, MySQL'),
('UI/UX Designer',        'bi-palette',          '#FEE2E2', '#B91C1C', 'Surakarta',  'Full-time', '7–10 jt',  'aktif',   'Merancang pengalaman pengguna yang intuitif.',        'S1 / Sederajat', 3.00, 'Figma, Adobe XD'),
('Data Analyst',          'bi-bar-chart-line',   '#FEF9C3', '#A16207', 'Jakarta',    'Full-time', '9–13 jt',  'aktif',   'Menganalisis data untuk mendukung keputusan bisnis.', 'S1 / Sederajat', 3.00, 'Python, SQL, Tableau'),
('Staff Human Resources', 'bi-people',           '#FEF3C7', '#D97706', 'Jakarta',    'Full-time', '7–11 jt',  'aktif',   'Mengelola rekrutmen dan pengembangan SDM.',           'S1 / Sederajat', 3.00, 'Komunikasi, MS Office'),
('Staff Keuangan',        'bi-cash-coin',        '#ECFDF5', '#059669', 'Bandung',    'Full-time', '8–12 jt',  'aktif',   'Mengelola laporan keuangan dan pembukuan.',           'S1 / Sederajat', 3.00, 'Akuntansi, SAP'),
('Marketing Officer',     'bi-megaphone',        '#F3F4F6', '#6B7280', 'Jakarta',    'Part-time', '5–8 jt',   'pending', 'Merencanakan dan menjalankan strategi pemasaran.',    'S1 / Sederajat', 3.00, 'Digital Marketing, Canva'),
('Sekretaris',            'bi-journal-text',     '#F5F3FF', '#7C3AED', 'Surakarta',  'Full-time', '5–8 jt',   'ditutup', 'Mendukung kegiatan administratif pimpinan.',          'S1 / Sederajat', 3.00, 'Administrasi, MS Office');

-- =============================================
-- TABEL PELAMAR
-- =============================================
CREATE TABLE pelamar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inisial VARCHAR(5),
    nama VARCHAR(100) NOT NULL,
    posisi VARCHAR(150),
    jurusan VARCHAR(100),
    universitas VARCHAR(150) DEFAULT 'Universitas Sebelas Maret',
    ipk DECIMAL(3,2),
    angkatan YEAR,
    email VARCHAR(100),
    telepon VARCHAR(30),
    tanggal DATE,
    status ENUM('menunggu','diproses','diterima','ditolak') DEFAULT 'menunggu',
    bg VARCHAR(10) DEFAULT '#DBEAFE',
    warna VARCHAR(10) DEFAULT '#1D4ED8',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO pelamar (inisial, nama, posisi, jurusan, universitas, ipk, angkatan, email, telepon, tanggal, status, bg, warna) VALUES
('AS', 'Andi Saputra',      'Frontend Developer',  'Informatika',             'Universitas Sebelas Maret', 3.87, 2023, 'andi.s@uns.ac.id',    '+62 812-3456-7890', '2025-04-10', 'menunggu', '#DBEAFE', '#1D4ED8'),
('SD', 'Sinta Dewi',        'Backend Developer',   'Informatika',             'Universitas Sebelas Maret', 3.75, 2022, 'sinta.d@uns.ac.id',   '+62 813-9876-5432', '2025-04-12', 'diterima', '#EDE9FE', '#6D28D9'),
('RW', 'Rizky Wibowo',      'Sekretaris',          'Administrasi Bisnis',     'Universitas Sebelas Maret', 3.65, 2023, 'rizky.w@uns.ac.id',   '+62 856-1122-3344', '2025-04-13', 'menunggu', '#FEF9C3', '#A16207'),
('SA', 'Sukma Adiningrat',  'Staff Keuangan',      'Akuntansi',               'Universitas Sebelas Maret', 3.80, 2022, 'sukma.a@uns.ac.id',   '+62 878-5566-7788', '2025-04-14', 'ditolak',  '#FEE2E2', '#B91C1C'),
('DN', 'Dewi Nuraini',      'Staff HRD',           'Psikologi',               'Universitas Sebelas Maret', 3.72, 2022, 'dewi.n@uns.ac.id',    '+62 812-7788-9900', '2025-04-14', 'diproses', '#DCFCE7', '#15803D'),
('BP', 'Bagas Pratama',     'Data Analyst',        'Statistika',              'Universitas Sebelas Maret', 3.90, 2023, 'bagas.p@uns.ac.id',   '+62 821-4455-6677', '2025-04-15', 'menunggu', '#F3F4F6', '#374151'),
('LH', 'Laila Hasanah',     'Marketing Officer',   'Manajemen',               'Universitas Sebelas Maret', 3.55, 2023, 'laila.h@uns.ac.id',   '+62 877-2233-4455', '2025-04-15', 'menunggu', '#FEF3C7', '#D97706'),
('MR', 'Muhammad Rasyid',   'UI/UX Designer',      'Desain Komunikasi Visual','Universitas Sebelas Maret', 3.60, 2023, 'm.rasyid@uns.ac.id',  '+62 811-3344-5566', '2025-04-16', 'diproses', '#DBEAFE', '#1D4ED8'),
('FS', 'Fitria Sari',       'Staff Keuangan',      'Ekonomi Pembangunan',     'Universitas Sebelas Maret', 3.68, 2023, 'fitria.s@uns.ac.id',  '+62 856-6677-8899', '2025-04-16', 'menunggu', '#ECFDF5', '#059669');
