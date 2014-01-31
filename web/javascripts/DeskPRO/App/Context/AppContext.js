define([
	'DeskPRO/App/Context/TabContext/TicketTabContext'
], function(
	TicketTabContext
) {
	var AppContext = new Orb.Class({
		initialize: function(contextParams) {
			this._appId          = contextParams.appId;
			this._packageName    = contextParams.packageName;
			this._scopeName      = contextParams.scope;
			this._settings       = contextParams.settings;
			this._regControllers = {};
			this.init();
		},

		init: function() {

		},

		_dp_init: function() {
			this.init();
		},

		run: function() {

		},

		getAppId: function() {
			return this._appId;
		},

		getPackageName: function() {
			return this._packageName;
		},

		getScopeName: function() {
			return this._scopeName;
		},

		getSetting: function(name, defaultValue) {
			if (typeof this._settings[name] == 'undefined') {
				return defaultValue;
			}
			return this._settings[name];
		},

		registerController: function(type, controller, params) {
			var baseClass;

			if (typeof controller != 'function') {
				switch (type) {
					case 'ticket':
						baseClass = TicketTabContext;
						break;
				}

				if (baseClass) {
					controller.Extends = baseClass;
					controller = Orb.Class(controller)
				}
			}

			if (!this._regControllers[type]) {
				this._regControllers[type] = [];
			}

			this._regControllers[type].push([controller, params || null]);
		},

		createFragmentContexts: function(frag) {
			var i, contextParams, ctrl, created = [], type = frag.TYPENAME;

			if (!this._regControllers[type]) {
				return created;
			}

			contextParams = {
				appContext: this,
				fragment: frag
			};

			for (i = 0; i < this._regControllers[type].length; i++) {
				// 0 = class, 1 = params
				ctrl = new this._regControllers[type][i][0](
					contextParams,
					this._regControllers[type][i][1]
				);

				created.push(ctrl);
			}

			return created;
		}
	});

	return AppContext;
});