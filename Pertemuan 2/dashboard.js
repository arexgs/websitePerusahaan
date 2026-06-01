// Dashboard initialization
document.addEventListener('DOMContentLoaded', function() {
    checkSession();
    initializeDashboard();
    initializeCharts();
});

// Check if user is logged in
function checkSession() {
    const userNameElement = document.getElementById('userName');
    const userNameGreeting = document.getElementById('userNameGreeting');
    
    // Get user data from session/localStorage
    const userData = localStorage.getItem('userData');
    
    if (!userData) {
        // Redirect to login if not logged in
        window.location.href = 'index.html';
        return;
    }
    
    const user = JSON.parse(userData);
    userNameElement.textContent = user.name || 'User';
    userNameGreeting.textContent = user.name || 'User';
}

// Initialize dashboard
function initializeDashboard() {
    console.log('Dashboard initialized');
}

// Initialize charts
function initializeCharts() {
    const ctx = document.getElementById('applicantChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Frontend', 'Backend', 'UI/UX', 'Data Analyst', 'Secretaris', 'HRD'],
                datasets: [{
                    label: 'Jumlah Pelamar',
                    data: [12, 15, 8, 7, 3, 2],
                    backgroundColor: '#0dcaf0',
                    borderColor: '#0ba5d8',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Switch page
function switchPage(page) {
    // Hide all pages
    document.querySelectorAll('.page-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected page
    const pageElement = document.getElementById(page + '-page');
    if (pageElement) {
        pageElement.classList.add('active');
    }
    
    // Update active nav link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    event.target.closest('.nav-link').classList.add('active');
}

// Toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Logout
function logout() {
    if (confirm('Anda yakin ingin logout?')) {
        localStorage.removeItem('userData');
        window.location.href = 'index.html';
    }
}
