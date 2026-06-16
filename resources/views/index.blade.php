<?php
// Helper Format Tanggal Indonesia (Dibungkus agar tidak redeclare)
if (!function_exists('formatTanggalIndo')) {
    function formatTanggalIndo($dateString) {
        $timestamp = strtotime($dateString);
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        return date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNSCollab - Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}" />      
</head>
<body>

    <div class="sidebar d-none d-lg-block">
        
        <div class="sidebar-brand">
            <img src="{{ asset('uns-logo.png') }}" alt="Logo" height="45" class="img-fluid">
        </div>

        <div class="nav-label">Menu Utama</div>
        <nav class="d-flex flex-column">
            <a class="nav-link-item active" href="{{ url('/dashboard') }}">
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
            <a class="nav-link-item" href="{{ url('/pengaturan') }}">
                <i class="bi bi-gear"></i> Pengaturan
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
        
        <header class="top-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Dashboard Admin</h2>
            </div>
            <div class="d-flex align-items-center">
                <a href="#">
                    <img src="https://ui-avatars.com/api/?name=Admin+Zahra&background=1FABE1&color=fff" class="rounded-circle" width="45" alt="Profile Admin">
                </a>
            </div>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card custom-card p-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Perusahaan</p>
                        <h3 class="fw-bold">{{ $totalCompany }}</h3>
                        <small class="text-success fw-bold"><i class="bi bi-arrow-up"></i> Terintegrasi DB</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card p-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Tim Aktif</p>
                        <h3 class="fw-bold">{{ $totalTeam }}</h3>
                        <small class="text-success fw-bold"><i class="bi bi-arrow-up"></i> Terintegrasi DB</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card p-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Menunggu ACC</p>
                        <h3 class="fw-bold">{{ $totalPending }}</h3>
                        <small class="text-warning fw-bold">Perlu dicek segera</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold">Daftar Dokumen Baru</h5>
                <a href="#" class="btn btn-sm btn-action" style="color: #1FABE1; border-color: #1FABE1; border-radius: 10px;">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless align-middle">
                    <thead class="text-muted border-bottom">
                        <tr>
                            <th>Perusahaan</th>
                            <th>Posisi Lowongan</th>
                            <th>Tanggal Masuk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($daftarDokumen) > 0)
                            @foreach ($daftarDokumen as $dokumen)
                                <tr class="border-bottom">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="text-white p-2 me-3" style="width: 40px; text-align: center; background-color: #1FABE1; border-radius: 10px; font-weight: 600;">
                                                {{ strtoupper(substr($dokumen->company_name, 0, 1)) }}
                                            </div>
                                            <span class="fw-bold">{{ $dokumen->company_name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $dokumen->internship_title }}</td>
                                    <td>{{ formatTanggalIndo($dokumen->apply_date) }}</td>
                                    <td>
                                        @php
                                        $statusLower = strtolower($dokumen->status);
                                        $badgeClass = ($statusLower == 'pending') ? 'badge-pending' : (($statusLower == 'accepted') ? 'badge bg-success-subtle text-success' : 'badge bg-danger-subtle text-danger');
                                        @endphp
                                        <span class="{{ $badgeClass }}">{{ ucfirst($dokumen->status) }}</span>
                                    </td>
                                    <td>
                                        <button class="button-kustom btn-sm" data-bs-toggle="modal" data-bs-target="#modalVerifikasi">Verifikasi</button>
                                        <button class="btn btn-light btn-sm btn-action text-danger"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Tidak ada data pengajuan magang baru.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card custom-card p-4 mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold">Daftar Aktivitas Tim Mahasiswa</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless align-middle">
                    <thead class="text-muted border-bottom">
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Kategori Lowongan</th>
                            <th>Deskripsi Tim</th>
                            <th>Tanggal Buat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($daftarFeedback) > 0)
                            @foreach ($daftarFeedback as $fb)
                                <tr class="border-bottom">
                                    <td class="py-3">
                                        <div>
                                            <span class="fw-bold d-block">{{ $fb->student_name }}</span>
                                            <small class="text-muted">{{ $fb->NIM }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge px-2 py-1" style="background-color: rgba(31, 171, 225, 0.1); color: #1FABE1; border-radius: 10px; font-weight: 600;">{{ $fb->category }}</span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 250px;">
                                        {{ $fb->team_description }}
                                    </td>
                                    <td>{{ formatTanggalIndo($fb->date_created) }}</td>
                                    <td>
                                        <button class="button-kustom btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalDetailFeedback">Baca</button>
                                        <button class="btn btn-light btn-sm btn-action text-danger"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada tim mahasiswa yang terdaftar.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>