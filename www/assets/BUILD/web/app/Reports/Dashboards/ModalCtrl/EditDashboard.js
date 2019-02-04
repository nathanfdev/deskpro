define(['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], (Arrays, Util) => [
  '$scope', '$q', '$modal', '$modalInstance', 'dashboard_id', 'modal_options', 'DashboardsInfo', 'DashboardService', 'Growl',
  function ($scope, $q, $modal, $modalInstance, dashboard_id, modal_options, DashboardsInfo, DashboardService, Growl) {
    let doSaveDashboard;
    $scope.loaded = false;
    $scope.dashboard = null;
    $scope.reports = [];
    $scope.agents = [];
    $scope.activeTab = 'info';
    $scope.is_new = false;
    $scope.shareLinks = [];
    $scope.shareLink = null;
    $scope.shareLinkView = null;

    $scope.did_edit_reports = false;

    // ###################################################################################################################
    // LOADING
    // ###################################################################################################################

    if (modal_options.activeTab) {
      $scope.activeTab = modal_options.activeTab;
    }

    const load_promises = [];
    load_promises.push(DashboardsInfo.getDashboardList(true).then(dbs => $scope.dashboards = dbs)
    );
    load_promises.push(DashboardsInfo.getAgents().then(agents => $scope.agents = agents)
    );
    load_promises.push(DashboardsInfo.getAgentTeams().then(teams => $scope.teams = teams)
    );
    load_promises.push(DashboardsInfo.getDepartments().then(departments => $scope.departments = departments)
    );

    if (dashboard_id) {
      load_promises.push(DashboardsInfo.getDashboardDetail(dashboard_id).then((db) => {
        $scope.dashboard = angular.copy(db);
        $scope.dashboard.permissions = { agent: [], department: [], team: [], all: '' };
        return (() => {
          const result = [];
          for (const permission of Array.from(db.permissions)) {
            if (!permission.person && !permission.department && !permission.team) {
              result.push($scope.dashboard.permissions.all = permission.name);
            } else if (permission.person) {
              result.push($scope.dashboard.permissions.agent.push(angular.copy(permission)));
            } else if (permission.team) {
              result.push($scope.dashboard.permissions.team.push(angular.copy(permission)));
            } else if (permission.department) {
              result.push($scope.dashboard.permissions.department.push(angular.copy(permission)));
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      })
      );
      load_promises.push(DashboardsInfo.getReportsList(dashboard_id).then(reports => $scope.reports = reports)
      );
      load_promises.push(DashboardService.getShareableLinks(dashboard_id).then(shareableLinks => $scope.shareLinks = shareableLinks)
      );
    } else {
      $scope.is_new = true;
      $scope.dashboard = {
        title:       '',
        reports:     [],
        is_default:  false,
        is_agent:    false,
        permissions: { agent: [], team: [], department: [], all: '' }
      };
    }

    $q.all(load_promises).then(() => $scope.loaded = true);

    // ###################################################################################################################
    // Sortable config
    // ###################################################################################################################

    $scope.sortableOptions = {
      axis:   'y',
      handle: '.drag-handle',
      update() {}

    };

    // ###################################################################################################################
    // UI handlers
    // ###################################################################################################################

    $scope.getInitials = function (agent) {
      if ((agent == null)) { return '?'; }
      const first    = agent.first_name;
      const last     = agent.last_name;
      const initials = (first && first.length ? first[0] : '') + (last && last.length ? last[0] : '');

      return initials;
    };

    $scope.cancel = () => $modalInstance.dismiss('cancel');

    /*
     * Removes a report from the dashboard
     */
    $scope.removeReport = function (report) {
      $scope.did_edit_reports = true;
      return $scope.reports = $scope.reports.filter(r => r.id !== report.id);
    };

    /*
     * Adds a blank report to the dashboard
     */
    $scope.addReport = function (reportTitle) {
      if (reportTitle == null) { reportTitle = ''; }
      $scope.did_edit_reports = true;
      return $scope.reports.push({
        id:      Util.uid('new'),
        isNew:   true,
        isAdded: true,
        title:   reportTitle
      });
    };

    if (!dashboard_id) {
      $scope.addReport('My report');
    }

    /*
     * Clones all reports from specified dashboard
     */
    $scope.cloneDashboard = function (db) {
      $scope.show_clone_menu = false;
      return DashboardsInfo.getReportsList(db.id).then(reports =>
        Array.from(reports).map(r =>
         $scope.cloneReport(db, r))
      );
    };

    /*
     * Clones a report from an existing dashboard
     */
    $scope.cloneReport = function (db, r) {
      $scope.did_edit_reports = true;
      $scope.show_clone_menu = false;
      return $scope.reports.push({
        id:                 Util.uid('new'),
        cloneId:            r.id,
        isAdded:            true,
        title:              r.title,
        fromDashboardTitle: db.title
      });
    };

    /*
     * Opens clone menu
     */
    $scope.openCloneMenu = () => $scope.show_clone_menu = true;

    /*
     * Closes clone menu
     */
    $scope.closeCloneMenu = () => $scope.show_clone_menu = false;

    /*
     * Save the form
     */
    $scope.saveDashboard = function () {
      $scope.saving = true;
      return doSaveDashboard().then(
        () => {
          DashboardsInfo.resetData();
          if (!$scope.is_new) {
            $scope.dashboard.version_id++;
            if ($scope.did_edit_reports) { $scope.dashboard.reports_version_id; }
          }

          $scope.saving = false;
          return $modalInstance.close();
        }
        , () => $scope.saving = false);
    };

    // All
    $scope.canAllAgentsViewDashboard = () => $scope.dashboard.permissions.all;

    $scope.canAllAgentsEditDashboard = () => ($scope.dashboard.permissions.all != null) === 'full';

    $scope.toggleAllAgentsViewDashboard = function () {
      if ($scope.dashboard.permissions.all) {
        // turn off
        $scope.dashboard.permissions.all = '';
        return;
      }

      if (!$scope.dashboard.permissions.all) {
        $scope.dashboard.permissions.all = 'view';
        return;
      }
    };

    $scope.toggleAllAgentsEditDashboard = function () {
      if ($scope.dashboard.permissions.all === 'full') {
        // turn off
        $scope.dashboard.permissions.all = 'view';
        return;
      }
      $scope.dashboard.permissions.all = 'full';
      return;
    };

    // Agents
    $scope.canAgentViewDashboard = agentId => (($scope.dashboard.permissions.agent || []).filter(permission => permission.person === agentId).length > 0) || $scope.dashboard.permissions.all;

    $scope.canAgentEditDashboard = agentId => (($scope.dashboard.permissions.agent || []).filter(permission => (permission.person === agentId) && (permission.name === 'full')).length > 0) || ($scope.dashboard.permissions.all === 'full');

    $scope.canViewAllAgents = function (agentId) {
      const permission = ($scope.dashboard.permissions.agent || []).filter(permission => permission.person === agentId)[0];
      if (!permission) {
        return false;
      }

      return permission.view_all;
    };

    $scope.toggleAgentViewDashboard = function (agentId) {
      const permission = $scope.dashboard.permissions.agent.filter(permission => permission.person === agentId)[0];
      if (!permission) {
        return $scope.addAgentViewDashboard(agentId, permission);
      }
      return $scope.removeAgentViewDashboard(agentId, permission);
    };

    $scope.removeAgentViewDashboard = function (agentId, permission) {
      if (permission == null) { permission = false; }
      if ($scope.dashboard.permissions.all) {
        return;
      }
      if (!permission) {
        permission = $scope.dashboard.permissions.agent.filter(permission => permission.person === agentId)[0];
      }
      if (permission) {
        return $scope.dashboard.permissions.agent.splice($scope.dashboard.permissions.agent.indexOf(permission), 1);
      }
    };

    $scope.addAgentViewDashboard = function (agentId, permission) {
      if (permission == null) { permission = false; }
      if ($scope.dashboard.permissions.all !== '') {
        return;
      }
      if (!permission) {
        permission = $scope.dashboard.permissions.agent.filter(permission => permission.person === agentId)[0];
      }
      if (!permission) {
        return $scope.dashboard.permissions.agent.push({
          name:       'view',
          person:     agentId,
          view_all:   false,
          team:       null,
          department: null
        });
      }
    };

    $scope.toggleAgentEditDashboard = function (agentId) {
      const permission = $scope.dashboard.permissions.agent.filter(permission => permission.person === agentId)[0];
      if (!permission) {
        return $scope.dashboard.permissions.agent.push({
          name:       'full',
          person:     agentId,
          view_all:   false,
          team:       null,
          department: null
        });
      } else if (permission.name === 'view') {
        return permission.name = 'full';
      } else if (permission.name === 'full') {
        return permission.name = 'view';
      }
    };

    $scope.toggleViewAllAgents = function (agentId) {
      const permission = $scope.dashboard.permissions.filter(permission => permission.person === agentId)[0];
      if (!permission) {
        return $scope.dashboard.permissions.agent.push({
          name:       'view',
          person:     agentId,
          team:       null,
          department: null,
          view_all:   true
        });
      }
      return permission.view_all = !permission.view_all;
    };


    // Teams
    $scope.canTeamViewDashboard = teamId => ($scope.dashboard.permissions.team || []).filter(permission => permission.team === teamId).length > 0;

    $scope.canTeamEditDashboard = teamId => ($scope.dashboard.permissions.team || []).filter(permission => (permission.team === teamId) && (permission.name === 'full')).length > 0;

    $scope.toggleTeamViewDashboard = function (teamId) {
      const permission = $scope.dashboard.permissions.team.filter(permission => permission.team === teamId)[0];
      if (!permission) {
        return $scope.dashboard.permissions.team.push({
          name:       'view',
          person:     null,
          department: null,
          team:       teamId
        });
      }
      return $scope.dashboard.permissions.team.splice($scope.dashboard.permissions.team.indexOf(permission), 1);
    };

    $scope.toggleTeamEditDashboard = function (teamId) {
      const permission = $scope.dashboard.permissions.team.filter(permission => permission.team === teamId)[0];
      if (!permission) {
        return $scope.dashboard.permissions.team.push({
          name:       'full',
          person:     null,
          department: null,
          team:       teamId
        });
      } else if (permission.name === 'view') {
        return permission.name = 'full';
      }
    };

    $scope.openNewShareLinkForm = function () {
      $scope.shareLink = {
        dashboard:      $scope.dashboard.id,
        title:          '',
        default_report: null,
        who_can_use:    'anyone',
        ip_whitelist:   ''
      };
      return $scope.shareLinkView = 'new';
    };

    $scope.openShareLinkForm = function (shareLink) {
      $scope.shareLink = angular.copy(shareLink);
      return $scope.shareLinkView = 'share';
    };

    $scope.openEditShareLinkForm = function (shareLink) {
      $scope.shareLink = shareLink;
      return $scope.shareLinkView = 'edit';
    };

    $scope.saveShareLink = function () {
      let promise;
      $scope.saving = true;
      if (!$scope.shareLink) {
        return;
      }
      if ($scope.shareLink.id) {
        promise = DashboardService.updateShareLink($scope.shareLink);
      } else {
        promise = DashboardService.createShareLink($scope.shareLink);
        promise.then(newSharedLink => $scope.shareLinks.push(newSharedLink));
      }

      return promise.then(
        () => {
          $scope.shareLink = null;
          $scope.shareLinkView = null;
          $scope.saving = false;
          return Growl.success('Shared link is saved');
        },
        (response) => {
          if (__guard__(__guard__(response.errors != null ? response.errors.fields : undefined, x1 => x1.title), x => x.errors[0])) {
            $scope.error = 'Title could not be blank';
          }
          return $scope.saving = false;
        });
    };

    $scope.deleteShareLink = function (shareLink) {
      const modalInstance = $modal.open({
        templateUrl: 'ReportsInterfaceBundle:Index:modal-confirm.html',
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.title   = 'Confirm discard';
          $scope.message = 'Are you sure you want to delete this link?';

          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.confirm = () => $modalInstance.close();
        }
        ]
      });
      return modalInstance.result.then(
        () => DashboardService.deleteDashboardShareableLink(shareLink).then(() => Arrays.removeValue($scope.shareLinks, shareLink)));
    };

    $scope.createShortUrlForShareLink = shareLink =>
      DashboardService.createShortUrlForShareLink(shareLink).then((shortUrl) => {
        shareLink.short_url = shortUrl;
        return (() => {
          const result = [];
          for (const link of Array.from($scope.shareLinks)) {
            if (link.id === shareLink.id) {
              result.push(link.short_url = shortUrl);
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      })
    ;

    $scope.successAlert = message => Growl.success(message);

    // ###################################################################################################################
    // SAVE
    // ###################################################################################################################

    return doSaveDashboard = function () {
      const d = $q.defer();

      const data = angular.copy($scope.dashboard);
      data.reports = [];
      for (const report of Array.from($scope.reports)) {
        const reportData = {
          title: report.title
        };

        if (!report.isAdded) {
          reportData.id = report.id;
        }
        if (report.cloneId) {
          reportData.clone_id = report.cloneId;
        }

        data.reports.push(reportData);
      }

      data.permissions = [];
      for (var permission of Array.from($scope.dashboard.permissions.agent)) { data.permissions.push(permission); }
      for (permission of Array.from($scope.dashboard.permissions.team)) { data.permissions.push(permission); }
      for (permission of Array.from($scope.dashboard.permissions.department)) { data.permissions.push(permission); }
      if ($scope.dashboard.permissions.all) {
        data.permissions.push({ person: null, team: null, department: null, name: $scope.dashboard.permissions.all });
      }

      DashboardService
      .saveDashboard(data)
      .then(
        (saved) => {
          $scope.error = null;
          return d.resolve(saved);
        }
        , (response) => {
          if (__guard__(__guard__(response.errors != null ? response.errors.fields : undefined, x1 => x1.title), x => x.errors[0])) {
            $scope.error = 'Dashboard title could not be blank';
          }
          return d.reject();
        });
      return d.promise;
    };
  }
]);
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
