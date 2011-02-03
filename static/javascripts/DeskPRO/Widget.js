Orb.createNamespace('DeskPRO');

DeskPRO.Widget = new Class({

	Implements: [Events, Options],

	widgetEl: null,
	options: {
		// Path to the root DeskPRO web dir without trailing slash
		deskproPath: '',

		// Proxy key for this user/session
		proxyKey: 'key',

		// The current persons ID
		personId: 0,

		// This widgets unique ID
		widgetId: 0,

		// This widgets system name.
		// This name should uniquely identify a specific type
		// of widget, whereas the ID above is the database ID.
		widgetNameId: 'widget'
	},

	userPrefs: {},

	initialize: function(options) {
		if (options) this.setOptions(options);
		this.init();

		this.addEvent('widgetReady', this.initWidget.bind(this));
	},


	/**
	 * Called when the DOM is ready and the widget is ready to be activated.
	 * el is the widget wrapper element.
	 *
	 * @param el
	 */
	setWidgetElement: function(el) {
		this.widgetEl = $(el);
		this.fireEvent('widgetReady');
	},


	/**
	 * Get the widget element wrapper
	 *
	 * @return jQuery
	 */
	getWidgetElement: function() {
		return this.widgetEl;
	},


	/**
	 * Hook method to initialize the object. This is called during
	 * initialization, the widget/DOM is not ready yet.
	 */
	init: function() {

	},


	/**
	 * Hook method to initialize the UI. This is caleld when the DOM is
	 * ready, and the widget is ready for interactions.
	 */
	initWidget: function() {

	},


	/**
	 * Just like jQuery.ajax() except the URL is modified to use the server proxy.
	 * If options specifies JSONP, then nothing changes.
	 *
	 * @param options
	 */
	remoteAjax: function(options) {

		if (options.dataType && options.dataType != 'jsonp') {
			var url = escape(options.url);
			options.url = this.options.deskproPath + '/proxy/' + this.options.proxyKey + '?url=' + url;
		}

		return $.ajax(options);
	},


   /**
	* Sets user preferences that are persisted and passed back to this widget
	* next time.
	*
	* @param name
	* @param value
	*/
	setUserPref: function(name, value) {
		this.userPrefs[name] = value;

		if (this._persistUserPref_timeout) {
			window.clearTimeout(this._persistUserPref_timeout);
		}
		this._persistUserPref_timeout = this._persistUserPref().delay(250, this);
	},

	_persistUserPref_timeout: null,
	_persistUserPref: function() {

	},


   /**
	* Get a user preference.
	*
	* @param name
	* @param default_value
	* @return mixed
	*/
	getUserPref: function(name, default_value) {
		if (typeof this.userPrefs[name] === undefined) {
			return default_value;
		}

		return this.userPrefs[name];
	},



   /**
	* Create a window message listener. This can be useful if you need to pop open a new window, or use an iframe,
	* and you need to easily pass around an ID used to pass a message back to this specific widget.
	*
	* <code>
	* var message_id = this.waitForMessage(this.handleMyMessage);
	* var pop = window.open('http://example.com/myscript?message_id=' + message_id);
	*
	* // On your own pages
	* // Pass around message_id to any subsequent page loads etc, then when you're ready
	* if (window.opener && window.opener.DeskPRO_Window) {
	*      window.opener.DeskPRO_Window.getMessageBroker().sendMessage(message_id, {my: data});
	* }
	* </code>
	*
	* @param callback
	*/
	openMessagePath: function(callback) {
		var uid = Orb.uuid();
		var id = 'widgetmsg.' + this.options.widgetNameId + '.' + this.options.widgetId + '.' + uid;

		if (!DeskPRO_Window || !DeskPRO_Window.getMessageBroker || DeskPRO_Window.getMessageBroker()) {
			return false;
		}

		DeskPRO_Window.getMessageBroker().addMessageListener(id, callback.bind(this));

		return id;
	}
});