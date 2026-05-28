const companyBtn = document.getElementById('companyBtn');
const adminBtn = document.getElementById('adminBtn');
const titleLogin = document.getElementById('titleLogin');
const registerText = document.getElementById('registerText');

setMode('company');

function setMode(mode) {
    if (mode === 'company') {
        companyBtn.classList.add('btn-info', 'text-white');
        companyBtn.classList.remove('btn-light');
        adminBtn.classList.add('btn-light');
        adminBtn.classList.remove('btn-info', 'text-white');
        titleLogin.textContent = 'Login Perusahaan';
        forgotPassword.style.display = 'block';
        registerText.style.display = 'block';
    } else if (mode === 'admin') {
        adminBtn.classList.add('btn-info', 'text-white');
        adminBtn.classList.remove('btn-light');
        companyBtn.classList.add('btn-light');
        companyBtn.classList.remove('btn-info', 'text-white');
        titleLogin.textContent = 'Login Admin';
        forgotPassword.style.display = 'none';
        registerText.style.display = 'none';
    }

}