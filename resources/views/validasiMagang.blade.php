<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNSCollab - Validasi Dokumen Mitra</title>
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
            <a class="nav-link-item active" href="{{ url('/validasi-magang') }}">
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
                <h2 class="fw-bold mb-1">Validasi Dokumen Lowongan Magang</h2>
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
                        <p class="text-muted mb-1">Menunggu Validasi</p>
                        <h3 class="fw-bold">{{ $totalPending }}</h3>
                        <small class="text-warning fw-bold">Perlu dicek segera</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card p-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Telah Disetujui</p>
                        <h3 class="fw-bold">{{ $totalAccepted }}</h3>
                        <small class="text-success fw-bold"><i class="bi bi-check-circle"></i> Lowongan Aktif</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <h5 class="fw-bold mb-0">Permohonan Dokumen Lowongan Baru</h5>
                
                <div style="max-width: 400px; width: 100%;">
                    <form class="d-flex" role="search" method="GET" action="{{ url('/validasi-magang') }}">
                        <input class="form-control me-2" type="search" name="search" placeholder="Cari mitra / lowongan..." aria-label="Search" value="{{ $searchQuery }}">
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
                            <th>Perusahaan</th>
                            <th>Posisi Lowongan</th>
                            <th>Dokumen Pendukung</th>
                            <th>Batas Pendaftaran</th>
                            <th>Status Verifikasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($daftarLowongan) > 0)
                            @foreach ($daftarLowongan as $row)
                                @php 
                                    $fileName = $row->supporting_document ? $row->supporting_document : 'MOU_Mitra_Undef.pdf';
                                    $fileLink = asset('uploads/' . $fileName);
                                @endphp
                                <tr class="border-bottom">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white p-2 rounded-3 me-3" style="width: 40px; text-align: center; font-weight: bold;">
                                                {{ strtoupper(substr($row->company_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="fw-bold d-block">{{ $row->company_name }}</span>
                                                <small class="text-muted">{{ $row->industry_field }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $row->internship_title }}</span>
                                        <small class="text-muted d-block"><i class="bi bi-geo-alt"></i> {{ $row->location }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ $fileLink }}" download class="text-decoration-none text-primary fw-semibold" title="Klik untuk mengunduh dokumen">
                                            <i class="bi bi-download me-1"></i> {{ $fileName }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($row->deadline)->locale('id')->isoFormat('D MMMM YYYY') }}
                                    </td>
                                    <td>
                                        @if (strtolower($row->approval_status) == 'pending')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded">Pending</span>
                                        @elseif (strtolower($row->approval_status) == 'approved')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded">Approved</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="button-kustom btn-sm btn-verifikasi" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalVerifikasi"
                                                    data-filename="{{ $fileName }}"
                                                    data-fileurl="{{ $fileLink }}"
                                                    data-company="{{ $row->company_name }}"
                                                    data-title="{{ $row->internship_title }}"
                                                    data-id="{{ $row->id_internship }}">
                                                Verifikasi
                                            </button>
                                            <button class="btn btn-light btn-sm btn-action text-secondary ms-1"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ditemukan dokumen lowongan kerja.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVerifikasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg" style="border-radius: 25px; border: none;">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">Detail Verifikasi Dokumen Pendukung</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="bg-light p-3 rounded-4 mb-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-1 me-3"></i>
                            <div>
                                <p class="mb-0 fw-bold" id="modalFileName">MOU_Mitra.pdf</p>
                                <small class="text-muted">Dokumen Kriteria Kerjasama Magang</small>
                            </div>
                        </div>
                        <a href="#" id="btnModalDownload" download class="btn btn-outline-primary btn-sm rounded-3">
                            <i class="bi bi-download"></i> Unduh
                        </a>
                    </div>
                    <p class="mb-1"><strong>Nama Mitra:</strong> <span id="modalCompanyName">-</span></p>
                    <p class="mb-3"><strong>Nama Program Magang:</strong> <span id="modalInternshipTitle">-</span></p>
                    <p class="mb-0">Apakah isi dokumen legal/kriteria lowongan ini sudah memenuhi aturan magang mahasiswa Universitas Sebelas Maret?</p>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 d-flex justify-content-end gap-2">
                    <a href="#" id="btnModalReject" class="btn btn-danger px-3 fw-bold" style="border-radius: 12px;">Tolak Dokumen</a>
                    <a href="#" id="btnModalApprove" class="button-kustom px-3 text-decoration-none text-center">Setujui & Terbitkan</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalVerifikasi = document.getElementById('modalVerifikasi');
        if (modalVerifikasi) {
            modalVerifikasi.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                
                const filename = button.getAttribute('data-filename');
                const fileurl = button.getAttribute('data-fileurl');
                const company = button.getAttribute('data-company');
                const title = button.getAttribute('data-title');
                const idInternship = button.getAttribute('data-id');
                
                document.getElementById('modalFileName').textContent = filename;
                document.getElementById('modalCompanyName').textContent = company;
                document.getElementById('modalInternshipTitle').textContent = title;
                
                document.getElementById('btnModalDownload').href = fileurl;
                
                // DIPERBAIKI: Mengarahkan rute tombol aksi modal sesuai dengan nama endpoint validasi
                document.getElementById('btnModalApprove').href = "{{ url('/validasi-magang/proses') }}?action=approve&id=" + idInternship;
                document.getElementById('btnModalReject').href = "{{ url('/validasi-magang/proses') }}?action=reject&id=" + idInternship;
            });
        }
    </script>
</body>
</html>