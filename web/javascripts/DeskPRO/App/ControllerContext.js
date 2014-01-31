define(function() {
	var ControllerContext = function(parentContext, instanceInfo, controllerClass) {

		var controller;

		this._dp_init = function() {
			controller = new controllerClass(this);
		};

		this.getApp = function() {
			return parentContext;
		};

		this.getAppController = function() {
			return parentContext.getController();
		}

		this.getController = function() {
			return controller;
		}
	};

	return ControllerContext;
});