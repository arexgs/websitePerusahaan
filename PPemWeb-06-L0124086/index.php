<?php

$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'dashboard';
$allowed = ['dashboard','lowongan','pelamar','detail','tambah','profil','pengaturan'];
if (!in_array($halaman, $allowed)) $halaman = 'dashboard';

require_once 'includes/koneksi.php';

if (isset($_GET['hapus']) && isset($_GET['tipe'])) {
    $id_hapus   = (int)$_GET['hapus'];
    $tipe_hapus = $_GET['tipe'];

    if ($tipe_hapus === 'lowongan' && $id_hapus > 0) {
        $stmt = $conn->prepare("DELETE FROM lowongan WHERE id = ?");
        $stmt->bind_param('i', $id_hapus);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php?hal=lowongan&hapus_ok=1');
        exit;
    }
    if ($tipe_hapus === 'pelamar' && $id_hapus > 0) {
        $stmt = $conn->prepare("DELETE FROM pelamar WHERE id = ?");
        $stmt->bind_param('i', $id_hapus);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php?hal=pelamar&hapus_ok=1');
        exit;
    }
}

require_once 'includes/header.php';

$page_file = 'pages/' . $halaman . '.php';
if (file_exists($page_file)) {
    require $page_file;
} else {
    echo '<div class="pg-header"><h4>404 — Halaman tidak ditemukan</h4></div>';
}

require_once 'includes/footer.php';
?>
