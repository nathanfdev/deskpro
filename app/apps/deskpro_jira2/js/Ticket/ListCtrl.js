define(function() {
	return function($scope, $tabScope, $ticket, $http, $modal, $app, $timeout, $q) {
		$tabScope.btnImg = $app.getResourcePath('jira.png');

		var metaDeferred = $q.defer(),
			metaPromise = metaDeferred.promise;

		$http.get('/agent/jira/meta')
			.success(function(data, status, headers, config){
				metaDeferred.resolve(data);
			})
			.error(function(data, status, headers, config){
				console.error(data);
				metaDeferred.resolve();
			});

		$scope.createIssueModal = function(){
			$modal.open({
				templateUrl: 'deskpro_jira2/Ticket/create-issue-modal.html',
				controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {

					$scope.issue = {
						project: null,
						issuetype: null,
						summary: '[Ticket #' + $ticket.id + '] ' + $ticket.subject
					};

					$scope.$watch('issue.project', function(project){
						$scope.issue.issuetype = null;
						if (!project) return;

						var types = project.issuetypes || [];
						for (var i = 0; i < types.length; i++) {
							var type = types[i];
							if (type.id == $scope.meta.default_issuetype) {
								$scope.issue.issuetype = type;
								break;
							}
						}

						if (!$scope.issue.issuetype && $scope.issue.project.issuetypes.length) {
							$scope.issue.issuetype = $scope.issue.project.issuetypes[0];
						}
					});

					$scope.$watch('issue.issuetype', function(type){
						if (!type) return;
						if (type.filtered_fields) return;

						type.filtered_fields = [];
						$.each(type.fields, function(id, field){
							if (['project', 'summary', 'priority', 'issuetype'].indexOf(id) > -1) return;
							if (!field.required && $scope.meta.default_fields_summary.indexOf(id) === -1) return;
							type.filtered_fields.push(field);
						});
					});


					metaPromise.then(function(meta){
						console.info(meta);

						$scope.meta = meta;

						for (var i = 0; i < $scope.meta.projects.length; i++) {
							var project = $scope.meta.projects[i];
							if (project.id == meta.default_project) {
								$scope.issue.project = project;
								break;
							}
						}
					});


					$scope.dismiss = function() { $modalInstance.dismiss(); };
				}]
			});
		};
	}
});