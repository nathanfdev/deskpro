define(function () {
  return ['$scope', 'Api', '$q', '$modal', function ($scope, Api, $q, $modal) {
	  $scope.has_own;
	  return Api.sendGet('/apps/packages/deskpro_sendgrid/get-url').then(
			  function (res) {
				  $scope.error = false;
				  $scope.url = res.data.url;
			  },
			  function (res) {
				  $scope.error = true;
			  }
	  );
  }];
});