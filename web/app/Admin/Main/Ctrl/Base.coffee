define [
  'DeskPRO/Main/Ctrl/Base'
], (
  DeskPRO_Main_Ctrl_Base
) ->
  class Admin_Ctrl_Base extends DeskPRO_Main_Ctrl_Base
    @CTRL_AS   = null
    @CTRL_ID   = 'Admin_Main_Ctrl_Base'
    @DEPS      = []



    ###*
    * Get the URL to the template
    *
    * @return {String}
    ###
    getTemplatePath: (path) ->
      return DP_BASE_ADMIN_URL+'/load-view/' + path