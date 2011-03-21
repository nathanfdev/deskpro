Orb.createNamespace('DeskPRO.Agent.WindowElement');

/**
 * The main menu can have a slider which "slides" content from the main section into sub-sections.
 * This is done by using a wide container whose overflow is hidden, and then we use Javascript to
 * natively scroll content into place.
 */
DeskPRO.Agent.WindowElement.MainMenuSlider = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {

		this.openSlide = null;
		this.inAnim = false;

		this.options = {
			menuLi: null
		};

		this.setOptions(options);

		this.menuLi = this.options.menuLi;
		this.wrap = $('> .wrap-dropdown:first', this.menuLi);
		this.track = $('> div.x-track:first', this.wrap);
		this.main = $('> div:first', this.track);

		// Automatically set up triggers
		var self = this;
		this.initSliderTrigger($('.slider-trigger', this.menuLi));
	},

	initSliderTrigger: function(el) {

		var self = this;
		el.click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var toel = $('#' + $(this).data('slide-to'));

			self.view(toel);
		});
	},

	viewMain: function() {

		this.wrap.css({
			'width': '',
			'height': '',
			'overflow': ''
		});

		this.main.css({
			'width': '',
			'height': '',
			'overflow': '',
			'float': ''
		});

		// Hide all
		$(' > div:not(#' + this.main.attr('id') + ')', this.track).css({
			'width': '',
			'height': '',
			'overflow': '',
			'float': '',
			'display': 'none'
		});

		this.wrap.scrollLeft(0);
		this.track.css('width', '');

		this.openSlide = null;
		this.inAnim = false;

		this.fireEvent('view', [this.main, this]);
	},

	view: function(slide) {

		if (this.inAnim) {
			return;
		}

		if (this.openSlide && slide.attr('id') == this.openSlide.attr('id')) {
			return;
		}

		if (slide.attr('id') == this.main.attr('id')) {
			this.viewMain();
			return;
		}

		if (this.openSlide) {
			this.viewMain();// reset
		}

		this.openSlide = slide;

		this.fireEvent('preBeforeView', [slide, this]);

		this.wrap.css({
			'width': this.wrap.width(),
			'height': this.wrap.height(),
			'overflow': 'hidden'
		});

		this.main.css({
			'width': this.main.width(),
			'height': this.main.height(),
			'overflow': 'hidden'
		});

		this.track.css({
			'width': (this.main.width()*10) + 100
		});

		slide.css({
			'width': this.main.width(),
			'height': this.main.height(),
			'overflow': 'hidden',
			'display': 'block'
		});

		this.main.css('float', 'left');
		slide.css('float', 'left');

		this.fireEvent('beforeView', [slide, this]);

		var pos = slide.position().left;

		this.wrap.scrollLeft(0);

		this.inAnim = true;
		var self = this;
		this.wrap.animate(
			{ scrollLeft: pos },
			200,
			'linear',
			function() {
				self.inAnim = false;
				self.fireEvent('view', [slide, this]);
			}
		);

		this.fireEvent('duringSlideView', [slide, this]);
	}
});