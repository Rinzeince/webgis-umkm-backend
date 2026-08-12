<script>
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('submit', function (e) {
        let action = e.target ? (e.target.action || e.target.getAttribute('action') || '') : '';
        if (action.includes('/logout') || action.includes('logout')) {
            if (!confirm('Apakah Anda yakin ingin keluar dari sistem SIGAP UMKM KBB?')) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    }, true);
});
</script>
