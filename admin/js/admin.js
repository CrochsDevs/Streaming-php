document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.querySelector('#togglePassword');
    if (togglePassword) {
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    }

    // Toggle sidebar on mobile
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('sidebar-active');
        });
    }

    // Initialize DataTables if any table has the 'datatable' class
    if (document.querySelector('.datatable')) {
        $('.datatable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            }
        });
    }
});