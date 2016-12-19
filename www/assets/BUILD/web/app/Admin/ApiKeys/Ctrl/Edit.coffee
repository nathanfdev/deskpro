define [
    'Admin/Main/Ctrl/Base'
    'angular'
], (
    Admin_Ctrl_Base
    angular
) ->
  class Admin_ApiKeys_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ApiKeys_Ctrl_Edit'
    @CTRL_AS = 'EditCtrl'
    @DEPS = ['$stateParams']

    init: ->
      @agents = []
      @form = {isSuperUser: false, flags: []}

      @service =
        keys:   @DataService.get 'ApiKeys'
        agents: @DataService.get 'Agents'
        tags:   @DataService.get 'ApiTags'

      @$scope.replayLogEntry = (entry) =>
        return if !entry?.id?
        entry.response = null
        @service.keys.replayLogEntry(entry).then(
          (data) => entry.response = data
          => entry.response = {status: null, content: null}
        )

      @$scope.toggle = (scope) ->
        scope.toggle()

      @$scope.enable = (node) =>
        node.value = 1
        @updateChildren node.nodes, node.value if node.nodes
        @service.tags.updateTags(node.path, node.value, @form.id)


      @$scope.default = (node) =>
        node.value = 0
        @updateChildren node.nodes, node.value if node.nodes
        @service.tags.updateTags(node.path, node.value, @form.id)

      @$scope.disable = (node) =>
        node.value = -1
        @updateChildren node.nodes, node.value if node.nodes
        @service.tags.updateTags(node.path, node.value, @form.id)

    updateChildren: (nodes, value) =>
      for node in nodes
        node.value = value
        @updateChildren node.nodes, value if node.nodes

    initialLoad: ->
      @form.daily_limit = @service.keys.limits.daily_limit
      @form.hourly_limit = @service.keys.limits.hourly_limit

      p1 = @service.keys.get(@$stateParams.id || null).then (model) =>
        return if !model?
        @form = angular.copy model
        @form.flags = @form.flags || []
        @form.isSuperUser = @form.flags.indexOf('super') > -1
        @form.isAdminManage = @form.flags.indexOf('admin_manage') > -1
        @form.daily_limit = @service.keys.limits.daily_limit if !@form.daily_limit
        @form.hourly_limit = @service.keys.limits.hourly_limit if !@form.hourly_limit


      p2 = @service.agents.all().then (agents) => @agents = agents

      # Load logs separately
      if @$stateParams.id
        @service.tags.getTags(@$stateParams.id).then((data) =>
          @tags = data.join(',')
        )

        @service.keys.getLogs({id: @$stateParams.id}).then((data) =>
          @logs = data.logs
        )
      else
        @tags = '*'


      return @$q.all([p1, p2])



    saveForm: ->
      is_new = !@form.id
      @form.flags = []

      if @form.isSuperUser
        @form.flags.push 'super'
      if @form.isAdminManage
        @form.flags.push 'admin_manage'

      @startSpinner 'saving'
      @service.keys.set(@form).then(
        (data) =>
          @form = data
          @form.flags = @form.flags || []
          @form.isSuperUser = @form.flags.indexOf('super') > -1
          @form.isAdminManage = @form.flags.indexOf('admin_manage') > -1
          @form
        =>
          @stopSpinner 'saving', true
          @Growl.error 'Error'
      ).then(
        (form) =>
          @service.tags.updateTags(@tags, form.id).then(
            (data) =>
              @stopSpinner 'saving', true
              @Growl.success 'Saved'
              @skipDirtyState()
              if is_new then @$state.go 'apps.api_keys.gocreate'
            (reason) =>
              @stopSpinner 'saving', true
              @Growl.error 'Error'
          )
      )



    ###
    # Show the delete dlg
    ###
    startDelete: (for_key_id) ->
      @service.keys.get(for_key_id).then (key) =>
        return if !key?

        inst = @$modal.open({
          templateUrl: @getTemplatePath('ApiKeys/delete-modal.html'),
          controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
            $scope.confirm = ->
              $modalInstance.close()

            $scope.dismiss = ->
              $modalInstance.dismiss()
          ]
        });

        inst.result.then =>
          @service.keys.remove(key).then(
            =>
              @$state.go 'apps.api_keys'
            (data) =>
              @applyErrorResponseToView data
          )



    regenerateApiKey: ->
      @service.keys.regenerateApiKey(@form).success =>
        @Growl.success("API Key regenerated")

  Admin_ApiKeys_Ctrl_Edit.EXPORT_CTRL()