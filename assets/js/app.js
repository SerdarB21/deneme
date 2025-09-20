document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-category-select]').forEach(select => {
        const target = document.getElementById(select.dataset.toggleTarget || 'credential-fields');
        const toggleFields = () => {
            if (!target) return;
            const requires = select.dataset.requires?.split(',') || [];
            if (requires.includes(select.value)) {
                target.classList.remove('hidden');
            } else {
                target.classList.add('hidden');
            }
        };
        select.addEventListener('change', toggleFields);
        toggleFields();
    });

    document.querySelectorAll('[data-auto-scroll]').forEach(container => {
        container.scrollTop = container.scrollHeight;
    });

    document.querySelectorAll('[data-copy]').forEach(button => {
        button.addEventListener('click', () => {
            const text = button.dataset.copy;
            navigator.clipboard.writeText(text).then(() => {
                button.textContent = 'Kopyalandı';
                setTimeout(() => button.textContent = 'Kopyala', 2000);
            });
        });
    });

    document.querySelectorAll('[data-toggle-custom]').forEach(checkbox => {
        const container = checkbox.closest('.form-group').querySelector('[data-custom-prices]');
        const toggle = () => {
            if (!container) return;
            container.classList.toggle('hidden', !checkbox.checked);
        };
        checkbox.addEventListener('change', toggle);
        toggle();
    });
});
