<?php require_once __DIR__ . '/../includes/data.php'; ?>

<div class="pg-header">
    <div>
        <h4>Kelola Pelamar</h4>
        <p>Semua posisi — <strong><?= count($pelamar) ?> pelamar</strong></p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <select id="filter-posisi" class="form-select" style="width:auto; font-size:13px">
            <option>Semua Posisi</option>
            <?php foreach (array_unique(array_column($pelamar, 'posisi')) as $pos): ?>
            <option><?= htmlspecialchars($pos) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filter-status" class="form-select" style="width:auto; font-size:13px">
            <option>Semua Status</option>
            <option value="menunggu">Menunggu</option>
            <option value="diproses">Sedang Diproses</option>
            <option value="diterima">Diterima</option>
            <option value="ditolak">Ditolak</option>
        </select>
        <input id="filter-cari" type="text" class="form-control"
               placeholder="Cari nama..." style="width:160px; font-size:13px">
    </div>
</div>

<?php if (isset($_GET['hapus_ok'])): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-check-circle-fill"></i> Pelamar berhasil dihapus.
</div>
<?php endif; ?>

<div class="ui-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Nama Pelamar</th>
                    <th>Posisi Dilamar</th>
                    <th>Jurusan / Prodi</th>
                    <th>IPK</th>
                    <th>Tanggal Lamar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pelamar)): ?>
                <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--muted)">
                    Belum ada pelamar
                </td></tr>
                <?php else: ?>
                <?php foreach ($pelamar as $p):
                    $badge = match($p['status']) {
                        'menunggu' => '<span class="pill pill-yellow"><i class="bi bi-hourglass-split" style="font-size:9px"></i> Menunggu</span>',
                        'diterima' => '<span class="pill pill-green"><i class="bi bi-check-circle" style="font-size:9px"></i> Diterima</span>',
                        'ditolak'  => '<span class="pill pill-red"><i class="bi bi-x-circle" style="font-size:9px"></i> Ditolak</span>',
                        'diproses' => '<span class="pill pill-purple"><i class="bi bi-arrow-repeat" style="font-size:9px"></i> Diproses</span>',
                        default    => ''
                    };
                ?>
                <tr class="pelamar-row"
                    style="cursor:pointer"
                    data-posisi="<?= htmlspecialchars(strtolower($p['posisi'])) ?>"
                    data-status="<?= $p['status'] ?>"
                    data-nama="<?= htmlspecialchars(strtolower($p['nama'])) ?>"
                    onclick="window.location='index.php?hal=detail&id=<?= $p['id'] ?>'">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="mini-avatar"
                                 style="background:<?= $p['bg'] ?>; color:<?= $p['warna'] ?>">
                                <?= htmlspecialchars($p['inisial']) ?>
                            </div>
                            <span style="font-weight:500"><?= htmlspecialchars($p['nama']) ?></span>
                        </div>
                    </td>
                    <td><span class="pill pill-blue" style="font-size:11px"><?= htmlspecialchars($p['posisi']) ?></span></td>
                    <td><?= htmlspecialchars($p['jurusan']) ?></td>
                    <td><code style="font-family:'DM Mono',monospace"><?= $p['ipk'] ?></code></td>
                    <td><?= $p['tanggal'] ?></td>
                    <td><?= $badge ?></td>
                    <td>
                        <div class="d-flex gap-1" onclick="event.stopPropagation()">
                            <a href="index.php?hal=detail&id=<?= $p['id'] ?>" class="btn-brand btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn-sm"
                                    style="background:#FEE2E2; color:#DC2626; border:none; border-radius:var(--radius-sm); padding:5px 8px; cursor:pointer"
                                    onclick="konfirmasiHapus('index.php?hapus=<?= $p['id'] ?>&tipe=pelamar', '<?= htmlspecialchars(addslashes($p['nama'])) ?>')">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr id="pelamar-empty" style="display:none">
                    <td colspan="7" style="text-align:center; padding:30px; color:var(--muted)">
                        <i class="bi bi-search" style="font-size:22px; display:block; margin-bottom:6px"></i>
                        Tidak ada pelamar yang cocok
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
