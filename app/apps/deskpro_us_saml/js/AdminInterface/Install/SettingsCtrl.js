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

		//##############################################################################################################
		//# Test modal
		//##############################################################################################################

		$scope.openTestModal = function (existing_results) {
			if (updateFormErrors()) {
				return;
			}

			return $modal.open({
				templateUrl: 'deskpro_us_saml/Install/test-settings-modal.html',
				controller: ['$scope', '$modalInstance', '$timeout', function ($modalScope, $modalInstance, $timeout) {

					$modalScope.is_verified = false;
					$modalScope.is_error = false;
					$modalScope.log = '';
					$modalScope.loading = true;

					Api.sendGet('/usersources/iframe/code/'+ $scope.Ctrl.usersourceType + '/' + $scope.Ctrl.instanceId).then(function(res) {
						 window.USERSOURCE_TEST_SCOPE = $modalScope;
						 angular.element('#iframe_html_usersource_test').html(res.data.iframe_html);
					});

					$modalScope.dismiss = function () { $modalInstance.dismiss(); };

					$modalScope.saveAndRun = function() {
						$scope.Ctrl.saveSettings();
						$timeout(function() {
								$modalScope.dismiss();
								$scope.openTestModal();
							},
							2000
						);
					};
				}]
			});
		};
	}];
});