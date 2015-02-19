define(['angular'], function (angular) {

  return function ($scope, $modalInstance, $ticket, $meta, issues, newissue) {

    var staticFields = {
      project: 1,
      issuetype: 1,
      summary: 1,
      reporter: 1
    };

    $scope.$watch('issue.project.id', function (val) {
      $scope.project = null;
      $meta.getCreateMeta(val).then(function(project){
        $scope.project = project
        if (!project && $meta.projects.length) {
          $scope.issue.project.id = $meta.projects[0].id;
        }
      });
    });

    var refreshFields = function () {
      if (!$scope.project) {
        return $scope.fields.length = 0;
      }

      var fields = [];
      $scope.project.issuetypes.each(function (type) {
        if ($scope.issue.issuetype.id != type.id) return;

        $.each(type.fields, function (id, field) {
          if (staticFields[id]) return;
          if (!field.required && !$meta.isEnabled('summary', id)) return;

          field.id = field.id || id;
          fields.push(field);
        });
      });
      $scope.fields = fields;
    };

    $scope.$watch('project.id', refreshFields);
    $scope.$watch('issue.issuetype.id', refreshFields);

    $scope.confirm = function () {
      $scope.sending = true;
      $scope.errors = null;

      var fields = angular.copy($scope.issue);
      issues.create({fields: fields}).then(
        function () {
          $scope.sending = false;
          $modalInstance.dismiss();
        },
        function (data) {
          $scope.sending = false;
          $scope.errors = data.errors;
        }
      );
    };


    $scope.meta = $meta;
    $scope.fields = [];

    $scope.issue = angular.extend({
      project: {id: $meta.default_project},
      issuetype: {id: $meta.default_issuetype},
      summary: '[Ticket #' + $ticket.id + '] ' + $ticket.subject
    }, newissue || {});

    $scope.dismiss = $modalInstance.dismiss;
  };
});