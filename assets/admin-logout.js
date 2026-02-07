// Logout Confirmation Functionality
document.addEventListener('DOMContentLoaded', function () {
    // Get logout link
    const logoutLink = document.querySelector('.logout-link');

    if (logoutLink) {
        logoutLink.addEventListener('click', function (e) {
            e.preventDefault();
            showLogoutModal();
        });
    }
});

function showLogoutModal() {
    // Create modal HTML
    const modalHTML = `
        <div class="logout-modal" id="logoutModal">
            <div class="logout-modal-content">
                <div class="logout-modal-header">
                    <div class="logout-modal-icon">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>
                    <h3>Confirm Logout</h3>
                </div>
                <div class="logout-modal-body">
                    <p>Are you sure you want to logout? You will need to login again to access the admin panel.</p>
                </div>
                <div class="logout-modal-actions">
                    <button class="btn-cancel-logout" onclick="closeLogoutModal()">Cancel</button>
                    <button class="btn-confirm-logout" onclick="confirmLogout()">Yes, Logout</button>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('logoutModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Show modal
    setTimeout(() => {
        document.getElementById('logoutModal').classList.add('show');
    }, 10);

    // Close on backdrop click
    document.getElementById('logoutModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeLogoutModal();
        }
    });
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

function confirmLogout() {
    // Redirect to logout page
    window.location.href = '../auth/logout.php';
}

// Close modal on ESC key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeLogoutModal();
    }
});