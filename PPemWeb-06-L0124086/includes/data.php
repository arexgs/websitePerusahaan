<?php
require_once __DIR__ . '/koneksi.php';

$res = $conn->query("SELECT * FROM perusahaan LIMIT 1");
$perusahaan = $res->fetch_assoc();

$lowongan = [];
$res = $conn->query("SELECT * FROM lowongan ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) { $lowongan[] = $row; }

$pelamar = [];
$res = $conn->query("SELECT * FROM pelamar ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) { $pelamar[] = $row; }

$stat_lowongan_aktif = count(array_filter($lowongan, fn($l) => $l['status'] === 'aktif'));
$stat_total_pelamar  = count($pelamar);
$stat_menunggu       = count(array_filter($pelamar, fn($p) => $p['status'] === 'menunggu'));
$stat_diterima       = count(array_filter($pelamar, fn($p) => $p['status'] === 'diterima'));
