function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('mob-overlay').classList.toggle('show');
}
function closeSidebarMobile() {
    var s = document.getElementById('sidebar');
    var o = document.getElementById('mob-overlay');
    if (s) s.classList.remove('open');
    if (o) o.classList.remove('show');
}
function checkMobile() {
    var btn = document.getElementById('menu-toggle');
    if (!btn) return;
    btn.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
    if (window.innerWidth > 768) closeSidebarMobile();
}

function toggleNotif() {
    var dd = document.getElementById('notif-dd');
    if (dd) dd.classList.toggle('open');
    var ndot = document.getElementById('ndot');
    if (ndot) ndot.style.display = 'none';
}
document.addEventListener('click', function(e) {
    var trigger = document.getElementById('notif-trigger');
    var dd = document.getElementById('notif-dd');
    if (!trigger || !dd) return;
    if (!trigger.contains(e.target) && !dd.contains(e.target)) {
        dd.classList.remove('open');
    }
});

var toastTimer = null;
function showToast(msg, type) {
    var toast = document.getElementById('toast');
    var toastMsg = document.getElementById('toast-msg');
    if (!toast) return;
    toastMsg.textContent = msg || 'Berhasil!';
    toast.style.background = (type === 'error') ? '#DC2626' : '#16A34A';
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function() {
        toast.classList.remove('show');
    }, 2800);
}

function initFilterTabs() {
    var tabs = document.querySelectorAll('.nav-pill-tab');
    if (!tabs.length) return;
    tabs.forEach(function(tab) {
        tab.style.cssText = 'cursor:pointer;padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;border:1px solid var(--border);background:var(--card-bg);color:var(--muted);transition:all .2s;display:inline-block;';
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) {
                t.style.background   = 'var(--card-bg)';
                t.style.color        = 'var(--muted)';
                t.style.borderColor  = 'var(--border)';
            });
            this.style.background  = 'var(--brand)';
            this.style.color       = '#fff';
            this.style.borderColor = 'var(--brand)';
            var filter = this.dataset.filter;
            document.querySelectorAll('.lw-col').forEach(function(col) {
                col.style.display = (filter === 'all' || col.dataset.status === filter) ? '' : 'none';
            });
        });
    });
    var allTab = document.querySelector('[data-filter="all"]');
    if (allTab) {
        allTab.style.background  = 'var(--brand)';
        allTab.style.color       = '#fff';
        allTab.style.borderColor = 'var(--brand)';
    }
}

function initPelamarFilter() {
    var selPosisi = document.getElementById('filter-posisi');
    var selStatus = document.getElementById('filter-status');
    var inputCari = document.getElementById('filter-cari');
    if (!selPosisi && !selStatus && !inputCari) return;

    function doFilter() {
        var posisi = selPosisi ? selPosisi.value.toLowerCase() : '';
        var status = selStatus ? selStatus.value.toLowerCase() : '';
        var cari   = inputCari ? inputCari.value.toLowerCase() : '';
        var rows   = document.querySelectorAll('tbody tr.pelamar-row');
        var visible = 0;
        rows.forEach(function(row) {
            var rPosisi = (row.dataset.posisi || '').toLowerCase();
            var rStatus = (row.dataset.status || '').toLowerCase();
            var rNama   = (row.dataset.nama   || '').toLowerCase();
            var ok = true;
            if (posisi && posisi !== 'semua posisi' && rPosisi !== posisi) ok = false;
            if (status && status !== 'semua status' && rStatus !== status) ok = false;
            if (cari   && !rNama.includes(cari))  ok = false;
            row.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });
        var emptyRow = document.getElementById('pelamar-empty');
        if (emptyRow) emptyRow.style.display = visible === 0 ? '' : 'none';
    }

    if (selPosisi) selPosisi.addEventListener('change', doFilter);
    if (selStatus) selStatus.addEventListener('change', doFilter);
    if (inputCari) inputCari.addEventListener('input', doFilter);
}

function konfirmasiHapus(url, nama) {
    var modal = document.getElementById('modal-hapus');
    var modalNama = document.getElementById('modal-hapus-nama');
    var modalBtn  = document.getElementById('modal-hapus-btn');
    if (!modal) return;
    if (modalNama) modalNama.textContent = nama;
    if (modalBtn)  modalBtn.onclick = function() { window.location = url; };
    modal.classList.add('show');
}
function tutupModal() {
    var modal = document.getElementById('modal-hapus');
    if (modal) modal.classList.remove('show');
}
document.addEventListener('click', function(e) {
    var modal = document.getElementById('modal-hapus');
    if (modal && e.target === modal) modal.classList.remove('show');
});

function togglePw(id, btn) {
    var input = document.getElementById(id);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="bi bi-eye"></i>';
    }
}

function toggleSwitch(el) {
    el.classList.toggle('active');
}

document.addEventListener('DOMContentLoaded', function() {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    initFilterTabs();
    initPelamarFilter();
});
