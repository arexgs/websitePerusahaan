// ============ GLOBAL STATE ============
let appState = {
  company: null,
  lowongans: [],
  pelamars: [],
  selectedPelamar: null,
  documents: [],
  currentFilter: {
    lowongan: 'all',
    pelamar: '',
    posisi: '',
    status: ''
  }
};

// ============ INITIALIZATION ============
document.addEventListener('DOMContentLoaded', async function() {
  console.log('Dashboard loading...');
  
  // Load user data from localStorage
  const userData = JSON.parse(localStorage.getItem('userData') || '{}');
  
  if (!userData.email) {
    window.location.href = 'index.html';
    return;
  }

  try {
    // Fetch company data dari database
    await loadCompanyData(userData.email);
    
    // Initialize UI
    initializeUI();
    
    // Load dashboard data
    await loadDashboardData();
    
    // Show dashboard page
    showPage('dashboard', null);
  } catch (error) {
    console.error('Error initializing dashboard:', error);
    showToast('❌ Error loading dashboard', 'error');
  }

  // Setup event listeners
  setupEventListeners();
});

// ============ LOAD DATA ============
async function loadCompanyData(email) {
  try {
    const response = await fetch('get-company.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email })
    });

    const data = await response.json();
    
    if (data.success && data.data) {
      appState.company = data.data;
      console.log('Company data loaded:', appState.company);
      updateCompanyUI();
    } else {
      throw new Error('Failed to load company data');
    }
  } catch (error) {
    console.error('Error loading company data:', error);
    // Fallback: ambil dari localStorage yang sudah disimpan saat login
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    appState.company = {
      id: userData.id || 1,
      name: userData.name || 'Perusahaan',
      email: email,
      industry: '',
      website: '',
      description: '',
      phone: '',
      address: '',
      logo_url: ''
    };
    updateCompanyUI();
  }
}

async function loadDashboardData() {
  try {
    if (!appState.company) return;

    // Load lowongans
    const lwResponse = await fetch('get-lowongans.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ company_id: appState.company.id })
    });
    
    const lwData = await lwResponse.json();
    if (lwData.success) {
      appState.lowongans = lwData.data || [];
    }

    // Load pelamars
    const pResponse = await fetch('get-pelamars.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ company_id: appState.company.id })
    });
    
    const pData = await pResponse.json();
    if (pData.success) {
      appState.pelamars = pData.data || [];
    }

    // Load documents
    const docResponse = await fetch('get-documents.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ company_id: appState.company.id })
    });
    
    const docData = await docResponse.json();
    if (docData.success) {
      appState.documents = docData.data || [];
    }

    // Update UI
    updateDashboardUI();
  } catch (error) {
    console.error('Error loading dashboard data:', error);
  }
}

