define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ApiKeys_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ApiKeys_Ctrl_List'
    @CTRL_AS = 'ListCtrl'

    init: ->
      @service = @DataService.get 'ApiKeys'



    ###
    # Loads the list
    ###
    initialLoad: ->
      @service.all().then (list) => @list = list



  Admin_ApiKeys_Ctrl_List.EXPORT_CTRL()