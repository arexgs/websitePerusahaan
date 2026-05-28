<?php
$sukses = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul'] ?? '');
    $lokasi    = trim($_POST['lokasi'] ?? '');
    $tipe      = trim($_POST['tipe'] ?? '');
    $gaji      = trim($_POST['gaji'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $deadline  = trim($_POST['deadline'] ?? '') ?: null;
    $pendidikan= trim($_POST['pendidikan'] ?? '');
    $ipk_min   = trim($_POST['ipk_min'] ?? '') ?: null;
    $keahlian  = trim($_POST['keahlian'] ?? '');

    if (!$judul)     $errors[] = 'Judul posisi wajib diisi.';
    if (!$lokasi)    $errors[] = 'Lokasi wajib diisi.';
    if (!$tipe)      $errors[] = 'Tipe pekerjaan wajib dipilih.';
    if (!$deskripsi) $errors[] = 'Deskripsi wajib diisi.';

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO lowongan (judul, lokasi, tipe, gaji, deskripsi, deadline, pendidikan, ipk_min, keahlian, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param('sssssssss', $judul, $lokasi, $tipe, $gaji, $deskripsi, $deadline, $pendidikan, $ipk_min, $keahlian);
        if ($stmt->execute()) {
            $sukses = true;
            $_POST = [];
        } else {
            $errors[] = 'Gagal menyimpan ke database: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<div class="pg-header">
    <div>
        <h4>Buat Lowongan Baru</h4>
        <p>Isi formulir berikut untuk membuat lowongan magang/kerja</p>
    </div>
    <a href="index.php?hal=lowongan" class="btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<?php if ($sukses): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i>
    Lowongan berhasil dikirim! Menunggu review admin sebelum ditampilkan.
    <a href="index.php?hal=lowongan" class="ms-auto btn-brand btn-sm">Lihat Lowongan</a>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4">
    <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" action="index.php?hal=tambah">
<div class="row g-3">
    <div class="col-md-8">
        <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-briefcase-fill me-2" style="color:var(--brand)"></i>Informasi Lowongan</h6></div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Judul Posisi <span style="color:#DC2626">*</span></label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Frontend Developer Intern"
                           value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Lokasi <span style="color:#DC2626">*</span></label>
                    <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Surakarta / Remote"
                           value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipe Pekerjaan <span style="color:#DC2626">*</span></label>
                    <select name="tipe" class="form-select">
                        <option value="">-- Pilih --</option>
                        <?php foreach (['Full-time','Part-time','Magang','Remote','Freelance'] as $t): ?>
                        <option value="<?= $t ?>" <?= (($_POST['tipe'] ?? '') === $t) ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rentang Gaji</label>
                    <input type="text" name="gaji" class="form-control" placeholder="Contoh: 5–8 jt"
                           value="<?= htmlspecialchars($_POST['gaji'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Batas Lamaran</label>
                    <input type="date" name="deadline" class="form-control"
                           value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi Pekerjaan <span style="color:#DC2626">*</span></label>
                    <textarea name="deskripsi" class="form-control" rows="5"
                              placeholder="Jelaskan tanggung jawab, kualifikasi, dan benefit..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-list-check me-2" style="color:#6366F1"></i>Persyaratan</h6></div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Minimal Pendidikan</label>
                    <select name="pendidikan" class="form-select">
                        <?php foreach (['S1 / Sederajat','D3 / Sederajat','SMA / Sederajat','S2'] as $t): ?>
                        <option><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Minimal IPK</label>
                    <input type="number" name="ipk_min" class="form-control" placeholder="Contoh: 3.00" step="0.01" min="0" max="4"
                           value="<?= htmlspecialchars($_POST['ipk_min'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Keahlian yang Dibutuhkan</label>
                    <input type="text" name="keahlian" class="form-control" placeholder="Contoh: JavaScript, React, Node.js"
                           value="<?= htmlspecialchars($_POST['keahlian'] ?? '') ?>">
                    <div class="form-text">Pisahkan dengan koma</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-info-circle-fill me-2" style="color:#D97706"></i>Info Pengiriman</h6></div>
            <div style="background:#FEF9C3; border-radius:var(--radius-sm); padding:12px; font-size:12px; color:#A16207; margin-bottom:12px">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                Lowongan akan masuk status <strong>Pending</strong> dan menunggu persetujuan admin.
            </div>
        </div>
        <div class="ui-card">
            <div class="card-head"><h6><i class="bi bi-building me-2" style="color:var(--brand)"></i>Profil Perusahaan</h6></div>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px">
                <div style="width:40px;height:40px;border-radius:var(--radius-sm);background:var(--brand);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700">AC</div>
                <div>
                    <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($perusahaan['nama']) ?></div>
                    <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($perusahaan['industri']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="d-flex gap-2">
            <button type="submit" class="btn-brand"><i class="bi bi-send"></i> Kirim Lowongan</button>
            <a href="index.php?hal=lowongan" class="btn-outline"><i class="bi bi-x"></i> Batal</a>
        </div>
    </div>
</div>
</form>
