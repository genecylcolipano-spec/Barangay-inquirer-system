document.addEventListener("scroll", function () {
    const navbar = document.querySelector(".main-navbar");

    if (window.scrollY > 50) {
        navbar.classList.add("scrolled-navbar");
        navbar.classList.remove("transparent-navbar");
    } else {
        navbar.classList.add("transparent-navbar");
        navbar.classList.remove("scrolled-navbar");
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.main-navbar .nav-toggle');
    var menu = document.querySelector('.main-navbar .nav-menu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('open');
        var icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        }
        // prevent body scroll when menu is open
        if (menu.classList.contains('open')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    });

    // close menu when clicking outside
    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            menu.classList.remove('open');
            var icon = toggle.querySelector('i');
            if (icon) {
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            }
            document.body.style.overflow = '';
        }
    });
    // close menu when clicking on any nav-link inside the menu (for single-page anchors)
    var navLinks = menu.querySelectorAll('.nav-link');
    navLinks.forEach(function(link){
        link.addEventListener('click', function(){
            menu.classList.remove('open');
            var icon = toggle.querySelector('i');
            if (icon) { icon.classList.add('fa-bars'); icon.classList.remove('fa-times'); }
            document.body.style.overflow = '';
        });
    });
});
