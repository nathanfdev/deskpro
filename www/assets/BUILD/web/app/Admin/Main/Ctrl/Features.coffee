define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_Main_Ctrl_Features extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Main_Ctrl_Features'
    @CTRL_AS = 'Features'
    @DEPS    = ['Api2', 'Growl', '$stateParams', '$sce', '$state']

    init: ->
      @feature = {
        id: @$stateParams.id
      }

      return

    initialLoad: ->
      return new Promise( (resolve) =>
        @Api2.sendGet('/features/' + @$stateParams.id).then( (res) =>
          @feature = res.data.data;
          @enable_description = @$sce.trustAsHtml(@feature.enable_description)
          @disable_description = @$sce.trustAsHtml(@feature.disable_description)

          if @$state.current.name == 'features.enable' and @feature.enabled and @feature.route_path
            window.location.hash = @feature.route_path
          else
            resolve()
        )
      )

    disableFeature: ->
      @Api2.sendPutJson('/features/' + @$stateParams.id + '/disable').then( =>
        @Growl.success('Feature is under disabling process');
        @$state.go('home');
      );

    enableFeature: ->
      @Api2.sendPutJson('/features/' + @$stateParams.id + '/enable').then( =>
        @Growl.success('Feature is under enabling process');
        @$state.go('home');
      );

  Admin_Main_Ctrl_Features.EXPORT_CTRL()
