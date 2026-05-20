document.addEventListener('DOMContentLoaded', function() {
    const themeSwitch = document.getElementById('theme-switch-checkbox');
    if (!themeSwitch) return; // Hentikan jika tombol tidak ada

    // Fungsi untuk mengubah kelas pada <body> dan menyimpan ke Local Storage
    const setTheme = (theme) => {
        if (theme === 'dark') {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        localStorage.setItem('theme', theme);
    };

    // Fungsi untuk mengirim preferensi ke server
    const saveThemePreference = (theme) => {
        // Ambil path API dari atribut data di tag <html>
        const apiPath = document.documentElement.dataset.apiPath || '';
        
        fetch(apiPath + 'api/save_theme.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ theme: theme })
        }).catch(err => console.error('Gagal menyimpan tema:', err));
    };

    // Tambahkan event listener ke tombol geser
    themeSwitch.addEventListener('change', () => {
        const newTheme = themeSwitch.checked ? 'dark' : 'light';
        setTheme(newTheme);
        saveThemePreference(newTheme);
    });

    // Inisialisasi tema saat halaman dimuat untuk menghindari kedipan
    const initialTheme = localStorage.getItem('theme');
    if (initialTheme) {
        document.body.classList.toggle('dark-mode', initialTheme === 'dark');
        themeSwitch.checked = initialTheme === 'dark';
    }
});