// ============ UPDATE UI ============
function updateCompanyUI() {
  // Ambil nama dari appState.company (prioritas dari database)
  // Fallback ke localStorage jika database kosong
  const companyName = appState.company?.name || 
    (localStorage.getItem('userData') && JSON.parse(localStorage.getItem('userData')).name) || 
    'Perusahaan';
  
  console.log('Updating UI with company name:', companyName);
  
  // Update greeting dengan nama perusahaan yang BENAR
  const greetEl = document.getElementById('greetName');
  if (greetEl) {
    greetEl.textContent = companyName;
  }
  
  // Update avatar
  const initials = companyName
    .split(' ')
    .map(w => w[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
  
  const avatarEl = document.getElementById('topbarAvatar');
  if (avatarEl) {
    avatarEl.textContent = initials;
  }

  // Update profile pages
  const profAvatarEl = document.getElementById('prof-avatar');
  if (profAvatarEl) profAvatarEl.textContent = initials;
  
  const profNameEl = document.getElementById('prof-name');
  if (profNameEl) profNameEl.textContent = companyName;
  
  const profJoinedEl = document.getElementById('prof-joined');
  if (profJoinedEl) profJoinedEl.textContent = `Bergabung sejak ${formatDate(appState.company?.created_at || new Date())}`;
  
  // Update profile form fields
  const profCompanyNameEl = document.getElementById('prof-company-name');
  if (profCompanyNameEl) profCompanyNameEl.value = appState.company?.name || '';
  
  const profIndustryEl = document.getElementById('prof-industry');
  if (profIndustryEl) profIndustryEl.value = appState.company?.industry || '';
  
  const profWebsiteEl = document.getElementById('prof-website');
  if (profWebsiteEl) profWebsiteEl.value = appState.company?.website || '';
  
  const profAddressEl = document.getElementById('prof-address');
  if (profAddressEl) profAddressEl.value = appState.company?.address || '';
  
  const profPhoneEl = document.getElementById('prof-phone');
  if (profPhoneEl) profPhoneEl.value = appState.company?.phone || '';
  
  const profDescriptionEl = document.getElementById('prof-description');
  if (profDescriptionEl) profDescriptionEl.value = appState.company?.description || '';
  
  // Update settings
  const setUsernameEl = document.getElementById('set-username');
  if (setUsernameEl) setUsernameEl.value = appState.company?.name || '';
  
  const setEmailEl = document.getElementById('set-email');
  if (setEmailEl) setEmailEl.value = appState.company?.email || '';
  
  const setPhoneEl = document.getElementById('set-phone');
  if (setPhoneEl) setPhoneEl.value = appState.company?.phone || '';

  // Update localStorage dengan data terbaru
  const userData = JSON.parse(localStorage.getItem('userData') || '{}');
  userData.name = companyName;
  userData.id = appState.company?.id;
  localStorage.setItem('userData', JSON.stringify(userData));
}

function updateDashboardUI() {
  updateStats();
  updateLowonganList();
  updatePelamarList();
  updateActivityTimeline();
  updateDocuments();
}

function updateStats() {
  const activeJobs = appState.lowongans.filter(l => l.status === 'active').length;
  const totalPelamars = appState.pelamars.length;
  const pendingJobs = appState.lowongans.filter(l => l.status === 'pending').length;
  const acceptedPelamars = appState.pelamars.filter(p => p.status === 'accepted').length;

  const sv1 = document.getElementById('sv1');
  if (sv1) sv1.textContent = activeJobs;
  
  const sv2 = document.getElementById('sv2');
  if (sv2) sv2.textContent = totalPelamars;
  
  const sv3 = document.getElementById('sv3');
  if (sv3) sv3.textContent = pendingJobs;
  
  const sv4 = document.getElementById('sv4');
  if (sv4) sv4.textContent = acceptedPelamars;

  // Update badges
  const lowonganBadge = document.getElementById('lowonganBadge');
  if (lowonganBadge) lowonganBadge.textContent = appState.lowongans.length;
  
  const pelamarBadge = document.getElementById('pelamarBadge');
  if (pelamarBadge) pelamarBadge.textContent = appState.pelamars.length;

  // Update counts
  const countAll = document.getElementById('count-all');
  if (countAll) countAll.textContent = appState.lowongans.length;
  
  const countAktif = document.getElementById('count-aktif');
  if (countAktif) countAktif.textContent = activeJobs;
  
  const countPending = document.getElementById('count-pending');
  if (countPending) countPending.textContent = pendingJobs;
  
  const countDitutup = document.getElementById('count-ditutup');
  if (countDitutup) countDitutup.textContent = appState.lowongans.filter(l => l.status === 'closed').length;

  // Update pelamar subtitle
  const totalPelamarEl = document.getElementById('total-pelamar');
  if (totalPelamarEl) totalPelamarEl.textContent = totalPelamars;

  // Update profile stats
  const profTotalJobs = document.getElementById('prof-total-jobs');
  if (profTotalJobs) profTotalJobs.textContent = appState.lowongans.length;

  // Show/hide empty state
  const emptyStateDashboard = document.getElementById('empty-state-dashboard');
  const statContainer = document.getElementById('stat-container');
  
  if (appState.lowongans.length === 0) {
    if (emptyStateDashboard) emptyStateDashboard.style.display = 'block';
    if (statContainer) statContainer.style.display = 'none';
  } else {
    if (emptyStateDashboard) emptyStateDashboard.style.display = 'none';
    if (statContainer) statContainer.style.display = 'grid';
  }

  // Pending banner
  const pendingBanner = document.getElementById('pending-banner');
  const pendingCount = document.getElementById('pending-count');
  
  if (pendingJobs > 0) {
    if (pendingBanner) pendingBanner.style.display = 'flex';
    if (pendingCount) pendingCount.textContent = pendingJobs;
  } else {
    if (pendingBanner) pendingBanner.style.display = 'none';
  }
}

function updateLowonganList() {
  const grid = document.getElementById('lw-grid');
  if (!grid) return;
  
  grid.innerHTML = '';

  if (appState.lowongans.length === 0) {
    const emptyLowongan = document.getElementById('empty-lowongan');
    if (emptyLowongan) emptyLowongan.style.display = 'block';
    return;
  }

  const emptyLowongan = document.getElementById('empty-lowongan');
  if (emptyLowongan) emptyLowongan.style.display = 'none';

  appState.lowongans.forEach(lw => {
    const statusClass = `${lw.status}-card`;
    const statusText = lw.status === 'active' ? 'Aktif' : lw.status === 'pending' ? 'Menunggu Persetujuan' : 'Ditutup';
    const statusColor = lw.status === 'active' ? 'pill-green' : lw.status === 'pending' ? 'pill-yellow' : 'pill-red';
    const statusIcon = lw.status === 'active' ? 'bi-circle-fill' : lw.status === 'pending' ? 'bi-hourglass-split' : 'bi-x-circle-fill';

    const html = `
      <div class="col-sm-6 col-xl-4 lw-col" data-status="${lw.status}">
        <div class="lw-card ${lw.status === 'pending' ? 'pending-card' : ''}" ${lw.status !== 'pending' ? `onclick="showPage('pelamar',null)"` : ''}>
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="lw-icon" style="background:${lw.icon_bg || '#DBEAFE'}">
              <i class="bi ${lw.icon || 'bi-briefcase'}" style="color:${lw.icon_color || '#1D4ED8'}"></i>
            </div>
            <div>
              <div style="font-size:13px;font-weight:600">${lw.title}</div>
              <div style="font-size:11px;color:var(--muted)">Dibuat ${formatDateAgo(lw.created_at)}</div>
            </div>
          </div>
          <div class="mb-2">
            <span class="float-label"><i class="bi bi-geo-alt" style="font-size:9px"></i> ${lw.location}</span>
            <span class="float-label">${lw.position_type}</span>
            <span class="float-label">${lw.salary_range || 'N/A'}</span>
          </div>
          <div class="clearfix mb-3">
            <span class="pill ${statusColor}">
              <i class="bi ${statusIcon}" style="font-size:${lw.status === 'pending' ? '9px' : '7px'}"></i>
              ${statusText}
            </span>
          </div>
          ${lw.status === 'pending' ? `
            <div style="font-size:11px;color:var(--muted);margin-bottom:10px">
              <i class="bi bi-info-circle"></i> Belum bisa dilihat mahasiswa
            </div>
          ` : ''}
          <div class="lw-footer">
            <span style="font-size:12px;color:var(--muted)"><i class="bi bi-people"></i> ${lw.applicants_count || 0} pelamar</span>
            <button class="btn-outline btn-sm" onclick="event.stopPropagation();showPage('pelamar',null)">
              Lihat <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    `;
    
    grid.innerHTML += html;
  });
}

function updatePelamarList() {
  const tbody = document.getElementById('pelamar-tbody');
  if (!tbody) return;
  
  tbody.innerHTML = '';

  if (appState.pelamars.length === 0) {
    const emptyPelamar = document.getElementById('empty-pelamar');
    const pelamarTableContainer = document.getElementById('pelamar-table-container');
    if (emptyPelamar) emptyPelamar.style.display = 'block';
    if (pelamarTableContainer) pelamarTableContainer.style.display = 'none';
    return;
  }

  const emptyPelamar = document.getElementById('empty-pelamar');
  const pelamarTableContainer = document.getElementById('pelamar-table-container');
  if (emptyPelamar) emptyPelamar.style.display = 'none';
  if (pelamarTableContainer) pelamarTableContainer.style.display = 'block';

  // Update posisi filter options
  const posisiSelect = document.getElementById('filter-posisi');
  if (posisiSelect) {
    const posisiOptions = new Set(appState.pelamars.map(p => p.job_title));
    posisiSelect.innerHTML = '<option value="">Semua Posisi</option>';
    posisiOptions.forEach(pos => {
      posisiSelect.innerHTML += `<option value="${pos}">${pos}</option>`;
    });
  }

  appState.pelamars.forEach(p => {
    const statusClass = `pill-${getStatusPillColor(p.status)}`;
    const statusIcon = getStatusIcon(p.status);
    const statusText = getStatusText(p.status);

    const row = `
      <tr onclick="goDetail('${p.id}')">
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="mini-avatar" style="background:${p.avatar_bg || '#DBEAFE'};color:${p.avatar_color || '#1D4ED8'}">
              ${p.name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()}
            </div>
            <span style="font-weight:500">${p.name}</span>
          </div>
        </td>
        <td><span class="pill ${statusClass}" style="font-size:11px">${p.job_title}</span></td>
        <td>${p.major || '-'}</td>
        <td><code style="font-family:'DM Mono',monospace">${p.ipk || '-'}</code></td>
        <td>${formatDate(p.applied_at)}</td>
        <td><span class="pill ${statusClass}"><i class="bi ${statusIcon}" style="font-size:9px"></i> ${statusText}</span></td>
        <td>
          <button class="btn-${p.status === 'pending' ? 'brand' : 'outline'} btn-sm" onclick="event.stopPropagation();goDetail('${p.id}')">
            <i class="bi bi-eye"></i> ${p.status === 'pending' ? 'Review' : 'Detail'}
          </button>
        </td>
      </tr>
    `;
    
    tbody.innerHTML += row;
  });
}

function updateActivityTimeline() {
  const timeline = document.getElementById('activity-timeline');
  if (!timeline) return;
  
  if (appState.pelamars.length === 0) {
    timeline.innerHTML = `
      <div style="text-align:center;color:var(--muted);font-size:12px;padding:30px">
        Belum ada aktivitas
      </div>
    `;
    return;
  }

  // Get recent activities
  const activities = appState.pelamars
    .sort((a, b) => new Date(b.applied_at) - new Date(a.applied_at))
    .slice(0, 4);

  let html = '';
  activities.forEach((activity, index) => {
    const isLast = index === activities.length - 1;
    const colors = ['var(--brand)', '#16A34A', '#D97706', '#6366F1'];
    
    html += `
      <div class="tl-item">
        <div class="tl-left">
          <div class="tl-dot" style="background:${colors[index % 4]}"></div>
          ${!isLast ? '<div class="tl-line"></div>' : ''}
        </div>
        <div class="tl-body">
          <div class="tl-title"><strong>${activity.name}</strong> melamar <strong>${activity.job_title}</strong></div>
          <div class="tl-time">${formatDateAgo(activity.applied_at)}</div>
        </div>
      </div>
    `;
  });

  timeline.innerHTML = html;
}

function updateDocuments() {
  const docList = document.getElementById('company-documents');
  if (!docList) return;
  
  if (appState.documents.length === 0) {
    docList.innerHTML = `
      <div class="empty-state" style="border:none;background:none;padding:40px 30px">
        <i class="bi bi-file-earmark"></i>
        <h5>Belum ada dokumen</h5>
        <p>Upload dokumen MoU, Proposal, atau Surat Kerjasama</p>
      </div>
    `;
    return;
  }

  let html = '';
  appState.documents.forEach(doc => {
    html += `
      <div class="doc-row" style="padding:14px 0">
        <div class="d-flex align-items-center gap-2" style="flex:1">
          <i class="bi bi-file-pdf" style="color:#DC2626;font-size:18px"></i>
          <div>
            <div style="font-size:13px;font-weight:600">${doc.document_name}</div>
            <div style="font-size:11px;color:var(--muted)">
              ${getDocumentTypeLabel(doc.document_type)} • ${formatFileSize(doc.file_size)}
            </div>
          </div>
        </div>
        <div class="d-flex gap-2">
          <button class="btn-outline btn-sm" onclick="downloadDocument('${doc.id}')">
            <i class="bi bi-download"></i>
          </button>
          <button class="btn-outline btn-sm" style="color:var(--danger);border-color:var(--danger)" onclick="deleteDocument('${doc.id}')">
            <i class="bi bi-trash3"></i>
          </button>
        </div>
      </div>
      <div style="border-bottom:1px solid var(--border)"></div>
    `;
  });

  docList.innerHTML = html;
}

// ============ PAGE NAVIGATION ============
function showPage(id, el) {
  // Hide all pages
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  
  // Show selected page
  const page = document.getElementById('page-' + id);
  if (page) {
    page.classList.add('active');
  }

  // Update sidebar active state
  document.querySelectorAll('.nav-link-item').forEach(l => l.classList.remove('active'));
  if (el) {
    el.classList.add('active');
  } else {
    document.querySelectorAll('.nav-link-item').forEach(l => {
      if (l.getAttribute('onclick')?.includes("'" + id + "'")) {
        l.classList.add('active');
      }
    });
  }

  // Scroll to top
  const content = document.getElementById('content');
  if (content) content.scrollTop = 0;
  window.scrollTo(0, 0);

  // Close mobile sidebar
  closeSidebar();
}

function goDetail(pelamarId) {
  const pelamar = appState.pelamars.find(p => p.id == pelamarId);
  if (!pelamar) return;

  appState.selectedPelamar = pelamar;

  // Update detail page
  const initials = pelamar.name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
  
  const detAvatar = document.getElementById('det-avatar');
  if (detAvatar) {
    detAvatar.textContent = initials;
    detAvatar.style.background = pelamar.avatar_bg || '#DBEAFE';
  }
  
  const detName = document.getElementById('det-name');
  if (detName) detName.textContent = pelamar.name;
  
  const detPosisi = document.getElementById('det-posisi');
  if (detPosisi) detPosisi.textContent = pelamar.job_title;
  
  const detDate = document.getElementById('det-date');
  if (detDate) detDate.textContent = formatDate(pelamar.applied_at);
  
  const detJurusan = document.getElementById('det-jurusan');
  if (detJurusan) detJurusan.textContent = pelamar.major || '-';
  
  const detIpk = document.getElementById('det-ipk');
  if (detIpk) detIpk.textContent = pelamar.ipk || '-';
  
  const detEmail = document.getElementById('det-email');
  if (detEmail) detEmail.textContent = pelamar.email;
  
  const detPhone = document.getElementById('det-phone');
  if (detPhone) detPhone.textContent = pelamar.phone || '-';
  
  const detAngkatan = document.getElementById('det-angkatan');
  if (detAngkatan) detAngkatan.textContent = pelamar.year || '-';
  
  const detSurat = document.getElementById('det-surat');
  if (detSurat) detSurat.textContent = pelamar.cover_letter || 'Tidak ada surat lamaran';

  // Update status
  const statusIcon = getStatusIcon(pelamar.status);
  const statusText = getStatusText(pelamar.status);
  const statusColor = getStatusColor(pelamar.status);
  const detStatusWrap = document.getElementById('det-status-wrap');
  if (detStatusWrap) {
    detStatusWrap.innerHTML = `
      <span class="pill" style="background:${statusColor};color:#fff">
        <i class="bi ${statusIcon}"></i> ${statusText}
      </span>
    `;
  }

  // Update skills
  const skills = pelamar.skills?.split(',') || [];
  const detSkills = document.getElementById('det-skills');
  if (detSkills) {
    detSkills.innerHTML = skills
      .filter(s => s.trim())
      .map(s => `<span class="skill-tag">${s.trim()}</span>`)
      .join('') || '-';
  }

  // Update documents
  const documents = pelamar.documents?.split(',') || [];
  const detDocuments = document.getElementById('det-documents');
  if (detDocuments) {
    detDocuments.innerHTML = documents
      .filter(d => d.trim())
      .map(d => `
        <div class="doc-row">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-pdf" style="color:#DC2626;font-size:18px"></i>
            <span style="font-size:13px">${d.trim()}</span>
          </div>
          <button class="btn-outline btn-sm"><i class="bi bi-download"></i></button>
        </div>
      `)
      .join('') || '-';
  }

  showPage('detail', null);
  
  // Update sidebar
  document.querySelectorAll('.nav-link-item').forEach(l => {
    if (l.getAttribute('onclick')?.includes("'pelamar'")) {
      l.classList.add('active');
    }
  });
}

// ============ FILTER & SEARCH ============
function filterLw(filter, el) {
  document.querySelectorAll('.nav-pill-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  appState.currentFilter.lowongan = filter;
  
  document.querySelectorAll('.lw-col').forEach(col => {
    if (filter === 'all' || col.dataset.status === filter) {
      col.style.display = '';
    } else {
      col.style.display = 'none';
    }
  });
}

function setupEventListeners() {
  // Pelamar filters
  const filterPosisi = document.getElementById('filter-posisi');
  const filterStatus = document.getElementById('filter-status');
  const searchPelamar = document.getElementById('search-pelamar');

  if (filterPosisi) {
    filterPosisi.addEventListener('change', filterPelamars);
  }
  if (filterStatus) {
    filterStatus.addEventListener('change', filterPelamars);
  }
  if (searchPelamar) {
    searchPelamar.addEventListener('input', filterPelamars);
  }
}

function filterPelamars() {
  const posisi = document.getElementById('filter-posisi').value;
  const status = document.getElementById('filter-status').value;
  const search = document.getElementById('search-pelamar').value.toLowerCase();

  const tbody = document.getElementById('pelamar-tbody');
  if (!tbody) return;
  
  const rows = tbody.querySelectorAll('tr');

  rows.forEach(row => {
    let show = true;

    if (posisi && !row.textContent.includes(posisi)) {
      show = false;
    }

    if (status) {
      const rowStatus = row.querySelector('.pill').textContent.toLowerCase();
      const statusMap = {
        'pending': 'menunggu',
        'review': 'diproses',
        'accepted': 'diterima',
        'rejected': 'ditolak'
      };
      if (!rowStatus.includes(statusMap[status])) {
        show = false;
      }
    }

    if (search && !row.textContent.toLowerCase().includes(search)) {
      show = false;
    }

    row.style.display = show ? '' : 'none';
  });
}

// ============ FORM SUBMISSION ============
async function handleSubmitLowongan() {
  const form = document.getElementById('form-lowongan');
  const formData = new FormData(form);

  const data = {
    company_id: appState.company.id,
    title: formData.get('title'),
    department: formData.get('department'),
    position_type: formData.get('position_type'),
    location: formData.get('location'),
    salary_range: formData.get('salary_range'),
    education: formData.get('education'),
    major: formData.get('major'),
    description: formData.get('description'),
    requirements: formData.get('requirements'),
    deadline: formData.get('deadline'),
    quota: formData.get('quota'),
    min_ipk: formData.get('min_ipk')
  };

  if (!data.title || !data.description || !data.requirements) {
    showToast('❌ Mohon isi semua field yang diperlukan', 'error');
    return;
  }

  try {
    const response = await fetch('create-lowongan.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (result.success) {
      showToast('✓ Lowongan berhasil dibuat dan masuk antrian review admin!', 'success');
      form.reset();
      await loadDashboardData();
      setTimeout(() => showPage('lowongan', null), 800);
    } else {
      showToast(`❌ ${result.message}`, 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Gagal membuat lowongan', 'error');
  }
}

async function handleSaveProfile() {
  const data = {
    company_id: appState.company.id,
    name: document.getElementById('prof-company-name').value,
    industry: document.getElementById('prof-industry').value,
    website: document.getElementById('prof-website').value,
    address: document.getElementById('prof-address').value,
    phone: document.getElementById('prof-phone').value,
    description: document.getElementById('prof-description').value
  };

  if (!data.name) {
    showToast('❌ Nama perusahaan tidak boleh kosong', 'error');
    return;
  }

  try {
    const response = await fetch('update-profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (result.success) {
      appState.company = { ...appState.company, ...data };
      updateCompanyUI();
      showToast('✓ Profil berhasil disimpan!', 'success');
    } else {
      showToast(`❌ ${result.message}`, 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Gagal menyimpan profil', 'error');
  }
}

async function handleSaveSettings() {
  const currentPass = document.getElementById('set-current-pass').value;
  const newPass = document.getElementById('set-new-pass').value;
  const confirmPass = document.getElementById('set-confirm-pass').value;

  const data = {
    company_id: appState.company.id,
    username: document.getElementById('set-username').value,
    email: document.getElementById('set-email').value,
    phone: document.getElementById('set-phone').value
  };

  // Validate password jika ada perubahan password
  if (newPass || confirmPass) {
    if (!currentPass) {
      showToast('❌ Masukkan password saat ini', 'error');
      return;
    }
    if (newPass.length < 6) {
      showToast('❌ Password baru minimal 6 karakter', 'error');
      return;
    }
    if (newPass !== confirmPass) {
      showToast('❌ Password tidak cocok', 'error');
      return;
    }
    data.current_password = currentPass;
    data.new_password = newPass;
  }

  try {
    const response = await fetch('update-settings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (result.success) {
      showToast('✓ Pengaturan berhasil disimpan!', 'success');
      // Clear password fields
      document.getElementById('set-current-pass').value = '';
      document.getElementById('set-new-pass').value = '';
      document.getElementById('set-confirm-pass').value = '';
    } else {
      showToast(`❌ ${result.message}`, 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Gagal menyimpan pengaturan', 'error');
  }
}

async function updatePelamarStatus(status) {
  if (!appState.selectedPelamar) return;

  try {
    const response = await fetch('update-pelamar-status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        pelamar_id: appState.selectedPelamar.id,
        status: status
      })
    });

    const result = await response.json();

    if (result.success) {
      const statusText = status === 'accepted' ? 'diterima' : 'ditolak';
      showToast(`✓ Pelamar berhasil ${statusText}!`, 'success');
      await loadDashboardData();
      showPage('pelamar', null);
    } else {
      showToast(`❌ ${result.message}`, 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Gagal update status pelamar', 'error');
  }
}

// ============ DOCUMENT MANAGEMENT ============
function openDocumentModal() {
  const modal = new bootstrap.Modal(document.getElementById('documentModal'));
  modal.show();
}

function handleDocumentUpload(event) {
  const files = event.target.files;
  const docList = document.getElementById('doc-list');

  if (files.length === 0) {
    if (docList) docList.innerHTML = '';
    return;
  }

  if (docList) {
    docList.innerHTML = '';
    Array.from(files).forEach(file => {
      docList.innerHTML += `
        <div style="padding:10px;background:var(--surface2);border-radius:var(--radius-sm);margin-bottom:8px;display:flex;justify-content:space-between;align-items:center">
          <div style="display:flex;align-items:center;gap:10px;font-size:12px">
            <i class="bi bi-file-pdf" style="color:#DC2626;font-size:18px"></i>
            <div>
              <div style="font-weight:600">${file.name}</div>
              <div style="color:var(--muted)">${formatFileSize(file.size)}</div>
            </div>
          </div>
          <button type="button" class="btn-outline btn-sm" onclick="this.parentElement.remove()">
            <i class="bi bi-x"></i>
          </button>
        </div>
      `;
    });
  }
}

function handleModalDocumentUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  const uploadStatus = document.getElementById('upload-status');
  if (uploadStatus) {
    uploadStatus.textContent = `✓ ${file.name} (${formatFileSize(file.size)})`;
  }
}

async function submitDocument() {
  const type = document.getElementById('doc-type').value;
  const fileInput = document.getElementById('modal-doc-input');
  const file = fileInput.files[0];

  if (!type || !file) {
    showToast('❌ Pilih jenis dokumen dan file', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('company_id', appState.company.id);
  formData.append('document_type', type);
  formData.append('document_name', file.name);
  formData.append('file', file);

  try {
    const response = await fetch('upload-document.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      showToast('✓ Dokumen berhasil diupload!', 'success');
      await loadDashboardData();
      
      // Close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('documentModal'));
      if (modal) modal.hide();
      
      // Reset form
      document.getElementById('doc-type').value = '';
      document.getElementById('modal-doc-input').value = '';
      const uploadStatus = document.getElementById('upload-status');
      if (uploadStatus) uploadStatus.textContent = 'Klik atau drag file ke sini';
    } else {
      showToast(`❌ ${result.message}`, 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Gagal mengupload dokumen', 'error');
  }
}

async function deleteDocument(docId) {
  if (!confirm('Hapus dokumen ini?')) return;

  try {
    const response = await fetch('delete-document.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ document_id: docId })
    });

    const result = await response.json();

    if (result.success) {
      showToast('✓ Dokumen berhasil dihapus!', 'success');
      await loadDashboardData();
    } else {
      showToast(`❌ ${result.message}`, 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Gagal menghapus dokumen', 'error');
  }
}

function downloadDocument(docId) {
  window.location.href = `download-document.php?id=${docId}`;
}

// ============ SIDEBAR & MOBILE ============
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const mobOverlay = document.getElementById('mob-overlay');
  if (sidebar) sidebar.classList.toggle('open');
  if (mobOverlay) mobOverlay.classList.toggle('show');
}

function closeSidebar() {
  const sidebar = document.getElementById('sidebar');
  const mobOverlay = document.getElementById('mob-overlay');
  if (sidebar) sidebar.classList.remove('open');
  if (mobOverlay) mobOverlay.classList.remove('show');
}

function toggleNotif() {
  const notifDd = document.getElementById('notif-dd');
  if (notifDd) notifDd.classList.toggle('open');
}

document.addEventListener('click', e => {
  if (!e.target.closest('#notif-trigger') && !e.target.closest('#notif-dd')) {
    const notifDd = document.getElementById('notif-dd');
    if (notifDd) notifDd.classList.remove('open');
  }
});

// ============ TAB NAVIGATION ============

// Style untuk active tab
const tabStyles = `
.prof-tab.active, .set-tab.active {
  color: var(--brand) !important;
  border-bottom-color: var(--brand) !important;
  font-weight: 600 !important;
}

.prof-tab, .set-tab {
  cursor: pointer;
  transition: all .15s;
}

.prof-tab:hover, .set-tab:hover {
  color: var(--text) !important;
}
`;

// Inject styles
const styleTag = document.createElement('style');
styleTag.textContent = tabStyles;
document.head.appendChild(styleTag);

// Switch Profil Tabs
function switchProfTab(tabName, element) {
  // Hide all tabs
  document.querySelectorAll('.prof-tab-content').forEach(el => {
    el.style.display = 'none';
  });
  
  // Remove active class from all tabs
  document.querySelectorAll('.prof-tab').forEach(el => {
    el.classList.remove('active');
  });
  
  // Show selected tab
  const tabEl = document.getElementById('tab-' + tabName);
  if (tabEl) {
    tabEl.style.display = 'block';
  }
  
  // Add active class to current tab
  element.classList.add('active');
  
  // Initialize map if lokasi tab
  if (tabName === 'lokasi') {
    setTimeout(() => {
      const mapEl = document.getElementById('company-map');
      if (mapEl && !mapEl._leaflet_id) {
        initializeMap();
      }
    }, 100);
  }
}

// Switch Settings Tabs
function switchSetTab(tabName, element) {
  // Hide all tabs
  document.querySelectorAll('.set-tab-content').forEach(el => {
    el.style.display = 'none';
  });
  
  // Remove active class from all tabs
  document.querySelectorAll('.set-tab').forEach(el => {
    el.classList.remove('active');
  });
  
  // Show selected tab
  const tabEl = document.getElementById('tab-' + tabName);
  if (tabEl) {
    tabEl.style.display = 'block';
  }
  
  // Add active class to current tab
  element.classList.add('active');
}

// ============ MAP INITIALIZATION ============
function initializeMap() {
  const mapEl = document.getElementById('company-map');
  if (!mapEl || mapEl._leaflet_id) {
    return; // Map already initialized
  }

  // Default ke Jakarta
  const lat = appState.company?.latitude || -6.2088;
  const lng = appState.company?.longitude || 106.8456;

  const map = L.map('company-map').setView([lat, lng], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  let marker = L.marker([lat, lng]).addTo(map);
  marker.bindPopup(`<b>${appState.company?.name}</b><br>${appState.company?.address || 'Alamat tidak tersedia'}`).openPopup();

  // Tambahkan kemampuan click to set location
  map.on('click', (e) => {
    const { lat: newLat, lng: newLng } = e.latlng;
    marker.setLatLng([newLat, newLng]);
    // Save to appState (bisa disimpan ke DB nanti)
    appState.company.latitude = newLat;
    appState.company.longitude = newLng;
  });
}

// ============ PASSWORD VISIBILITY TOGGLE ============
function togglePasswordVisibility(fieldId) {
  const field = document.getElementById(fieldId);
  if (!field) return;
  
  if (field.type === 'password') {
    field.type = 'text';
  } else {
    field.type = 'password';
  }
}

// ============ TOGGLE SWITCH ============
function toggleSwitch(el) {
  el.classList.toggle('active');
}

// ============ UTILITY FUNCTIONS ============
function showToast(msg, type = 'success') {
  const toast = document.getElementById('toast');
  const msgEl = document.getElementById('toast-msg');
  
  if (toast && msgEl) {
    msgEl.textContent = msg;
    toast.classList.add('show');
    
    setTimeout(() => toast.classList.remove('show'), 3000);
  }
}

function formatDate(date) {
  if (!date) return '-';
  const d = new Date(date);
  return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateAgo(date) {
  if (!date) return '-';
  const d = new Date(date);
  const now = new Date();
  const diff = Math.floor((now - d) / 1000);

  if (diff < 60) return 'Baru saja';
  if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
  if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
  if (diff < 604800) return Math.floor(diff / 86400) + ' hari lalu';
  
  return formatDate(date);
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function getStatusText(status) {
  const map = {
    'pending': 'Menunggu',
    'review': 'Diproses',
    'accepted': 'Diterima',
    'rejected': 'Ditolak'
  };
  return map[status] || status;
}

function getStatusIcon(status) {
  const map = {
    'pending': 'bi-hourglass-split',
    'review': 'bi-arrow-repeat',
    'accepted': 'bi-check-circle',
    'rejected': 'bi-x-circle'
  };
  return map[status] || 'bi-circle';
}

function getStatusColor(status) {
  const map = {
    'pending': 'rgba(249, 115, 22, 0.2)',
    'review': 'rgba(59, 130, 246, 0.2)',
    'accepted': 'rgba(34, 197, 94, 0.2)',
    'rejected': 'rgba(239, 68, 68, 0.2)'
  };
  return map[status] || 'rgba(100, 116, 139, 0.2)';
}

function getStatusPillColor(status) {
  const map = {
    'pending': 'yellow',
    'review': 'purple',
    'accepted': 'green',
    'rejected': 'red'
  };
  return map[status] || 'blue';
}

function getDocumentTypeLabel(type) {
  const map = {
    'mou': 'MoU',
    'proposal': 'Proposal Kerja Sama',
    'agreement': 'Surat Kerjasama',
    'other': 'Dokumen Lainnya'
  };
  return map[type] || type;
}

function initializeUI() {
  // Setup mobile menu toggle
  const menuToggle = document.getElementById('menu-toggle');
  if (menuToggle) {
    if (window.innerWidth < 769) {
      menuToggle.style.display = 'grid';
    }

    window.addEventListener('resize', () => {
      if (window.innerWidth < 769) {
        menuToggle.style.display = 'grid';
      } else {
        menuToggle.style.display = 'none';
        closeSidebar();
      }
    });
  }
}

function doLogout() {
  if (confirm('Anda yakin ingin keluar?')) {
    localStorage.removeItem('userData');
    window.location.href = 'index.html';
  }
}
