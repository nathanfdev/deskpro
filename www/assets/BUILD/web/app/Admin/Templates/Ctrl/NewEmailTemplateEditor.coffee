define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], (Admin_Ctrl_Base, Strings) ->
  class Admin_Templates_Ctrl_NewEmailTemplateEditor extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Templates_Ctrl_NewEmailTemplateEditor'
    @CTRL_AS   = 'NewEmailTemplateEditor'
    @DEPS      = ['$modalInstance', 'templateName', 'dpTemplateManager', 'dpObTypesDefTicketActions', 'dpObTypesDefTicketCriteria']

    init: ->
      @$scope.display_title = @templateName

      @$scope.is_new_email = @templateName == null

      @$scope.dismiss = =>
        @$modalInstance.dismiss('cancel')

      @mountReactComponent()

    mountReactComponent: =>
      element = document.getElementById('new-email-template-editor')


      if (element == null)
        setTimeout @mountReactComponent, 1
        return

      modal = document.getElementById('new-email-template-editor-modal')

      modal.parentNode.parentNode.style.width = document.body.clientWidth * 0.9 + "px"
      modal.parentNode.parentNode.parentNode.style.zIndex = 10;

      backdrop = document.getElementsByClassName('modal-backdrop')[0]
      backdrop.style.zIndex = 10
      element.style.height = (document.body.clientHeight * 0.9 - 51) + "px"

      reactProps = {
        routePath:   'emails/templates_editor/' + @templateName,
        template:    @templateName,
        newTemplate: @templateName == null
        onSave:      @onSave
      }

      window.AdminBundle.render(reactProps, element)

      @$scope.$on('$destroy', ->
        window.AdminBundle.unmount(element);
      )

    onSave: (name) =>
      if (@$scope.is_new_email)
        tpl = {
          name: name,
          title: name.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html')
        }
        if @dpObTypesDefTicketActions.options_data
          @dpObTypesDefTicketActions.options_data.custom_email_tpls.push(tpl)
        if @dpObTypesDefTicketCriteria.options_data
          @dpObTypesDefTicketCriteria.options_data.custom_email_tpls.push(tpl)

        @$modalInstance.close({
          templateName: name,
          isNewEmail:   @$scope.is_new_email,
          mode:         'custom'
        })
      else
        @$scope.dismiss()


  Admin_Templates_Ctrl_NewEmailTemplateEditor.EXPORT_CTRL()