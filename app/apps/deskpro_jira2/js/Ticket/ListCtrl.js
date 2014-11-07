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
			issues = new Issues($http, $q, $ticket),
			enabled = {list: {}, summary: {}};

		$scope.issues = issues;

		var render = function(data) {
			if (!data) return data;
			return 'object' == typeof data ? '[object]' : data;
		};

		var isFieldEnabled = function(type, field) {
			return undefined !== enabled[type][field];
		};
		$scope.isFieldEnabled = isFieldEnabled;

		$http.get('/agent/jira/meta')
			.success(function(data, status, headers, config){
				console.info(data);
				$scope.meta = data;
				data.default_fields_list.each(function(field){ enabled.list[field] = 1; });
				data.default_fields_summary.each(function(field){ enabled.summary[field] = 1; });
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
							$scope.names = issues.names;

							if (!issue.filtered_fields) {
								issue.filtered_fields = [];
								$.each(issue.fields, function (id, field) {
									if (meta.system_fields.indexOf(id) > -1) return;
									if (isFieldEnabled('summary', id)) {
										issue.filtered_fields.push({id: id, value: field});
									}
								});
							}
						});
						$scope.render = render;
						$scope.isFieldEnabled = isFieldEnabled;
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
						$scope.sending_comment = false;
						$scope.names = issues.names;

						if (!issue.filtered_fields) {
							issue.filtered_fields = [];
							$.each(issue.fields, function (id, field) {
								if (meta.system_fields.indexOf(id) > -1) return;
								if (isFieldEnabled('summary', id)) {
									issue.filtered_fields.push({id: id, value: field});
								}
							});
						}

						$scope.sendComment = function(msg){
							if (!issue.fields.comment) return false;
							$scope.sending_comment = true;
							issues.sendComment(msg, issue.id).then(function(data){
								$scope.sending_comment = false;
								data && issue.fields.comment.comments.push(data);
							});
						};
					});
					$scope.render = render;
					$scope.isFieldEnabled = isFieldEnabled;
				}]
			});
		};



		$scope.sendCommentModal = function(){
			$modal.open({
				templateUrl: 'deskpro_jira2/Ticket/send-comment-modal.html',
				controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
					$scope.confirm = function(msg){
						issues.sendComment(msg);
						$modalInstance.dismiss();
					};
				}]
			});
		};

		$scope.render = render;
	}
});