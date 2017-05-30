define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Templates_Ctrl_EmailGroupList extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Templates_Ctrl_EmailGroupList'
    @CTRL_AS   = 'ListCtrl'
    @DEPS      = []

    initialLoad: ->
      promise1 = @Api.sendDataGet({
        info: '/email-templates-info'
      }).then( (res) =>
        @templateInfo = res.data.info.list
      )
      promise2 = @loadLegacyTemplates()

      return @$q.all([promise1, promise2])

    loadLegacyTemplates: ->
      @Api2.sendGet('/email_templates/legacy_templates').then(
        (res) =>
          @$scope.toUpgradeTemplates = res.data
      )


    ###
    # Open an editor
    ###
    openEditor: (tpl) ->
      modalInstance = @$modal.open({
        templateUrl: @getTemplatePath('Templates/modal-template-editor.html'),
        controller: 'Admin_Templates_Ctrl_TemplateEditor',
        resolve: {
          templateName: ->
            return tpl.name
        }
      }).result.then( (info) =>
        if info.mode == 'custom'
          tpl.is_custom = true
        else if info.mode == 'revert'
          tpl.is_custom = false
      )

      return modalInstance

    revertTemplate: (id, triggers) ->
      message = if triggers then 'Are you sure you want to revert this template? The associated triggers will send the default template.' else
        'Are you sure you want to revert this template? Your changes will be completely lost and the template will be returned to the default.'
      @showConfirm(message).result.then(=>
        @Api2.sendGet('/email_templates/revert_legacy_template/' + id).then(
          () =>
            @loadLegacyTemplates()
        )
      )

    deleteTemplate: (id, triggers) ->
      message = if triggers then 'Are you sure you want to delete this template? The associated triggers actions will be also deleted.' else
        'Are you sure you want to delete this template? Your changes will be completely lost.'
      @showConfirm(message).result.then(=>
        @Api2.sendDelete('/email_templates/legacy_template/' + id).then(
          () =>
            @loadLegacyTemplates()
        )
      )

  Admin_Templates_Ctrl_EmailGroupList.EXPORT_CTRL()