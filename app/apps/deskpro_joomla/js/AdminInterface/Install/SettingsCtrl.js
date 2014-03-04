define(['DeskPRO/Util/Strings'], function(Strings) {
	return ['$scope', 'Api', '$q', '$modal', function($scope, Api, $q, $modal) {
		var touched = {};
		$scope.enableCustomFooter();
		$scope.has_errors = false;
		$scope.errors = {};

		//##############################################################################################################
		//# Form validation / errors
		//##############################################################################################################

		function updateFormErrors() {
			$scope.has_errors = false;
			$scope.errors = {};

			if (!Strings.trim($scope.setting_values.joomla_url || '')) {
				$scope.has_errors = true;
				$scope.errors.joomla_url = true;
			}
			if (!Strings.trim($scope.setting_values.joomla_secret || '')) {
				$scope.has_errors = true;
				$scope.errors.joomla_secret = true;
			}

			return $scope.has_errors;
		};

		$scope.$watch('setting_values.joomla_url',    function() {
			if ($scope.setting_values.joomla_url) {
				$scope.setting_values.joomla_url = $scope.setting_values.joomla_url.replace(/\//g, '');
			}
			if (touched.joomla_url || touched.always || $scope.errors.joomla_url) {
				updateFormErrors();
			}
		});
		$scope.$watch('setting_values.joomla_secret', function() {
			if (touched.joomla_secret || touched.always || $scope.errors.joomla_secret) {
				updateFormErrors();
			}
		});

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

			if (!postData.joomla_url || !postData.joomla_secret) {
				deferred.resolve({
					log: 'Missing Joomla URL and/or Joomla Secret.',
					error: 'Please fill in the Joomla URL and Jommla Secret.',
					error_code: 1
				});
				return deferred.promise;
			}

			Api.sendPostJson('/apps/packages/deskpro_joomla/test-settings', postData).then(function(res) {
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
				templateUrl: 'deskpro_joomla/Install/test-settings-modal.html',
				controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {

					function setResults(results) {
						$scope.loading     = false;
						$scope.has_results = true;
						$scope.log         = results.log;
						$scope.error       = results.error || false;
						$scope.error_code  = results.error_code;
					};

					$scope.test = {
						username: '',
						password: ''
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
						runTest($scope.test.username, $scope.test.password).then(function(results) {
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