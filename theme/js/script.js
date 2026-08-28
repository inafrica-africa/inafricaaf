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
	// Covers every section that sits directly under the fixed header
	// (below-fixed-header on news-details.php, page-title-section on
	// about/contact/documents/donate/events/gallery/region.php, hero-section
	// on the homepage) so none of them can end up hidden underneath it.
	function syncFixedHeaderOffset() {
		var $header = $('header.fixed-top');
		if (!$header.length) { return; }
		var wasHidden = $('.top-header').hasClass('hide');
		if (wasHidden) { $('.top-header').removeClass('hide'); }
		var headerHeight = $header.outerHeight();
		if (wasHidden) { $('.top-header').addClass('hide'); }
		// +8px safety buffer: on slower connections the webfont/ad images can
		// finish loading (and reflow the header a little taller) after this
		// first measurement runs.
		$('.below-fixed-header, .page-title-section, .hero-section').css('padding-top', (headerHeight + 8) + 'px');
	}
	$(window).on('load resize orientationchange', syncFixedHeaderOffset);
	syncFixedHeaderOffset();
	// Re-measure a bit later too, in case a slow webfont or ad image swap
	// changed the header's real height after the initial 'load'-time pass.
	setTimeout(syncFixedHeaderOffset, 600);
	setTimeout(syncFixedHeaderOffset, 1800);
	if (document.fonts && document.fonts.ready) {
		document.fonts.ready.then(syncFixedHeaderOffset);
	}
	// Region/Category dropdowns on mobile already open/close correctly via
	// Bootstrap's own dropdown-toggle handler (data-toggle="dropdown" in
	// header.php). A second jQuery height-toggle animation bound to the same
	// click used to fight that handler for control of the menu's
	// show/hide state, leaving a stale inline height from the last animation
	// that could clip the menu open on the next tap.

	// Background-images
	$('[data-background]').each(function () {
		$(this).css({
			'background-image': 'url(' + $(this).data('background') + ')'
		});
	});

	//Hero Slider (guarded: news-details.php/subcategory.php load this script
	// without slick.min.js since they have no hero-slider on the page, and
	// calling a plugin method that was never registered throws)
	//
	// Initialized per-instance (not with one .slick() call across the whole
	// .hero-slider selector) because this class matches both the quotes
	// slider and the unrelated "Recent Updates" slider, and each needs its
	// own slide count checked: with only one slide, autoplay+infinite still
	// re-triggers the fade transition to that same slide forever, which
	// reads as a pointless flicker rather than "no sliding" when there's
	// nothing to slide to.
	if (typeof $.fn.slick === 'function') {
		$('.hero-slider').each(function () {
			var $slider = $(this);
			var slideCount = $slider.children('.hero-slider-item').length;
			if (!slideCount) {
				return;
			}
			var hasMultiple = slideCount > 1;
			$slider.slick({
				autoplay: hasMultiple,
				autoplaySpeed: 7500,
				pauseOnFocus: false,
				pauseOnHover: false,
				infinite: hasMultiple,
				arrows: false, // autoplay + swipe already move the slide; arrows aren't needed
				fade: true,
				dots: hasMultiple
			});
			// slickAnimation (bundled in slick.min.js) fades each
			// [data-animation-in] element (photo/year/quote/attribution)
			// OUT a few seconds after it first appears, and only fades it
			// back IN on Slick's "afterChange" event — i.e. on the next
			// slide change. With only one slide, autoplay above is
			// correctly off, so that event never fires again: the content
			// fades out once, permanently, and never returns. Skip the
			// plugin entirely when there's nothing to slide to, so
			// single-slide content just stays visible with no fade at all.
			if (hasMultiple) {
				$slider.slickAnimation();
			}
		});
	}

	// venobox popup
	$(document).ready(function () {
		if (typeof $.fn.venobox === 'function') {
			$('.venobox').venobox();
		}
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

  const topHeaderLink = document.querySelector('a[href="#top-header"]');
  if (topHeaderLink) {
    topHeaderLink.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector('#top-header');
      const offset = 100; // adjust this number based on your navbar height
      const position = target.offsetTop - offset;

      window.scrollTo({
        top: position,
        behavior: 'smooth'
      });
    });
  }

