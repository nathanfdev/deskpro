define(['DeskPRO/Util/Strings', 'DeskPRO/Util/Util'], function(Strings, Util) {
	return ['$scope', 'Api', '$q', '$modal', function($scope, Api, $q, $modal) {
		var touched = {},
			validateFn = [];

		$scope.enableCustomFooter();
		$scope.has_errors = false;
		$scope.errors = {};

		//##############################################################################################################
		//# Form validation / errors
		//##############################################################################################################

		['cert_fingerprint', 'sso_url'].forEach(function (field) {
			$scope.$watch('setting_values.' + field, function () {
				if (touched[field] || touched.always || $scope.errors[field]) {
					updateFormErrors();
				}
			});

			validateFn.push([field, function () {
				return ($scope.setting_values[field] && Strings.trim($scope.setting_values[field]));
			}]);
		});

		function updateFormErrors() {
			$scope.has_errors = false;
			$scope.errors = {};

			validateFn.forEach(function (val) {
				if (!val[1]()) {
					$scope.has_errors = true;
					$scope.errors[val[0]] = true;
				}
			});

			return $scope.has_errors;
		}

		$scope.setPresaveCallback(function () {
			var deferred = $q.defer();
			updateFormErrors();

			if ($scope.has_errors) {
				deferred.reject();
			} else {
				deferred.resolve();
			}

			return deferred.promise;
		});

	}];
});