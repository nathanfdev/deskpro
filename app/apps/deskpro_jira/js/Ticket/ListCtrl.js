define([
  'angular',
  'deskpro_jira/Ticket/CreateIssueCtrl',
  'DeskPRO/Util/Functions'
], function (angular,
             CreateIssueCtrl,
             Functions
  ) {
  return function ($scope, $tabScope, $ticket, $http, $modal, $app, $timeout, $q, $meta, Issues) {
    $tabScope.btnImg = $app.getResourcePath('jira.png');

    var issues = $scope.issues = new Issues($ticket),
      lastSearchTime = 0;
    $scope.meta = $meta;
    $scope.search_results = [];
    $scope.active_searches = 0;

    $scope.$watch('issues.length', function(l) {
      if (!l || l < 1) {
        $tabScope.btnBadge = null;
      } else {
        $tabScope.btnBadge = l+"";
      }
    });

    $scope.enableSearchMode = function() {
        $scope.link_search_mode = true;
    };

    var startSearch = Functions.debounce(function(val){

      if (!val) {
        lastSearchTime = new Date().getTime();
        $scope.search_results.length = 0;
        $scope.active_searches = 0;
        return;
      }

      (function(time){
        $scope.active_searches++;
        issues.search($scope.search).then(
          function (issues) {
            $scope.active_searches--;
            if (time < lastSearchTime) return;
            lastSearchTime = time;
            $scope.search_results.length = 0;
            if (!issues || !issues.length) return;
            issues.each(function(el){$scope.search_results.push(el);console.info(el);});
          },
          function() {
            $scope.active_searches--;
          }
        );
      })(new Date().getTime());

    }, 150);

    $scope.$watch('search', startSearch);

    $scope.openLinkModal = function(issue) {
      var $parent = $scope;
      $modal.open({
        templateUrl: 'deskpro_jira/Ticket/link-issue-modal.html',
        controller: ['$scope', '$modalInstance', function ($scope, $modalInstance) {

          $scope.meta = $meta;
          $scope.issue = issue;
          $scope.names = issues.names;

          $scope.already_linked = false;
          issues.each(function(el){
            if (el.key == issue.key) $scope.already_linked = true;
          });

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
            $parent.active_searches++;
            issues.link(issue).then(
              function () {
                $parent.search = '';
                $parent.active_searches--;
                $parent.link_search_mode = false;
              },
              function (status) {
                $parent.search = '';
                $parent.active_searches--;
                $parent.search_issue_state = status;
              }
            );
            $modalInstance.dismiss();
          };

          $scope.dismiss = $modalInstance.dismiss;
        }]
      });
    };

    /**
     * unlink issue
     * @param issue
     */
    $scope.unlink = function (issue) {
	    $modal.open({
		    templateUrl: 'deskpro_jira/Ticket/unlink-confirm-modal.html',
		    controller: ['$scope', '$modalInstance', function ($scope, $modalInstance) {
			    if (!issue.filtered_fields) {
				    issue.filtered_fields = [];
				    $.each(issue.fields, function (id, field) {
					    if ('comment' === id) return;
					    if ($meta.isEnabled('summary', id)) {
						    issue.filtered_fields.push({id: id, value: field});
					    }
				    });
			    }
			    $scope.issue = issue;
			    $scope.meta = $meta;
			    $scope.confirm = function (msg) {
				    $scope.active_searches = 1;
				    issues.unlink(issue).then(function () {
				      $scope.active_searches = 0;
				    });
				    $modalInstance.dismiss();
			    };
			    $scope.dismiss = $modalInstance.dismiss;
		    }]
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

	        $scope.dismiss = $modalInstance.dismiss;
        }]
      });
    };


    /**
     * send comment to all linked issues
     */
    $scope.sendCommentModal = function (issue) {
      $modal.open({
        templateUrl: 'deskpro_jira/Ticket/send-comment-modal.html',
        controller: ['$scope', '$modalInstance', function ($scope, $modalInstance) {
	        $scope.issues = issues;
	        $scope.meta = $meta;
	        $scope.issue = issue
          $scope.confirm = function (msg) {
            issues.sendComment(msg, issue);
            $modalInstance.dismiss();
          };

	        $scope.dismiss = $modalInstance.dismiss;
        }]
      });
    };
  }
});