define ['DeskPRO/Util/Arrays'], (Arrays) ->
  class DashboardService
    constructor: (Api, $q) ->
      @Api = Api
      @$q = $q
      @data = {}
      @storage = { dbs: [], reports: [] }
      @lastDashboard = null
      @lastReport = null

    getDbIndexById: (dbs, id) ->
      index = -1
      index = Arrays.findIndex dbs,
      (v) ->
        if v? and v.id is id
          return true
      return index

    findReportIndex: (report, reports) ->
      index = -1
      index = Arrays.findIndex reports,
        (v) ->
          if v? and v.id is report.id
            return true
      return index


    ###
    # Operations about dashboards
    ###
    saveDashboard: (dashboard) ->
      deferred = @$q.defer()
      url = '/dashboards'
      oldOne = false
      if dashboard.id
        oldOne = true
        url += "/#{dashboard.id}";

      data =
        title: dashboard.title
        reports: dashboard.reports
        permissions: dashboard.permissions

      @Api
        .sendPostJson url, data
        .then (response) =>
            if(response)
              dashboard.id = response.data.id
              dashboard.reports = response.data.reports
              dashboard.permissions = response.data.permissions
              if !oldOne
                @storage.dbs.push(dashboard)
              deferred.resolve dashboard
          , () =>
            console.error 'something goes wrong!'
      deferred.promise

    fillReportData: (report, data) ->
      name = "report#{report.id}"
      data[name] =
        deleted: report.deleted
        title: report.title
        id: report.id


    cloneDashboard: (dashboard) ->
      deferred = @$q.defer()
      url = "/dashboards/clone/#{dashboard.id}"
      @Api
        .sendPost url
        .then (response) =>
          if(response)
            clonedOne = response.data
            @storage.dbs.push clonedOne
            deferred.resolve clonedOne
        , () =>
          console.error('something goes wrong!')
      deferred.promise

    getDashboards: () ->
      deferred = @$q.defer()
      if @storage.dbs.length == 0
        @getDashboardsData().then \
          (resp) =>
            dbs = []
            if resp? and resp.data.length > 0

              dbs.push element for element in resp.data
            @storage.dbs = dbs;
            deferred.resolve(@storage.dbs)
      else deferred.resolve(@storage.dbs)

      return deferred.promise

    getDashboardById: (id) ->
      deferred = @$q.defer()

      id = parseInt(id)

      if @lastDashboard and @lastDashboard.id == id
        deferred.resolve(@lastDashboard)
        return deferred.promise

      @getDashboards().then((dbs) =>
        db = dbs.find((x) -> x.id == id)
        if not db
          deferred.reject()
        else
          @getDashboard(db).then((real_db) ->
            @lastDashboard = real_db
            deferred.resolve(real_db)
          )
      , -> deferred.reject())

      return deferred.promise

    getDashboard: (dashboard) ->
      deferred = @$q.defer()
      dashboardIndex = Arrays.findIndex @storage.dbs
      , (v, i) ->
        if v.id is dashboard.id then true else false
      @Api
        .sendGet "/dashboards/#{dashboard.id}"
        .then (resp) =>
          @storage.dbs[dashboardIndex] = resp.data
          deferred.resolve(resp.data)
      return deferred.promise

    getDashboardsData: () ->
      @Api.sendGet('/dashboards')

    deleteDashboard: (dashboard) ->
      promise = @Api.sendDelete("/dashboards/#{dashboard.id}")
      promise.then () =>
        Arrays.removeValue @storage.dbs, dashboard, 1
        return promise

    ###
    # Operations about reports
    ###

    getReportById: (id) ->
      deferred = @$q.defer()
      id = parseInt(id)

      if @lastReport and @lastReport.id == id
        deferred.resolve(@lastReport)
        return deferred.promise

      @getReport({id: id}).then((r) =>
        @lastReport = r
        deferred.resolve(@lastReport)
      , -> deferred.reject())

      return deferred.promise

    getReport: (report) ->
      deferred = @$q.defer()
      reportIndex = Arrays.findIndex @storage.reports
      , (v, i) ->
        if v.id is report.id then true else false
      if(!report.loaded)
        @Api
          .sendGet("/dashboards/reports/#{report.id}")
          .then (resp) =>
            @storage.reports[reportIndex] = resp.data
            deferred.resolve @storage.reports[reportIndex]
            return deferred.promise
      else
        deferred.resolve @storage.reports[reportIndex]
        return deferred.promise


    getReportsData: () ->
      @Api.sendGet('/dashboards/reports')

    getReports: () ->
      deferred = @$q.defer();
      if @storage.reports.length == 0
        @getReportsData().then \
          (resp) =>
            reports = []
            if resp? and resp.data.length > 0
              reports.push = element for element in resp.data
            @storage.reports = reports;
            deferred.resolve(reports)
      else deferred.resolve(@storage.reports)

    removeReport: (report) ->
      deferred = @$q.defer()
      url = "/dashboards/reports/#{report.id}"
      @Api
      .sendDelete url
      .then (response) =>
        if(response)
          db_id = @getDbIndexById @storage.dbs, report.dashboard_id
          Arrays.removeValue @storage.dbs[db_id].reports, report
          deferred.resolve @storage.dbs[db_id].reports
      , () =>
        console.error('something goes wrong!')
      deferred.promise

    createReport: (report) ->
      deferred = @$q.defer()
      url = "/dashboards/reports/#{report.dashboard_id}"

      newReport =
        title: report.title
        columns: if report.columns? then report.columns else 0
        loaded: false
        deleted: false
        widgets: []

      @Api
      .sendPost url, newReport
      .then (response) =>
        if(response)
          @storage.reports.push response.data
          deferred.resolve response.data
      , () =>
        console.error 'something goes wrong!'
      deferred.promise

    saveReport: (report) ->
      deferred = @$q.defer()
      url = "/dashboards/reports/#{report.id}/save"
      @Api
      .sendPostJson url, report
      .then (response) =>
        if(response)
          index = @findReportIndex(report, @storage.reports)
          @storage.reports[index] = response.data
          deferred.resolve response.data
      , () =>
        console.error 'something goes wrong!'
      deferred.promise

    cloneReport: (report, dashboard_id) ->
      deferred = @$q.defer()
      url = "/dashboards/reports/clone/#{report.id}/#{dashboard_id}"
      newReport =
        title: report.title
        columns: if report.columns? then report.columns else 0
        loaded: false
        widgets: []

      @Api
      .sendPost url, newReport
      .then (response) =>
        if(response)
          clonedOne = response.data
          clonedOne.dashboard_id = dashboard_id
          clonedOne.cloned = true
          @storage.reports.push clonedOne
          deferred.resolve clonedOne
      , () =>
        console.error 'something goes wrong!'
      deferred.promise


