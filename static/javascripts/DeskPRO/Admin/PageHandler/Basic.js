Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.Basic = new Class({

	Implements: [Events],

	meta: {},

	initPage: function() {

	},

	/**
	 * Init all triggers to become iframe overlays.
	 *
	 * @param context
	 */
	initPopoutTriggers: function(context) {
		if (!context) context = $(document);

		var self = this;
		$('.popout-trigger', context).click(function(ev) {
			var el = $(this);
			ev.preventDefault();

			var url = el.attr('href');
			if (!url) url = el.data('href');

			var maxHeight = 700;
			var maxWidth = 900;

			if (el.data('width')) maxWidth = el.data('width');
			if (el.data('height')) maxHeight = el.data('height');

			var overlay = new DeskPRO.UI.Overlay({
				contentMethod: 'iframe',
				iframeUrl: url,
				destroyOnClose: true,
				maxWidth: maxWidth,
				maxHeight: maxHeight
			});
			overlay.openOverlay();

			overlay.addEvent('destroyed', function() { delete overlay; self._openedOverlay = null; });

			self._openedOverlay = overlay;
		});
	},
	_openedOverlay: null,

	/**
	 * Set metadata about this page.
	 *
	 * @param mixed name Either a string name to use with value, or an object of key/value pairs
	 * @param mixed value Only used if name is a string, the value to set
	 */
	setMetaData: function(name, value) {
		// Assigning multiple values from a hash
		if (value === undefined && typeOf(name) == 'object') {
			this.meta = Object.merge(this.meta, name);
		} else {
			this.meta[name] = value;
		}
	},



	/**
	 * Get a hash of all the metadata.
	 *
	 * @return {Object}
	 */
	getAllMetaData: function() {
		return this.meta;
	},



	/**
	 * Get a specific piece of metadata.
	 *
	 * @param {String} name The name of the data you want
	 * @param mixed default_value The value to return if the metadata is undefined
	 */
	getMetaData: function(name, default_value) {
		if (default_value === undefined) {
			default_value = null;
		}

		if (this.meta[name] === undefined) {
			return default_value;
		}

		return this.meta[name];
	},



	handleListChange: function(info) {
		var list = $('ul.item-list:first');
		var exist = $('li.'+info.typename+'-'+info[info.typename+'_id']);

		var row = $(info.row_html);
		this.initPopoutTriggers(row);

		if (exist.length) {
			exist.replaceWith(row);
		} else {
			list.prepend(row);
		}
	},



	/**
	 * Get the parent windows DeskPRO_Window object. Used for when the child
	 * needs to send a message back to the parent, such as if something needs to be updated.
	 */
	getOpenerDeskPRO: function() {
		var parent_win = null;
		if (window.opener && window.opener.DeskPRO_Window) {
			parent_win = window.opener.DeskPRO_Window;
		} else if (window.parent && window.parent.DeskPRO_Window) {
			parent_win = window.parent.DeskPRO_Window;
		}

		return parent_win;
	},


	/**
	 * Sends a message through to the parent window to close this popout.
	 */
	closeThisPopout: function() {
		if (this.getThisOverlay()) {
			this.getThisOverlay().closeOverlay();
		}
	},


	getThisOverlay: function() {
		if (window.parent && window.parent.DeskPRO_Page && window.parent.DeskPRO_Page._openedOverlay) {
			return window.parent.DeskPRO_Page._openedOverlay;
		}

		return null;
	}
});