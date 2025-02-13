document.addEventListener('DOMContentLoaded', function() {
    const currentLocation = location.href;
    const menuItems = document.querySelectorAll('.nav-link');
    
    menuItems.forEach(link => {
        if(link.href === currentLocation) {
            link.classList.add('active');
        }
    });
});