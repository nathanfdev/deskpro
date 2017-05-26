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
      promise2 = @Api2.sendGet('/email_templates/legacy_templates').then(
        (res) =>
          @toUpgradeTemplates = res.data
      )

      return @$q.all([promise1, promise2])

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

  Admin_Templates_Ctrl_EmailGroupList.EXPORT_CTRL()