/**
 * Enhanced Interactions for ChiBank
 * Enterprise-level frontend dynamic effects and micro-interactions
 */

(function ($) {
  "use strict";

  // ===== Smooth Page Load =====
  $(window).on('load', function () {
    // Add loaded class for animations
    $('body').addClass('page-loaded');
    
    // Trigger stagger animations
    $('.stagger-item').each(function(index) {
      $(this).css('animation-delay', (index * 0.1) + 's');
    });
  });

  // ===== Enhanced Scroll Effects =====
  let lastScroll = 0;
  $(window).on('scroll', function() {
    const currentScroll = $(this).scrollTop();
    
    // Add parallax effect to banner images
    if ($('.banner-thumb-area img').length) {
      const scrolled = currentScroll * 0.3;
      $('.banner-thumb-area img').css('transform', 'translateY(' + scrolled + 'px)');
    }
    
    // Fade in elements on scroll
    $('.fade-on-scroll').each(function() {
      const elementTop = $(this).offset().top;
      const elementBottom = elementTop + $(this).outerHeight();
      const viewportTop = currentScroll;
      const viewportBottom = viewportTop + $(window).height();
      
      if (elementBottom > viewportTop && elementTop < viewportBottom) {
        $(this).addClass('visible');
      }
    });
    
    lastScroll = currentScroll;
  });

  // ===== Enhanced Card Interactions =====
  $('.dashbord-item, .service-item, .choose-item, .blog-item').each(function() {
    $(this).on('mouseenter', function() {
      $(this).find('i, img').addClass('animated-hover');
    }).on('mouseleave', function() {
      $(this).find('i, img').removeClass('animated-hover');
    });
  });

  // ===== Ripple Effect on Buttons =====
  $('.btn, .app-btn, button').on('click', function(e) {
    const $this = $(this);
    const $ripple = $('<span class="ripple"></span>');
    
    const btnOffset = $this.offset();
    const xPos = e.pageX - btnOffset.left;
    const yPos = e.pageY - btnOffset.top;
    
    $ripple.css({
      top: yPos + 'px',
      left: xPos + 'px'
    });
    
    $this.append($ripple);
    
    setTimeout(function() {
      $ripple.remove();
    }, 600);
  });

  // ===== Add CSS for ripple effect =====
  if (!$('#ripple-styles').length) {
    $('head').append(`
      <style id="ripple-styles">
        .ripple {
          position: absolute;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.5);
          width: 10px;
          height: 10px;
          pointer-events: none;
          animation: ripple-animation 0.6s ease-out;
        }
        @keyframes ripple-animation {
          to {
            transform: scale(20);
            opacity: 0;
          }
        }
        .animated-hover {
          animation: icon-bounce 0.5s ease;
        }
        @keyframes icon-bounce {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-5px); }
        }
      </style>
    `);
  }

  // ===== Enhanced Input Focus =====
  $('input, textarea, select').on('focus', function() {
    $(this).parent().addClass('input-focused');
  }).on('blur', function() {
    $(this).parent().removeClass('input-focused');
    if ($(this).val() !== '') {
      $(this).parent().addClass('input-filled');
    } else {
      $(this).parent().removeClass('input-filled');
    }
  });

  // ===== Number Counter Animation =====
  function animateCounter($element, target) {
    const duration = 2000;
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(function() {
      current += increment;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      $element.text(Math.floor(current));
    }, 16);
  }

  // ===== Trigger counter animation on scroll =====
  let counterAnimated = false;
  $(window).on('scroll', function() {
    if (!counterAnimated) {
      $('.counter-number').each(function() {
        const elementTop = $(this).offset().top;
        const viewportBottom = $(window).scrollTop() + $(window).height();
        
        if (elementTop < viewportBottom - 100) {
          const target = parseInt($(this).data('count'));
          if (!isNaN(target)) {
            animateCounter($(this), target);
          }
          counterAnimated = true;
        }
      });
    }
  });

  // ===== Enhanced Image Lazy Loading =====
  const lazyLoadImages = function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver(function(entries, observer) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.add('loaded');
          imageObserver.unobserve(img);
        }
      });
    });
    
    images.forEach(function(img) {
      imageObserver.observe(img);
    });
  };

  if ('IntersectionObserver' in window) {
    lazyLoadImages();
  }

  // ===== Smooth Anchor Links =====
  $('a[href^="#"]').on('click', function(e) {
    const target = $(this.hash);
    if (target.length) {
      e.preventDefault();
      $('html, body').animate({
        scrollTop: target.offset().top - 100
      }, 800, 'swing');
    }
  });

  // ===== Enhanced Table Row Animations =====
  $('table tbody tr').each(function(index) {
    $(this).css('animation-delay', (index * 0.05) + 's');
    $(this).addClass('fade-in-row');
  });

  // ===== Add table animation CSS =====
  if (!$('#table-animations').length) {
    $('head').append(`
      <style id="table-animations">
        .fade-in-row {
          animation: fadeInRow 0.5s ease-out backwards;
        }
        @keyframes fadeInRow {
          from {
            opacity: 0;
            transform: translateX(-20px);
          }
          to {
            opacity: 1;
            transform: translateX(0);
          }
        }
      </style>
    `);
  }

  // ===== Dashboard Card Number Animation =====
  $('.dashbord-item').each(function(index) {
    $(this).css('animation-delay', (index * 0.15) + 's');
  });

  // ===== Enhanced Modal Transitions =====
  $('.modal').on('show.bs.modal', function() {
    $(this).find('.modal-dialog').addClass('modal-slide-in');
  }).on('hidden.bs.modal', function() {
    $(this).find('.modal-dialog').removeClass('modal-slide-in');
  });

  // ===== Progress Bar Animation =====
  function animateProgressBars() {
    $('.progress-bar').each(function() {
      const $bar = $(this);
      const width = $bar.attr('aria-valuenow');
      $bar.css('width', '0%');
      
      setTimeout(function() {
        $bar.css({
          'width': width + '%',
          'transition': 'width 1.5s cubic-bezier(0.4, 0, 0.2, 1)'
        });
      }, 100);
    });
  }

  // Trigger progress bar animation on scroll
  $(window).on('scroll', function() {
    $('.progress-bar').each(function() {
      const elementTop = $(this).offset().top;
      const viewportBottom = $(window).scrollTop() + $(window).height();
      
      if (elementTop < viewportBottom - 100 && !$(this).hasClass('animated')) {
        $(this).addClass('animated');
        const width = $(this).attr('aria-valuenow');
        $(this).css('width', width + '%');
      }
    });
  });

  // ===== Add Loading Spinner =====
  function showLoadingSpinner() {
    if (!$('.loading-overlay').length) {
      $('body').append(`
        <div class="loading-overlay">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
      `);
      
      $('head').append(`
        <style>
          .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
          }
          .loading-overlay.active {
            opacity: 1;
            pointer-events: all;
          }
        </style>
      `);
    }
  }

  // ===== Form Submission with Loading =====
  $('form').on('submit', function() {
    const $form = $(this);
    if (!$form.hasClass('no-loading')) {
      showLoadingSpinner();
      $('.loading-overlay').addClass('active');
    }
  });

  // ===== Enhanced Notification System =====
  function showNotification(message, type = 'info', duration = 3000) {
    const $notification = $(`
      <div class="custom-notification ${type}">
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
      </div>
    `);
    
    if (!$('#notification-styles').length) {
      $('head').append(`
        <style id="notification-styles">
          .custom-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            animation: slideInRight 0.3s ease-out;
            max-width: 300px;
          }
          .custom-notification.success {
            border-left: 4px solid #28a745;
          }
          .custom-notification.error {
            border-left: 4px solid #dc3545;
          }
          .custom-notification.info {
            border-left: 4px solid #17a2b8;
          }
          .custom-notification i {
            font-size: 20px;
          }
          .custom-notification.success i {
            color: #28a745;
          }
          .custom-notification.error i {
            color: #dc3545;
          }
          .custom-notification.info i {
            color: #17a2b8;
          }
        </style>
      `);
    }
    
    $('body').append($notification);
    
    setTimeout(function() {
      $notification.css('animation', 'slideOutRight 0.3s ease-out forwards');
      setTimeout(function() {
        $notification.remove();
      }, 300);
    }, duration);
  }

  // Make notification function globally available
  window.showNotification = showNotification;

  // ===== Enhance Cookie Banner =====
  $('.cookie-btn, .cookie-btn-cross').on('click', function() {
    $('.cookie-main-wrapper').css({
      'animation': 'slideOutDown 0.5s ease-out forwards'
    });
  });

  // ===== Add slide out animation =====
  if (!$('#slide-animations').length) {
    $('head').append(`
      <style id="slide-animations">
        @keyframes slideOutDown {
          to {
            transform: translateY(100%);
            opacity: 0;
          }
        }
        @keyframes slideInRight {
          from {
            transform: translateX(100%);
            opacity: 0;
          }
          to {
            transform: translateX(0);
            opacity: 1;
          }
        }
        @keyframes slideOutRight {
          to {
            transform: translateX(100%);
            opacity: 0;
          }
        }
      </style>
    `);
  }

  // ===== Enhanced Dropdown Animations =====
  $('.dropdown').on('show.bs.dropdown', function() {
    $(this).find('.dropdown-menu').addClass('show-dropdown');
  });

  // ===== Prevent Animation on Page Unload =====
  $(window).on('beforeunload', function() {
    $('body').addClass('page-unloading');
  });

  // ===== Initialize Tooltips with Animation =====
  if (typeof $.fn.tooltip !== 'undefined') {
    $('[data-toggle="tooltip"]').tooltip({
      animation: true,
      delay: { show: 200, hide: 100 }
    });
  }

  // ===== Initialize Popovers with Animation =====
  if (typeof $.fn.popover !== 'undefined') {
    $('[data-toggle="popover"]').popover({
      animation: true,
      trigger: 'hover focus'
    });
  }

  // ===== Enhance ScrollToTop Button =====
  $('.scrollToTop').on('click', function(e) {
    e.preventDefault();
    $('html, body').animate({
      scrollTop: 0
    }, 800, 'swing');
  });

  // ===== Add Hover Sound Effect (Optional - disabled by default) =====
  // Uncomment to enable subtle sound feedback
  /*
  const hoverSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIHWm98OScT); 
  $('.btn, .dashbord-item, .service-item').on('mouseenter', function() {
    hoverSound.play();
  });
  */

})(jQuery);

// ===== Document Ready =====
$(document).ready(function() {
  // Trigger initial animations
  setTimeout(function() {
    $('.banner-content, .banner-thumb-area').addClass('animated');
  }, 100);

  // Add floating animation to specific icons
  $('.dashboard-icon i').addClass('float-animation');

  // Initialize any custom animations
  console.log('Enhanced interactions initialized successfully');
});
