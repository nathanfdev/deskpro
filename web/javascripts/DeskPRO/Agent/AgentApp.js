Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.AgentAppFactory = function() {
	var AgentApp = angular.module('AgentApp', ['ngAnimate']);

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
				} else {
					config.url = config.url.replace(/DP_URL\//g, window.BASE_URL.replace(/\/+$/, '')+'/')
				}

				return config;
			}
		};
	}]);

	AgentApp.filter('formatTimestampAgo', function() {
		return function(ts) {
			return moment().unix(ts).fromNow();
		}
	});

	AgentApp.filter('formatTimestamp', function() {
		return function(ts, format) {
			if (!format) {
				format = 'day';
			}
			switch (format) {
				case 'day':
					format = 'MMM D YYYY';
					break;
				case 'day_short':
					format = 'MMM D';
					break;
				case 'time':
					format = 'h:m a'
					break;
			}
			return moment().unix(ts).format(format);
		}
	});

	AgentApp.config(['$httpProvider', function($httpProvider) {
		$httpProvider.interceptors.push('dpAppAssetInterceptor');
	}]);

	return AgentApp;
};