
    const btn = document.getElementById('toggleForm');
    const form = document.getElementById('prefsForm');
    btn.addEventListener('click', () => {
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
