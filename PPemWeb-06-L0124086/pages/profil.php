<?php
$simpan_ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $industri  = trim($_POST['industri'] ?? '');
    $website   = trim($_POST['website'] ?? '');
    $ukuran    = trim($_POST['ukuran'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    $stmt = $conn->prepare("UPDATE perusahaan SET nama=?, industri=?, website=?, ukuran=?, deskripsi=? WHERE id=1");
    $stmt->bind_param('sssss', $nama, $industri, $website, $ukuran, $deskripsi);
    $stmt->execute();
    $stmt->close();

    $res = $conn->query("SELECT * FROM perusahaan LIMIT 1");
    $perusahaan = $res->fetch_assoc();
    $simpan_ok = true;
}
?>

<div class="pg-header">
    <div>
        <h4>Profil Perusahaan</h4>
        <p>Informasi yang terlihat oleh para pelamar</p>
    </div>
</div>

<?php if ($simpan_ok): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i> Profil berhasil disimpan!
</div>
<?php endif; ?>

<form method="POST" action="index.php?hal=profil">
<div class="row g-3">
    <div class="col-md-8">
        <div class="ui-card">
            <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                <div style="width:64px;height:64px;border-radius:var(--radius);background:var(--brand);display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:700;flex-shrink:0">AC</div>
                <div>
                    <div style="font-size:15px;font-weight:700"><?= htmlspecialchars($perusahaan['nama']) ?></div>
                    <div style="font-size:12px;color:var(--muted);margin-top:2px">Bergabung sejak <?= $perusahaan['bergabung'] ?></div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Perusahaan</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($perusahaan['nama']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bidang Industri</label>
                    <select name="industri" class="form-select">
                        <?php foreach (['Teknologi & Agrikultur','Teknologi','Keuangan','Pendidikan','Kesehatan'] as $ind): ?>
                        <option <?= ($perusahaan['industri'] === $ind) ? 'selected' : '' ?>><?= $ind ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="text" name="website" class="form-control" value="<?= htmlspecialchars($perusahaan['website']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ukuran Perusahaan</label>
                    <select name="ukuran" class="form-select">
                        <?php foreach (['1–50 karyawan','50–200 karyawan','200–1000 karyawan','1000+ karyawan'] as $uk): ?>
                        <option <?= ($perusahaan['ukuran'] === $uk) ? 'selected' : '' ?>><?= $uk ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi Perusahaan</label>
                    <textarea name="deskripsi" class="form-control" rows="4"><?= htmlspecialchars($perusahaan['deskripsi']) ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn-brand"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ui-card">
            <div class="card-head"><h6>Statistik Profil</h6></div>
            <div class="d-flex flex-column gap-2">
                <div class="stat-card" style="padding:14px"><div class="stat-val" style="color:var(--brand)">312</div><div class="stat-label">Dilihat pelamar bulan ini</div></div>
                <div class="stat-card" style="padding:14px"><div class="stat-val"><?= count($lowongan) ?></div><div class="stat-label">Total lowongan dibuat</div></div>
                <div class="stat-card" style="padding:14px"><div class="stat-val" style="color:#16A34A">4.8 ★</div><div class="stat-label">Rating dari 18 ulasan</div></div>
            </div>
        </div>
    </div>
</div>
</form>
