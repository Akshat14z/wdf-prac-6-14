// Landing Page JavaScript - SecureAuth Pro
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('a[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const navHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = targetSection.offsetTop - navHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', function() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 2px 20px rgba(124, 60, 33, 0.1)';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = 'none';
        }
        
        lastScrollY = currentScrollY;
    });
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animateElements = document.querySelectorAll('.feature-card, .security-item, .demo-feature, .tech-item');
    animateElements.forEach(el => {
        observer.observe(el);
    });
    
    // Stats counter animation
    const statsNumbers = document.querySelectorAll('.stat-number');
    const statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    statsNumbers.forEach(stat => {
        statsObserver.observe(stat);
    });
    
    function animateCounter(element) {
        const target = element.textContent;
        const isPercentage = target.includes('%');
        const targetNumber = parseInt(target);
        
        if (isNaN(targetNumber)) return;
        
        let current = 0;
        const increment = targetNumber / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= targetNumber) {
                current = targetNumber;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current) + (isPercentage ? '%' : (targetNumber > 10 ? '+' : ''));
        }, 40);
    }
    
    // Floating badges animation
    const floatingBadges = document.querySelectorAll('.floating-badge');
    
    function randomFloat() {
        floatingBadges.forEach((badge, index) => {
            const delay = index * 500;
            const duration = 3000 + Math.random() * 2000;
            
            setTimeout(() => {
                badge.style.animation = `float ${duration}ms ease-in-out infinite`;
            }, delay);
        });
    }
    
    randomFloat();
    
    // Parallax effect for hero section
    const heroSection = document.querySelector('.hero');
    const heroContent = document.querySelector('.hero-content');
    const heroVisual = document.querySelector('.hero-visual');
    
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        if (heroSection && scrolled < heroSection.offsetHeight) {
            heroContent.style.transform = `translateY(${rate * 0.3}px)`;
            heroVisual.style.transform = `translateY(${rate * 0.2}px)`;
        }
    });
    
    // Security matrix rotation
    const matrixLayers = document.querySelectorAll('.matrix-layer');
    let rotationAngle = 0;
    
    function rotateMatrix() {
        rotationAngle += 0.5;
        matrixLayers.forEach((layer, index) => {
            const speed = (index + 1) * 0.5;
            const direction = index % 2 === 0 ? 1 : -1;
            layer.style.transform = `scale(${0.6 + index * 0.2}) rotate(${rotationAngle * speed * direction}deg)`;
        });
        requestAnimationFrame(rotateMatrix);
    }
    
    rotateMatrix();
    
    // Technology icons hover effect
    const techItems = document.querySelectorAll('.tech-item');
    
    techItems.forEach(item => {
        const icon = item.querySelector('.tech-icon');
        
        item.addEventListener('mouseenter', function() {
            icon.style.transform = 'scale(1.2) rotate(5deg)';
            icon.style.transition = 'transform 0.3s ease';
        });
        
        item.addEventListener('mouseleave', function() {
            icon.style.transform = 'scale(1) rotate(0deg)';
        });
    });
    
    // Feature cards stagger animation
    const featureCards = document.querySelectorAll('.feature-card');
    const featureObserver = new IntersectionObserver(function(entries) {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, { threshold: 0.2 });
    
    // Initialize feature cards
    featureCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        featureObserver.observe(card);
    });
    
    // Demo features interaction
    const demoFeatures = document.querySelectorAll('.demo-feature');
    
    demoFeatures.forEach(feature => {
        feature.addEventListener('click', function() {
            // Add click animation
            this.style.transform = 'translateY(-10px) scale(1.05)';
            setTimeout(() => {
                this.style.transform = 'translateY(-5px) scale(1)';
            }, 200);
        });
    });
    
    // Security checklist animation
    const securityItems = document.querySelectorAll('.security-item');
    
    securityItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-30px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 200);
    });
    
    // CTA buttons pulse effect
    const ctaButtons = document.querySelectorAll('.cta-primary, .demo-btn');
    
    function pulseEffect() {
        ctaButtons.forEach(btn => {
            btn.style.boxShadow = '0 8px 30px rgba(236, 130, 58, 0.5)';
            setTimeout(() => {
                btn.style.boxShadow = '0 6px 20px rgba(236, 130, 58, 0.3)';
            }, 1000);
        });
    }
    
    // Pulse every 5 seconds
    setInterval(pulseEffect, 5000);
    
    // Mobile menu toggle (if needed)
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Scroll progress indicator
    const scrollIndicator = document.createElement('div');
    scrollIndicator.style.cssText = `
        position: fixed;
        top: 70px;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #EC823A, #F9C49A);
        z-index: 999;
        transition: width 0.3s ease;
    `;
    document.body.appendChild(scrollIndicator);
    
    window.addEventListener('scroll', function() {
        const windowHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (window.scrollY / windowHeight) * 100;
        scrollIndicator.style.width = scrolled + '%';
    });
    
    // Easter egg: Konami code
    let konamiCode = [];
    const konamiSequence = [
        'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown',
        'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight',
        'KeyB', 'KeyA'
    ];
    
    document.addEventListener('keydown', function(e) {
        konamiCode.push(e.code);
        if (konamiCode.length > konamiSequence.length) {
            konamiCode.shift();
        }
        
        if (JSON.stringify(konamiCode) === JSON.stringify(konamiSequence)) {
            showEasterEgg();
        }
    });
    
    function showEasterEgg() {
        const easterEgg = document.createElement('div');
        easterEgg.innerHTML = '🎉 Super Secure Mode Activated! 🎉';
        easterEgg.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #EC823A, #F9C49A);
            color: white;
            padding: 20px 40px;
            border-radius: 20px;
            font-size: 24px;
            font-weight: bold;
            z-index: 9999;
            animation: bounce 1s ease infinite;
        `;
        
        document.body.appendChild(easterEgg);
        
        setTimeout(() => {
            easterEgg.remove();
        }, 3000);
    }
    
    // Performance monitoring
    window.addEventListener('load', function() {
        if ('performance' in window) {
            const loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
            console.log(`🚀 SecureAuth Pro loaded in ${loadTime}ms`);
        }
    });
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        .animate-in {
            animation: slideInUp 0.6s ease-out forwards;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translate(-50%, -50%) translateY(0); }
            40% { transform: translate(-50%, -50%) translateY(-10px); }
            60% { transform: translate(-50%, -50%) translateY(-5px); }
        }
    `;
    document.head.appendChild(style);
    
    console.log('🛡️ SecureAuth Pro Landing Page Initialized');
    console.log('🔐 All security features loaded and ready');
});