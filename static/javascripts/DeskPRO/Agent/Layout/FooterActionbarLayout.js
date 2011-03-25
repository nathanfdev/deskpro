Orb.createNamespace('DeskPRO.Agent.Layout');

DeskPRO.Agent.Layout.FooterActionbarLayout = Orb.Class({
	Implements: [Orb.Util.Events],

	initialize: function(wrapper) {
		this.paneWrapper = wrapper.parent();
		this.wrapper = wrapper;
		this.wrapper.addClass('has-layout').data('layout', this);

		this.content  = $('div.layout-content:first', this.wrapper);
		this.footer = $('div.layout-footer:first', this.wrapper);

		this.isFooterOpen = false;

		var parentLayout = this.paneWrapper.closest('.has-layout');
		if (parentLayout.length) {
			parentLayout.data('layout').addEvent('resized', this.doLayout.bind(this));
		}

		this.doLayout();
	},

	doLayout: function() {

		var w = this.paneWrapper.width();
		var h = this.paneWrapper.height();

		if (this.isFooterOpen) {
			var foot_height = 33;

			this.footer.css({
				width: w,
				height: foot_height,
				overflow: 'hidden'
			});

			if (!this.footer.is('.expanded')) {
				//this.footer.slideDown(300);
				this.footer.show();
			}

			this.footer.addClass('expanded');
		} else {
			var foot_height = 0;

			this.footer.css({
				width: w,
				overflow: 'hidden',
				display: 'none'
			});

			this.footer.removeClass('expanded');
		}

		this.content.css({
			height: h-foot_height,
			width: w,
			overflow: 'auto'
		});

		this.fireEvent('resized', [this]);
	},

	expandFooter: function() {
		this.isFooterOpen = true;
		this.doLayout();
	},

	collapseFooter: function() {
		this.isFooterOpen = false;
		this.doLayout();
	}
});