export function initPasswordToggles(root = document) {
    root.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const iconNode = button.querySelector('i');

        if (!input) {
            return;
        }

        button.addEventListener('click', (event) => {
            event.preventDefault();

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
            button.setAttribute('aria-pressed', String(show));

            if (iconNode) {
                iconNode.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
            }
        });
    });
}
