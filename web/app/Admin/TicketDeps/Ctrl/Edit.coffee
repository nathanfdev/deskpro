define [
  'Admin/Main/Ctrl/Base',
  'Admin/Main/Model/DepAgentPermMatrix',
  'DeskPRO/Util/Util'
], (
  Admin_Ctrl_Base,
  Admin_Main_Model_DepAgentPermMatrix,
  Util
) ->
  class Admin_TicketDeps_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketDeps_Ctrl_Edit'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['$templateCache', 'dpObTypesDefTicketActions', '$location', '$upload', '$http']

    init: ->
      @actionsTypeDef = @dpObTypesDefTicketActions
      @$scope.actionOptionTypes = []
      @$scope.actions_form = {}
      @$scope.actions_form2 = {}

      @$scope.icon_image = null
      @$scope.$on 'icon.selected', (e, path) => @selectIcon path

      @depId = parseInt(@$stateParams.id)
      @depData = @DataService.get('TicketDeps')
      @$scope.$watch('EditCtrl.form.parent_id', (newVal) =>
        newVal = parseInt(newVal)
        if not newVal
          @$scope.show_parent_warning = false
          return

        parent = @depData.findListModelById(newVal)
        if parent and not parent.children.length
          @$scope.show_parent_warning = parent
        else
          @$scope.show_parent_warning = false
      )

      @$scope.embed_code_type = 'department'

      @$scope.embedEditorLoaded = (editor) ->
        $(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor')

    resetForm: ->
      @form = Util.clone(@origForm, true)

    updateCriteriaOptionTypes: ->
      types = ['web', 'web.user']
      setActionOptions = @actionsTypeDef.getOptionsForTypes(types, { dynamicOptions: @customActions })
      @$scope.actionOptionTypes.length = 0
      for opt in setActionOptions
        @$scope.actionOptionTypes.push(opt)

    initialLoad: ->
      promise1 = @depData.getEditDepartmentData(@depId || null).then( (data) =>
        @dep  = data.dep
        @form = data.form
        @is_custom_layout = @form.use_custom_layout
        @origForm = Util.clone(@form, true)
        @layout_info = data.layout_info

        @setAvatar @dep.avatar

        if @depId
          @layout_info.default = @layout_info.default.filter((x) => return x.id != @depId)
          @layout_info.custom = @layout_info.custom.filter((x) => return x.id != @depId)

        @usergroups  = data.usergroups
        @agentgroups = data.agentgroups
        @agents      = data.agents

        @email_accounts  = data.email_accounts
        @dep_parent_list = data.dep_parent_list

        for name in ['link', 'win', 'embed', 'phpapi']
          tpl = @getTemplatePath("TicketDeps/code-"+name+".html")
          code = @$templateCache.get(tpl).replace(/%DEPID%/g, @dep.id)
          code_all = @$templateCache.get(tpl).replace(/%DEPID%/g, 0)
          @$scope['code_' + name] = code
          @$scope['code_all_' + name] = code_all
      )

      get = {
        customActions: '/ticket_triggers/get-custom-actions'
      }
      if @depId
        get.trigger = "/ticket_triggers/departments/#{@depId}"
        get.trigger2 = "/ticket_triggers/departments_changed/#{@depId}"

      promise2 = @Api.sendDataGet(get).then( (result) =>
        @customActions = result.data.customActions.action_defs

        if result.data?.trigger?.trigger?
          @trigger = result.data.trigger.trigger
          @triggerId = @trigger.id

          if @trigger.actions?.actions?.length
            @$scope.actions_form = {}
            for action in @trigger.actions.actions
              rowId = _.uniqueId('action')
              @$scope.actions_form[rowId] = action
        else
          @trigger = {}
          @triggerId = 0

        if result.data?.trigger2?.trigger?
          @trigger2 = result.data.trigger2.trigger
          @trigger2Id = @trigger2.id

          if @trigger2.actions?.actions?.length
            @$scope.actions_form2 = {}
            for action in @trigger2.actions.actions
              rowId = _.uniqueId('action')
              @$scope.actions_form2[rowId] = action
        else
          @trigger2 = {}
          @trigger2Id = 0
      )

      promise3 = @actionsTypeDef.loadDataOptions()

      promises = [promise1, promise2, promise3]

      return @$q.all(promises).then(=>
        @updateCriteriaOptionTypes()

        search = @$location.search()
        if search and search.tab
          @$scope.dp_tab_ids.main = search.tab
      )

    isDirtyState: ->
      return not Util.equals(@form, @origForm)

    ###*
    # Save everything
    ###
    saveAll: ->
      if not @$scope.form_props.$valid
        return

      # @form is used due to the reason that upon clicking on submit button parent_id still has old value
      if @depData.hasChildrenAndChangedParent(@dep, @form)
        @showAlert("You cannot change parent of this department as it has sub-departments. Move or delete the sub-departments first.")
        return

      @startSpinner('saving_dep')

      deferred2 = @$q.defer()

      triggerSaver = =>
        if @dep.has_children then return
        postData = {
          actions:       []
        }
        if @$scope.actions_form
          for own _, act of @$scope.actions_form
            if act.type
              postData.actions.push(act)
        p1 = @Api.sendPostJson('/ticket_triggers/departments/' + @dep.id, postData)

        postData = {
          actions:       []
        }
        if @$scope.actions_form2
          for own _, act of @$scope.actions_form2
            if act.type
              postData.actions.push(act)
        p2 = @Api.sendPostJson('/ticket_triggers/departments_changed/' + @dep.id, postData)

        return @$q.all([p1,p2])

      if @form.enable_avatar
        @form.avatar = @dep.avatar?.id || null
      else
        @form.avatar = null

      promise = @depData.saveFormModel(@dep, @form)
      promise.then(=>
        triggerSaver()

        if @dep.has_children then return

        if (@form.use_custom_layout)
          @Api.sendPostJson("/ticket_layouts/#{@dep.id}", {layout: @form.custom_layout}).then(-> deferred2.resolve())
        else
          @Api.sendPostJson("/ticket_layouts/default", {layout: @form.default_layout}).then(-> deferred2.resolve())
          @Api.sendDelete("/ticket_layouts/#{@dep.id}")

        @is_custom_layout = @form.use_custom_layout
      )
      promise.error( (info, code) =>
        @stopSpinner('saving_dep')
        @applyErrorResponseToView(info)
      )

      deferred2.promise.then(=>
        @origForm = Util.clone(@form, true)
        @stopSpinner('saving_dep').then(=>
          @Growl.success(@getRegisteredMessage('saved_dep'), =>
            @$state.go('tickets.ticket_deps.edit', {id: @dep.id})
          )
        )
      )

      return deferred2.promise

    propogatePermission: (obj, perm) ->
      if @_propogatePermission_running then return
      @_propogatePermission_running = true
      if obj.type == 'group'
        @form.agent_perms.setGroupPerm(obj.model.id, perm, '&')
      else
        @form.agent_perms.setAgentPerm(obj.model.id, perm, '&')
      @_propogatePermission_running = false

    ###
    # Open the email editor
    ###
    showEmailEditor: (template_name, custom_name) ->
      modalInstance = @$modal.open({
        templateUrl: DP_BASE_ADMIN_URL+'/load-view/Templates/modal-email-editor.html',
        controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
        resolve: {
          templateName: ->
            return custom_name

          variantOf: ->
            return template_name
        }
      })

      return modalInstance



    setAvatar: (blob) =>
      @dep.avatar = blob
      if !blob?
        @$scope.icon_image = null
        @form.enable_avatar = false
      else
        @$scope.icon_image = blob.thumbnail_url_50
        @form.enable_avatar = true



    onFileSelect: (files) ->
      @$scope.uploading = false
      file = files[0]

      @$upload.upload({
        url: @$http.formatApiUrl('/misc/upload'),
        data: { is_image: true },
        file: file
      }).success( (data) =>
        @$scope.uploading = false
        @setAvatar data.blob
      ).error( (data) =>
        @$scope.uploading = false
        @Growl.error data?.error_message || 'Error'
      )



    selectIcon: (image) =>
      setAvatar null if !image?

      @$scope.uploading = true
      @Api.sendPostJson('/misc/upload', {path: image, is_image: true}).then(
        (data) =>
          @$scope.uploading = false
          @setAvatar data.data.blob
        () =>
          @$scope.uploading = false
      )

  Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL()