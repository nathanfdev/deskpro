Orb.createNamespace('DeskPRO.Agent.Layout');

DeskPRO.Agent.Layout.FooterLayout = Orb.Class({
	Implements: [Orb.Util.Events],

	initialize: function(wrapper) {
		this.paneWrapper = wrapper.parent();
		this.wrapper = wrapper;
		this.wrapper.addClass('has-layout').data('layout', this);

		this.content  = $('div.layout-content:first', this.wrapper);
		this.footer = $('div.layout-footer:first', this.wrapper);

		var parentLayout = this.paneWrapper.closest('.has-layout');
		if (parentLayout.length) {
			parentLayout.data('layout').addEvent('resized', this.doLayout.bind(this));
		}

		this.doLayout();
	},

	doLayout: function() {

		var w = this.paneWrapper.width();
		var h = this.paneWrapper.height();

		if (this.footer.is('.no-expander')) {
			var foot_height = 166;
		} else if (this.footer.is('.is-ticket-list')) {
			if (this.isFooterOpen) {
				var foot_height = 200;
			} else {
				var foot_height = 33;
			}
		} else {

			var foot_height = 26;
			if ($('.tab-bottom', this.footer).length) {
				foot_height = 33;
				if (this.isFooterOpen) {
					foot_height = 198;
				}
			}
		}
		this.footer.css({
			height: foot_height,
			width: w,
			overflow: 'hidden'
		});

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