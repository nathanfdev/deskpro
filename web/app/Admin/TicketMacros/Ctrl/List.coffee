define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TicketMacros_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketMacros_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@DEPS = ['$state', '$stateParams', 'DataService']
		@CTRL_TYPE = 'list'

		init: ->
			@list = []
			@macroData = @DataService.get('TicketMacros')

		initialLoad: ->
			promise = @macroData.loadList()
			promise.then( (list) =>
				@list = list
			)

			return promise

		###
		# Show the delete dlg
		###
		startDelete: (macro) ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('TicketMacros/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close();

					$scope.dismiss = ->
						$modalInstance.dismiss();
				]
			});

			inst.result.then( =>
				@macroData.deleteMacroById(macro.id).then(=>
					if @$state.current.name == 'tickets.ticket_macros.edit' and parseInt(@$state.params.id) == macro.id
						@$state.go('tickets.ticket_macros')
				)
			)

	Admin_TicketMacros_Ctrl_List.EXPORT_CTRL()