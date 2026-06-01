// ============ LOGIN PAGE ============
const companyBtn = document.getElementById('companyBtn');
const adminBtn = document.getElementById('adminBtn');
const titleLogin = document.getElementById('titleLogin');
const registerText = document.getElementById('registerText');
const loginBtn = document.getElementById('loginBtn');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');

let currentMode = 'company';

// Initialize login page if elements exist
if (companyBtn && adminBtn) {
    setMode('company');
    loginBtn.addEventListener('click', handleLogin);
    [emailInput, passwordInput].forEach(input => {
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    loginBtn.click();
                }
            });
        }
    });
}

function setMode(mode) {
    currentMode = mode;
    if (mode === 'company') {
        companyBtn.classList.add('btn-info', 'text-white');
        companyBtn.classList.remove('btn-light');
        adminBtn.classList.add('btn-light');
        adminBtn.classList.remove('btn-info', 'text-white');
        titleLogin.textContent = 'Login Perusahaan';
        document.getElementById('forgotPassword').style.display = 'block';
        registerText.style.display = 'block';
    } else if (mode === 'admin') {
        adminBtn.classList.add('btn-info', 'text-white');
        adminBtn.classList.remove('btn-light');
        companyBtn.classList.add('btn-light');
        companyBtn.classList.remove('btn-info', 'text-white');
        titleLogin.textContent = 'Login Admin';
        document.getElementById('forgotPassword').style.display = 'none';
        registerText.style.display = 'none';
    }
}

// Toggle password visibility
function togglePasswordVisibility(fieldId = 'password') {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId === 'password' ? 'passwordToggleIcon' : 'confirmPasswordToggleIcon');
    
    if (field) {
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
}

// Show notification
function showNotification(message, type = 'success', containerId = 'alertContainer') {
    const alertContainer = document.getElementById(containerId);
    if (!alertContainer) return;

    const alertDiv = document.createElement('div');
    const icon = type === 'success' ? '✓' : type === 'warning' ? '⚠' : '✗';
    const title = type === 'success' ? 'Berhasil' : type === 'warning' ? 'Perhatian' : 'Error';
    
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <strong>${icon} ${title}!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    alertContainer.innerHTML = '';
    alertContainer.appendChild(alertDiv);

    // Auto close after 5 seconds
    setTimeout(() => {
        alertDiv.style.transition = 'opacity 0.3s';
        alertDiv.style.opacity = '0';
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.remove();
            }
        }, 300);
    }, 5000);
}

// Validate login form
function validateLoginForm() {
    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();
    const errors = [];

    if (!email) {
        errors.push('Email tidak boleh kosong');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push('Format email tidak valid');
    }

    if (!password) {
        errors.push('Password tidak boleh kosong');
    } else if (password.length < 6) {
        errors.push('Password minimal 6 karakter');
    }

    return errors;
}

// Handle login
async function handleLogin() {
    const errors = validateLoginForm();

    if (errors.length > 0) {
        showNotification(errors.join(' | '), 'danger');
        return;
    }

    // Loading state
    const originalText = loginBtn.textContent;
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

    try {
        const response = await fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: emailInput.value.trim(),
                password: passwordInput.value.trim(),
                mode: currentMode
            })
        });

        const data = await response.json();

        if (data.success) {
            // Store user data in localStorage
            localStorage.setItem('userData', JSON.stringify({
                email: emailInput.value.trim(),
                name: 'User',
                type: currentMode
            }));
            
            showNotification('Login berhasil! Mengalihkan...', 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showNotification(data.message, 'danger');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan saat login. Silakan coba lagi.', 'danger');
        console.error('Error:', error);
    } finally {
        loginBtn.disabled = false;
        loginBtn.textContent = originalText;
    }
}

// Handle forgot password
async function handleForgotPassword() {
    const forgotPasswordEmail = document.getElementById('forgotPasswordEmail');
    const forgotPasswordAlert = document.getElementById('forgotPasswordAlert');
    const sendResetBtn = document.getElementById('sendResetBtn');
    const email = forgotPasswordEmail.value.trim();

    if (!email) {
        showNotification('Email tidak boleh kosong', 'danger', 'forgotPasswordAlert');
        return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showNotification('Format email tidak valid', 'danger', 'forgotPasswordAlert');
        return;
    }

    // Loading state
    const originalText = sendResetBtn.textContent;
    sendResetBtn.disabled = true;
    sendResetBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

    try {
        const response = await fetch('forgot-password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                mode: currentMode
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message, 'success', 'forgotPasswordAlert');
            setTimeout(() => {
                forgotPasswordEmail.value = '';
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                if (modal) modal.hide();
            }, 2000);
        } else {
            showNotification(data.message, 'warning', 'forgotPasswordAlert');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan. Silakan coba lagi.', 'danger', 'forgotPasswordAlert');
        console.error('Error:', error);
    } finally {
        sendResetBtn.disabled = false;
        sendResetBtn.textContent = originalText;
    }
}

// ============ REGISTER PAGE ============
const signupBtn = document.getElementById('signupBtn');
const nameInput = document.getElementById('name');
const registerEmailInput = document.getElementById('email');
const registerPasswordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirmPassword');

// Initialize register page if elements exist
if (signupBtn) {
    signupBtn.addEventListener('click', handleRegister);
    [nameInput, registerEmailInput, registerPasswordInput, confirmPasswordInput].forEach(input => {
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    signupBtn.click();
                }
            });
        }
    });
}

// Validate register form
function validateRegisterForm() {
    const name = nameInput.value.trim();
    const email = registerEmailInput.value.trim();
    const password = registerPasswordInput.value.trim();
    const confirmPassword = confirmPasswordInput.value.trim();
    const errors = [];

    if (!name) {
        errors.push('Nama perusahaan tidak boleh kosong');
    } else if (name.length < 3) {
        errors.push('Nama perusahaan minimal 3 karakter');
    }

    if (!email) {
        errors.push('Email tidak boleh kosong');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push('Format email tidak valid');
    }

    if (!password) {
        errors.push('Password tidak boleh kosong');
    } else if (password.length < 6) {
        errors.push('Password minimal 6 karakter');
    }

    if (!confirmPassword) {
        errors.push('Konfirmasi password tidak boleh kosong');
    } else if (password !== confirmPassword) {
        errors.push('Password dan konfirmasi password tidak cocok');
    }

    return errors;
}

// Handle register
async function handleRegister() {
    const errors = validateRegisterForm();

    if (errors.length > 0) {
        showNotification(errors.join(' | '), 'danger');
        return;
    }

    // Loading state
    const originalText = signupBtn.textContent;
    signupBtn.disabled = true;
    signupBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

    try {
        const response = await fetch('register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: nameInput.value.trim(),
                email: registerEmailInput.value.trim(),
                password: registerPasswordInput.value.trim()
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            if (data.errors && Array.isArray(data.errors)) {
                showNotification(data.errors.join(' | '), 'danger');
            } else {
                showNotification(data.message, 'danger');
            }
        }
    } catch (error) {
        showNotification('Terjadi kesalahan saat registrasi. Silakan coba lagi.', 'danger');
        console.error('Error:', error);
    } finally {
        signupBtn.disabled = false;
        signupBtn.textContent = originalText;
    }
}
