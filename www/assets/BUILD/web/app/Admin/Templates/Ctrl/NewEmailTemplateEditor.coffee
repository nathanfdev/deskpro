define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], (Admin_Ctrl_Base, Strings) ->
  class Admin_Templates_Ctrl_NewEmailTemplateEditor extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Templates_Ctrl_NewEmailTemplateEditor'
    @CTRL_AS   = 'NewEmailTemplateEditor'
    @DEPS      = ['$modalInstance', 'templateName', 'dpTemplateManager', 'dpObTypesDefTicketActions', 'dpObTypesDefTicketCriteria']

    init: ->
      @$scope.display_title = @templateName

      @$scope.is_new_email = @templateName == null
      if @$scope.is_new_email
        @$scope.$watch('email.email_name', =>
          @$scope.email.email_name = @$scope.email.email_name || ''
          @$scope.email.email_name = @$scope.email.email_name.toLowerCase()
          @$scope.email.email_name = @$scope.email.email_name.replace(/\s/g, '-')
          @$scope.email.email_name = @$scope.email.email_name.replace(/[^a-z0-9\-_\.]/g, '')
          @validateName();
        )

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
        routePath: 'emails/templates_editor/' +  @templateName,
        template:  @templateName,
        onSave:    @$scope.dismiss
      }

      window.AdminBundle.render(reactProps, element)

      @$scope.$on('$destroy', ->
        window.AdminBundle.unmount(element);
      )

  Admin_Templates_Ctrl_NewEmailTemplateEditor.EXPORT_CTRL()