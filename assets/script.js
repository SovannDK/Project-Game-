// ================================
// XO Arena - Shared JavaScript
// ================================

// Auto-dismiss flash messages after 4 seconds
document.addEventListener('DOMContentLoaded', () => {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(flash => {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-20px)';
            setTimeout(() => flash.remove(), 300);
        }, 4000);
    });
});

// Keyboard shortcut: ESC to close modals
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('registerModal');
        if (modal && modal.style.display !== 'none') {
            modal.style.display = 'none';
        }
    }
});
