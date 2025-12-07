/**
 * ChiBank Premium Interactive Effects
 * Advanced Dynamic Interactions for Next-Gen Financial Platform
 * Version 2.0.0 - Premium Release
 */

(function($) {
    'use strict';

    // ===== Configuration =====
    const CONFIG = {
        particleCount: 100,
        scrollRevealOffset: 100,
        magneticStrength: 0.3,
        cursorTrailLength: 20,
        counterDuration: 2000,
        debug: false
    };

    // ===== Utility Functions =====
    const Utils = {
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        debounce: function(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        },

        lerp: function(start, end, factor) {
            return start + (end - start) * factor;
        },

        isInViewport: function(element, offset = 0) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight - offset) &&
                rect.bottom >= 0
            );
        },

        isMobile: function() {
            return window.innerWidth < 768;
        }
    };

    // ===== Scroll Reveal Animation =====
    class ScrollReveal {
        constructor() {
            this.elements = [];
            this.init();
        }

        init() {
            // Add scroll-reveal class to elements
            this.addRevealClasses();
            
            // Get all reveal elements
            this.elements = document.querySelectorAll('.scroll-reveal, .scroll-reveal-left, .scroll-reveal-right, .scroll-reveal-scale');
            
            // Initial check
            this.checkElements();
            
            // Listen for scroll
            window.addEventListener('scroll', Utils.throttle(() => this.checkElements(), 50));
            window.addEventListener('resize', Utils.debounce(() => this.checkElements(), 100));
        }

        addRevealClasses() {
            // Service items
            document.querySelectorAll('.service-item').forEach((el, i) => {
                el.classList.add('scroll-reveal');
                el.style.transitionDelay = `${i * 0.1}s`;
            });

            // Choose items
            document.querySelectorAll('.choose-item').forEach((el, i) => {
                el.classList.add('scroll-reveal-scale');
                el.style.transitionDelay = `${i * 0.1}s`;
            });

            // Testimonial items
            document.querySelectorAll('.testimonial-item').forEach(el => {
                el.classList.add('scroll-reveal');
            });

            // Section titles
            document.querySelectorAll('.section-header').forEach(el => {
                el.classList.add('scroll-reveal');
            });

            // About content
            document.querySelectorAll('.about-content').forEach(el => {
                el.classList.add('scroll-reveal-left');
            });

            document.querySelectorAll('.about-thumb').forEach(el => {
                el.classList.add('scroll-reveal-right');
            });

            // FAQ items
            document.querySelectorAll('.faq-item').forEach((el, i) => {
                el.classList.add('scroll-reveal');
                el.style.transitionDelay = `${i * 0.1}s`;
            });
        }

        checkElements() {
            this.elements.forEach(element => {
                if (Utils.isInViewport(element, CONFIG.scrollRevealOffset)) {
                    element.classList.add('visible');
                }
            });
        }
    }

    // ===== Particle Background System =====
    class ParticleBackground {
        constructor(container) {
            this.container = container || document.body;
            this.particles = [];
            this.canvas = null;
            this.ctx = null;
            this.animationId = null;
            this.mouse = { x: null, y: null, radius: 150 };
            
            if (!Utils.isMobile()) {
                this.init();
            }
        }

        init() {
            this.createCanvas();
            this.createParticles();
            this.setupEvents();
            this.animate();
        }

        createCanvas() {
            this.canvas = document.createElement('canvas');
            this.canvas.classList.add('particle-canvas');
            this.canvas.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 0;
                opacity: 0.6;
            `;
            this.container.prepend(this.canvas);
            this.ctx = this.canvas.getContext('2d');
            this.resize();
        }

        resize() {
            this.canvas.width = window.innerWidth;
            this.canvas.height = window.innerHeight;
        }

        createParticles() {
            const colors = ['#FFC324', '#FFD700', '#1E4D8B', '#2563EB'];
            
            for (let i = 0; i < CONFIG.particleCount; i++) {
                this.particles.push({
                    x: Math.random() * this.canvas.width,
                    y: Math.random() * this.canvas.height,
                    vx: (Math.random() - 0.5) * 0.5,
                    vy: (Math.random() - 0.5) * 0.5,
                    radius: Math.random() * 3 + 1,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    alpha: Math.random() * 0.5 + 0.2
                });
            }
        }

        setupEvents() {
            window.addEventListener('resize', Utils.debounce(() => this.resize(), 200));
            
            window.addEventListener('mousemove', (e) => {
                this.mouse.x = e.clientX;
                this.mouse.y = e.clientY;
            });

            window.addEventListener('mouseout', () => {
                this.mouse.x = null;
                this.mouse.y = null;
            });
        }

        drawParticle(particle) {
            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            this.ctx.fillStyle = particle.color;
            this.ctx.globalAlpha = particle.alpha;
            this.ctx.fill();
        }

        connectParticles() {
            for (let i = 0; i < this.particles.length; i++) {
                for (let j = i + 1; j < this.particles.length; j++) {
                    const dx = this.particles[i].x - this.particles[j].x;
                    const dy = this.particles[i].y - this.particles[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < 120) {
                        this.ctx.beginPath();
                        this.ctx.strokeStyle = '#FFC324';
                        this.ctx.globalAlpha = 0.1 * (1 - distance / 120);
                        this.ctx.lineWidth = 0.5;
                        this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
                        this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
                        this.ctx.stroke();
                    }
                }
            }
        }

        updateParticle(particle) {
            // Mouse interaction
            if (this.mouse.x !== null && this.mouse.y !== null) {
                const dx = this.mouse.x - particle.x;
                const dy = this.mouse.y - particle.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < this.mouse.radius) {
                    const force = (this.mouse.radius - distance) / this.mouse.radius;
                    particle.vx -= (dx / distance) * force * 0.5;
                    particle.vy -= (dy / distance) * force * 0.5;
                }
            }

            // Apply velocity
            particle.x += particle.vx;
            particle.y += particle.vy;

            // Apply friction
            particle.vx *= 0.99;
            particle.vy *= 0.99;

            // Boundary check
            if (particle.x < 0 || particle.x > this.canvas.width) {
                particle.vx *= -1;
            }
            if (particle.y < 0 || particle.y > this.canvas.height) {
                particle.vy *= -1;
            }

            // Keep within bounds
            particle.x = Math.max(0, Math.min(this.canvas.width, particle.x));
            particle.y = Math.max(0, Math.min(this.canvas.height, particle.y));
        }

        animate() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            this.particles.forEach(particle => {
                this.updateParticle(particle);
                this.drawParticle(particle);
            });

            this.connectParticles();

            this.animationId = requestAnimationFrame(() => this.animate());
        }

        destroy() {
            cancelAnimationFrame(this.animationId);
            if (this.canvas) {
                this.canvas.remove();
            }
        }
    }

    // ===== Magnetic Button Effect =====
    class MagneticEffect {
        constructor() {
            // Use more specific selector for magnetic buttons only
            this.buttons = document.querySelectorAll('.magnetic-btn');
            this.init();
        }

        init() {
            if (Utils.isMobile()) return;

            this.buttons.forEach(button => {
                button.addEventListener('mousemove', (e) => this.handleMove(e, button));
                button.addEventListener('mouseleave', (e) => this.handleLeave(e, button));
            });
        }

        handleMove(e, button) {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;

            button.style.transform = `translate(${x * CONFIG.magneticStrength}px, ${y * CONFIG.magneticStrength}px)`;
        }

        handleLeave(e, button) {
            button.style.transform = 'translate(0, 0)';
        }
    }

    // ===== Ripple Effect =====
    class RippleEffect {
        constructor() {
            this.init();
        }

        init() {
            // Use event delegation for better performance
            document.addEventListener('click', (e) => {
                const element = e.target.closest('.ripple-effect');
                if (element) {
                    this.createRipple(e, element);
                }
            });
        }

        createRipple(e, element) {
            const rect = element.getBoundingClientRect();
            const ripple = document.createElement('span');
            const diameter = Math.max(rect.width, rect.height);
            const radius = diameter / 2;

            ripple.style.cssText = `
                width: ${diameter}px;
                height: ${diameter}px;
                left: ${e.clientX - rect.left - radius}px;
                top: ${e.clientY - rect.top - radius}px;
            `;
            ripple.classList.add('ripple');

            // Remove existing ripple
            const existingRipple = element.querySelector('.ripple');
            if (existingRipple) {
                existingRipple.remove();
            }

            element.appendChild(ripple);

            // Remove ripple after animation
            setTimeout(() => ripple.remove(), 600);
        }
    }

    // ===== Counter Animation =====
    class CounterAnimation {
        constructor() {
            this.counters = document.querySelectorAll('.odometer, .counter, .stat-number');
            this.animated = new Set();
            this.init();
        }

        init() {
            this.checkCounters();
            window.addEventListener('scroll', Utils.throttle(() => this.checkCounters(), 100));
        }

        checkCounters() {
            this.counters.forEach(counter => {
                if (Utils.isInViewport(counter, 50) && !this.animated.has(counter)) {
                    this.animated.add(counter);
                    this.animateCounter(counter);
                }
            });
        }

        animateCounter(counter) {
            const target = parseInt(counter.dataset.count || counter.textContent.replace(/[^0-9]/g, ''));
            const duration = CONFIG.counterDuration;
            const startTime = performance.now();
            const startValue = 0;

            const updateCounter = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Ease out cubic
                const easeProgress = 1 - Math.pow(1 - progress, 3);
                const currentValue = Math.floor(startValue + (target - startValue) * easeProgress);
                
                counter.textContent = currentValue.toLocaleString();

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString();
                }
            };

            requestAnimationFrame(updateCounter);
        }
    }

    // ===== Smooth Scroll =====
    class SmoothScroll {
        constructor() {
            this.init();
        }

        init() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', (e) => {
                    const targetId = anchor.getAttribute('href');
                    if (targetId !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(targetId);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });
        }
    }

    // ===== Parallax Effect =====
    class ParallaxEffect {
        constructor() {
            this.elements = document.querySelectorAll('.parallax-element, .banner-thumb-area img');
            this.init();
        }

        init() {
            if (Utils.isMobile()) return;

            window.addEventListener('scroll', Utils.throttle(() => this.updateParallax(), 16));
        }

        updateParallax() {
            const scrollY = window.pageYOffset;

            this.elements.forEach((element, index) => {
                const speed = element.dataset.speed || 0.1;
                const direction = index % 2 === 0 ? 1 : -1;
                const offset = scrollY * speed * direction;
                
                element.style.transform = `translateY(${offset}px)`;
            });
        }
    }

    // ===== Header Scroll Effect =====
    class HeaderScroll {
        constructor() {
            this.header = document.querySelector('.header-section');
            this.lastScrollY = 0;
            this.init();
        }

        init() {
            if (!this.header) return;

            window.addEventListener('scroll', Utils.throttle(() => this.handleScroll(), 50));
        }

        handleScroll() {
            const currentScrollY = window.pageYOffset;

            // Add/remove fixed class
            if (currentScrollY > 100) {
                this.header.classList.add('header-fixed');
            } else {
                this.header.classList.remove('header-fixed');
            }

            // Hide/show on scroll direction
            if (currentScrollY > this.lastScrollY && currentScrollY > 300) {
                this.header.style.transform = 'translateY(-100%)';
            } else {
                this.header.style.transform = 'translateY(0)';
            }

            this.lastScrollY = currentScrollY;
        }
    }

    // ===== Image Lazy Loading with Reveal =====
    class LazyImageReveal {
        constructor() {
            this.images = document.querySelectorAll('img[data-src]');
            this.init();
        }

        init() {
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.loadImage(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { rootMargin: '50px' });

                this.images.forEach(img => observer.observe(img));
            } else {
                this.images.forEach(img => this.loadImage(img));
            }
        }

        loadImage(img) {
            const src = img.dataset.src;
            if (src) {
                img.src = src;
                img.classList.add('loaded');
                img.parentElement?.classList.add('image-reveal', 'visible');
            }
        }
    }

    // ===== Tooltip Enhancement =====
    class TooltipEnhancement {
        constructor() {
            this.activeTooltip = null;
            this.activeElement = null;
            this.init();
        }

        init() {
            document.querySelectorAll('[data-tooltip]').forEach(element => {
                element.addEventListener('mouseenter', (e) => this.showTooltip(e, element));
                element.addEventListener('mouseleave', (e) => this.hideTooltip(element));
            });
        }

        showTooltip(e, element) {
            // Remove any existing tooltip first
            this.hideTooltip(this.activeElement);
            
            const text = element.dataset.tooltip;
            
            const tooltip = document.createElement('div');
            tooltip.className = 'premium-tooltip';
            tooltip.textContent = text;
            tooltip.style.cssText = `
                position: fixed;
                background: linear-gradient(135deg, #0F2B5B, #1E4D8B);
                color: #FFC324;
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 14px;
                z-index: 10000;
                pointer-events: none;
                opacity: 0;
                transform: translateY(10px);
                transition: all 0.3s ease;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            `;

            document.body.appendChild(tooltip);
            
            // Store reference to active tooltip and element
            this.activeTooltip = tooltip;
            this.activeElement = element;

            const rect = element.getBoundingClientRect();
            tooltip.style.left = `${rect.left + rect.width / 2 - tooltip.offsetWidth / 2}px`;
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 10}px`;

            requestAnimationFrame(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateY(0)';
            });
        }

        hideTooltip(element) {
            // Only hide if the tooltip belongs to this element
            if (this.activeTooltip && this.activeElement === element) {
                const tooltip = this.activeTooltip;
                tooltip.style.opacity = '0';
                tooltip.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    if (tooltip.parentNode) {
                        tooltip.remove();
                    }
                }, 300);
                this.activeTooltip = null;
                this.activeElement = null;
            }
        }
    }

    // ===== Morph Background =====
    class MorphBackground {
        constructor() {
            this.init();
        }

        init() {
            const bannerSection = document.querySelector('.banner-section');
            if (bannerSection && !Utils.isMobile()) {
                this.createMorphShapes(bannerSection);
            }
        }

        createMorphShapes(container) {
            const shape1 = document.createElement('div');
            shape1.className = 'morph-bg';
            shape1.style.cssText = `
                position: absolute;
                top: 10%;
                right: 10%;
                opacity: 0.5;
            `;

            const shape2 = document.createElement('div');
            shape2.className = 'morph-bg';
            shape2.style.cssText = `
                position: absolute;
                bottom: 10%;
                left: 10%;
                opacity: 0.3;
                animation-delay: -7s;
            `;

            container.style.position = 'relative';
            container.style.overflow = 'hidden';
            container.prepend(shape1, shape2);
        }
    }

    // ===== Animated Grid Background =====
    class GridBackground {
        constructor() {
            this.init();
        }

        init() {
            const bannerSection = document.querySelector('.banner-section');
            if (bannerSection) {
                const grid = document.createElement('div');
                grid.className = 'grid-background';
                bannerSection.prepend(grid);
            }
        }
    }

    // ===== Stagger List Animation =====
    class StaggerList {
        constructor() {
            this.lists = document.querySelectorAll('.banner-floting-right-list, .footer-list');
            this.init();
        }

        init() {
            this.lists.forEach(list => {
                list.classList.add('stagger-list');
                Array.from(list.children).forEach((child, i) => {
                    child.classList.add('stagger-item');
                    child.style.animationDelay = `${i * 0.1}s`;
                });
            });

            window.addEventListener('scroll', Utils.throttle(() => this.checkLists(), 100));
            this.checkLists();
        }

        checkLists() {
            this.lists.forEach(list => {
                if (Utils.isInViewport(list, 100)) {
                    list.classList.add('visible');
                }
            });
        }
    }

    // ===== Page Transition =====
    class PageTransition {
        constructor() {
            this.init();
        }

        init() {
            // Add transition overlay
            const overlay = document.createElement('div');
            overlay.className = 'page-transition-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, #0F2B5B 0%, #1E4D8B 100%);
                z-index: 99999;
                transform: translateY(-100%);
                transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            `;
            document.body.appendChild(overlay);

            // Handle internal links
            document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript"])').forEach(link => {
                const href = link.getAttribute('href');
                if (href && (href.startsWith('/') || href.includes(window.location.hostname))) {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        overlay.style.transform = 'translateY(0)';
                        setTimeout(() => {
                            window.location.href = href;
                        }, 500);
                    });
                }
            });

            // Hide overlay on page load
            window.addEventListener('load', () => {
                overlay.style.transform = 'translateY(-100%)';
            });
        }
    }

    // ===== Initialize All Effects =====
    class ChiBankPremiumEffects {
        constructor() {
            this.effects = {};
            this.init();
        }

        init() {
            // Wait for DOM
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initEffects());
            } else {
                this.initEffects();
            }
        }

        initEffects() {
            try {
                // Core effects
                this.effects.scrollReveal = new ScrollReveal();
                this.effects.smoothScroll = new SmoothScroll();
                this.effects.headerScroll = new HeaderScroll();
                this.effects.rippleEffect = new RippleEffect();
                this.effects.counterAnimation = new CounterAnimation();
                this.effects.lazyImageReveal = new LazyImageReveal();
                this.effects.staggerList = new StaggerList();

                // Desktop-only effects
                if (!Utils.isMobile()) {
                    this.effects.particleBackground = new ParticleBackground();
                    this.effects.magneticEffect = new MagneticEffect();
                    this.effects.parallaxEffect = new ParallaxEffect();
                    this.effects.morphBackground = new MorphBackground();
                    this.effects.gridBackground = new GridBackground();
                    this.effects.tooltipEnhancement = new TooltipEnhancement();
                }

                // Optional effects
                // this.effects.pageTransition = new PageTransition();

                if (CONFIG.debug) {
                    console.log('ChiBank Premium Effects initialized:', this.effects);
                }
            } catch (error) {
                console.error('Error initializing ChiBank Premium Effects:', error);
            }
        }

        destroy() {
            Object.values(this.effects).forEach(effect => {
                if (typeof effect.destroy === 'function') {
                    effect.destroy();
                }
            });
        }
    }

    // Initialize
    window.ChiBankPremiumEffects = new ChiBankPremiumEffects();

    // Export for external use
    window.ChiBankUtils = Utils;

})(jQuery);
