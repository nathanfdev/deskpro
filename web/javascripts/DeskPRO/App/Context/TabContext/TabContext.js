define(function() {
	var TabContext = new Orb.Class({
		initialize: function(contextParams, params) {
			this._appContext  = contextParams.appContext;
			this._frag        = contextParams.fragment;
			this._params      = params || {};
			this.init();
		},

		init: function() {

		},

		getApp: function() {
			return this._appContext;
		},

		getFragment: function() {
			return this._fragment;
		},

		getFragmentElement: function() {
			return this._fragment.wrapper || this._fragment.el;
		},

		getParameter: function(name, defaultValue) {
			if (typeof this._params.packageName[name] == 'undefined') {
				return defaultValue;
			}
			return this._params.packageName[name];
		}
	});

	return TabContext;
});