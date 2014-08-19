define(['DeskPRO/Util/Strings'], function(Strings) {
	return ['$scope', 'Api', '$q', '$modal', function($scope, Api, $q, $modal) {
		var touched = {},
			validateFn = [];

		$scope.enableCustomFooter();
		$scope.has_errors = false;
		$scope.errors = {};

		// do a bg check for soap support
		Api.sendGet('/apps/packages/deskpro_jira/check-requirements').then(function(res) {
			if (!res.data.curl_support) {
				$scope.no_curl_support = true;
			}
		});

		//##############################################################################################################
		//# Form validation / errors
		//##############################################################################################################

		['jira_url', 'jira_username', 'jira_password'].forEach(function(field) {
			$scope.$watch('setting_values.' + field, function() {
				if (touched[field] || touched.always || $scope.errors[field]) {
					updateFormErrors();
				}
			});

			validateFn.push([field, function() {
				return ($scope.setting_values[field] && Strings.trim($scope.setting_values[field]));
			}]);
		});

		function updateFormErrors() {
			$scope.has_errors = false;
			$scope.errors = {};

			validateFn.forEach(function(val) {
				if (!val[1]()) {
					$scope.has_errors = true;
					$scope.errors[val[0]] = true;
				}
			});

			return $scope.has_errors;
		};

		$scope.setPresaveCallback(function() {
			var deferred = $q.defer();
			updateFormErrors();

			if ($scope.has_errors) {
				deferred.reject();
			} else {
				// sanitize url
				var a = document.createElement('a');
				a.href = $scope.setting_values.jira_url;
				$scope.setting_values.jira_url = a.href;

				deferred.resolve();
			}

			return deferred.promise;
		});
	}];
});