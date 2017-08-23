define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_ServerJobs_Ctrl_View extends Admin_Ctrl_Base

    @CTRL_ID   = 'Admin_ServerJobs_Ctrl_View'
    @CTRL_AS   = 'JobsViewCtrl'
    @DEPS      = ['$stateParams']

    init: ->
      @service = @DataService.get 'Jobs'
      @job =
        id: @$stateParams.id

    initialLoad: ->
      @service.get(@$stateParams.id).then (data) =>
        @job = data
        @service.loadJob(@job.id).then (data) =>
          @job = data

    getData: ->
      return angular.toJson(@job.data, true)


  Admin_ServerJobs_Ctrl_View.EXPORT_CTRL()
