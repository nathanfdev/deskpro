define(['angular'], function(angular) {
	var AgentApp = angular.module('AgentApp', []);

	//-------------------------------------------------------------------------
	// dpAppAssetInterceptor
	//-------------------------------------------------------------------------

	// The asset interceptor re-writes the path to app assets (mainly for templates)
	// For example, in source, apps would reference a template file like:
	// <div ng-include="com.deskpro.apps.test/html/some-template.html"></div>
	// But that file obviously doesn't exist. We use the interceptor to rewrite it
	// to the real file.php/xxx/some-template.html file.

	AgentApp.factory('dpAppAssetInterceptor', [function() {
		return  {
			request: function(config) {
				var assetPath = window.AppPlatform.getAssetPath(config.url);
				if (assetPath) {
					console.log("[dpAppAssetInterceptor] %s -> %s", config.url, assetPath);
					config.url = assetPath;
					config.dpIsAppAsset = true;
				}

				return config;
			}
		};
	}]);

	AgentApp.config(['$httpProvider', function($httpProvider) {
		$httpProvider.interceptors.push('dpAppAssetInterceptor');
	}]);

	return AgentApp;
});