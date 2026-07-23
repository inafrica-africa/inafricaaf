/**
 * WEBSITE: https://themefisher.com
 * TWITTER: https://twitter.com/themefisher
 * FACEBOOK: https://www.facebook.com/themefisher
 * GITHUB: https://github.com/themefisher/
 */

(function ($) {
	'use strict';

	// Preloader js    
	$(window).on('load', function () {
		$('.preloader').fadeOut(700);
	});

	// Sticky Menu
	$(window).scroll(function () {
		if ($(window).scrollTop() > 10) {
			$('.top-header').addClass('hide');
			$('.navigation').addClass('nav-bg');
		} else {
			$('.top-header').removeClass('hide');
			$('.navigation').removeClass('nav-bg');
		}
	});

	// Measures the real, current height of the fixed header and applies it as
	// padding-top on any element that needs to clear it, instead of a guessed
	// pixel value that breaks every time the header's own content changes.
	function syncFixedHeaderOffset() {
		var $header = $('header.fixed-top');
		if (!$header.length) { return; }
		var wasHidden = $('.top-header').hasClass('hide');
		if (wasHidden) { $('.top-header').removeClass('hide'); }
		var headerHeight = $header.outerHeight();
		if (wasHidden) { $('.top-header').addClass('hide'); }
		$('.below-fixed-header').css('padding-top', headerHeight + 'px');
	}
	$(window).on('load resize', syncFixedHeaderOffset);
	syncFixedHeaderOffset();
	// navbarDropdown
	if ($(window).width() < 992) {
		$('.navigation .dropdown-toggle').on('click', function () {
			$(this).siblings('.dropdown-menu').animate({
				height: 'toggle'
			}, 300);
		});
	}

	// Background-images
	$('[data-background]').each(function () {
		$(this).css({
			'background-image': 'url(' + $(this).data('background') + ')'
		});
	});

	//Hero Slider
	$('.hero-slider').slick({
		autoplay: true,
		autoplaySpeed: 7500,
		pauseOnFocus: false,
		pauseOnHover: false,
		infinite: true,
		arrows: true,
		fade: true,
		prevArrow: '<button type=\'button\' class=\'prevArrow\'><i class=\'ti-angle-left\'></i></button>',
		nextArrow: '<button type=\'button\' class=\'nextArrow\'><i class=\'ti-angle-right\'></i></button>',
		dots: true
	});
	$('.hero-slider').slickAnimation();

	// venobox popup
	$(document).ready(function () {
		$('.venobox').venobox();
	});


	// filter
	$(document).ready(function () {
		var containerEl = document.querySelector('.filtr-container');
		var filterizd;
		if (containerEl) {
			filterizd = $('.filtr-container').filterizr({});
		}
		//Active changer
		$('.filter-controls li').on('click', function () {
			$('.filter-controls li').removeClass('active');
			$(this).addClass('active');
		});
	});

	//  Count Up
	function counter() {
		var oTop;
		if ($('.count').length !== 0) {
			oTop = $('.count').offset().top - window.innerHeight;
		}
		if ($(window).scrollTop() > oTop) {
			$('.count').each(function () {
				var $this = $(this),
					countTo = $this.attr('data-count');
				$({
					countNum: $this.text()
				}).animate({
					countNum: countTo
				}, {
					duration: 1000,
					easing: 'swing',
					step: function () {
						$this.text(Math.floor(this.countNum));
					},
					complete: function () {
						$this.text(this.countNum);
					}
				});
			});
		}
	}
	$(window).on('scroll', function () {
		counter();
	});

})(jQuery);

  document.querySelector('a[href="#top-header"]').addEventListener('click', function(e) {
    e.preventDefault();
    const target = document.querySelector('#top-header');
    const offset = 100; // adjust this number based on your navbar height
    const position = target.offsetTop - offset;

    window.scrollTo({
      top: position,
      behavior: 'smooth'
    });
  });

