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
		@DEPS      = ['em', '$stateParams', 'dpObTypesDefTicketCriteria']

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

			@$scope.criteriaOptionTypes = []
			@updateCriteriaOptionTypes()

			@$scope.trigger_criteria_set = {
				first: {},
				second: {}
			}

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

			setOptions = @criteraTypeDef.getOptionsForTypes(types)

			@$scope.criteriaOptionTypes.length = 0
			for opt in setOptions
				@$scope.criteriaOptionTypes.push(opt)

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