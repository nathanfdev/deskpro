define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Languages_Ctrl_Install extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Languages_Ctrl_Install'
    @CTRL_AS = 'EditCtrl'

    init: ->
      @id = @$stateParams.id.replace(/^install\-/, '')

    initialLoad: ->
      promise = @Api.sendGet("/langs/#{@id}").then( (result) =>
        if result.data.language
          @$state.go('setup.languages.edit', {id: @id})
          return

        @pack = result.data.pack
        @lang = result.data.language
      )
      return promise

    doInstall: ->
      @startSpinner('saving')
      @$scope.$parent.ListCtrl.installLang(@id).then(=>
        @stopSpinner('saving', true)
        @$state.go('setup.languages.edit', {id: @id})
      )

  Admin_Languages_Ctrl_Install.EXPORT_CTRL()