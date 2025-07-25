document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initUniverse();
    initParticles();
    initNavigation();
    initScrollEffects();
    initContactForm();
    initEmailLinks();
    
    // Set current year in footer
    document.getElementById('year').textContent = new Date().getFullYear();
});

// ================= THREE.JS UNIVERSE BACKGROUND =================
function initUniverse() {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: true
    });
    
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.getElementById('universe').appendChild(renderer.domElement);

    // Create stars
    const starsGeometry = new THREE.BufferGeometry();
    const starsMaterial = new THREE.PointsMaterial({
        color: 0xffffff,
        size: 0.05,
        transparent: true,
        opacity: 0.8
    });

    const starsVertices = [];
    for (let i = 0; i < 10000; i++) {
        starsVertices.push(
            (Math.random() - 0.5) * 2000,
            (Math.random() - 0.5) * 2000,
            (Math.random() - 0.5) * 2000
        );
    }

    starsGeometry.setAttribute('position', new THREE.Float32BufferAttribute(starsVertices, 3));
    const stars = new THREE.Points(starsGeometry, starsMaterial);
    scene.add(stars);

    // Create galaxy
    const galaxyGeometry = new THREE.BufferGeometry();
    const galaxyMaterial = new THREE.PointsMaterial({
        color: 0x3b82f6,
        size: 0.1,
        transparent: true,
        opacity: 0.6
    });

    const galaxyVertices = [];
    for (let i = 0; i < 5000; i++) {
        const radius = Math.random() * 50 + 20;
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.random() * Math.PI * 2;
        galaxyVertices.push(
            radius * Math.sin(phi) * Math.cos(theta),
            radius * Math.sin(phi) * Math.sin(theta),
            radius * Math.cos(phi)
        );
    }

    galaxyGeometry.setAttribute('position', new THREE.Float32BufferAttribute(galaxyVertices, 3));
    const galaxy = new THREE.Points(galaxyGeometry, galaxyMaterial);
    scene.add(galaxy);

    // Position camera
    camera.position.z = 100;

    // Animation loop
    function animate() {
        requestAnimationFrame(animate);
        
        stars.rotation.x += 0.0001;
        stars.rotation.y += 0.0001;
        galaxy.rotation.x += 0.0005;
        galaxy.rotation.y += 0.0005;
        
        renderer.render(scene, camera);
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    animate();
}

// ================= PARTICLES.JS INITIALIZATION =================
function initParticles() {
    particlesJS('particles-js', {
        "particles": {
            "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
            "color": { "value": "#93c5fd" },
            "shape": { 
                "type": "circle",
                "stroke": { "width": 0, "color": "#000000" },
                "polygon": { "nb_sides": 5 }
            },
            "opacity": {
                "value": 0.5,
                "random": true,
                "anim": { "enable": true, "speed": 1, "opacity_min": 0.1, "sync": false }
            },
            "size": {
                "value": 3,
                "random": true,
                "anim": { "enable": true, "speed": 2, "size_min": 0.1, "sync": false }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#3b82f6",
                "opacity": 0.2,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 1,
                "direction": "none",
                "random": true,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": { "enable": true, "rotateX": 600, "rotateY": 1200 }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": { "enable": true, "mode": "grab" },
                "onclick": { "enable": true, "mode": "push" },
                "resize": true
            },
            "modes": {
                "grab": { "distance": 140, "line_linked": { "opacity": 0.5 } },
                "bubble": { "distance": 400, "size": 40, "duration": 2, "opacity": 8, "speed": 3 },
                "repulse": { "distance": 200, "duration": 0.4 },
                "push": { "particles_nb": 4 },
                "remove": { "particles_nb": 2 }
            }
        },
        "retina_detect": true
    });
}

// ================= NAVIGATION FUNCTIONALITY =================
function initNavigation() {
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    const navbar = document.querySelector('.glass-nav');
    const body = document.body;

    // Mobile menu toggle
    hamburger.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMenu();
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (navLinks.classList.contains('active') && 
            !navLinks.contains(e.target) && 
            !hamburger.contains(e.target)) {
            closeMenu();
        }
    });

    // Close menu when clicking on a link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navLinks.classList.contains('active')) {
            closeMenu();
        }
    });

    function toggleMenu() {
        navLinks.classList.toggle('active');
        hamburger.classList.toggle('active');
        body.classList.toggle('nav-open');
        document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
    }

    function closeMenu() {
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
        body.classList.remove('nav-open');
        document.body.style.overflow = '';
    }
}

