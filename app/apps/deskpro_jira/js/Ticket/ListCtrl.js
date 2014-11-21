define([
  'angular',
  'deskpro_jira/Ticket/CreateIssueCtrl'
], function (angular,
             CreateIssueCtrl) {
  return function ($scope, $tabScope, $ticket, $http, $modal, $app, $timeout, $q, $meta, Issues) {
    $tabScope.btnImg = $app.getResourcePath('jira.png');

    var issues = $scope.issues = new Issues($ticket);
    $scope.meta = $meta;


    /**
     * search issue for linking
     * @returns {number}
     */
    $scope.search = function () {
      $scope.search_issue_state = 1;

      for (var i = 0; i < issues.length; i++) {
        if ($scope.search_issue.toLowerCase().indexOf(issues[i].key.toLowerCase()) > -1) {
          return $scope.search_issue_state = 409;
        }
      }

      issues.search($scope.search_issue).then(
        function (issue) {

          if (!issue) {
            return $scope.search_issue_state = 404;
          }
          $scope.search_issue = null;
          $scope.search_issue_state = 0;
          var $parent = $scope;

          $modal.open({
            templateUrl: 'deskpro_jira/Ticket/link-issue-modal.html',
            controller: ['$scope', '$modalInstance', function ($scope, $modalInstance) {

              $scope.meta = $meta;
              $scope.issue = issue;
              $scope.names = issues.names;
              $parent.search_issue_state = 1;

              if (!issue.filtered_fields) {
                issue.filtered_fields = [];
                $.each(issue.fields, function (id, field) {
                  if ('comment' === id) return;
                  if ($meta.isEnabled('summary', id)) {
                    issue.filtered_fields.push({id: id, value: field});
                  }
                });
              }

              $scope.confirm = function () {
                $scope.search_issue_state = 1;
                $modalInstance.dismiss();

                issues.link(issue).then(
                  function () {
                    $parent.search_issue_state = 0;
                  },
                  function (status) {
                    $parent.search_issue_state = status;
                  }
                );
              };
            }]
          });
        }
      );
    };


    /**
     * unlink issue
     * @param issue
     */
    $scope.unlink = function (issue) {
      $scope.search_issue_state = 1;
      issues.unlink(issue).then(function () {
        $scope.search_issue_state = 0;
      });
    };


    /**
     * create new issue
     */
    $scope.createIssueModal = function () {
      $modal.open({
        templateUrl: 'deskpro_jira/Ticket/create-issue-modal.html',
        controller: CreateIssueCtrl,
        resolve: {
          $ticket: function () {
            return $ticket;
          },
          $meta: function () {
            return $meta;
          },
          issues: function () {
            return issues;
          }
        }
      });
    };


    /**
     * show issue details
     * @param issue
     */
    $scope.issueModal = function (issue) {
      $modal.open({
        templateUrl: 'deskpro_jira/Ticket/issue-details-modal.html',
        controller: ['$scope', '$modalInstance', function ($scope, $modalInstance) {

          $scope.meta = $meta;
          $scope.issue = issue;
          $scope.sending_comment = false;
          $scope.names = issues.names;

          if (!issue.filtered_fields) {
            issue.filtered_fields = [];
            $.each(issue.fields, function (id, field) {
              if ('comment' === id) return;
              if ($meta.isEnabled('summary', id)) {
                issue.filtered_fields.push({id: id, value: field});
              }
            });
          }

          $scope.sendComment = function (msg) {
            if (!issue.fields.comment) return false;
            $scope.sending_comment = true;
            issues.sendComment(msg, issue).then(function () {
              $scope.sending_comment = false;
            });
          };
        }]
      });
    };


    /**
     * send comment to all linked issues
     */
    $scope.sendCommentModal = function () {
      $modal.open({
        templateUrl: 'deskpro_jira/Ticket/send-comment-modal.html',
        controller: ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.confirm = function (msg) {
            issues.sendComment(msg);
            $modalInstance.dismiss();
          };
        }]
      });
    };
  }
});