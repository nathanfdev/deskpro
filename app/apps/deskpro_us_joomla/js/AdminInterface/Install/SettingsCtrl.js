define(['DeskPRO/Util/Strings'], function(Strings) {
	return ['$scope', 'Api', '$q', '$modal', function($scope, Api, $q, $modal) {
		var touched = {},
			validateFn = [];

		$scope.enableCustomFooter();
		$scope.has_errors = false;
		$scope.errors = {};

		//##############################################################################################################
		//# Form validation / errors
		//##############################################################################################################

		['joomla_url', 'joomla_secret'].forEach(function(field) {
			$scope.$watch('setting_values.' + field, function() {
				if (field == 'joomla_url' && $scope.setting_values[field]) {
					$scope.setting_values[field] = $scope.setting_values[field].replace(/\//g, '');
				}

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
				deferred.resolve();
			}

			return deferred.promise;
		});

		//##############################################################################################################
		//# Test modal
		//##############################################################################################################

		function runTest(username, password) {
			var deferred, postData;

			postData = {
				joomla_url:    Strings.trim($scope.setting_values.joomla_url || ''),
				joomla_secret: Strings.trim($scope.setting_values.joomla_secret || ''),
				username:      username,
				password:      password
			};

			deferred = $q.defer();

			Api.sendPostJson('/apps/packages/deskpro_us_joomla/test-settings', postData).then(function(res) {
				deferred.resolve({
					log: res.data.log || '',
					error: res.data.error || false,
					error_code: res.data.error_code || false
				});
			}, function(res) {
				deferred.resolve({
					log: res.data.log || 'A server error occurred. Check the PHP error logs for more information. You should contact support@deskpro.com.',
					error: res.data.error || 'There was a problem on the server that prevented the test from returning normally.',
					error_code: res.data.error_code || 500
				});
			});

			return deferred.promise;
		};

		$scope.openTestModal = function(existing_results) {
			if (updateFormErrors()) {
				return;
			}

			var inst = $modal.open({
				templateUrl: 'deskpro_us_joomla/Install/test-settings-modal.html',
				controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {

					function setResults(results) {
						$scope.loading     = false;
						$scope.has_results = true;
						$scope.log         = results.log;
						$scope.error       = results.error || false;
						$scope.error_code  = results.error_code;
					};

					$scope.resetTest = function() {
						$scope.show_log    = false;
						$scope.loading     = false;
						$scope.has_results = false;
						$scope.log         = null;
						$scope.error       = null;
						$scope.error_code  = null;
					};

					$scope.dismiss = function() { $modalInstance.dismiss(); }
					$scope.doTest = function() {
						$scope.loading = true;
						runTest().then(function(results) {
							setResults(results);
						});
					};

					if (existing_results) {
						setResults(existing_results);
					}
				}]
			});

			return inst;
		};
	}];
});