// ================= SCROLL EFFECTS =================
function initScrollEffects() {
    const navbar = document.querySelector('.glass-nav');
    const scrollUpBtn = document.getElementById('scrollUp');
    const sections = document.querySelectorAll('section');
    const navItems = document.querySelectorAll('.nav-links a');
    
    let lastScroll = window.pageYOffset;
    const navbarHeight = navbar.offsetHeight;
    const scrollThreshold = 100;
    
    // Hide/show navbar on scroll
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        // Always show navbar when at top of page
        if (currentScroll <= 0) {
            navbar.classList.remove('scrolled-up');
            navbar.classList.remove('scrolled-down');
            navbar.classList.add('scrolled');
            lastScroll = currentScroll;
            return;
        }
        
        // Scrolling down
        if (currentScroll > lastScroll && !navbar.classList.contains('scrolled-down')) {
            // Hide navbar
            navbar.classList.remove('scrolled');
            navbar.classList.add('scrolled-down');
            navbar.classList.remove('scrolled-up');
        } 
        // Scrolling up
        else if (currentScroll < lastScroll && navbar.classList.contains('scrolled-down')) {
            // Show navbar
            navbar.classList.remove('scrolled-down');
            navbar.classList.add('scrolled');
            navbar.classList.remove('scrolled-up');
        }
        
        lastScroll = currentScroll;
        
        // Scroll up button visibility
        scrollUpBtn.classList.toggle('show', currentScroll > 300);

        // Section highlighting
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (currentScroll >= (sectionTop - sectionHeight / 3)) {
                current = section.getAttribute('id');
            }
        });
        
        navItems.forEach(item => {
            item.classList.toggle('active', item.getAttribute('href') === `#${current}`);
        });
    });

    // Smooth scroll to top
    scrollUpBtn.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            if (this.classList.contains('dropdown-toggle') || this.getAttribute('data-toggle') === 'dropdown') {
                return;
            }
            
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const navbarHeight = document.querySelector('.glass-nav').offsetHeight;
                const targetPosition = targetElement.offsetTop - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ================= CONTACT FORM =================
function initContactForm() {
    const contactForm = document.querySelector('.contact-form');
    if (!contactForm) return;

    // Create modal element
    const modal = document.createElement('div');
    modal.id = 'formModal';
    modal.style.cssText = `
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        font-family: 'Space Grotesk', sans-serif;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background-color: white;
        padding: 3rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        max-width: 600px;
        width: 90%;
        text-align: center;
    `;
    
    const modalIcon = document.createElement('div');
    modalIcon.style.cssText = `
        font-size: 5rem;
        margin-bottom: 1.5rem;
    `;
    
    const modalTitle = document.createElement('h1');
    modalTitle.style.marginBottom = '1rem';
    
    const modalMessage = document.createElement('p');
    modalMessage.style.cssText = `
        font-size: 1.1rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    `;
    
    const modalButton = document.createElement('a');
    modalButton.className = 'btn';
    modalButton.style.cssText = `
        display: inline-block;
        background-color: #2563eb;
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    `;
    modalButton.innerHTML = 'Return to Contact Page <i class="fas fa-arrow-right"></i>';
    
    modalButton.addEventListener('mouseenter', () => {
        modalButton.style.backgroundColor = '#1e40af';
        modalButton.style.transform = 'translateY(-2px)';
        modalButton.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
    });
    
    modalButton.addEventListener('mouseleave', () => {
        modalButton.style.backgroundColor = '#2563eb';
        modalButton.style.transform = 'translateY(0)';
        modalButton.style.boxShadow = 'none';
    });
    
    modalButton.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    modalContent.append(modalIcon, modalTitle, modalMessage, modalButton);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);

    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Form validation and submission logic
        // ... (keep your existing form submission logic)
    });
}

// ================= EMAIL LINKS =================
function initEmailLinks() {
    document.querySelectorAll('a[href^="mailto:"]').forEach(emailLink => {
        emailLink.addEventListener('click', function(event) {
            event.preventDefault();
            const gmailUrl = 'https://mail.google.com/mail/?view=cm&fs=1&to=luigimaretto292@gmail.com';
            const mailtoUrl = this.href;
            const newWindow = window.open(gmailUrl, '_blank', 'noopener,noreferrer');
            if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                window.location.href = mailtoUrl;
            }
        });
    });
}