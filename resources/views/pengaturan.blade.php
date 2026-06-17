<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNSCollab - Pengaturan Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}" />      
</head>
<body>

    <div class="sidebar d-none d-lg-block">
        <div class="sidebar-brand">
            <img src="{{ asset('uns-logo.png') }}" alt="Logo" height="45" class="img-fluid">
        </div>

        <div class="nav-label">Menu Utama</div>
        <nav class="d-flex flex-column">
            <a class="nav-link-item" href="{{ url('/dashboard') }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a> 
            <a class="nav-link-item" href="{{ url('/validasi-magang') }}">
                <i class="bi bi-file-earmark-check"></i> Validasi Magang
            </a>
            <a class="nav-link-item" href="{{ url('/daftar-perusahaan') }}">
                <i class="bi bi-buildings"></i> Daftar Perusahaan
            </a>
            <a class="nav-link-item" href="{{ url('/daftar-team') }}">
                <i class="bi bi-people"></i> Daftar Team
            </a>
        </nav>

        <div class="nav-label">Pengaturan</div>
        <nav class="d-flex flex-column">
            <a class="nav-link-item active" href="{{ url('/pengaturan') }}">
                <i class="bi bi-gear"></i> Pengaturan Akun
            </a>
        </nav>

        <div class="sidebar-bottom">
            <form action="{{ url('/logout') }}" method="POST" class="d-inline w-100">
                @csrf
                <button type="submit" class="nav-link-item text-danger border-0 bg-transparent w-100" style="text-align: left;">
                    <i class="bi bi-box-arrow-left"></i> Keluar
                </button>
            </form>
        </div>
    </div>

    <div class="main-content" style="margin-left: 248px; padding: 2rem;">
        
        <form action="{{ url('/pengaturan/simpan') }}" method="POST">
            @csrf
            @method('PUT')

            <header class="top-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Pengaturan Kontrol Web</h2>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold d-flex align-items-center gap-2 rounded-3 shadow-sm">
                        <i class="bi bi-check2-circle"></i> Simpan Pengaturan
                    </button>
                </div>
            </header>

            <div class="row g-4">
                <div class="col-xl-8">
                    
                    <div class="card custom-card p-4 mb-4 border-0 shadow-sm rounded-4">
                        <div class="d-flex align-items-center gap-2 mb-4 text-primary">
                            <i class="bi bi-sliders fs-5"></i>
                            <h5 class="fw-bold mb-0 text-dark">Parameter Kontrol Verifikasi</h5>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Nama Pengatur (Admin)</label>
                                <input type="text" class="form-control" name="admin_name" value="Admin Zahra" placeholder="Nama Admin">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Email Akun Utama *</label>
                                <input type="email" class="form-control bg-light" value="admin.zahra@uns.ac.id" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Metode Validasi Dokumen Magang</label>
                                <select class="form-select" name="validation_method">
                                    <option value="manual" selected>Review Manual (Rekomendasi)</option>
                                    <option value="auto">Otomatis Lolos (Tanpa Cek)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Masa Berlaku Antrean Dokumen Magang</label>
                                <div class="input-group">
                                    <select class="form-select" name="document_expiration">
                                        <option value="7">7 Hari (1 Minggu)</option>
                                        <option value="14" selected>14 Hari (2 Minggu)</option>
                                        <option value="30">30 Hari (1 Bulan)</option>
                                    </select>
                                    <span class="input-group-text bg-light text-muted small">Sejak Diupload</span>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-3">
                            <i class="bi bi-info-circle me-1"></i> Email di atas terikat dengan hak akses super admin utama platform dan tidak dapat diganti secara langsung.
                        </small>
                    </div>

                    <div class="card custom-card p-4 border-0 shadow-sm rounded-4">
                        <div class="d-flex align-items-center gap-2 mb-4 text-primary">
                            <i class="bi bi-shield-lock fs-5"></i>
                            <h5 class="fw-bold mb-0 text-dark">Kredensial Keamanan Admin</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary small">Password Saat Ini</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" placeholder="Masukkan password lama untuk konfirmasi">
                                    <span class="input-group-text bg-transparent text-muted" style="cursor: pointer;"><i class="bi bi-eye"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Password Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" placeholder="Minimal 8 karakter">
                                    <span class="input-group-text bg-transparent text-muted" style="cursor: pointer;"><i class="bi bi-eye"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" placeholder="Ulangi password baru">
                                    <span class="input-group-text bg-transparent text-muted" style="cursor: pointer;"><i class="bi bi-eye"></i></span>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-3">
                            <i class="bi bi-info-circle me-1"></i> Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol unik untuk menjaga keamanan dashboard utama.
                        </small>
                    </div>

                </div>

                <div class="col-xl-4">
                    
                    <div class="card custom-card p-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex align-items-center gap-2 mb-3 text-secondary">
                            <i class="bi bi-clock-history"></i>
                            <h6 class="fw-bold mb-0 text-dark">Aktivitas Terakhir Admin</h6>
                        </div>
                        
                        <div class="position-relative ps-2">
                            @if(isset($adminLogs) && count($adminLogs) > 0)
                                @foreach($adminLogs as $log)
                                    <div class="mb-3 border-start border-2 ps-3 pb-2 position-relative">
                                        <span class="position-absolute top-0 start-0 translate-middle p-1 rounded-circle style-dot {{ $loop->first ? 'bg-primary' : 'bg-secondary' }}" style="margin-left: -1px;"></span>
                                        
                                        <div class="fw-bold text-dark small">{{ $log->activity_description }}</div>
                                        
                                        <small class="text-muted d-block">
                                            {{ \Carbon\Carbon::parse($log->created_at)->locale('id')->diffForHumans() }}
                                        </small>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted small py-2 ps-2">
                                    <i class="bi bi-info-circle me-1"></i> Belum ada riwayat aktivitas tercatat.
                                </div>
                            @endif
                        </div>              <div class="card p-4 border border-danger-subtle bg-danger-subtle bg-opacity-10 rounded-4 shadow-sm">
                        <div class="d-flex align-items-center gap-2 mb-2 text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <h6 class="fw-bold mb-0">Zona Berbahaya</h6>
                        </div>
                        <p class="text-muted small mb-4">Tindakan pembersihan berikut bersifat permanen dan tidak dapat dibatalkan oleh sistem.</p>
                        
                        <div class="d-flex flex-column gap-2">
                            <button type="button" class="btn btn-danger btn-sm py-2 rounded-3 fw-semibold text-white d-flex align-items-center justify-content-center gap-2" onclick="return confirm('Apakah Anda yakin ingin mengosongkan seluruh log antrean dokumen?')">
                                <i class="bi bi-trash3"></i> Kosongkan Riwayat Log Validasi
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>