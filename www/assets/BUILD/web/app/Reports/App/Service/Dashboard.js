define(['DeskPRO/Util/Arrays'], function(Arrays) {
  class DashboardService {
    constructor(Api, Api2, $q) {
      this.getShareableLinks = this.getShareableLinks.bind(this);
      this.Api = Api;
      this.Api2 = Api2;
      this.$q = $q;
      this.data = {};
      this.storage = { dbs: [], reports: [] };
      this.lastDashboard = null;
      this.lastReport = null;
    }

    getDbIndexById(dbs, id) {
      let index = -1;
      index = Arrays.findIndex(dbs,
      function(v) {
        if ((v != null) && (v.id === id)) {
          return true;
        }
      });
      return index;
    }

    findReportIndex(report, reports) {
      let index = -1;
      index = Arrays.findIndex(reports,
        function(v) {
          if ((v != null) && (v.id === report.id)) {
            return true;
          }
      });
      return index;
    }


    /*
     * Operations about dashboards
     */
    saveDashboard(dashboard) {
      const deferred = this.$q.defer();
      const data = {
        title: dashboard.title,
        reports: dashboard.reports,
        permissions: dashboard.permissions,
        is_agent: dashboard.is_agent
      };

      if (dashboard.id) {
        this.Api2
          .sendPutJson(`/dashboards/${dashboard.id}`, data)
          .then(response => {
            return deferred.resolve(response);
        }).catch(response => {
            return deferred.reject(response.data);
        });
      } else {
        this.Api2
          .sendPostJson('/dashboards', data)
          .then(response => {
            if(response) {
              dashboard.id = response.data.data.id;
              dashboard.reports = response.data.data.reports;
              dashboard.permissions = response.data.data.permissions;
              this.storage.dbs.push(dashboard);
              return deferred.resolve(dashboard);
            }
        }).catch(response => {
            return deferred.reject(response.data);
        });
      }

      return deferred.promise;
    }

    fillReportData(report, data) {
      const name = `report${report.id}`;
      return data[name] = {
        deleted: report.deleted,
        title: report.title,
        id: report.id
      };
    }


    cloneDashboard(dashboard) {
      const deferred = this.$q.defer();
      const url = `/dashboards${dashboard.id}/clone`;
      this.Api2
        .sendPost(url)
        .then(response => {
          if(response) {
            const clonedOne = response.data.data;
            this.storage.dbs.push(clonedOne);
            return deferred.resolve(clonedOne);
          }
        }
        , () => console.error('something goes wrong!'));
      return deferred.promise;
    }

    getDashboards() {
      const deferred = this.$q.defer();
      if (this.storage.dbs.length === 0) {
        this.getDashboardsData().then( 
          resp => {
            const dbs = [];
            if ((resp != null) && (resp.data.data.length > 0)) {

              for (let element of Array.from(resp.data.data)) { dbs.push(element); }
            }
            this.storage.dbs = dbs;
            return deferred.resolve(this.storage.dbs);
        });
      } else { deferred.resolve(this.storage.dbs); }

      return deferred.promise;
    }

    getDashboardById(id) {
      const deferred = this.$q.defer();

      id = parseInt(id);

      if (this.lastDashboard && (this.lastDashboard.id === id)) {
        deferred.resolve(this.lastDashboard);
        return deferred.promise;
      }

      this.getDashboards().then(dbs => {
        const db = dbs.find(x => x.id === id);
        if (!db) {
          return deferred.reject();
        } else {
          return this.getDashboard(db).then(function(real_db) {
            this.lastDashboard = real_db;
            return deferred.resolve(real_db);
          });
        }
      }
      , () => deferred.reject());

      return deferred.promise;
    }

    getDashboard(dashboard) {
      const deferred = this.$q.defer();
      const dashboardIndex = Arrays.findIndex(this.storage.dbs
      , function(v, i) {
        if (v.id === dashboard.id) { return true; } else { return false; }
      });
      this.Api2
        .sendGet(`/dashboards/${dashboard.id}?include=reports&inline_sideloads=true`)
        .then(resp => {
          this.storage.dbs[dashboardIndex] = resp.data.data;
          return deferred.resolve(resp.data.data);
      });
      return deferred.promise;
    }

    getDashboardsData() {
      return this.Api2.sendGet('/dashboards?include=reports&inline_sideloads=true');
    }

    deleteDashboard(dashboard) {
      const promise = this.Api2.sendDelete(`/dashboards/${dashboard.id}`);
      promise.then(() => {
        this.storage.dbs.splice(this.storage.dbs.indexOf(dashboard), 1);
        return promise;
      });

      return promise;
    }

    /*
     * Operations about reports
     */

    getReportById(id) {
      const deferred = this.$q.defer();
      id = parseInt(id);

      if (this.lastReport && (this.lastReport.id === id)) {
        deferred.resolve(this.lastReport);
        return deferred.promise;
      }

      this.getReport({id}).then(r => {
        this.lastReport = r;
        return deferred.resolve(this.lastReport);
      }
      , () => deferred.reject());

      return deferred.promise;
    }

    getReport(report) {
      const deferred = this.$q.defer();
      const reportIndex = Arrays.findIndex(this.storage.reports
      , function(v, i) {
        if (v.id === report.id) { return true; } else { return false; }
      });
      if(!report.loaded) {
        return this.Api2
          .sendGet(`/reports/${report.id}`)
          .then(resp => {
            this.storage.reports[reportIndex] = resp.data.data;
            deferred.resolve(this.storage.reports[reportIndex]);
            return deferred.promise;
        });
      } else {
        deferred.resolve(this.storage.reports[reportIndex]);
        return deferred.promise;
      }
    }


    getReportsData() {
      return this.Api2.sendGet('/dashboard_reports');
    }

    getReports() {
      const deferred = this.$q.defer();
      if (this.storage.reports.length === 0) {
        return this.getReportsData().then( 
          resp => {
            const reports = [];
            if ((resp != null) && (resp.data.length > 0)) {
              reports.push = (Array.from(resp.data));
            }
            this.storage.reports = reports;
            return deferred.resolve(reports);
        });
      } else { return deferred.resolve(this.storage.reports); }
    }

    removeReport(report) {
      const deferred = this.$q.defer();
      const url = `/dashboard_reports/${report.id}`;
      this.Api2
      .sendDelete(url)
      .then(response => {
        if(response) {
          const db_id = this.getDbIndexById(this.storage.dbs, report.dashboard_id);
          Arrays.removeValue(this.storage.dbs[db_id].reports, report);
          return deferred.resolve(this.storage.dbs[db_id].reports);
        }
      }
      , () => console.error('something goes wrong!'));
      return deferred.promise;
    }

    createReport(report) {
      const deferred = this.$q.defer();
      const url = "/dashboard_reports";

      const newReport = {
        dashboard: report.dashboard_id,
        title: report.title
      };

      this.Api2
      .sendPost(url, newReport)
      .then(response => {
        if(response) {
          this.storage.reports.push(response.data.data);
          return deferred.resolve(response.data.data);
        }
      }
      , () => console.error('something goes wrong!'));
      return deferred.promise;
    }

    saveReport(report) {
      const deferred = this.$q.defer();
      const url = `/dashboard_reports/${report.id}`;
      const data = {
        title: report.title,
        variables: report.variables
      };

      this.Api2
      .sendPutJson(url, data)
      .then(function() {
        deferred.resolve();
      }
      , function() {
        deferred.reject();
        return console.error('something goes wrong!');
      });
      return deferred.promise;
    }


    saveReportVars(report, saveForCurrentAgent) {
      const d = this.$q.defer();

      this.Api2.sendPostJson( 
        `/dashboard_reports/${report.id}/variables`,
        {
          variables: report.variables,
          saveForCurrentAgent: !!saveForCurrentAgent
        })
        .then( function(resp) {
          d.resolve(resp.data);
          return d.promise;
        });

      return d.promise;
    }

    cloneReport(report, dashboard_id) {
      const deferred = this.$q.defer();
      const url = "/dashboard_reports/{report.id}/clone";
      const newReport = {
        title: report.title,
        loaded: false,
        widgets: []
      };

      this.Api2
      .sendPost(url, newReport)
      .then(response => {
        if(response) {
          const clonedOne = response.data.data;
          clonedOne.dashboard_id = dashboard_id;
          clonedOne.cloned = true;
          this.storage.reports.push(clonedOne);
          return deferred.resolve(clonedOne);
        }
      }
      , () => console.error('something goes wrong!'));
      return deferred.promise;
    }

    scheduleReport(report, schedule, enabled) {
      let data;
      const deferred = this.$q.defer();
      if (parseInt(enabled, 10) === 1) {
        data = {
          schedule: {
            frequency: schedule.frequency,
            send_to: (schedule.send_to || '').split(','),
            when: {
              time: schedule.when.time
            }
          }
        };

        if (schedule.frequency === 'weekly') {
          data.schedule.when.weekday = schedule.when.weekday;
        } else if (schedule.frequency === 'monthly') {
          data.schedule.when.monthday = schedule.when.monthday;
        } else if (schedule.frequency === 'bimonthly') {
          data.schedule.when.monthday = schedule.when.monthday;
          data.schedule.when.monthday2 = schedule.when.monthday2;
        }
      } else {
        data = {
          schedule: null
        };
      }

      const url = `/dashboard_reports/${report.id}`;
      this.Api2.sendPutJson(url, data)
      .then(() => deferred.resolve());
      return deferred.promise;
    }

    getShareableLinks(dashboardId) {
      const d = this.$q.defer();
      this.Api2.sendGet(`/dashboards/${dashboardId}/shareable_links`).then( function(res) {
        const links = res.data.data;
        for (let link of Array.from(links)) {
          link.ip_whitelist = link.ip_whitelist.join(',');
        }

        return d.resolve(links);
      });

      return d.promise;
    }

    createShareLink(sharedLink) {
      const data = {
        title: sharedLink.title,
        dashboard: sharedLink.dashboard,
        default_report: sharedLink.default_report,
        who_can_use: sharedLink.who_can_use,
        ip_whitelist: sharedLink.ip_whitelist
      };

      const d = this.$q.defer();
      this.Api2.sendPostJson("/dashboard_shareable_links", data)
        .success(res => d.resolve(res.data))
        .catch(res => d.reject(res.data));

      return d.promise;
    }

    updateShareLink(sharedLink) {
      const data = {
        title: sharedLink.title,
        default_report: sharedLink.default_report,
        who_can_use: sharedLink.who_can_use,
        ip_whitelist: sharedLink.ip_whitelist
      };

      const d = this.$q.defer();
      this.Api2.sendPutJson(`/dashboard_shareable_links/${sharedLink.id}`, data)
        .success(res => d.resolve(res.data))
        .catch(res => d.reject(res.data));

      return d.promise;
    }

    deleteDashboardShareableLink(sharedLink) {
      return this.Api2.sendDelete(`/dashboard_shareable_links/${sharedLink.id}`);
    }

    createShortUrlForShareLink(sharedLink) {
      const d = this.$q.defer();
      this.Api2.sendPostJson(`/dashboard_shareable_links/${sharedLink.id}/create_short_url`).then(( res => d.resolve(res.data.data))
      );

      return d.promise;
    }
  }
  return DashboardService;
});
