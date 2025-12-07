/**
 * ChiBank Advanced Dynamic Interactions
 * Enterprise AI Financial Platform - Interactive Features
 * Particle System, Data Flow Visualization, Real-time Updates
 */

(function ($) {
  "use strict";

  // ===== Particle Background System =====
  class ParticleSystem {
    constructor(options) {
      this.options = {
        density: options.density || 150,
        color: options.color || '#FFC324',
        motionPath: options.motionPath || 'wave',
        interactivity: options.interactivity || true,
        container: options.container || 'body'
      };
      this.particles = [];
      this.init();
    }

    init() {
      const container = $(this.options.container);
      if (!container.find('.particle-background').length) {
        container.prepend('<div class="particle-background"></div>');
      }
      
      this.createParticles();
      if (this.options.interactivity) {
        this.setupInteractivity();
      }
    }

    createParticles() {
      const $container = $('.particle-background');
      const particleCount = this.options.density;
      
      for (let i = 0; i < particleCount; i++) {
        const $particle = $('<div class="particle"></div>');
        $particle.css({
          '--index': i,
          left: Math.random() * 100 + '%',
          animationDelay: Math.random() * 15 + 's'
        });
        $container.append($particle);
        this.particles.push($particle);
      }
    }

    setupInteractivity() {
      $(document).on('mousemove', (e) => {
        const mouseX = e.pageX;
        const mouseY = e.pageY;
        
        this.particles.forEach(($particle, index) => {
          if (index % 5 === 0) { // Only affect every 5th particle for performance
            const particleX = $particle.offset().left;
            const particleY = $particle.offset().top;
            const distance = Math.sqrt(
              Math.pow(mouseX - particleX, 2) + Math.pow(mouseY - particleY, 2)
            );
            
            if (distance < 100) {
              const force = (100 - distance) / 100;
              const angle = Math.atan2(mouseY - particleY, mouseX - particleX);
              const pushX = Math.cos(angle) * force * 50;
              const pushY = Math.sin(angle) * force * 50;
              
              $particle.css('transform', `translate(${-pushX}px, ${-pushY}px)`);
              setTimeout(() => {
                $particle.css('transform', '');
              }, 300);
            }
          }
        });
      });
    }
  }

  // ===== Value Card Carousel =====
  class ValueCardCarousel {
    constructor(selector, options) {
      this.$container = $(selector);
      this.$cards = this.$container.find('.value-card');
      this.currentIndex = 0;
      this.interval = options.interval || 5000;
      this.init();
    }

    init() {
      this.$cards.eq(0).addClass('active');
      this.$cards.eq(1).addClass('next');
      this.startAutoRotate();
    }

    rotate() {
      const total = this.$cards.length;
      const prevIndex = this.currentIndex;
      this.currentIndex = (this.currentIndex + 1) % total;
      const nextIndex = (this.currentIndex + 1) % total;

      this.$cards.removeClass('active prev next');
      this.$cards.eq(prevIndex).addClass('prev');
      this.$cards.eq(this.currentIndex).addClass('active');
      this.$cards.eq(nextIndex).addClass('next');
    }

    startAutoRotate() {
      setInterval(() => this.rotate(), this.interval);
    }
  }

  // ===== IBAN Input Validator with Animation =====
  class IBANValidator {
    constructor(inputSelector) {
      this.$input = $(inputSelector);
      this.init();
    }

    init() {
      this.$input.on('input', (e) => this.validate(e.target.value));
      this.$input.on('blur', (e) => this.finalValidate(e.target.value));
    }

    validate(value) {
      this.$input.removeClass('valid invalid').addClass('validating');
      
      // Remove spaces for validation
      const cleanValue = value.replace(/\s/g, '');
      
      // Basic IBAN format check
      if (cleanValue.length >= 15) {
        setTimeout(() => {
          const isValid = this.checkIBAN(cleanValue);
          this.$input.removeClass('validating');
          this.$input.addClass(isValid ? 'valid' : 'invalid');
          
          if (isValid) {
            this.loadBankLogo(cleanValue.substring(0, 2));
          }
        }, 500);
      }
    }

    checkIBAN(iban) {
      // Simplified IBAN validation
      const countryCode = iban.substring(0, 2);
      const checkDigits = iban.substring(2, 4);
      return /^[A-Z]{2}[0-9]{2}/.test(iban) && iban.length >= 15 && iban.length <= 34;
    }

    finalValidate(value) {
      const cleanValue = value.replace(/\s/g, '');
      const isValid = this.checkIBAN(cleanValue);
      this.$input.removeClass('validating').addClass(isValid ? 'valid' : 'invalid');
    }

    loadBankLogo(countryCode) {
      const $logo = this.$input.siblings('.bank-logo');
      if ($logo.length) {
        // Simulate loading bank logo
        setTimeout(() => {
          $logo.addClass('loaded');
        }, 300);
      }
    }
  }

  // ===== Real-time Exchange Rate Updater =====
  class ExchangeRateUpdater {
    constructor(selector) {
      this.$element = $(selector);
      this.currentRate = parseFloat(this.$element.text());
    }

    update(newRate) {
      this.$element.addClass('updating');
      
      // Animate number change
      $({ rate: this.currentRate }).animate(
        { rate: newRate },
        {
          duration: 800,
          easing: 'swing',
          step: (now) => {
            this.$element.text(now.toFixed(4));
          },
          complete: () => {
            this.$element.removeClass('updating');
            this.currentRate = newRate;
          }
        }
      );
    }
  }

  // ===== Approval Flow Animator =====
  class ApprovalFlowAnimator {
    constructor(containerSelector) {
      this.$container = $(containerSelector);
      this.$dots = this.$container.find('.approval-dot');
      this.$avatars = this.$container.find('.approver-avatar');
      this.$progress = this.$container.find('.progress-bar-fill');
      this.currentStep = 0;
    }

    nextStep() {
      if (this.currentStep < this.$dots.length) {
        this.$dots.eq(this.currentStep).removeClass('active').addClass('completed');
        this.$avatars.eq(this.currentStep).addClass('show');
        
        this.currentStep++;
        if (this.currentStep < this.$dots.length) {
          this.$dots.eq(this.currentStep).addClass('active');
        }
        
        const progress = ((this.currentStep) / this.$dots.length) * 100;
        this.$progress.css('width', progress + '%');
      }
    }

    start() {
      this.$dots.eq(0).addClass('active');
      const interval = setInterval(() => {
        this.nextStep();
        if (this.currentStep >= this.$dots.length) {
          clearInterval(interval);
        }
      }, 2000);
    }
  }

  // ===== Virtual IBAN Balance Updater =====
  class VIBANBalanceUpdater {
    constructor(cardSelector) {
      this.$card = $(cardSelector);
      this.$balance = this.$card.find('.viban-balance');
    }

    updateBalance(newBalance) {
      this.$balance.addClass('updating');
      
      const currentBalance = parseFloat(this.$balance.text().replace(/[^0-9.-]+/g, ''));
      
      $({ balance: currentBalance }).animate(
        { balance: newBalance },
        {
          duration: 600,
          easing: 'swing',
          step: (now) => {
            this.$balance.text('€' + now.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
          },
          complete: () => {
            this.$balance.removeClass('updating');
            
            // Add ripple effect
            this.$card.addClass('data-ripple');
            setTimeout(() => this.$card.removeClass('data-ripple'), 2000);
          }
        }
      );
    }
  }

  // ===== Risk Radar Visualizer =====
  class RiskRadarVisualizer {
    constructor(containerSelector) {
      this.$container = $(containerSelector);
      this.init();
    }

    init() {
      this.$container.html(`
        <div class="risk-radar">
          <div class="radar-scan-line"></div>
          <svg viewBox="0 0 300 300" class="radar-chart">
            <circle cx="150" cy="150" r="120" fill="none" stroke="rgba(255,195,36,0.1)" stroke-width="1"/>
            <circle cx="150" cy="150" r="90" fill="none" stroke="rgba(255,195,36,0.1)" stroke-width="1"/>
            <circle cx="150" cy="150" r="60" fill="none" stroke="rgba(255,195,36,0.1)" stroke-width="1"/>
            <circle cx="150" cy="150" r="30" fill="none" stroke="rgba(255,195,36,0.1)" stroke-width="1"/>
          </svg>
        </div>
      `);
    }

    addRiskPoint(angle, distance, severity) {
      const x = 150 + Math.cos(angle * Math.PI / 180) * distance;
      const y = 150 + Math.sin(angle * Math.PI / 180) * distance;
      const color = severity === 'high' ? '#dc3545' : severity === 'medium' ? '#ffc107' : '#28a745';
      
      const $point = $(`<circle cx="${x}" cy="${y}" r="5" fill="${color}" class="${severity === 'high' ? 'risk-alert' : ''}"/>`);
      this.$container.find('svg').append($point);
    }
  }

  // ===== Number Roll Animation =====
  function animateNumberRoll(element, targetValue, duration = 1000) {
    const $element = $(element);
    const startValue = parseFloat($element.text().replace(/[^0-9.-]+/g, '')) || 0;
    
    $({ value: startValue }).animate(
      { value: targetValue },
      {
        duration: duration,
        easing: 'swing',
        step: function(now) {
          const formatted = now.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
          $element.text(formatted);
          $element.find('.digit').addClass('rolling');
        },
        complete: function() {
          $element.find('.digit').removeClass('rolling');
        }
      }
    );
  }

  // ===== Success Celebration Animation =====
  function triggerSuccessCelebration(containerSelector) {
    const $container = $(containerSelector);
    $container.addClass('success-celebration');
    
    // Create particle burst
    for (let i = 0; i < 20; i++) {
      const angle = (i / 20) * 360;
      const distance = 100;
      const tx = Math.cos(angle * Math.PI / 180) * distance;
      const ty = Math.sin(angle * Math.PI / 180) * distance;
      
      const $particle = $('<div class="particle-burst"></div>');
      $particle.css({
        '--tx': tx + 'px',
        '--ty': ty + 'px',
        left: '50%',
        top: '50%'
      });
      
      $container.append($particle);
      
      setTimeout(() => $particle.remove(), 800);
    }
    
    // Animate checkmark
    const $checkmark = $container.find('.success-checkmark');
    $checkmark.css('animation', 'none');
    setTimeout(() => {
      $checkmark.css('animation', '');
    }, 10);
  }

  // ===== Data Flow Path Animation =====
  function animateDataFlow(pathSelector) {
    const $path = $(pathSelector);
    const length = $path[0].getTotalLength();
    
    $path.css({
      'stroke-dasharray': length,
      'stroke-dashoffset': length
    });
    
    $path.animate(
      { 'stroke-dashoffset': 0 },
      { duration: 2000, easing: 'swing' }
    );
  }

  // ===== Initialize on Document Ready =====
  $(document).ready(function() {
    // Initialize particle system if on homepage
    if ($('.banner-section').length) {
      new ParticleSystem({
        density: 100,
        color: '#FFC324',
        motionPath: 'wave',
        interactivity: true,
        container: '.banner-section'
      });
    }

    // Initialize value card carousel
    if ($('.value-card-carousel').length) {
      new ValueCardCarousel('.value-card-carousel', { interval: 5000 });
    }

    // Initialize IBAN validators
    $('.iban-input').each(function() {
      new IBANValidator(this);
    });

    // Real-time number counters on scroll
    $('.counter-number').each(function() {
      const $this = $(this);
      const targetValue = parseInt($this.data('count'));
      
      $(window).on('scroll', function() {
        const elementTop = $this.offset().top;
        const elementBottom = elementTop + $this.outerHeight();
        const viewportTop = $(window).scrollTop();
        const viewportBottom = viewportTop + $(window).height();
        
        if (elementBottom > viewportTop && elementTop < viewportBottom && !$this.hasClass('counted')) {
          $this.addClass('counted');
          animateNumberRoll($this, targetValue, 2000);
        }
      });
    });

    // Add gradient scroll to banner
    $('.banner-section').addClass('gradient-scroll');

    console.log('ChiBank Advanced Dynamic Effects Initialized');
  });

  // Make classes globally available
  window.ChiBank = {
    ParticleSystem,
    ValueCardCarousel,
    IBANValidator,
    ExchangeRateUpdater,
    ApprovalFlowAnimator,
    VIBANBalanceUpdater,
    RiskRadarVisualizer,
    animateNumberRoll,
    triggerSuccessCelebration,
    animateDataFlow
  };

})(jQuery);
