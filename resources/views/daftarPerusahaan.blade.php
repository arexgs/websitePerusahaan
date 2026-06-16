<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNSCollab - Daftar Perusahaan</title>
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
            <a class="nav-link-item" href="{{ url('/dashboard') }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a> 
            <a class="nav-link-item" href="{{ url('/validasi-magang') }}">
                <i class="bi bi-file-earmark-check"></i> Validasi Magang
            </a>
            <a class="nav-link-item active" href="{{ url('/daftar-perusahaan') }}">
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
                <h2 class="fw-bold mb-1">Daftar Perusahaan</h2>
            </div>
            <div class="d-flex align-items-center">
                <a href="#">
                    <img src="https://ui-avatars.com/api/?name=Admin+Zahra&background=1FABE1&color=fff" class="rounded-circle" width="45" alt="Profile Admin">
                </a>
            </div>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-xl-6">
                <div class="card custom-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Tren Registrasi Mitra</h5>
                            <small class="text-muted">Pertumbuhan pendaftaran akun perusahaan</small>
                        </div>
                        <div class="bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                            <span class="fw-bold">+{{ $compThisMonth }}</span> Bulan Ini
                        </div>
                    </div>
                    <div style="position: relative; height:220px; width:100%;">
                        <canvas id="companiesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card custom-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Peningkatan Lowongan Magang</h5>
                            <small class="text-muted">Jumlah publikasi projek magang baru</small>
                        </div>
                        <div class="bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                            <span class="fw-bold">+{{ $internThisMonth }}</span> Bulan Ini
                        </div>
                    </div>
                    <div style="position: relative; height:220px; width:100%;">
                        <canvas id="internshipChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <h5 class="fw-bold mb-0">Daftar Perusahaan Mitra Terdaftar</h5>
                
                <div style="max-width: 400px; width: 100%;">
                    <form class="d-flex" role="search" method="GET" action="{{ url('/daftar-perusahaan') }}">
                        <input class="form-control me-2" type="search" name="search" placeholder="Cari nama perusahaan / industri..." aria-label="Search" value="{{ $searchQuery }}">
                        <button class="button-kustom btn-sm me-1" type="submit">Cari</button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-borderless align-middle">
                    <thead class="text-muted border-bottom">
                        <tr>
                            <th>Nama Perusahaan</th>
                            <th>Bidang Industri</th>
                            <th>Kontak & Email</th>
                            <th>Bergabung Pada</th>
                            <th class="text-center">Total Lowongan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($daftarPerusahaan) > 0)
                            @foreach ($daftarPerusahaan as $row)
                                <tr class="border-bottom">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-secondary text-white p-2 rounded-3 me-3 fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($row->company_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="fw-bold d-block">{{ $row->company_name }}</span>
                                                <small class="text-muted">ID: #CP-{{ $row->id_company }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1.5 rounded" style="font-weight: 500;">
                                            {{ $row->industry_field ? $row->industry_field : 'Umum/Lainnya' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="d-block fw-semibold text-dark"><i class="bi bi-telephone me-1 text-muted"></i> {{ $row->contact ? $row->contact : '-' }}</span>
                                        <small class="text-muted"><i class="bi bi-envelope me-1"></i> {{ $row->email }}</small>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1 text-muted"></i> 
                                        {{ $row->create_at ? \Carbon\Carbon::parse($row->create_at)->locale('id')->isoFormat('D MMM YYYY') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold">
                                            {{ $row->total_lowongan }} Program
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ url('/detail-perusahaan?id=' . $row->id_company) }}" class="btn btn-outline-secondary btn-sm px-2 rounded-3">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                            <button class="btn btn-light btn-sm text-danger border-0" 
                                                    onclick="if(confirm('Apakah Anda yakin ingin menghapus kemitraan perusahaan {{ $row->company_name }}?')) { window.location.href='{{ url('/daftar-perusahaan/hapus/' . $row->id_company) }}'; }">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Tidak ada data perusahaan mitra ditemukan.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 1. GRAFIK REGISTRASI PERUSAHAAN (LINE CHART)
        const ctxCompany = document.getElementById('companiesChart').getContext('2d');
        new Chart(ctxCompany, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthsCompanies) !!},
                datasets: [{
                    label: 'Perusahaan Registrasi',
                    data: {!! json_encode($countsCompanies) !!},
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#4e73df',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { drawBorder: false }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. GRAFIK KENAIKAN INTERNSHIP (BAR CHART)
        const ctxIntern = document.getElementById('internshipChart').getContext('2d');
        new Chart(ctxIntern, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthsIntern) !!},
                datasets: [{
                    label: 'Lowongan Diupload',
                    data: {!! json_encode($countsIntern) !!},
                    backgroundColor: '#1cc88a',
                    borderRadius: 6,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { drawBorder: false }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>