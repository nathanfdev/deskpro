define(function() {

	return function($scope, $modalInstance, $ticket, meta, issues) {

		console.info(meta);

		$scope.meta = meta;
		$scope.issue = {
			project: null,
			issuetype: null,
			summary: '[Ticket #' + $ticket.id + '] ' + $ticket.subject,
			priority: null
		};

/**** init defaults ****/
		if (meta.default_project) {
			for (var i = 0; i < $scope.meta.projects.length; i++) {
				var project = $scope.meta.projects[i];
				if (project.id == meta.default_project) {
					$scope.issue.project = project;
					break;
				}
			}
		}

		if (meta.default_priority) {
			for (var i = 0; i < $scope.meta.priorities.length; i++) {
				var priority = $scope.meta.priorities[i];
				if (priority.id == meta.default_priority) {
					$scope.issue.priority = priority;
					break;
				}
			}
		}

		if (!$scope.issue.project && $scope.meta.projects.length) {
			$scope.issue.project = $scope.meta.projects[0];
		}

		if (!$scope.issue.priority && $scope.meta.priorities.length) {
			$scope.issue.priority = $scope.meta.priorities[0];
		}
/**** end of init defaults ****/





		$scope.$watch('issue.project', function (project) {
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

		$scope.$watch('issue.issuetype', function (type) {
			if (!type) return;
			if (type.filtered_fields) return;

			type.filtered_fields = [];
			$.each(type.fields, function (id, field) {
				if (['project', 'summary', 'priority', 'issuetype'].indexOf(id) > -1) return;
				if (!field.required && $scope.meta.default_fields_summary.indexOf(id) === -1) return;
				type.filtered_fields.push(field);
			});
		});

		$scope.confirm = function () {

			// todo run spinner

			var fields = angular.copy($scope.issue);
			fields.project = {id: fields.project.id};
			fields.issuetype = {id: fields.issuetype.id};
			fields.priority = {id: fields.priority.id};

			issues.create({fields: fields}).then(function(){ $modalInstance.dismiss(); });
		};
	};
});