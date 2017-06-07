define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_UserGroups_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_UserGroups_Ctrl_Edit'
    @CTRL_AS = 'EditCtrl'
    @DEPS    = ['$stateParams', '$q']

    init: ->
      @groupId = parseInt(@$stateParams.id) || 0
      @ugData = @DataService.get('UserGroups')

      @service =
        ticketDeps: @DataService.get 'TicketDeps'
        chatDeps: @DataService.get 'ChatDeps'

      @all_perms =
        perms: {}
        deps_perms:
          tickets: {full: false}
          chat: {full: false}

      @group = null

    initialLoad: ->
      groupPromise = @ugData.loadEditUserGroupData(@$stateParams.id || null);
      promises = [groupPromise, @service.ticketDeps.all(), @service.chatDeps.all()]

      @$q.all(promises).then (res) =>

        @group     = res[0].group
        @form      = res[0].form
        @perm_form = @group.perms
        @perm_form.options = {}

        if @group.sys_name == 'everyone'
          @perm_form_everyone = null
          @perm_form_reg      = null
        else if @group.sys_name == 'registered'
          @perm_form_everyone = if res[0].everyone_group.is_enabled then res[0].everyone_group.perms else null
          @perm_form_reg      = null
        else
          @perm_form_everyone = if res[0].everyone_group.is_enabled then res[0].everyone_group.perms else null
          @perm_form_reg      = if res[0].reg_group.is_enabled     then res[0].reg_group.perms       else null

        if @perm_form?.ticket?.reopen_resolved_createnew || @perm_form_reg?.ticket?.reopen_resolved_createnew
          @perm_form.options.reopen_resolved_createnew = 'new_ticket'
        else
          @perm_form.options.reopen_resolved_createnew = 'reject'


        # deps need to be flattened to show in the table
        @chatDeps = []
        for dep in res[2]
          @chatDeps.push(dep)
          if dep.children
            for subdep in dep.children
              subdep.depth = 1
              @chatDeps.push(subdep)


        # deps need to be flattened to show in the table
        @ticketDeps = []
        for dep in res[1]
          @ticketDeps.push(dep)
          if dep.children
            for subdep in dep.children
              subdep.depth = 1
              @ticketDeps.push(subdep)

        @assignDepsPerms @group
        @updateAllPermsState()

    saveForm: ->
      if not @$scope.form_props.$valid
        return

      is_new = !@group.id
      promise = @ugData.saveFormModel(@group, @form, @perm_form)

      @startSpinner('saving')
      promise.then( =>
        @stopSpinner('saving', true).then(=>
          @Growl.success("Saved")
        )

        @skipDirtyState()
        if is_new
          @$state.go('crm.groups.gocreate')
      )

    ###
      # Shows the copy settings modal
      ###
    showDelete: ->
      deleteGroup = =>
        p = @ugData.removeGroupById(@groupId)
        p.then(=>
          @$state.go('crm.groups')
        )
        return p

      group = @group
      inst = @$modal.open({
        templateUrl: @getTemplatePath('UserGroups/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.group = group
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.doDelete = (options) ->
            $scope.is_loading = true
            deleteGroup().then(-> $modalInstance.dismiss())
        ]
      });


    assignDepsPerms: (group) ->

      group.deps_perms = {
        tickets: {},
        chat: {}
      }

      for dep in @ticketDeps
        assign = false
        full = false

        if dep.permissions?.usergroups
          u = dep.permissions.usergroups.filter((x) => x.id == group.id)[0]
          if u
            if u.name == 'full' then full = true else assign = true

        group.deps_perms.tickets[dep.id] = { assign: assign, full: full }

      for dep in @chatDeps
        full = false
        if dep.permissions?.usergroups
          u = dep.permissions.usergroups.filter((x) => x.id == group.id)[0]
          if u
            full = true

        group.deps_perms.chat[dep.id] = { full: full }

    updateAllPermsState: ->
      return if !@group?

      for own section, perms of @group.perms
        enabled = true
        for own perm of perms
          if !perms[perm]
            enabled = false
            break
        @all_perms.perms[section] = enabled

      return if !@group.deps_perms
      for own type, sections of @all_perms.deps_perms
        for own section of sections
          enabled = true
          for own dep of @group.deps_perms[type]
            if !@group.deps_perms[type][dep][section]
              enabled = false
          @all_perms.deps_perms[type][section] = enabled

  changeAllPerms: (type, section) ->
    return if !@group?

    if 'perms' == type and @group.perms and @group.perms[section]
      for own perm of @group.perms[section]
        @group.perms[section][perm] = @all_perms[type][section]

      if 'people' == section
        @changeAllPerms('perms', 'org')

    else if 'deps_perms_tickets' == type and @group.deps_perms.tickets
      for own dep of @group.deps_perms.tickets
        @group.deps_perms.tickets[dep][section] = @all_perms.deps_perms.tickets[section]

    else if 'deps_perms_chat' == type and @group.deps_perms.chat
      for own dep of @group.deps_perms.chat
        @group.deps_perms.chat[dep][section] = @all_perms.deps_perms.chat[section]

  Admin_UserGroups_Ctrl_Edit.EXPORT_CTRL()