define([
	'angular',
	'deskpro_jira2/Ticket/CreateIssueCtrl',
	'deskpro_jira2/Ticket/Issues'
], function(
	angular,
	CreateIssueCtrl,
    Issues
	) {
	return function($scope, $tabScope, $ticket, $http, $modal, $app, $timeout, $q) {
		$tabScope.btnImg = $app.getResourcePath('jira.png');

		var metaDeferred = $q.defer(),
			metaPromise = metaDeferred.promise,
			issues = new Issues($http, $q, $ticket);

		$scope.issues = issues;

		$http.get('/agent/jira/meta')
			.success(function(data, status, headers, config){
				console.info(data);
				$scope.meta = data;
				metaDeferred.resolve(data);
			})
			.error(function(data, status, headers, config){
				console.error(data);
				metaDeferred.resolve();
			});

		$scope.search = function(){
			$scope.search_issue_state = 1;
			issues.search($scope.search_issue).then(function(issue){
				if (!issue) {
					return $scope.search_issue_state = 404;
				}

				$scope.search_issue_state = 0;

				$modal.open({
					templateUrl: 'deskpro_jira2/Ticket/link-issue-modal.html',
					controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
						metaPromise.then(function(meta){
							$scope.meta = meta;
							$scope.issue = issue;
						});
					}]
				});
			});
		};

		$scope.createIssueModal = function(){
			$modal.open({
				templateUrl: 'deskpro_jira2/Ticket/create-issue-modal.html',
				controller: CreateIssueCtrl,
				resolve: {
					$ticket: function() { return $ticket; },
					meta: function() { return metaPromise; },
					issues: function() { return issues; }
				}
			});
		};



		$scope.issueModal = function(issue){
			$modal.open({
				templateUrl: 'deskpro_jira2/Ticket/issue-details-modal.html',
				controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
					metaPromise.then(function(meta){
						$scope.meta = meta;
						$scope.issue = issue;
					});
				}]
			});
		};



		$scope.sendCommentModal = function(){
			$modal.open({
				templateUrl: 'deskpro_jira2/Ticket/send-comment-modal.html',
				controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
					$scope.confirm = function(){
						issues.sendComment($scope.message);
						$modalInstance.dismiss();
					};
				}]
			});
		};
	}
});