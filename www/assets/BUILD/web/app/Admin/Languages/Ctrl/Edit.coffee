define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Languages_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Languages_Ctrl_Edit'
    @CTRL_AS = 'EditCtrl'

    init: ->
      @id = @$stateParams.id

      format = (flag) ->
        if not flag or not flag.text then return ''
        return "<img src='"+DP_ASSET_URL+"/images/flags/" + flag.id.toLowerCase() + "' style='margin-right: 2px;' />" + flag.text;

      @$scope.select2Flag = {
        formatResult: format,
        formatSelection: format,
        escapeMarkup: (m) -> return m
      }

      @$scope.isDefaultLang = =>
        return @lang && @lang.id && @lang.id == parseInt(@$scope.$parent.ListCtrl.default_lang_id)

    initialLoad: ->
      promise = @Api.sendGet("/langs/#{@id}").then( (result) =>
        if not result.data.language
          @$state.go('setup.languages.install', {id: "install-#{@id}"})
          return

        @pack = result.data.pack
        @lang = result.data.language
        @form = {
          title: @lang.title,
          flag_image: @lang.flag_image,
          locale: @lang.locale
        }
      )
      return promise

    startUninstall: ->
      if @lang.id == parseInt(@$scope.$parent.ListCtrl.default_lang_id)
        @showAlert('@no_delete_default')
        return

      inst = @$modal.open({
        templateUrl: @getTemplatePath('Languages/uninstall-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then(=>
        @startSpinner('saving')
        @$scope.$parent.ListCtrl.uninstallLang(@id).then(=>
          @$state.go('setup.languages')
        )
      )

    doSave: ->
      return if @$scope.form_props.$invalid

      @startSpinner('saving')
      @$scope.$parent.ListCtrl.saveLanguage(@id, {
        title: @form.title,
        flag_image: @form.flag_image,
        locale: @form.locale
      }).then( =>
        @stopSpinner('saving', true)
      )

  Admin_Languages_Ctrl_Edit.EXPORT_CTRL()