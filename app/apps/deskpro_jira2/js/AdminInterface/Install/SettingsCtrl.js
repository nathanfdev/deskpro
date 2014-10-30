define(['DeskPRO/Util/Strings'], function(Strings) {
	return ['$scope', 'Api', '$q', '$modal', function($scope, Api, $q, $modal) {

		$scope.enableCustomFooter();
		$scope.has_errors = false;
		$scope.errors = {};


		// do a bg check for curl support
		//Api.sendGet('/apps/packages/deskpro_jira/check-requirements').then(function(res) {
		//	if (!res.data.curl_support) {
		//		$scope.no_curl_support = true;
		//	}
		//});

		$scope.setPresaveCallback(function() {
			var deferred = $q.defer();

			//sanitize url
			var a = document.createElement('a');
			a.href = $scope.setting_values.url;
			$scope.setting_values.url = a.href;

			deferred.resolve();

			return deferred.promise;
		});

	}];
});