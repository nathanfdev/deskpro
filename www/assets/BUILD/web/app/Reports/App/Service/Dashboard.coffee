define ['DeskPRO/Util/Arrays'], (Arrays) ->
  class DashboardService
    constructor: (Api, Api2, $q) ->
      @Api = Api
      @Api2 = Api2
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
      data =
        title: dashboard.title
        reports: dashboard.reports
        permissions: dashboard.permissions
        is_agent: dashboard.is_agent

      if dashboard.id
        @Api2
          .sendPutJson "/dashboards/#{dashboard.id}", data
          .then (response) =>
            deferred.resolve response
      else
        @Api2
          .sendPostJson '/dashboards', data
          .then (response) =>
            if(response)
              dashboard.id = response.data.id
              dashboard.reports = response.data.reports
              dashboard.permissions = response.data.permissions
              @storage.dbs.push(dashboard)
              deferred.resolve dashboard
          , () ->
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
      url = "/dashboards#{dashboard.id}/clone"
      @Api2
        .sendPost url
        .then (response) =>
          if(response)
            clonedOne = response.data.data
            @storage.dbs.push clonedOne
            deferred.resolve clonedOne
        , () ->
          console.error 'something goes wrong!'
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
      @Api2
        .sendGet "/dashboards/#{dashboard.id}"
        .then (resp) =>
          @storage.dbs[dashboardIndex] = resp.data.data
          deferred.resolve(resp.data.data)
      return deferred.promise

    getDashboardsData: () ->
      @Api2.sendGet('/dashboards')

    deleteDashboard: (dashboard) ->
      promise = @Api2.sendDelete("/dashboards/#{dashboard.id}")
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
        @Api2
          .sendGet("/reports/#{report.id}")
          .then (resp) =>
            @storage.reports[reportIndex] = resp.data.data
            deferred.resolve @storage.reports[reportIndex]
            return deferred.promise
      else
        deferred.resolve @storage.reports[reportIndex]
        return deferred.promise


    getReportsData: () ->
      @Api2.sendGet('/dashboard_reports')

    getReports: () ->
      deferred = @$q.defer()
      if @storage.reports.length == 0
        @getReportsData().then \
          (resp) =>
            reports = []
            if resp? and resp.data.length > 0
              reports.push = (element for element in resp.data)
            @storage.reports = reports
            deferred.resolve(reports)
      else deferred.resolve(@storage.reports)

    removeReport: (report) ->
      deferred = @$q.defer()
      url = "/dashboard_reports/#{report.id}"
      @Api2
      .sendDelete url
      .then (response) =>
        if(response)
          db_id = @getDbIndexById @storage.dbs, report.dashboard_id
          Arrays.removeValue @storage.dbs[db_id].reports, report
          deferred.resolve @storage.dbs[db_id].reports
      , () ->
        console.error('something goes wrong!')
      deferred.promise

    createReport: (report) ->
      deferred = @$q.defer()
      url = "/dashboard_reports"

      newReport =
        dashboard: report.dashboard_id
        title: report.title

      @Api2
      .sendPost url, newReport
      .then (response) =>
        if(response)
          @storage.reports.push response.data.data
          deferred.resolve response.data.data
      , () ->
        console.error 'something goes wrong!'
      deferred.promise

    saveReport: (report) ->
      deferred = @$q.defer()
      url = "/dashboard_reports/#{report.id}"
      data = {
        title: report.title
        variables: report.variables
      }

      @Api2
      .sendPutJson url, data
      .then () ->
        return
      , () ->
        console.error 'something goes wrong!'
      deferred.promise


    saveReportVars: (report) ->
      d = @$q.defer()

      @Api2.sendPutJson \
        "/dashboard_reports/#{report.id}",
        {
          variables: report.variables
        }
        .then( (resp) ->
          d.resolve(resp.data)
          return d.promise
        )

      return d.promise

    cloneReport: (report, dashboard_id) ->
      deferred = @$q.defer()
      url = "/dashboard_reports/{report.id}/clone"
      newReport =
        title: report.title
        loaded: false
        widgets: []

      @Api2
      .sendPost url, newReport
      .then (response) =>
        if(response)
          clonedOne = response.data.data
          clonedOne.dashboard_id = dashboard_id
          clonedOne.cloned = true
          @storage.reports.push clonedOne
          deferred.resolve clonedOne
      , () ->
        console.error 'something goes wrong!'
      deferred.promise

    scheduleReport: (report, schedule, enabled) ->
      deferred = @$q.defer()
      if enabled == '1'
        data = {
          schedule: {
            frequency: schedule.frequency
            send_to: (schedule.send_to || '').split(',')
            when: {
              time: schedule.when.time
            }
          }
        }

        if schedule.frequency == 'weekly'
          data.schedule.when.weekday = schedule.when.weekday
        else if schedule.frequency == 'monthly'
          data.schedule.when.monthday = schedule.when.monthday
        else if schedule.frequency == 'bimonthly'
          data.schedule.when.monthday = schedule.when.monthday
          data.schedule.when.monthday2 = schedule.when.monthday2
      else
        data = {
          schedule: null
        }

      url = "/dashboard_reports/#{report.id}"
      @Api2.sendPutJson url, data
      .then () ->
        deferred.resolve()
      deferred.promise
