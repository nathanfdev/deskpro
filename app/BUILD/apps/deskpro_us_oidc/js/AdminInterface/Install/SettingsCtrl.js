define(['DeskPRO/Util/Strings', 'DeskPRO/Util/Util'], function(Strings, Util) {
	return ['$scope', 'Api', '$q', '$modal', '$timeout', function($scope, Api, $q, $modal, $timeout) {
		var touched = {},
			validateFn = [];

		$scope.enableCustomFooter();
		$scope.has_errors = false;
		$scope.errors = {};

		//##############################################################################################################
		//# Form validation / errors
		//##############################################################################################################

		$scope.$watch('setting_values.identity', function () {
			if (touched.identity || touched.always || $scope.errors.identity) {
				updateFormErrors();
			}
		});

		validateFn.push(['identity', function () {
			return ($scope.setting_values.identity && Strings.trim($scope.setting_values.identity));
		}]);

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

		$scope.readContentsFunction = function($fileContents, setting_name) {
			$scope.setting_values[setting_name] = $fileContents;
		};
	}];
});