<?php
require_once __DIR__ . '/../includes/data.php';

$posisi_count = [];
foreach ($pelamar as $p) {
    $pos = $p['posisi'];
    $posisi_count[$pos] = ($posisi_count[$pos] ?? 0) + 1;
}
arsort($posisi_count);
$top_posisi = array_slice($posisi_count, 0, 6, true);
$max_pelamar = max(array_values($top_posisi) ?: [1]);

$bar_colors = ['#1A56DB','#6366F1','#8B5CF6','#F59E0B','#10B981','#EF4444'];

$menunggu_n = count(array_filter($pelamar, fn($p) => $p['status'] === 'menunggu'));
$diproses_n = count(array_filter($pelamar, fn($p) => $p['status'] === 'diproses'));
$diterima_n = count(array_filter($pelamar, fn($p) => $p['status'] === 'diterima'));
$ditolak_n  = count(array_filter($pelamar, fn($p) => $p['status'] === 'ditolak'));
$total_n    = count($pelamar);

$aktivitas_raw = [];

$res = $conn->query("SELECT * FROM pelamar ORDER BY created_at DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $dot = '#1A56DB';
    $waktu = date('d M Y, H:i', strtotime($row['created_at']));
    if ($row['status'] === 'diterima') {
        $dot = '#16A34A';
        $label = "<strong>{$row['nama']}</strong> diterima sebagai {$row['posisi']}";
    } elseif ($row['status'] === 'ditolak') {
        $dot = '#DC2626';
        $label = "<strong>{$row['nama']}</strong> ditolak untuk {$row['posisi']}";
    } elseif ($row['status'] === 'diproses') {
        $dot = '#D97706';
        $label = "<strong>{$row['nama']}</strong> sedang diproses — {$row['posisi']}";
    } else {
        $label = "<strong>{$row['nama']}</strong> melamar posisi {$row['posisi']}";
    }
    $aktivitas_raw[] = ['dot' => $dot, 'title' => $label, 'waktu' => $waktu];
}

$lowongan_terbaru = array_slice(
    array_filter($lowongan, fn($l) => $l['status'] === 'aktif'),
    0, 3
);
?>

