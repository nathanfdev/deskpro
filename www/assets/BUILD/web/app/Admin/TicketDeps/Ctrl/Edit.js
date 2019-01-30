define [
  'Admin/Main/Ctrl/Base',
  'Admin/Main/Model/DepAgentPermMatrix',
  'DeskPRO/Util/Util',
  'underscore'
], (
  Admin_Ctrl_Base,
  Admin_Main_Model_DepAgentPermMatrix,
  Util,
  _
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
      @$scope.usergroups_locked = {}

      @$scope.icon_image = null
      @$scope.$on 'icon.selected', (e, path) => @selectIcon path

      @depId = parseInt(@$stateParams.id)
      @depData = @DataService.get('TicketDeps')
      @all_perms =
        user_full:    true
        agent_assign: true
        agent_full:   true

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
        @brands      = data.brands

        @email_accounts  = data.email_accounts
        @dep_parent_list = data.dep_parent_list

        for name in ['link', 'win', 'embed', 'phpapi']
          tpl = @getTemplatePath("TicketDeps/code-"+name+".html")
          code = @$templateCache.get(tpl).replace(/%DEPID%/g, @dep.id)
          code_all = @$templateCache.get(tpl).replace(/%DEPID%/g, 0)
          @$scope['code_' + name] = code
          @$scope['code_all_' + name] = code_all

        for group in @usergroups
          if group.sys_name != 'everyone' && group.sys_name != 'registered' then continue
          @["group_#{group.sys_name}_id"] = group.id
          ((group) =>
            @["group_#{group.sys_name}_perm"] = @form.usergroup_perms[group.id].full
            @$scope.$watch (=> return @form.usergroup_perms[group.id].full), (newVal) =>
              @["group_#{group.sys_name}_perm"] = newVal
              for own id, g of @form.usergroup_perms
                id = parseInt(id)
                if id == @group_everyone_id then continue
                g.full = @group_everyone_perm || @group_registered_perm || g.full
                if id == @group_registered_id then @$scope.usergroups_locked[id] = @group_everyone_perm
                else @$scope.usergroups_locked[id] = @group_everyone_perm || @group_registered_perm
          )(group)

        for own id, group of @form.agent_perms.groups
          @all_perms.agent_assign = false if !group.perms.assign.locked && !group.perms.assign.state
          @all_perms.agent_full = false if !group.perms.full.locked && !group.perms.full.state

        for own id, agent of @form.agent_perms.agents
          @all_perms.agent_assign = false if !agent.perms.assign.locked && !agent.perms.assign.state
          @all_perms.agent_full = false if !agent.perms.full.locked && !agent.perms.full.state
      )

      get = {
        customActions: '/ticket_triggers/get-custom-actions'
      }
      if @depId
        get.trigger = "/ticket_triggers/departments/#{@depId}"
        get.trigger2 = "/ticket_triggers/departments_changed/#{@depId}"
      else
        get.trigger = "/ticket_triggers/newticket"
        get.trigger2 = "/ticket_triggers/update"

      promise2 = @Api.sendDataGet(get).then( (result) =>
        @customActions = result.data.customActions.action_defs

        if result.data && result.data.trigger
          if result.data.trigger.trigger
            @trigger = result.data.trigger.trigger
            @triggerId = @trigger.id

            if @trigger.actions?.actions?.length
              @$scope.actions_form = {}
              for action in @trigger.actions.actions
                rowId = _.uniqueId('action')
                @$scope.actions_form[rowId] = action
          else
            @trigger = result.data.trigger
            @triggerId = 0
        else
          @trigger = {}
          @triggerId = 0

        if result.data && result.data.trigger2
          if result.data.trigger2.trigger
            @trigger2 = result.data.trigger2.trigger
            @trigger2Id = @trigger2.id

            if @trigger2.actions?.actions?.length
              @$scope.actions_form2 = {}
              for action in @trigger2.actions.actions
                rowId = _.uniqueId('action')
                @$scope.actions_form2[rowId] = action
          else
            @trigger2 = result.data.trigger2
            @trigger2Id = 0
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
      return false
      # should check this
      # return not Util.equals(@form, @origForm)

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
          for own key, act of @$scope.actions_form
            if act.type
              postData.actions.push(act)
        p1 = @Api.sendPostJson('/ticket_triggers/departments/' + @dep.id, postData)

        postData = {
          actions:       []
        }
        if @$scope.actions_form2
          for own key, act of @$scope.actions_form2
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

        if @dep.has_children
          @successSaving()
          return

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
        @Growl.error info?.error_message if info?.error_message
      )

      deferred2.promise.then(=>
        @origForm = Util.clone(@form, true)
        @successSaving()
      )

      return deferred2.promise

    successSaving: ->
      @stopSpinner('saving_dep').then(=>
        @Growl.success(@getRegisteredMessage('saved_dep'), =>
          @$state.go('tickets.ticket_deps.edit', {id: @dep.id})
        )
      )

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


    handleBrand: (brandId, e) ->
      index = @form.brands.indexOf brandId
      if index == -1
        @form.brands.unshift brandId
      else
        if (@form.brands.length > 1)
          @form.brands.splice(index, 1)
        else
          alert "Departments need to be linked to at least one Brand"
          $(e.target).prop("checked", true)
          return true




    changeAllPerms: (group, perm) =>
      _perm = @all_perms[group + '_' + perm]
      if 'user' == group
        for own id, group of @form.usergroup_perms
          group.full = _perm
      if 'agent' == group
        for own id, group of @form.agent_perms.groups
          group.perms[perm].state = _perm if !group.perms[perm].locked && 'agent_all_perms' != group.model.sys_name && 'agent_all_safe_perms' != group.model.sys_name
        for own id, agent of @form.agent_perms.agents
          agent.perms[perm].state = _perm if !agent.perms[perm].locked




  Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL()
