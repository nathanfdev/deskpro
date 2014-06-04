define(['DeskPRO/Util/Strings', 'DeskPRO/Util/Util'], function(Strings, Util) {
	return ['$scope', 'Api', '$q', '$modal', function($scope, Api, $q, $modal) {
		$scope.enableCustomFooter();

		//##############################################################################################################
		//# Test modal
		//##############################################################################################################

		function runTest(username, password) {
			var deferred, postData;

			postData = {};
			postData.settings = Util.clone($scope.setting_values);
			postData.username = username;
			postData.password = password;

			deferred = $q.defer();

			Api.sendPostJson('/apps/packages/deskpro_us_db/test-settings', postData).then(function(res) {
				deferred.resolve({
					log:        res.data.log || '',
					error:     !res.data.is_valid,
					error_code: res.data.error_code || false,
					raw_data:   res.data.raw_data || null
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
			var inst = $modal.open({
				templateUrl: 'deskpro_us_db/Install/test-settings-modal.html',
				controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {

					$scope.test = { username: '', pasword: '' };

					function setResults(results) {
						$scope.loading     = false;
						$scope.has_results = true;
						$scope.log         = results.log;
						$scope.error       = results.error;
						$scope.raw_data    = results.raw_data || null;
					};

					$scope.resetTest = function() {
						$scope.show_userdata = false;
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