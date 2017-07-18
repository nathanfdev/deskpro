define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ServerJobs_Ctrl_List extends Admin_Ctrl_Base

    @CTRL_ID   = 'Admin_ServerJobs_Ctrl_List'
    @CTRL_AS   = 'JobsListCtrl'
    @DEPS      = ['Api2']

    init: ->
      @service = @DataService.get 'Jobs'
      @pagination =
        page: 1

    initialLoad: ->
      @service.loadList(null, {page: 1}).then( (data) =>
        @jobs = data
        @pagination = @service.getPagination()
      )

  Admin_ServerJobs_Ctrl_List.EXPORT_CTRL()