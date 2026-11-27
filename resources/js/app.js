import './bootstrap';

/*
 * PWA KotakRasa.
 *
 * Service worker hanya mendaftar pada build produksi supaya tidak mengganggu
 * Vite dev server. Data aplikasi tidak pernah disimpan di cache.
 */
if ('serviceWorker' in navigator && import.meta.env.PROD) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Pendaftaran gagal tidak boleh mengganggu aplikasi.
        });
    });
}

(function () {
    const sudahTerpasang = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;

    const sembunyikan = () => {
        document.querySelectorAll('[data-install-wrapper]').forEach((wrapper) => {
            wrapper.classList.add('hidden');
        });
    };

    if (sudahTerpasang) {
        sembunyikan();

        return;
    }

    let promptPasang = null;

    document.querySelectorAll('[data-install-app]').forEach((tombol) => {
        tombol.addEventListener('click', async () => {
            if (!promptPasang) {
                return;
            }

            await promptPasang.prompt();
            await promptPasang.userChoice;

            promptPasang = null;
            sembunyikan();
        });
    });

    window.addEventListener('beforeinstallprompt', (event) => {
        // Mencegah prompt otomatis browser agar tombol kita yang dipakai.
        event.preventDefault();
        promptPasang = event;

        document.querySelectorAll('[data-install-app]').forEach((tombol) => {
            tombol.classList.remove('hidden');
        });
    });

    window.addEventListener('appinstalled', sembunyikan);

    const perangkatApple = /iPad|iPhone|iPod/.test(window.navigator.userAgent)
        && !window.MSStream;

    if (perangkatApple) {
        document.querySelectorAll('[data-install-hint]').forEach((petunjuk) => {
            petunjuk.classList.remove('hidden');
        });
    }
})();
