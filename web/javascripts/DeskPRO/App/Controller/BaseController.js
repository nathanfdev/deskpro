define(function() {
	function BaseController(context) {}

	BaseController.prototype._initContext = function(context) {
		this._appInstance = context.appInstance;
	};

	BaseController.prototype.init = function() { };

	/**
	 * Gets the app instance
	 * @return {BaseAppPackage}
	 */
	BaseController.prototype.getApp = function() {
		return this._appInstance;
	};

	return BaseController;
});