<div class="pg-header">
    <div>
        <h4>Dashboard</h4>
        <p>Selamat datang kembali, <strong><?= htmlspecialchars($perusahaan['nama']) ?></strong> 👋
        &nbsp;<span style="font-size:12px; color:var(--muted)"><?= date('l, d F Y') ?></span></p>
    </div>
    <a href="index.php?hal=tambah" class="btn-brand">
        <i class="bi bi-plus-lg"></i> Buat Lowongan
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#DBEAFE"><i class="bi bi-briefcase-fill" style="color:#1D4ED8"></i></div>
            <div class="stat-val" style="color:#1D4ED8" id="sv1">0</div>
            <div class="stat-label">Lowongan Aktif</div>
            <div class="stat-trend" style="color:#16A34A">
                <i class="bi bi-arrow-up-short"></i>
                dari <?= count($lowongan) ?> total lowongan
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7"><i class="bi bi-people-fill" style="color:#D97706"></i></div>
            <div class="stat-val" id="sv2">0</div>
            <div class="stat-label">Total Pelamar</div>
            <div class="stat-trend" style="color:#16A34A">
                <i class="bi bi-arrow-up-short"></i>
                <?= $diproses_n ?> sedang diproses
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF9C3"><i class="bi bi-clock-fill" style="color:#A16207"></i></div>
            <div class="stat-val" style="color:#A16207" id="sv3">0</div>
            <div class="stat-label">Menunggu Review</div>
            <div class="stat-trend" style="color:<?= $menunggu_n > 0 ? '#DC2626' : '#16A34A' ?>">
                <i class="bi bi-<?= $menunggu_n > 0 ? 'exclamation-circle' : 'check-circle' ?>"></i>
                <?= $menunggu_n > 0 ? 'Perlu tindakan' : 'Semua sudah diproses' ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#F0FDF4"><i class="bi bi-check-circle-fill" style="color:#16A34A"></i></div>
            <div class="stat-val" style="color:#16A34A" id="sv4">0</div>
            <div class="stat-label">Pelamar Diterima</div>
            <div class="stat-trend" style="color:var(--muted)">
                <?= $total_n > 0 ? round($diterima_n / $total_n * 100) : 0 ?>% acceptance rate
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="ui-card h-100">
            <div class="card-head">
                <h6><i class="bi bi-bar-chart-fill me-2" style="color:var(--brand)"></i>Pelamar per Posisi</h6>
                <span style="font-size:11px; color:var(--muted)"><?= $total_n ?> total pelamar</span>
            </div>
            <?php if (empty($top_posisi)): ?>
            <div style="text-align:center; padding:40px 0; color:var(--muted); font-size:13px">
                <i class="bi bi-inbox" style="font-size:32px; display:block; margin-bottom:8px"></i>
                Belum ada pelamar
            </div>
            <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:10px; padding-top:4px">
                <?php $i = 0; foreach ($top_posisi as $pos => $jml):
                    $pct = round($jml / $max_pelamar * 100);
                    $color = $bar_colors[$i % count($bar_colors)];
                    $p_diterima = count(array_filter($pelamar, fn($p) => $p['posisi'] === $pos && $p['status'] === 'diterima'));
                    $p_ditolak  = count(array_filter($pelamar, fn($p) => $p['posisi'] === $pos && $p['status'] === 'ditolak'));
                    $p_proses   = count(array_filter($pelamar, fn($p) => $p['posisi'] === $pos && $p['status'] === 'diproses'));
                    $p_tunggu   = count(array_filter($pelamar, fn($p) => $p['posisi'] === $pos && $p['status'] === 'menunggu'));
                ?>
                <div class="hbar-row" data-pos="<?= htmlspecialchars($pos) ?>">
                    <div class="hbar-label">
                        <span class="hbar-dot" style="background:<?= $color ?>"></span>
                        <span class="hbar-name"><?= htmlspecialchars($pos) ?></span>
                        <span class="hbar-count"><?= $jml ?></span>
                    </div>
                    <div class="hbar-track">
                        <div class="hbar-fill"
                             style="width:0%; background:<?= $color ?>"
                             data-w="<?= $pct ?>%"></div>
                    </div>
                    <div class="hbar-detail">
                        <?php if ($p_diterima): ?><span class="pill pill-green" style="font-size:10px; padding:1px 6px"><?= $p_diterima ?> diterima</span><?php endif; ?>
                        <?php if ($p_proses):   ?><span class="pill pill-purple" style="font-size:10px; padding:1px 6px"><?= $p_proses ?> diproses</span><?php endif; ?>
                        <?php if ($p_tunggu):   ?><span class="pill pill-yellow" style="font-size:10px; padding:1px 6px"><?= $p_tunggu ?> menunggu</span><?php endif; ?>
                        <?php if ($p_ditolak):  ?><span class="pill pill-red" style="font-size:10px; padding:1px 6px"><?= $p_ditolak ?> ditolak</span><?php endif; ?>
                    </div>
                </div>
                <?php $i++; endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-6">
        <div class="ui-card h-100">
            <div class="card-head">
                <h6><i class="bi bi-pie-chart-fill me-2" style="color:#6366F1"></i>Status Pelamar</h6>
                <span style="font-size:11px; color:var(--muted)"><?= $total_n ?> total</span>
            </div>
            <?php
            $prog_items = [
                ['label'=>'Menunggu Review', 'n'=>$menunggu_n, 'color'=>'#EAB308', 'id'=>'p1'],
                ['label'=>'Sedang Diproses', 'n'=>$diproses_n, 'color'=>'var(--brand)', 'id'=>'p2'],
                ['label'=>'Diterima',         'n'=>$diterima_n, 'color'=>'#16A34A', 'id'=>'p3'],
                ['label'=>'Ditolak',          'n'=>$ditolak_n,  'color'=>'#EF4444', 'id'=>'p4'],
            ];
            foreach ($prog_items as $pg):
                $pct = $total_n > 0 ? round($pg['n'] / $total_n * 100) : 0;
            ?>
            <div class="prog-row">
                <div class="prog-top">
                    <span style="font-size:12px"><?= $pg['label'] ?></span>
                    <span style="font-size:12px; color:var(--muted)"><?= $pg['n'] ?> / <?= $total_n ?> (<?= $pct ?>%)</span>
                </div>
                <div class="prog-bar">
                    <div class="prog-fill" id="<?= $pg['id'] ?>"
                         style="width:0%; background:<?= $pg['color'] ?>"
                         data-w="<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="ui-card">
            <div class="card-head">
                <h6><i class="bi bi-activity me-2" style="color:#6366F1"></i>Aktivitas Terbaru</h6>
                <a href="index.php?hal=pelamar" style="font-size:12px; color:var(--brand)">Lihat semua</a>
            </div>
            <?php if (empty($aktivitas_raw)): ?>
            <div style="text-align:center; padding:30px 0; color:var(--muted); font-size:13px">
                <i class="bi bi-inbox" style="font-size:28px; display:block; margin-bottom:8px"></i>
                Belum ada aktivitas
            </div>
            <?php else:
                $last = count($aktivitas_raw) - 1;
                foreach ($aktivitas_raw as $i => $ak): ?>
            <div class="tl-item">
                <div class="tl-left">
                    <div class="tl-dot" style="background:<?= $ak['dot'] ?>"></div>
                    <?php if ($i < $last): ?><div class="tl-line"></div><?php endif; ?>
                </div>
                <div class="tl-body">
                    <div class="tl-title"><?= $ak['title'] ?></div>
                    <div class="tl-time"><?= $ak['waktu'] ?></div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="col-md-5">
        <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-lightning-fill me-2" style="color:#EAB308"></i>Aksi Cepat</h6></div>
            <div class="d-flex flex-column gap-2">
                <a href="index.php?hal=tambah" class="btn-brand w-100 justify-content-center">
                    <i class="bi bi-plus-circle"></i> Buat Lowongan Baru
                </a>
                <?php if ($menunggu_n > 0): ?>
                <a href="index.php?hal=pelamar" class="btn-brand w-100 justify-content-center" style="background:#D97706; border-color:#D97706">
                    <i class="bi bi-hourglass-split"></i> Review <?= $menunggu_n ?> Pelamar Menunggu
                </a>
                <?php endif; ?>
                <a href="index.php?hal=pelamar" class="btn-outline w-100 justify-content-center">
                    <i class="bi bi-people"></i> Lihat Semua Pelamar
                </a>
                <a href="index.php?hal=lowongan" class="btn-outline w-100 justify-content-center">
                    <i class="bi bi-briefcase"></i> Kelola Lowongan
                </a>
            </div>
        </div>

        <?php if (!empty($lowongan_terbaru)): ?>
        <div class="ui-card">
            <div class="card-head">
                <h6><i class="bi bi-fire me-2" style="color:#EF4444"></i>Lowongan Aktif</h6>
                <a href="index.php?hal=lowongan" style="font-size:12px; color:var(--brand)">Semua</a>
            </div>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($lowongan_terbaru as $lw):
                    $jml = count(array_filter($pelamar, fn($p) => $p['posisi'] === $lw['judul']));
                ?>
                <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-size:13px; font-weight:600"><?= htmlspecialchars($lw['judul']) ?></div>
                        <div style="font-size:11px; color:var(--muted)"><?= $lw['lokasi'] ?> · <?= $jml ?> pelamar</div>
                    </div>
                    <a href="index.php?hal=pelamar" class="btn-outline btn-sm">Lihat</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var targets = [
        { id: 'sv1', val: <?= $stat_lowongan_aktif ?> },
        { id: 'sv2', val: <?= $stat_total_pelamar ?> },
        { id: 'sv3', val: <?= $stat_menunggu ?> },
        { id: 'sv4', val: <?= $stat_diterima ?> }
    ];
    targets.forEach(function (item) {
        var el = document.getElementById(item.id);
        if (!el || item.val === 0) { if (el) el.textContent = 0; return; }
        var cur = 0, step = Math.ceil(item.val / 20);
        var iv = setInterval(function () {
            cur = Math.min(cur + step, item.val);
            el.textContent = cur;
            if (cur >= item.val) clearInterval(iv);
        }, 40);
    });

    document.querySelectorAll('.hbar-fill[data-w]').forEach(function (el, i) {
        setTimeout(function () {
            el.style.width = el.dataset.w;
        }, 120 + i * 100);
    });

    document.querySelectorAll('.prog-fill[data-w]').forEach(function (el, i) {
        setTimeout(function () {
            el.style.width = el.dataset.w;
        }, 200 + i * 100);
    });
});
</script>
