define ['angular', 'Admin/Main/Ctrl/Base'], (angular, Admin_Ctrl_Base) ->
  class Admin_Templates_Ctrl_TemplateList extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Templates_Ctrl_TemplateList'
    @CTRL_AS   = 'ListCtrl'
    @DEPS      = []

    init: ->
      parts = @$stateParams.groupName.split(':')
      @bundle = parts.shift()
      @tplDir = parts.shift()

    initialLoad: ->
      promise = @Api.sendDataGet({
        info: '/templates-info'
      }).then( (res) =>
        @templates = res.data.info.list[@bundle][@tplDir].templates
      )
      return promise

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

  Admin_Templates_Ctrl_TemplateList.EXPORT_CTRL()