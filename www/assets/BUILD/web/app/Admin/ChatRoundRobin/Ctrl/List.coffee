define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ChatRoundRobin_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ChatRoundRobin_Ctrl_List'
    @DEPS = ['$timeout']
    @CTRL_AS = 'ListCtrl'



    init: ->
      @service = @DataService.get 'ChatRoundRobin'
      @robins = []
      @settings = null



    initialLoad: ->
      @service.all().then (robins) =>
        @robins = robins
      @service.getSettings().then (settings) =>
        @settings = settings



    save: ($event) ->
      $event.stopImmediatePropagation();

      @settings.enabled = !@settings.enabled
      return @service.saveSettings()


  Admin_ChatRoundRobin_Ctrl_List.EXPORT_CTRL()