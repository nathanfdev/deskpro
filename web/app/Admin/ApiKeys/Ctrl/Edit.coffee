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

			@$scope.replayLogEntry = (entry) =>
				return if !entry?.id?
				entry.response = null

				@service.keys.replayLogEntry(entry).then(
					(data) => entry.response = data
					=> entry.response = {status: null, content: null}
				)



		initialLoad: ->
			p1 = @service.keys.get(@$stateParams.id || null).then (model) =>
				console.log(model)
				return if !model?
				@form = angular.copy model
				@form.flags = @form.flags || []
				@form.isSuperUser = @form.flags.indexOf('super') > -1
				@form.isAdminManage = @form.flags.indexOf('admin_manage') > -1

			p2 = @service.agents.all().then (agents) => @agents = agents

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
				=>
					@stopSpinner 'saving', true
					@Growl.success 'Saved'
					@skipDirtyState()
					if is_new then @$state.go 'apps.api_keys.gocreate'
				=>
					@stopSpinner 'saving', true
					@Growl.error 'Error'
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