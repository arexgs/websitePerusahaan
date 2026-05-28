<?php
$simpan_ok = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw_baru    = $_POST['pw_baru'] ?? '';
    $pw_confirm = $_POST['pw_confirm'] ?? '';

    if ($pw_baru && $pw_baru !== $pw_confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }
    if ($pw_baru && strlen($pw_baru) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }
    if (empty($errors)) {
        $simpan_ok = true;
    }
}
?>

<div class="pg-header">
    <div>
        <h4>Pengaturan Akun</h4>
        <p>Kelola keamanan dan preferensi akun <?= htmlspecialchars($perusahaan['nama']) ?></p>
    </div>
</div>

<?php if ($simpan_ok): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i> Pengaturan berhasil disimpan!
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

<form method="POST" action="index.php?hal=pengaturan">
<div class="row g-3">
    <div class="col-md-8">

        <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-person-fill me-2" style="color:var(--brand)"></i>Informasi Akun</h6></div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Pengguna</label>
                    <input type="text" name="username" class="form-control"
                           value="<?= htmlspecialchars($_POST['username'] ?? $perusahaan['nama']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Akun</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? $perusahaan['email']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="telepon" class="form-control"
                           value="<?= htmlspecialchars($_POST['telepon'] ?? $perusahaan['telepon']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Zona Waktu</label>
                    <select name="timezone" class="form-select">
                        <option selected>WIB — Jakarta (UTC+7)</option>
                        <option>WITA — Makassar (UTC+8)</option>
                        <option>WIT — Jayapura (UTC+9)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-shield-lock-fill me-2" style="color:#6366F1"></i>Keamanan</h6></div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Password Saat Ini</label>
                    <div style="position:relative">
                        <input type="password" name="pw_lama" class="form-control" placeholder="Masukkan password lama" id="pw-old">
                        <button type="button" onclick="togglePw('pw-old', this)"
                                style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--muted); cursor:pointer; font-size:15px">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password Baru</label>
                    <div style="position:relative">
                        <input type="password" name="pw_baru" class="form-control" placeholder="Minimal 8 karakter" id="pw-new">
                        <button type="button" onclick="togglePw('pw-new', this)"
                                style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--muted); cursor:pointer; font-size:15px">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <div style="position:relative">
                        <input type="password" name="pw_confirm" class="form-control" placeholder="Ulangi password baru" id="pw-confirm">
                        <button type="button" onclick="togglePw('pw-confirm', this)"
                                style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--muted); cursor:pointer; font-size:15px">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <div style="background:#EEF3FF; border-radius:var(--radius-sm); padding:10px 14px; font-size:12px; color:var(--brand); display:flex; align-items:center; gap:8px">
                        <i class="bi bi-info-circle-fill"></i>
                        Password harus mengandung huruf besar, huruf kecil, angka, dan minimal 8 karakter.
                    </div>
                </div>
            </div>
        </div>

        <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-bell-fill me-2" style="color:#D97706"></i>Preferensi Notifikasi</h6></div>
            <div style="display:flex; flex-direction:column; gap:14px">
                <?php
                $notif_items = [
                    ['label'=>'Pelamar baru masuk',              'desc'=>'Notif setiap ada mahasiswa melamar lowonganmu',          'aktif'=>true],
                    ['label'=>'Status lowongan diperbarui admin', 'desc'=>'Notif saat lowongan disetujui atau ditolak admin',       'aktif'=>true],
                    ['label'=>'Pengingat batas lamaran',          'desc'=>'Ingatkan 3 hari sebelum lowongan ditutup',              'aktif'=>false],
                    ['label'=>'Laporan mingguan',                 'desc'=>'Ringkasan aktivitas rekrutmen setiap Senin',            'aktif'=>true],
                ];
                foreach ($notif_items as $i => $notif): ?>
                <?php if ($i > 0): ?><div style="border-top:1px solid var(--border)"></div><?php endif; ?>
                <div style="display:flex; align-items:center; justify-content:space-between">
                    <div>
                        <div style="font-size:13px; font-weight:500"><?= $notif['label'] ?></div>
                        <div style="font-size:12px; color:var(--muted)"><?= $notif['desc'] ?></div>
                    </div>
                    <div class="toggle-switch <?= $notif['aktif'] ? 'active' : '' ?>" onclick="toggleSwitch(this)"></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn-brand"><i class="bi bi-check-lg"></i> Simpan Pengaturan</button>
    </div>

    <div class="col-md-4">
        <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-star-fill me-2" style="color:#D97706"></i>Paket Saat Ini</h6></div>
            <div style="background:linear-gradient(135deg,var(--brand),#6366F1); border-radius:var(--radius-sm); padding:16px; color:#fff; margin-bottom:12px">
                <div style="font-size:11px; opacity:.8; margin-bottom:4px">Paket Aktif</div>
                <div style="font-size:18px; font-weight:700">Business Pro</div>
                <div style="font-size:12px; opacity:.8; margin-top:4px">Aktif hingga 31 Des 2025</div>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; font-size:12px">
                <div style="display:flex; justify-content:space-between"><span style="color:var(--muted)">Lowongan aktif</span><span style="font-weight:600"><?= $stat_lowongan_aktif ?> / 10</span></div>
                <div class="prog-bar"><div class="prog-fill" style="width:<?= ($stat_lowongan_aktif/10*100) ?>%; background:var(--brand)"></div></div>
                <div style="display:flex; justify-content:space-between; margin-top:4px"><span style="color:var(--muted)">Pelamar bulan ini</span><span style="font-weight:600"><?= count($pelamar) ?> / 200</span></div>
                <div class="prog-bar"><div class="prog-fill" style="width:<?= (count($pelamar)/200*100) ?>%; background:#6366F1"></div></div>
            </div>
            <button type="button" class="btn-brand w-100 justify-content-center mt-3">
                <i class="bi bi-arrow-up-circle"></i> Upgrade Paket
            </button>
        </div>

        <div class="ui-card" style="border-color:#FECACA">
            <div class="card-head"><h6><i class="bi bi-exclamation-triangle-fill me-2" style="color:#DC2626"></i>Zona Berbahaya</h6></div>
            <p style="font-size:12px; color:var(--muted); margin-bottom:12px">Tindakan berikut bersifat permanen dan tidak dapat dibatalkan.</p>
            <div style="display:flex; flex-direction:column; gap:8px">
                <button type="button" class="btn-danger-soft w-100 justify-content-center" onclick="showToast('⚠ Fitur ini memerlukan konfirmasi lebih lanjut.')">
                    <i class="bi bi-trash3"></i> Hapus Semua Data Lowongan
                </button>
                <button type="button" class="btn-danger-soft w-100 justify-content-center" onclick="showToast('⚠ Fitur ini memerlukan konfirmasi lebih lanjut.')">
                    <i class="bi bi-x-octagon"></i> Nonaktifkan Akun
                </button>
            </div>
        </div>
    </div>
</div>
</form>
