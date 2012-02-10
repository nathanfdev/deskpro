Orb.createNamespace('DeskPRO.Agent.WindowElement');

/**
 * The tabbar handles adding and removing tabs in the right pane of the window.
 */
DeskPRO.Agent.WindowElement.TabBarOverflow = new Orb.Class({
	initialize: function() {
		var self = this;
		this.tabList     = $('#tabNavigationPane ul.dp-tab-list');
		this.tabPane     = $('#tabNavigationPane');
		this.goLeft      = $('#tabNavSelectorLeft');
		this.goRight     = $('#tabNavSelectorRight');
		this.menuBtn     = $('#tabDropdownPicker');
		this.scrollable  = $('#tabNavigationPane > .deskproTabList');

		// Padding to the left side of the tabs
		this.padLeft = 10;

		// Padding to the left side when theres the nav control
		// This is room so you see the control and it doesnt overlap the tab
		this.padLeftCtrl = 24;

		this.padRight = 0;
		this.padRightCtrl = 24; // More than left because theres the down btn too

		this.goLeft.on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.scrollLeft();
		});
		this.goRight.on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.scrollRight();
		});

		this.overflowEnabled = false;
	},


	/**
	 * Update the overflow
	 */
	update: function() {
		if (this.isOverflowRequired()) {
			this.enableOverflow();
		} else {
			this.disableOverflow();
		}
	},


	/**
	 * Check if overflow controls are required right now
	 */
	isOverflowRequired: function() {
		var paneW = this.tabPane.width();
		var tabW = this.tabList.width() + this.padLeft + this.padRightCtrl;

		if (tabW > paneW) {
			return true;
		} else {
			return false;
		}
	},

	/**
	 * Enable the overflow controls
	 */
	enableOverflow: function() {
		this.tabPane.addClass('with-overflow');

		this.maxScroll = this.tabList.width() - this.tabPane.width();
		this.maxScroll += 9 + this.padLeft + this.padRightCtrl;

		if (this.overflowEnabled) {
			// If its already enabled then we should check
			// if a tab was removed, so we dont have empty space to the right
			if (this.scrollable.scrollLeft() > this.maxScroll) {
				this.scrollable.scrollLeft(this.maxScroll);
			}
		}

		this.overflowEnabled = true;
	},

	/**
	 *
	 */
	disableOverflow: function() {
		this.scrollable.scrollLeft(0);
		this.tabPane.removeClass('with-overflow');
		this.overflowEnabled = false;
	},


	/**
	 * Scroll to the left
	 *
	 * @param {Integer} [amount]
	 */
	scrollLeft: function(amount) {
		if (!amount) amount = 200;

		this.scrollable.animate({scrollLeft: '-=' + amount }, 200);
	},


	/**
	 * Scroll to the left
	 *
	 * @param {Integer} [amount]
	 */
	scrollRight: function(amount) {
		if (!amount) amount = 200;

		var current = this.scrollable.scrollLeft();
		if ((current+amount) > this.maxScroll) {
			amount = this.maxScroll - current;
		}

		this.scrollable.animate({scrollLeft: '+=' + amount }, 200);
	}
});
