// Toggle password visibility
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId || 'contrasena');
    const button = passwordInput.parentElement.querySelector('.toggle-password');
    const eyeIcon = button.querySelector('.eye-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        eyeIcon.textContent = '👁';
    }
}
