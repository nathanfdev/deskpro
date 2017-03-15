define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_Main_Ctrl_Features extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Main_Ctrl_Features'
    @CTRL_AS = 'Features'
    @DEPS    = ['Api2', 'Growl', '$stateParams', '$sce']

    init: ->

      @feature = {
        id: @$stateParams.id
      }

      return

    initialLoad: ->
      @Api2.sendGet('/features/' + @$stateParams.id).then( (res) =>
        @feature = res.data.data;
        @enable_description = @$sce.trustAsHtml(res.data.data.enable_description)
        @disable_description = @$sce.trustAsHtml(res.data.data.disable_description)
      )

    disableFeature: ->
      @Api2.sendPutJson('/features/' + @$stateParams.id + '/disable').then( =>
        @Growl.success('Feature is under disabling process');
      );

    enableFeature: ->
      @Api2.sendPutJson('/features/' + @$stateParams.id + '/enable').then( =>
        @Growl.success('Feature is under enabling process');
      );

  Admin_Main_Ctrl_Features.EXPORT_CTRL()