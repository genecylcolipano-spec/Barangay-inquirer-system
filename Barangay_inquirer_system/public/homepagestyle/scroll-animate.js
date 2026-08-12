document.addEventListener("DOMContentLoaded", function () {
    const animatedElements = document.querySelectorAll("[data-animate]");

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const delay = entry.target.getAttribute("data-delay") || 0;
                    setTimeout(() => {
                        entry.target.classList.add("animate");
                    }, delay);
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.15
        }
    );

    animatedElements.forEach(el => observer.observe(el));


    
});
