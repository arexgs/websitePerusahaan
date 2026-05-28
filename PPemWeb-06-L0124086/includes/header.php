<?php
$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'dashboard';
$allowed = ['dashboard','lowongan','pelamar','detail','tambah','profil','pengaturan'];
if (!in_array($halaman, $allowed)) $halaman = 'dashboard';

$nav_map = [
    'dashboard'   => 0,
    'lowongan'    => 1,
    'pelamar'     => 2,
    'detail'      => 3,
    'tambah'      => 4,
    'profil'      => 5,
    'pengaturan'  => 6,
];

require_once __DIR__ . '/data.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPemWeb-02-L0124086</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header id="topbar">
    <div class="logo-wrap">
        <button class="btn btn-light" id="menu-toggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="sidebar-brand">
            <img src="assets/logo.png" alt="Logo" class="logo" width="120" height="120">
        </div>
    </div>
    <div class="topbar-search">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Cari lowongan, pelamar ...">
    </div>
    <div class="topbar-right">
        <div class="icon-btn" onclick="toggleNotif()" id="notif-trigger">
            <i class="bi bi-bell"></i>
            <span class="notif-dot" id="ndot"></span>
        </div>
        <div class="notif-dd" id="notif-dd">
            <div class="notif-header">Notifikasi</div>
            <div class="notif-item">
                <div class="notif-ico" style="background:#1A56DB"></div>
                <div class="notif-content">
                    <div class="notif-text">2 pelamar baru untuk Backend Developer</div>
                    <div class="notif-time">2 jam yang lalu</div>
                </div>
            </div>
            <div class="notif-item">
                <div class="notif-ico" style="background:#16A34A"></div>
                <div class="notif-content">
                    <div class="notif-text">Lowongan UI Designer disetujui Admin</div>
                    <div class="notif-time">5 jam yang lalu</div>
                </div>
            </div>
            <div class="notif-item">
                <div class="notif-ico" style="background:#DC2626"></div>
                <div class="notif-content">
                    <div class="notif-text">Lowongan Data Analyst ditolak Admin</div>
                    <div class="notif-time">1 hari yang lalu</div>
                </div>
            </div>
        </div>
        <div class="icon-btn" title="Pesan"><i class="bi bi-envelope"></i></div>
        <div class="avatar-btn" title="AirCrops">AC</div>
    </div>
</header>

<div id="mob-overlay" onclick="toggleSidebar()"></div>

<nav id="sidebar">
    <div class="nav-label">Menu Utama</div>
    <?php
    $nav_items = [
        ['hal' => 'dashboard',  'icon' => 'bi-grid-1x2',    'label' => 'Dashboard',         'badge' => ''],
        ['hal' => 'lowongan',   'icon' => 'bi-briefcase',   'label' => 'Kelola Lowongan',    'badge' => '<span class="nav-badge pill pill-blue">'. $stat_lowongan_aktif .'</span>'],
        ['hal' => 'pelamar',    'icon' => 'bi-people',      'label' => 'Kelola Pelamar',     'badge' => '<span class="nav-badge pill pill-red">'. $stat_total_pelamar .'</span>'],
        ['hal' => 'detail',     'icon' => 'bi-person-vcard','label' => 'Detail Pelamar',     'badge' => ''],
        ['hal' => 'tambah',     'icon' => 'bi-plus-circle', 'label' => 'Buat Lowongan',      'badge' => ''],
    ];
    foreach ($nav_items as $item):
        $active = ($halaman === $item['hal']) ? 'active' : '';
    ?>
    <a class="nav-link-item <?= $active ?>" href="index.php?hal=<?= $item['hal'] ?>">
        <i class="bi <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
        <?= $item['badge'] ?>
    </a>
    <?php endforeach; ?>

    <div class="nav-label" style="margin-top:12px">Pengaturan</div>
    <?php
    $nav_settings = [
        ['hal' => 'profil',      'icon' => 'bi-person-circle', 'label' => 'Profil Perusahaan'],
        ['hal' => 'pengaturan',  'icon' => 'bi-gear',          'label' => 'Pengaturan Akun'],
    ];
    foreach ($nav_settings as $item):
        $active = ($halaman === $item['hal']) ? 'active' : '';
    ?>
    <a class="nav-link-item <?= $active ?>" href="index.php?hal=<?= $item['hal'] ?>">
        <i class="bi <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
    </a>
    <?php endforeach; ?>

    <div class="sidebar-bottom">
        <a class="nav-link-item" style="color:#DC2626" href="index.php?hal=dashboard">
            <i class="bi bi-box-arrow-right"></i> Keluar
        </a>
    </div>
</nav>

<main id="content">
