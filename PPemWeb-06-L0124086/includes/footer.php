</main>

<div id="toast">
    <i class="bi bi-check-circle-fill"></i>
    <span id="toast-msg">Berhasil!</span>
</div>

<div class="modal-overlay" id="modal-hapus">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-trash3-fill"></i></div>
        <div class="modal-title">Hapus Data?</div>
        <div class="modal-desc">
            Kamu akan menghapus <span class="modal-nama" id="modal-hapus-nama"></span>.<br>
            Tindakan ini <strong>tidak dapat dibatalkan</strong>.
        </div>
        <div class="modal-actions">
            <button class="btn-batal-modal" onclick="tutupModal()">Batal</button>
            <button class="btn-hapus-confirm" id="modal-hapus-btn">Ya, Hapus</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/script.js"></script>
</body>
</html>
