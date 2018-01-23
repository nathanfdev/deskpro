Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Used on tabs with RTE editors to accent links from listings to insert links to articels etc
 */
DeskPRO.Agent.PageHelper.AcceptContentLink = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(options) {
		var self = this;
		this.options = {
			/**
			 * The page fragment
			 */
			page: null,

			/**
			 * The TinyMCE editor to add link to
			 */
			rte: null,

			/**
			 * Called to check if the tab is ready to accept. For example,
			 * in view pages, the editor must be activated first.
			 *
			 * @return {Boolean}
			 */
			isReadyCallback: null
		};

		this.setOptions(options);
	},

	isReady: function() {
		if (this.options.isReadyCallback) {
			return this.options.isReadyCallback();
		}

		return true;
	},

	sendLink: function(linkTitle, url) {
		var tiny = this.options.rte.tinymce();
		if (tiny) {
			var title = tiny.selection.getContent({ format: 'text' });
			if (title) title = title.trim();
			if (!title.length) {
				title = linkTitle;
			}

			tiny.selection.setContent('<a href="' + url + '">' + Orb.escapeHtml(title) + '</a>');
		} else {
      var title = this.options.rte.froalaEditor('selection.text');

      if (!title.length) {
        title = linkTitle;
      }

      this.options.rte.froalaEditor('html.insert', '<a href="' + url + '">' + Orb.escapeHtml(title) + '</a>', true);
		}

	}
});
