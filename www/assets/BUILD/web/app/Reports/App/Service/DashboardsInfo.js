define(['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], function(Arrays, Util) {
  class DashboardsInfo {
    constructor(Api, Api2, $q) {
      // this is just a cheap way that controllers
      // can listen on to refresh their state if we change
      // something
      this.getAgents = this.getAgents.bind(this);
      this.getAgentTeams = this.getAgentTeams.bind(this);
      this.getDepartments = this.getDepartments.bind(this);
      this.Api = Api;
      this.Api2 = Api2;
      this.$q = $q;
      this.version_id = 0;

      this.agents               = null;
      this.dashboardList        = [];
      this.dashboardListPromise = null;
      this.lastDashboardDetail  = null;
      this.lastReportDetail     = null;
      this.agents               = null;
      this.teams                = null;
      this.departments          = null;
    }

    resetData() {
      this.version_id += 1;

      if (this.dashboardList) {
        for (let db of Array.from(this.dashboardList)) {
          db.version_id = this.version_id;
          db.reports_version_id = this.version_id;
        }
      }

      if (this.lastDashboardDetail) {
        this.lastDashboardDetail.version_id = this.version_id;
        this.lastDashboardDetail.reports_version_id = this.version_id;
      }

      this.dashboardList        = [];
      this.dashboardListPromise = null;
      this.lastDashboardDetail  = null;
      return this.lastReportDetail     = null;
    }

    /*
     * Gets a list of dashboards. This is basic information like id and title.
     * For more information you should use getDashboard().
     *
     * @return {promise}
     */
    getDashboardList(includeReports) {
      if (includeReports == null) { includeReports = false; }
      const d = this.$q.defer();

      if (!includeReports && this.dashboardListPromise) {
        this.dashboardListPromise.then( l => d.resolve(l)
        , () => d.reject());
        return d.promise;
      }

      this.dashboardListPromise = d.promise;

      let url = '/dashboards';
      if (includeReports) {
        url += '?include=reports';
      }

      this.Api2.sendGet(url).then(res => {
        this.dashboardList = Arrays.replaceArray(this.dashboardList, res.data.data);
        for (let db of Array.from(this.dashboardList)) {
          db.version_id = this.version_id;
          db.reports_version_id = this.version_id;
          if (includeReports) {
            db.reports = res.data.linked.reports[db.id];
          }
        }

        return d.resolve(this.dashboardList);
      }
      , () => {
        d.reject();
        return this.dashboardListPromise = null;
      });

      return this.dashboardListPromise;
    }

    /*
     * Gets full details about a dashboard (suitable for edit)
     *
     * @param {Integer} dashboard_id
     * @return {promise}
     */
    getDashboardDetail(dashboard_id, forceReload) {
      if (forceReload == null) { forceReload = false; }
      dashboard_id = parseInt(dashboard_id);

      const d = this.$q.defer();

      if (this.lastDashboardDetail && (this.lastDashboardDetail.id === dashboard_id) && !forceReload) {
        d.resolve(this.lastDashboardDetail);
      } else {
        this.Api2.sendGet(`/dashboards/${dashboard_id}`).then( resp => {
          this.lastDashboardDetail = resp.data.data;
          this.lastDashboardDetail.version_id = this.version_id;
          this.lastDashboardDetail.reports_version_id = this.version_id;
          return d.resolve(this.lastDashboardDetail);
        });
      }

      return d.promise;
    }

    /*
     * Gets full details about a dashboard (suitable for view/edit)
     *
     * @param {Integer} dashboard_id
     * @return {promise}
     */
    getReportDetail(report_id, forceReload) {
      if (forceReload == null) { forceReload = false; }
      report_id = parseInt(report_id);

      const d = this.$q.defer();

      if (this.lastReportDetail && (this.lastReportDetail.id === report_id) && !forceReload) {
        d.resolve(this.lastReportDetail);
      } else {
        this.Api2.sendGet(`/dashboard_reports/${report_id}`).then( resp => {
          this.lastReportDetail = resp.data.data;
          return d.resolve(resp.data.data);
        });
      }

      return d.promise;
    }

    clearLastReportDetail() {
      return this.lastReportDetail = null;
    }

    /*
     * Gets a list of report id/title that exist on a dashboard.
     *
     * @return {promise}
     */
    getReportsList(dashboard_id) {
      const d = this.$q.defer();
      this.Api2.sendGet(`/dashboards/${dashboard_id}/reports`).then( resp => d.resolve(resp.data.data));

      return d.promise;
    }

    getAgents() {
      const d = this.$q.defer();

      if(this.agents) {
        d.resolve(this.agents);
      } else {
        this.Api2.sendGet('/agents/extended').then( function(res) {
          const agents = res.data.data;
          agents.map(agent => agent.avatar.url = (agent.avatar.url_pattern || agent.avatar.default_url_pattern).replace('{{IMG_SIZE}}', 20));
          this.agents = agents;

          return d.resolve(this.agents);
        });
      }

      return d.promise;
    }

    getAgentTeams() {
      const d = this.$q.defer();
      if(this.teams) {
        d.resolve(this.teams);
      } else {
        this.Api2.sendGet('/agent_teams').then( function(res) {
          const teams = res.data.data;
          teams.map(team => team.avatar.url = (team.avatar.url_pattern || team.avatar.default_url_pattern).replace('{{IMG_SIZE}}', 20));
          this.teams = teams;
          return d.resolve(this.teams);
        });
      }

      return d.promise;
    }

    getDepartments() {
      const d = this.$q.defer();
      if(this.departments) {
        d.resolve(this.departments);
      } else {
        this.Api2.sendGet('/ticket_departments').then( function(res) {
          this.departments = res.data.data;
          return d.resolve(this.departments);
        });
      }

      return d.promise;
    }

    getMe() {
      const d = this.$q.defer();
      this.Api2.sendGet('/me').then( res => d.resolve(res.data.data));

      return d.promise;
    }
  }
  return DashboardsInfo;
});
