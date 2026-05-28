<?php
$id_dipilih = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$p = null;

if ($id_dipilih > 0) {
    $stmt = $conn->prepare("SELECT * FROM pelamar WHERE id = ?");
    $stmt->bind_param('i', $id_dipilih);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
if (!$p && !empty($pelamar)) $p = $pelamar[0];
if (!$p) { echo '<p>Pelamar tidak ditemukan.</p>'; return; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    $aksi_baru = ($_POST['aksi'] === 'terima') ? 'diterima' : 'ditolak';
    $stmt = $conn->prepare("UPDATE pelamar SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $aksi_baru, $p['id']);
    $stmt->execute();
    $stmt->close();
    header('Location: index.php?hal=detail&id=' . $p['id'] . '&updated=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['catatan'])) {
    $catatan_baru = trim($_POST['catatan']);
    $stmt = $conn->prepare("UPDATE pelamar SET catatan = ? WHERE id = ?");
    $stmt->bind_param('si', $catatan_baru, $p['id']);
    $stmt->execute();
    $stmt->close();
    $p['catatan'] = $catatan_baru;
}

$status_label = match($p['status']) {
    'menunggu' => 'Menunggu Review',
    'diterima' => 'Diterima',
    'ditolak'  => 'Ditolak',
    'diproses' => 'Sedang Diproses',
    default    => $p['status']
};
?>

<div class="pg-header">
    <a href="index.php?hal=pelamar" class="btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<?php if (isset($_GET['updated'])): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-check-circle-fill"></i>
    Status pelamar berhasil diubah.
</div>
<?php endif; ?>

<?php if (isset($_POST['aksi'])): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-check-circle-fill"></i>
    Status pelamar berhasil diubah menjadi <strong><?= $status_label ?></strong>.
</div>
<?php endif; ?>

<div class="profile-hero mb-3">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="big-avatar"><?= htmlspecialchars($p['inisial']) ?></div>
        <div class="flex-grow-1">
            <div style="font-size:20px; font-weight:700"><?= htmlspecialchars($p['nama']) ?></div>
            <div style="font-size:13px; opacity:.8; margin-top:3px">
                Melamar: <?= htmlspecialchars($p['posisi']) ?> &nbsp;·&nbsp; <?= $p['tanggal'] ?>
            </div>
            <div style="margin-top:10px">
                <span class="pill" style="background:rgba(255,255,255,.2); color:#fff">
                    <i class="bi bi-hourglass-split"></i> <?= $status_label ?>
                </span>
            </div>
        </div>
        <?php if ($p['status'] === 'menunggu' || $p['status'] === 'diproses'): ?>
        <div class="d-flex gap-2 flex-wrap">
            <form method="POST" style="display:inline">
                <input type="hidden" name="aksi" value="terima">
                <button type="submit" class="btn-success-soft"><i class="bi bi-check-lg"></i> Terima</button>
            </form>
            <form method="POST" style="display:inline">
                <input type="hidden" name="aksi" value="tolak">
                <button type="submit" class="btn-danger-soft"><i class="bi bi-x-lg"></i> Tolak</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="ui-card mb-3">
    <div class="card-head"><h6>Alur Seleksi</h6></div>
    <div class="d-flex" style="padding:8px 0">
        <div class="flow-step"><div class="flow-dot done"><i class="bi bi-check-lg"></i></div><div class="flow-label">Lamar</div></div>
        <div class="flow-step"><div class="flow-dot current">2</div><div class="flow-label">Review CV</div></div>
        <div class="flow-step"><div class="flow-dot wait">3</div><div class="flow-label">Wawancara</div></div>
        <div class="flow-step"><div class="flow-dot wait">4</div><div class="flow-label">Keputusan</div></div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="ui-card h-100">
            <div class="card-head"><h6><i class="bi bi-person-fill me-2" style="color:var(--brand)"></i>Data Pribadi</h6></div>
            <div class="row g-2">
                <div class="col-6"><div class="info-box"><div class="info-key">Universitas</div><div class="info-val"><?= htmlspecialchars($p['universitas']) ?></div></div></div>
                <div class="col-6"><div class="info-box"><div class="info-key">Jurusan</div><div class="info-val"><?= htmlspecialchars($p['jurusan']) ?></div></div></div>
                <div class="col-6"><div class="info-box"><div class="info-key">IPK</div><div class="info-val" style="color:#1D4ED8;font-family:'DM Mono',monospace"><?= $p['ipk'] ?> / 4.00</div></div></div>
                <div class="col-6"><div class="info-box"><div class="info-key">Angkatan</div><div class="info-val"><?= $p['angkatan'] ?></div></div></div>
                <div class="col-6"><div class="info-box"><div class="info-key">Email</div><div class="info-val" style="font-size:12px;word-break:break-all"><?= htmlspecialchars($p['email']) ?></div></div></div>
                <div class="col-6"><div class="info-box"><div class="info-key">No. HP</div><div class="info-val"><?= htmlspecialchars($p['telepon']) ?></div></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ui-card h-100">
            <div class="card-head"><h6><i class="bi bi-file-earmark-text-fill me-2" style="color:#6366F1"></i>Dokumen Lamaran</h6></div>
            <div class="d-flex flex-column gap-2">
                <?php foreach ([
                    ['Curriculum Vitae','bi-file-earmark-person','#DBEAFE','#1D4ED8','245 KB'],
                    ['Surat Lamaran','bi-file-earmark-text','#EDE9FE','#6D28D9','128 KB'],
                    ['Transkrip Nilai','bi-file-earmark-bar-graph','#FEF3C7','#D97706','512 KB'],
                    ['Sertifikat Pendukung','bi-award','#F0FDF4','#16A34A','310 KB'],
                ] as [$nama,$icon,$bg,$cw,$size]): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;background:var(--bg);border-radius:var(--radius-sm);padding:10px 12px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:34px;height:34px;border-radius:8px;background:<?= $bg ?>;display:flex;align-items:center;justify-content:center">
                            <i class="bi <?= $icon ?>" style="color:<?= $cw ?>;font-size:15px"></i>
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:500"><?= $nama ?></div>
                            <div style="font-size:11px;color:var(--muted)"><?= $size ?> · PDF</div>
                        </div>
                    </div>
                    <button class="btn-outline btn-sm" onclick="showToast('✓ Dokumen diunduh.')"><i class="bi bi-download"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="ui-card">
    <div class="card-head"><h6><i class="bi bi-chat-left-text-fill me-2" style="color:#D97706"></i>Catatan Rekruter</h6></div>
    <form method="POST">
        <textarea name="catatan" class="form-control mb-3" rows="3"
                  placeholder="Tambahkan catatan untuk pelamar ini..."><?= htmlspecialchars($p['catatan'] ?? '') ?></textarea>
        <button type="submit" class="btn-brand"><i class="bi bi-save"></i> Simpan Catatan</button>
    </form>
</div>
