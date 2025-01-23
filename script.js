// Smooth scrolling behavior for navigation links
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', event => {
        event.preventDefault();
        const targetId = event.target.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);

        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - document.querySelector('.navbar').offsetHeight,
                behavior: 'smooth'
            });
        }
    });
});

// Carousel effect for testimonials
let currentTestimonial = 0;
const testimonials = document.querySelectorAll('.testimonial');
const totalTestimonials = testimonials.length;

setInterval(() => {
    testimonials[currentTestimonial].style.display = 'none'; // Hide current
    currentTestimonial = (currentTestimonial + 1) % totalTestimonials; // Next index
    testimonials[currentTestimonial].style.display = 'block'; // Show next
}, 3000);


// Show Login Modal
document.getElementById("loginBtn").addEventListener("click", function () {
    document.getElementById("loginModal").classList.add("active");
});

// Show Register Modal when clicking "Register here"
document.getElementById("openRegisterModal").addEventListener("click", function () {
    document.getElementById("loginModal").classList.remove("active");
    document.getElementById("registerModal").classList.add("active");
});

// Switch to Login Modal from Register Modal
document.getElementById("openLoginModal").addEventListener("click", function () {
    document.getElementById("registerModal").classList.remove("active");
    document.getElementById("loginModal").classList.add("active");
});

// Close Modal on Close Button
document.querySelectorAll(".modal .close").forEach((closeBtn) => {
    closeBtn.addEventListener("click", function () {
        closeBtn.closest(".modal").classList.remove("active");
    });
});

// Close Modal on Outside Click
window.addEventListener("click", function (event) {
    if (event.target.classList.contains("modal")) {
        event.target.classList.remove("active");
    }
});
