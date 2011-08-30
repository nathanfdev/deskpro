Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * This handles the listing page of content that can be linked with others
 */
DeskPRO.Agent.PageHelper.RelatedContentList = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;
		this.page = page;

		this.options = {
			/**
			 * The main list element that contains rows.
			 */
			contentListEl: null
		};

		this.setOptions(options);

		this.contentListEl = $(this.options.contentListEl);

		this.addEvent('watchedTabActivated', this.enableControls.bind(this));
		this.addEvent('watchedTabDeactivated', this.disableControls.bind(this));

		var selectedTabType = DeskPRO_Window.getTabWatcher().getActiveTabType();
		var doEnable = false;

		var types = ['article', 'download', 'news', 'idea'];
		Array.each(types, function(t) {
			DeskPRO_Window.getTabWatcher().addTabTypeWatcher(t, this);

			// Check currently selected tab too,  might need to activate right now
			if (selectedTabType == t) {
				doEnable = true;
			}
		}, this);

		if (doEnable) {
			this.enableControls(DeskPRO_Window.getTabWatcher().getActiveTab());
		}

		var findLinkable = function(el) {
			var x = 0;
			while (!el.is('.related-is-linkable')) {
				if (x++ > 15) return null;
				el = el.parent();
			}

			return el;
		};

		this.contentListEl.delegate('.related-link', 'click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			var el = findLinkable($(this));
			if (!el) {
				return;
			}

			self.fireEvent('relatedLinkClick', [el, $(this), this]);

			var tab = DeskPRO_Window.getTabWatcher().getActiveTab();
			if (!tab || !tab.page || !tab.page.relatedContent) {
				return;
			}

			tab.page.relatedContent.addLinkByElement(el);
		});

		this.contentListEl.delegate('.related-unlink', 'click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			var el = findLinkable($(this));
			if (!el) {
				return;
			}

			self.fireEvent('relatedUnlinkClick', [el, $(this), this]);

			var tab = DeskPRO_Window.getTabWatcher().getActiveTab();
			if (!tab || !tab.page || !tab.page.relatedContent) {
				return;
			}

			tab.page.relatedContent.removeLinkByElement(el);
		});
	},

	enableControls: function(tab) {
		var page = tab.page;
		if (!page.relatedContent) {
			return;
		}

		this.activePage = page;

		page.relatedContent.setActiveRelatedListController(this);

		var self = this;
		$('.related-is-linkable', this.contentListEl).each(function() {
			var el = $(this);
			el.removeClass('related-not-linkable').removeClass('related-is-linked');

			if (page.relatedContent.elementIsLinkable(el)) {
				if (page.relatedContent.elementIsLinked(el)) {
					el.addClass('related-is-linked');
				}
			} else {
				el.addClass('related-not-linkable');
			}
		});

		this.contentListEl.addClass('with-related-content-controls');

		this.fireEvent('relatedControlsActivated', [this.contentListEl, this]);
	},

	disableControls: function() {
		this.activePage = null;
		this.contentListEl.removeClass('with-related-content-controls');
		this.fireEvent('relatedControlsDeactivated', [this.contentListEl, this]);
	},

	elementIsLinked: function(typename, content_id) {
		$('.' + typename + '-' + content_id + '.related-is-linkable', this.contentListEl).addClass('related-is-linked');
	},

	elementIsUnlinked: function(typename, content_id) {
		$('.' + typename + '-' + content_id + '.related-is-linkable', this.contentListEl).removeClass('related-is-linked');
	},

	destroy: function() {
		if (this.activePage) {
			this.activePage.relatedContent.setActiveRelatedListController(null);
		}
	}
});
