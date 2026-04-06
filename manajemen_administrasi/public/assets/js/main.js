document.addEventListener('DOMContentLoaded', function () {
    // Fokus otomatis ke input username
    const usernameInput = document.querySelector('input[name="username"]');
    if (usernameInput) {
        usernameInput.focus();
    }

    // Toggle password visibility
    const togglePassword = document.querySelector('.toggle-password');
    if (togglePassword) {
        togglePassword.addEventListener('click', function () {
            const passwordInput = document.querySelector('input[name="password"]');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.classList.add('text-primary');
            } else {
                passwordInput.type = 'password';
                this.classList.remove('text-primary');
            }
        });
    }

    // Flash message fade out
    const flash = document.querySelector('.flash-message');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
        }, 3000);
    }
});