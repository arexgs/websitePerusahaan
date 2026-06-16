<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNSCollab - Daftar Team</title>
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
            <a class="nav-link-item" href="{{ url('/daftar-perusahaan') }}">
                <i class="bi bi-buildings"></i> Daftar Perusahaan
            </a>
            <a class="nav-link-item active" href="{{ url('/daftar-team') }}">
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
                <h2 class="fw-bold mb-1">Daftar Team</h2>
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
                            <h5 class="fw-bold mb-0">Tren Pembentukan Team</h5>
                            <small class="text-muted">Pertumbuhan pembuatan kelompok baru oleh mahasiswa</small>
                        </div>
                        <div class="bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                            <span class="fw-bold">+{{ $teamsThisMonth }}</span> Bulan Ini
                        </div>
                    </div>
                    <div style="position: relative; height:220px; width:100%;">
                        <canvas id="teamsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card custom-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Peningkatan Team</h5>
                            <small class="text-muted">Jumlah publikasi team baru</small>
                        </div>
                        <div class="bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                            <span class="fw-bold">+{{ $teamsThisMonth }}</span> Bulan Ini
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
                <h5 class="fw-bold mb-0">Database Kelompok Projek Terdaftar</h5>
                
                <div style="max-width: 400px; width: 100%;">
                    <form class="d-flex" role="search" method="GET" action="{{ url('/daftar-team') }}">
                        <input class="form-control me-2" type="search" name="search" placeholder="Cari nama team / kategori projek..." aria-label="Search" value="{{ $searchQuery }}">
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

            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <thead class="text-muted border-bottom">
                        <tr>
                            <th>Nama Team</th>
                            <th>Kategori Projek</th>
                            <th>Ketua (Creator)</th>
                            <th class="text-center">Kapasitas Anggota</th>
                            <th>Batas Registrasi</th>
                            <th class="text-center">Aksi</th>
                        <tr>
                    </thead>
                    <tbody>
                        @if (count($daftarTeams) > 0)
                            @foreach ($daftarTeams as $row)
                                <tr class="border-bottom">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            @if($row->team_logo)
                                                <img src="{{ asset('storage/' . $row->team_logo) }}" class="rounded-3 me-3 object-fit-cover" style="width: 40px; height: 40px;" alt="Logo Team">
                                            @else
                                                <div class="bg-info text-white p-2 rounded-3 me-3 fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 14px;">
                                                    {{ strtoupper(substr($row->team_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="fw-bold d-block text-dark mb-0" style="font-size: 14.5px;">{{ $row->team_name }}</span>
                                                <small class="text-muted">ID: #TM-{{ $row->id_team }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1.5 rounded" style="font-weight: 500;">
                                            {{ $row->category ? $row->category : 'Umum/Lainnya' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="d-block fw-semibold text-dark" style="font-size: 14px;">{{ $row->creator->full_name ?? 'Tidak Diketahui' }}</span>
                                        <small class="text-muted">NIM: {{ $row->creator->nim ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold">
                                            {{ $row->total_anggota }} / {{ $row->max_member ?? '∞' }} Anggota
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-dark" style="font-size: 14px; font-weight: 500;">
                                            <i class="bi bi-calendar-event me-1 text-muted"></i> 
                                            {{ $row->deadline ? \Carbon\Carbon::parse($row->deadline)->locale('id')->isoFormat('D MMM YYYY') : 'Tanpa Tenggat' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ url('/detail-team?id=' . $row->id_team) }}" class="btn btn-outline-secondary btn-sm px-2 rounded-3" style="font-size: 13px;">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                            <form action="{{ url('/daftar-team/hapus/' . $row->id_team) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelompok {{ $row->team_name }} dari platform?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light btn-sm text-danger border-0 rounded-3 px-2">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-people text-muted d-block mb-2" style="font-size: 2rem;"></i>
                                    Tidak ada data kelompok/team ditemukan.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
           @if (count($daftarTeams) > 0)
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 pt-4 border-top mt-3">
        <div class="text-muted" style="font-size: 14px;">
            Menampilkan <span class="fw-semibold text-dark">{{ $daftarTeams->firstItem() }}</span> 
            sampai <span class="fw-semibold text-dark">{{ $daftarTeams->lastItem() }}</span> 
            dari <span class="fw-semibold text-dark">{{ $daftarTeams->total() }}</span> total kelompok
        </div>
        
        <div class="custom-pagination">
            <ul class="pagination mb-0">
                {{-- Tombol Ke Halaman Sebelumnya --}}
                @if ($daftarTeams->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $daftarTeams->appends(['search' => $searchQuery])->previousPageUrl() }}" rel="prev">&lsaquo;</a></li>
                @endif

                {{-- Tombol Angka Halaman --}}
                @foreach ($daftarTeams->getUrlRange(1, $daftarTeams->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $daftarTeams->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url . (str_contains($url, '?') ? '&' : '?') . http_build_query(['search' => $searchQuery]) }}">{{ $page }}</a>
                    </li>
                @endforeach

                {{-- Tombol Ke Halaman Selanjutnya --}}
                @if ($daftarTeams->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $daftarTeams->appends(['search' => $searchQuery])->nextPageUrl() }}" rel="next">&rsaquo;</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link">&rsaquo;</span></li>
                @endif
            </ul>
        </div>
    </div>
@endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 1. GRAFIK REGISTRASI TEAM (LINE CHART)
        const ctxTeam = document.getElementById('teamCharts').getContext('2d');
        new Chart(ctxTeam, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthsTeams) !!},
                datasets: [{
                    label: 'Team Baru Dibentuk',
                    data: {!! json_encode($countsTeams) !!},
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.05)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#17a2b8',
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