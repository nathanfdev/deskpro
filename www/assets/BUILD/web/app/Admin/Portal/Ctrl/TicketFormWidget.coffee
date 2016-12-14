define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Functions', 'jquery'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_TicketFormWidget extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_TicketFormWidget'
    @CTRL_AS = 'Ctrl'
    @DEPS    = ['$http']

    init: ->
      @$scope.code = ''
      @$scope.language = ''
      @$scope.department = ''
      @$scope.width = 500
      @$scope.departments = []
      @$scope.languages = []

    initialLoad: ->
      @Api2.sendGet('/ticket_departments').then ({data}) =>
        departments = (data.data || []).map (dep) =>
          if dep.parent then dep.title = "- #{dep.title}"
          return dep
        @$scope.departments = departments
      @Api2.sendGet('/languages').then ({data}) =>
        @$scope.languages = data.data
        @$scope.language = data.data[0].locale

    getCode: ->
      params = {language: @$scope.language, department: @$scope.department, width: @$scope.width}
      if params.department
        params.hide_department = 1
      httpParams = {
        transformResponse: undefined
      }
      @Api2.sendGet('/ticket-form-widget/code', params, httpParams).then (response) =>
        @$scope.code = response.data

  Admin_Portal_Ctrl_TicketFormWidget.EXPORT_CTRL()
