define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TicketSlas_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketSlas_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'

		init: ->
			@slaData = @DataService.get('TicketSlas')
			@sla = null

			@criteraTypeDef = @dpObTypesDefTicketCriteria
			@actionsTypeDef = @dpObTypesDefTicketActions

			@$scope.criteriaOptionTypes = []
			@$scope.actionOptionTypes = []
			@updateCriteriaOptionTypes()

		updateCriteriaOptionTypes: ->
			types = []
			setCritOptions = @criteraTypeDef.getOptionsForTypes(types)
			@$scope.criteriaOptionTypes.length = 0
			for opt in setCritOptions
				@$scope.criteriaOptionTypes.push(opt)

			setActionOptions = @actionsTypeDef.getOptionsForTypes(types)
			@$scope.actionOptionTypes.length = 0
			for opt in setActionOptions
				@$scope.actionOptionTypes.push(opt)


		initialLoad: ->
			if @$stateParams.id
				promise = @slaData.loadEditSlaData(@$stateParams.id).then( (data) =>
					@sla = data.sla
					@form = @getFormFromModel(@sla)
				)
				return promise
			else
				@macro = {}
				@form = @getFormFromModel(@sla)
				return null

		getFormFromModel: (slaModel) ->
			form = {}
			form.title = slaModel.title || ''

			return form

	Admin_TicketSlas_Ctrl_Edit.EXPORT_CTRL()