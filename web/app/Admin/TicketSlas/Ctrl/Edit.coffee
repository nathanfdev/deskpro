define [
	'Admin/Main/Ctrl/Base',
	'Admin/TicketSlas/SlaFormMapper'
], (
	Admin_Ctrl_Base,
	SlaFormMapper
) ->
	class Admin_TicketSlas_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketSlas_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['dpObTypesDefTicketActions', 'dpObTypesDefTicketCriteria']

		init: ->
			@form = @getFormFromModel({})
			@slaData = @DataService.get('TicketSlas')
			@sla = null

			@actionsTypeDef = @dpObTypesDefTicketActions
			@criteraTypeDef = @dpObTypesDefTicketCriteria

			@$scope.criteriaOptionTypes = []
			@$scope.actionOptionTypes = []
			@updateCriteriaOptionTypes()

		updateCriteriaOptionTypes: ->
			types = []
			setActionOptions = @actionsTypeDef.getOptionsForTypes(types)
			@$scope.actionOptionTypes.length = 0
			for opt in setActionOptions
				@$scope.actionOptionTypes.push(opt)

			types = [
				'web', 'web.user', 'email', 'email.user', 'api', 'api.user',
				'web.agent', 'email.agent', 'api.agent'
			]
			setCritOptions = @criteraTypeDef.getOptionsForTypes(types)
			@$scope.criteriaOptionTypes.length = 0
			for opt in setCritOptions
				@$scope.criteriaOptionTypes.push(opt)


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
			form.criteria_sets = {}

			return form

	Admin_TicketSlas_Ctrl_Edit.EXPORT_CTRL()