document.addEventListener('DOMContentLoaded', function() {
// Scroll Animation Observer
const animationObserver = new IntersectionObserver((entries) => {
 entries.forEach(entry => {
     if (entry.isIntersecting) {
         entry.target.classList.add('animate-fadeIn');
     }
 });
}, { threshold: 0.1 });

document.querySelectorAll('.animate-on-scroll').forEach((element) => {
 animationObserver.observe(element);
});

// Testimonial Carousel Logic
const testimonialSection = document.querySelector('#testimonials');
const slides = document.querySelectorAll('.testimonial-slide');
const indicatorsContainer = document.querySelector('.carousel-indicators');
let currentSlide = 0;
let autoSlideInterval;

// Create indicator dots
if (slides.length > 0 && indicatorsContainer) {
 slides.forEach((_, index) => {
     const indicator = document.createElement('div');
     indicator.className = `carousel-indicator ${index === 0 ? 'active' : ''}`;
     indicator.addEventListener('click', () => showSlide(index));
     indicatorsContainer.appendChild(indicator);
 });
}

function showSlide(index) {
 // Wrap around if at ends
 if (index >= slides.length) index = 0;
 if (index < 0) index = slides.length - 1;

 // Update slides
 slides[currentSlide].classList.remove('active-slide');
 slides[index].classList.add('active-slide');
 
 // Update indicators
 document.querySelectorAll('.carousel-indicator').forEach(indicator => {
     indicator.classList.remove('active');
 });
 indicatorsContainer.children[index].classList.add('active');

 currentSlide = index;
}

function autoAdvanceSlide() {
 showSlide(currentSlide + 1);
}

// Testimonial Carousel Observer
const carouselObserver = new IntersectionObserver((entries) => {
 entries.forEach(entry => {
     if (entry.isIntersecting) {
         // Start autoplay when visible
         autoSlideInterval = setInterval(autoAdvanceSlide, 5000);
         entry.target.classList.add('animate-fadeIn');
     } else {
         // Pause when not visible
         clearInterval(autoSlideInterval);
     }
 });
}, { threshold: 0.1 });

if (testimonialSection) {
 carouselObserver.observe(testimonialSection);
}

// Initial setup
if (slides.length > 0) {
 slides[0].classList.add('active-slide');
}
});

function initializeServiceCarousel() {
 const serviceCarousel = document.querySelector('.services-carousel');
 if (!serviceCarousel) return;

 const slides = serviceCarousel.querySelectorAll('.service-slide');
 const indicators = serviceCarousel.querySelector('.carousel-indicators');
 let currentServiceSlide = 0;
 let autoSlideInterval;

 // Create indicators
 slides.forEach((_, index) => {
     const indicator = document.createElement('div');
     indicator.className = `carousel-indicator ${index === 0 ? 'active' : ''}`;
     indicator.addEventListener('click', () => showServiceSlide(index));
     indicators.appendChild(indicator);
 });

 function showServiceSlide(index) {
     slides[currentServiceSlide].classList.remove('active-slide');
     slides[index].classList.add('active-slide');
     
     indicators.querySelectorAll('.carousel-indicator').forEach(ind => 
         ind.classList.remove('active'));
     indicators.children[index].classList.add('active');
     
     currentServiceSlide = index;
 }

 function nextSlide() {
     const newIndex = (currentServiceSlide + 1) % slides.length;
     showServiceSlide(newIndex);
 }

 function prevSlide() {
     const newIndex = (currentServiceSlide - 1 + slides.length) % slides.length;
     showServiceSlide(newIndex);
 }

 // Auto-advance
 function startAutoSlide() {
     autoSlideInterval = setInterval(nextSlide, 5000);
 }

 // Controls
 serviceCarousel.querySelector('.next').addEventListener('click', nextSlide);
 serviceCarousel.querySelector('.prev').addEventListener('click', prevSlide);

 // Pause on hover
 serviceCarousel.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
 serviceCarousel.addEventListener('mouseleave', startAutoSlide);

 // Intersection Observer
 const observer = new IntersectionObserver((entries) => {
     entries.forEach(entry => {
         if (entry.isIntersecting) {
             startAutoSlide();
             entry.target.classList.add('animate-fadeIn');
         } else {
             clearInterval(autoSlideInterval);
         }
     });
 }, { threshold: 0.1 });

 observer.observe(serviceCarousel);
 showServiceSlide(0); // Initialize first slide
}

document.addEventListener('DOMContentLoaded', initializeServiceCarousel);
document.addEventListener('DOMContentLoaded', function() {
const form = document.getElementById('appointmentForm');
const submitBtn = document.getElementById('submitBtn');
const loadingSpinner = document.querySelector('.loading-spinner');
const successMessage = document.querySelector('.success-message');

// Real-time validation
form.addEventListener('input', function(e) {
 validateField(e.target);
});

// Form submission handler
form.addEventListener('submit', async function(e) {
 e.preventDefault();
 if (validateForm()) {
     submitBtn.disabled = true;
     loadingSpinner.style.display = 'inline-block';
     document.querySelector('.submit-text').style.display = 'none';

     try {
         // Simulate API call
         await new Promise(resolve => setTimeout(resolve, 1500));
         
         successMessage.style.display = 'block';
         form.reset();
         setTimeout(() => {
             $('#appointmentModal').modal('hide');
             successMessage.style.display = 'none';
         }, 2000);
     } catch (error) {
         console.error('Submission error:', error);
     } finally {
         submitBtn.disabled = false;
         loadingSpinner.style.display = 'none';
         document.querySelector('.submit-text').style.display = 'inline';
     }
 }
});

function validateForm() {
 let isValid = true;
 const fields = form.querySelectorAll('input, select, textarea');
 fields.forEach(field => {
     if (!validateField(field)) isValid = false;
 });
 return isValid;
}

function validateField(field) {
 const errorElement = document.getElementById(`${field.id}Error`);
 if (!errorElement) return true;

 let isValid = true;
 errorElement.textContent = '';

 if (field.required && !field.value.trim()) {
     errorElement.textContent = 'This field is required';
     isValid = false;
 }

 if (field.type === 'email' && !/^\S+@\S+\.\S+$/.test(field.value)) {
     errorElement.textContent = 'Please enter a valid email address';
     isValid = false;
 }

 if (field.id === 'phone' && field.value && !/^\d{10}$/.test(field.value.replace(/\D/g, ''))) {
     errorElement.textContent = 'Please enter a valid 10-digit phone number';
     isValid = false;
 }

 errorElement.style.display = isValid ? 'none' : 'block';
 field.style.borderColor = isValid ? '#e9ecef' : '#dc3545';
 return isValid;
}

// Phone number formatting
const phoneInput = document.getElementById('phone');
phoneInput.addEventListener('input', function(e) {
 const numbers = e.target.value.replace(/\D/g, '');
 const char = {0:'(', 3:') ', 6:' - '};
 e.target.value = numbers.length <= 10 ? numbers.split('').map((n,i) => char[i] || n).join('') : numbers;
});
});
