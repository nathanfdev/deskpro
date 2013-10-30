define [
	'DeskPRO/Util/Arrays'
	'Admin/Main/Ctrl/Base',
	'Admin/Main/Model/DepAgentPermMatrix'
], (
	Arrays,
	Admin_Ctrl_Base,
	Admin_Main_Model_DepAgentPermMatrix
) ->
	class Admin_TicketTriggers_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketTriggers_Ctrl_Edit'
		@CTRL_AS   = 'TicketTriggersEdit'
		@CTRL_TYPE = 'page'
		@DEPS      = ['em', '$stateParams', 'dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions']

		init: ->
			@triggerType = @$stateParams.type
			@trigger     = null
			@triggerId   = @$stateParams.id
			@options     = {}

			@$scope.triggerType = @$stateParams.type
			@$scope.triggerId   = @$stateParams.id

			@$scope.typeForm = {
				by_user: true,
				by_agent: false,
				by_agent_opt: {
					web: true,
					email: true,
					api: true
				},
				by_user_opt: {
					web_portal: true,
					web_widget: true,
					web_form: true,
					email: true,
					api: true
				}
			}

			@criteraTypeDef = @dpObTypesDefTicketCriteria
			@actionsTypeDef = @dpObTypesDefTicketActions

			@$scope.criteriaOptionTypes = []
			@$scope.actionOptionTypes = []
			@updateCriteriaOptionTypes()

			@$scope.trigger_criteria_set = {
				first: {},
				second: {}
			}

			@$scope.trigger_actions = {}

			@$scope.$watch('typeForm', =>
				@updateCriteriaOptionTypes()
			, true)

			return

		updateCriteriaOptionTypes: ->
			types = []

			if @$scope.typeForm.by_user
				if @$scope.typeForm.by_user_opt.web_portal or @$scope.typeForm.by_user_opt.web_widget or @$scope.typeForm.by_user_opt.web_form
					Arrays.pushUnique(types, 'web')
					Arrays.pushUnique(types, 'web.user')
				if @$scope.typeForm.by_user_opt.email
					Arrays.pushUnique(types, 'email')
					Arrays.pushUnique(types, 'email.user')
				if @$scope.typeForm.by_user_opt.api
					Arrays.pushUnique(types, 'api')
					Arrays.pushUnique(types, 'api.user')

			if @$scope.typeForm.by_agent
				if @$scope.typeForm.by_agent_opt.web
					Arrays.pushUnique(types, 'web')
					Arrays.pushUnique(types, 'web.agent')
				if @$scope.typeForm.by_user_opt.email
					Arrays.pushUnique(types, 'email')
					Arrays.pushUnique(types, 'email.agent')
				if @$scope.typeForm.by_user_opt.api
					Arrays.pushUnique(types, 'api')
					Arrays.pushUnique(types, 'api.agent')

			setCritOptions = @criteraTypeDef.getOptionsForTypes(types)
			@$scope.criteriaOptionTypes.length = 0
			for opt in setCritOptions
				@$scope.criteriaOptionTypes.push(opt)

			setActionOptions = @actionsTypeDef.getOptionsForTypes(types)
			@$scope.actionOptionTypes.length = 0
			for opt in setActionOptions
				@$scope.actionOptionTypes.push(opt)

		###
		# Load the trigger
		###
		initialLoad: ->
			if @triggerId
				promise = @Api.sendGet("/ticket_triggers/#{@triggerId}").success( (data) =>
					@trigger = data.trigger
					@form = {
						title: @trigger.title
					}
				)

				return promise

			@trigger = {}

			return null

	Admin_TicketTriggers_Ctrl_Edit.EXPORT_CTRL()