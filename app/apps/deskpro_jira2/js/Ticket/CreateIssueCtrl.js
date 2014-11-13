define(function() {

	return function($scope, $modalInstance, $ticket, $meta, issues) {

		console.info($meta);
		console.info($ticket);

        var staticFields = {
            project: 1,
            issuetype: 1,
            summary: 1,
            reporter: 1
        }, issue;

		$scope.$watch('issue.project.id', function(val) {
            $meta.projects.each(function(project){
                if (val != project.id) return;
                $scope.project = project;
            });

            if (!$scope.project && $meta.projects.length) {
                issue.project.id = $meta.projects[0].id;
            }
		});

        var refreshFields = function() {
            if (!$scope.project) return;

            var fields = [];
            $scope.project.issuetypes.each(function(type) {
                if (issue.issuetype.id != type.id) return;

                $.each(type.fields, function(id, field) {
                    if (staticFields[id]) return;
                    if (!field.required && $meta.default_fields_summary.indexOf(id) === -1) return;

                    field.id = field.id || id;
                    fields.push(field);
                });
            });
            $scope.fields = fields;
        };

        $scope.$watch('project.id', refreshFields);
        $scope.$watch('issue.issuetype.id', refreshFields);

		$scope.confirm = function() {
			$scope.sending = true;

            var fields = angular.copy($scope.issue);
            console.info(fields);
			issues.create({fields: fields}).then(
				function(){ $scope.sending = false; $modalInstance.dismiss(); },
				function(){ $scope.sending = false; /* todo show errors */ }
			);
		};


        $scope.meta = $meta;
        $scope.fields = [];
        $scope.issue = issue = {
            project: {id: $meta.default_project},
            issuetype: {id: $meta.default_issuetype},
            summary: '[Ticket #' + $ticket.id + '] ' + $ticket.subject
        };
	};
});