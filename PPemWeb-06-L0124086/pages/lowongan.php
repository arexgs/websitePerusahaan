<?php
require_once __DIR__ . '/../includes/data.php';

$jml_aktif   = count(array_filter($lowongan, fn($l) => $l['status'] === 'aktif'));
$jml_pending = count(array_filter($lowongan, fn($l) => $l['status'] === 'pending'));
$jml_ditutup = count(array_filter($lowongan, fn($l) => $l['status'] === 'ditutup'));
$jml_semua   = count($lowongan);
?>

<div class="pg-header">
    <div>
        <h4>Kelola Lowongan</h4>
        <p>Semua lowongan yang kamu buat</p>
    </div>
    <a href="index.php?hal=tambah" class="btn-brand"><i class="bi bi-plus-lg"></i> Buat Lowongan</a>
</div>

<?php if (isset($_GET['hapus_ok'])): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-check-circle-fill"></i> Lowongan berhasil dihapus.
</div>
<?php endif; ?>

<ul class="nav nav-pills mb-3" style="gap:6px">
    <li class="nav-item"><a class="nav-pill-tab" data-filter="all">Semua (<?= $jml_semua ?>)</a></li>
    <li class="nav-item"><a class="nav-pill-tab" data-filter="aktif">Aktif (<?= $jml_aktif ?>)</a></li>
    <li class="nav-item"><a class="nav-pill-tab" data-filter="pending">Pending (<?= $jml_pending ?>)</a></li>
    <li class="nav-item"><a class="nav-pill-tab" data-filter="ditutup">Ditutup (<?= $jml_ditutup ?>)</a></li>
</ul>

<?php if (empty($lowongan)): ?>
<div style="text-align:center; padding:60px 0; color:var(--muted)">
    <i class="bi bi-briefcase" style="font-size:40px; display:block; margin-bottom:12px"></i>
    <div style="font-size:15px; font-weight:600; margin-bottom:6px">Belum ada lowongan</div>
    <a href="index.php?hal=tambah" class="btn-brand mt-2"><i class="bi bi-plus-lg"></i> Buat Lowongan</a>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($lowongan as $lw):
        $jml_p = count(array_filter($pelamar, fn($p) => $p['posisi'] === $lw['judul']));
    ?>
    <div class="col-sm-6 col-xl-4 lw-col" data-status="<?= $lw['status'] ?>">
        <div class="lw-card">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="lw-icon" style="background:<?= $lw['warna_bg'] ?? '#EEF3FF' ?>">
                    <i class="bi <?= $lw['ikon'] ?? 'bi-briefcase' ?>" style="color:<?= $lw['warna'] ?? '#1A56DB' ?>"></i>
                </div>
                <div style="flex:1; min-width:0">
                    <div style="font-size:13px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">
                        <?= htmlspecialchars($lw['judul']) ?>
                    </div>
                    <div style="font-size:11px; color:var(--muted)">
                        Dibuat <?= date('d M Y', strtotime($lw['created_at'])) ?>
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <?php if ($lw['lokasi']): ?><span class="float-label"><?= htmlspecialchars($lw['lokasi']) ?></span><?php endif; ?>
                <?php if ($lw['tipe']):   ?><span class="float-label"><?= htmlspecialchars($lw['tipe']) ?></span><?php endif; ?>
                <?php if ($lw['gaji']):   ?><span class="float-label"><?= htmlspecialchars($lw['gaji']) ?></span><?php endif; ?>
            </div>

            <div class="clearfix mb-3">
                <?php if ($lw['status'] === 'aktif'): ?>
                    <span class="pill pill-green"><i class="bi bi-circle-fill" style="font-size:7px"></i> Aktif</span>
                <?php elseif ($lw['status'] === 'pending'): ?>
                    <span class="pill pill-yellow"><i class="bi bi-hourglass-split" style="font-size:9px"></i> Pending Admin</span>
                <?php else: ?>
                    <span class="pill pill-red"><i class="bi bi-x-circle" style="font-size:9px"></i> Ditutup</span>
                <?php endif; ?>
            </div>

            <div class="lw-footer">
                <span style="font-size:12px; color:var(--muted)">
                    <i class="bi bi-people"></i> <?= $jml_p ?> pelamar
                </span>
                <div class="d-flex gap-1">
                    <?php if ($lw['status'] !== 'pending'): ?>
                    <a href="index.php?hal=pelamar" class="btn-outline btn-sm">
                        <i class="bi bi-eye"></i>
                    </a>
                    <?php endif; ?>
                    <button class="btn-sm"
                            style="background:#FEE2E2; color:#DC2626; border:none; border-radius:var(--radius-sm); padding:5px 8px; cursor:pointer; font-size:12px"
                            onclick="konfirmasiHapus('index.php?hapus=<?= $lw['id'] ?>&tipe=lowongan', '<?= htmlspecialchars(addslashes($lw['judul'])) ?>')">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
