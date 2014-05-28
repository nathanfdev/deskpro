define [
	'DeskPRO/Util/Arrays'
	'Admin/Main/Ctrl/Base',
	'Admin/TicketTriggers/TriggerEditFormMapper',
], (
	Arrays,
	Admin_Ctrl_Base,
	TriggerEditFormMapper
) ->
	class Admin_TicketTriggers_Ctrl_EditBase extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketTriggers_Ctrl_Edit'
		@CTRL_AS   = 'TicketTriggersEdit'
		@DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions']

		init: ->
			@triggerType = @$stateParams.type
			@trigger     = null
			@triggerId   = @$stateParams.id
			@options     = {}
			@editFormMapper = new TriggerEditFormMapper()

			@$scope.form = @editFormMapper.getFormFromModel({})

			@$scope.triggerType = @$stateParams.type
			@$scope.triggerId   = @$stateParams.id

			with_changed_ops = true
			if @$stateParams.type == 'newticket'
				with_changed_ops = false
				@dpTriggers = @DataService.get('TriggersNew')
			else if @$stateParams.type == 'newreply'
				@dpTriggers = @DataService.get('TriggersReply')
			else
				@dpTriggers = @DataService.get('TriggersUpdate')

			@criteraTypeDef = @dpObTypesDefTicketCriteria
			@criteraTypeDef.setWithChangedOps(with_changed_ops)

			@actionsTypeDef = @dpObTypesDefTicketActions

			@$scope.criteriaOptionTypes = []
			@$scope.actionOptionTypes = []

			@customInit()
			return

		customInit: -> return null

		updateCriteriaOptionTypes: ->
			types = []

			if @$scope.form.typeForm.by_user
				if @$scope.form.typeForm.by_user_mode.portal or @$scope.form.typeForm.by_user_mode.widget or @$scope.form.typeForm.by_user_mode.form
					Arrays.pushUnique(types, 'web')
					Arrays.pushUnique(types, 'web.user')
				if @$scope.form.typeForm.by_user_mode.email
					Arrays.pushUnique(types, 'email')
					Arrays.pushUnique(types, 'email.user')
				if @$scope.form.typeForm.by_user_mode.api
					Arrays.pushUnique(types, 'api')
					Arrays.pushUnique(types, 'api.user')

			if @$scope.form.typeForm.by_agent
				if @$scope.form.typeForm.by_agent_mode.web
					Arrays.pushUnique(types, 'web')
					Arrays.pushUnique(types, 'web.agent')
				if @$scope.form.typeForm.by_agent_mode.email
					Arrays.pushUnique(types, 'email')
					Arrays.pushUnique(types, 'email.agent')
				if @$scope.form.typeForm.by_agent_mode.api
					Arrays.pushUnique(types, 'api')
					Arrays.pushUnique(types, 'api.agent')

			setCritOptions = @criteraTypeDef.getOptionsForTypes(types)
			@$scope.criteriaOptionTypes.length = 0
			for opt in setCritOptions
				@$scope.criteriaOptionTypes.push(opt)

			setActionOptions = @actionsTypeDef.getOptionsForTypes(types, { dynamicOptions: @customActions })
			@$scope.actionOptionTypes.length = 0
			for opt in setActionOptions
				@$scope.actionOptionTypes.push(opt)

		###
		# Load the trigger
		###
		initialLoad: ->
			get = {
				customActions: '/ticket_triggers/get-custom-actions'
			}

			if @triggerId
				get.trigger = "/ticket_triggers/#{@triggerId}"

			promise = @Api.sendDataGet(get).then( (result) =>

				@customActions = result.data.customActions.action_defs

				if result.data?.trigger?.trigger?
					@trigger = result.data.trigger.trigger
					@triggerId = @trigger.id
				else
					@trigger = {}
					@triggerId = 0

				@$scope.form = @editFormMapper.getFormFromModel(@trigger)
			)

			promise2 = @criteraTypeDef.loadDataOptions()
			promise3 = @actionsTypeDef.loadDataOptions()

			promises = [promise, promise2, promise3]

			return @$q.all(promises).then(=>
				@updateCriteriaOptionTypes()

				@$scope.$watch('form.typeForm', =>
					@updateCriteriaOptionTypes()
				, true)
			)

		###
		# Save the trigger
		###
		saveTrigger: ->
			return if @$scope.form_props.$invalid

			@resetErrors()

			postData = {
				title:         @$scope.form.title,
				event_trigger: @triggerType,
				flags:         @$scope.form.flags,
				by_user_mode:  [],
				by_agent_mode: [],
				criteria_sets: [],
				actions:       [],
			}

			if @$scope.form.typeForm.by_user
				for own mode, enabled of @$scope.form.typeForm.by_user_mode
					if enabled
						postData.by_user_mode.push(mode)
			if @$scope.form.typeForm.by_agent
				for own mode, enabled of @$scope.form.typeForm.by_agent_mode
					if enabled
						postData.by_agent_mode.push(mode)

			for own _, crit_set of @$scope.form.terms_set
				set = []
				for own _, crit of crit_set
					if crit.type
						set.push(crit)
				if set.length
					postData.criteria_sets.push(set)

			if @$scope.form.actions
				for own _, act of @$scope.form.actions
					if act.type
						postData.actions.push(act)

			@startSpinner('saving')
			if @trigger.id
				is_new = false
				promise = @Api.sendPostJson('/ticket_triggers/' + @trigger.id, postData)
			else
				is_new = true
				promise = @Api.sendPutJson('/ticket_triggers', postData)

			promise.success( (result) =>
				@trigger.id = result.trigger_id

				if is_new
					@trigger.is_enabled = true

				@trigger.title = postData.title

				@stopSpinner('saving', true).then(=>
					@Growl.success("Saved")
				)

				@dpTriggers.mergeDataModel({
					id: @trigger.id,
					title: @trigger.title
				})

				@skipDirtyState()
				if is_new
					@$state.go('tickets.triggers.gocreate')
			)
			promise.error( (result, code) =>
				@stopSpinner('saving', true)
				if result?.error_code == 'invalid'
					@showErrors(result.error_info)
			)

			return promise

		findCriteriaTypeTitle: (type) ->
			option_title = null
			for v in @$scope.criteriaOptionTypes
				if v.subOptions
					for sb in v.subOptions
						if type == sb.value
							option_title = sb.title
							break
				else
					if type == v.value
						option_title = v.title
				if option_title then break

			return option_title || type

		findActionTypeTitle: (type) ->
			option_title = null
			for v in @$scope.actionOptionTypes
				if v.subOptions
					for sb in v.subOptions
						if type == sb.value
							option_title = sb.title
							break
				else
					if type == v.value
						option_title = v.title
				if option_title then break

			return option_title || type

		resetErrors: ->
			@$scope.show_errors = false
			@$scope.criteria_errors = null
			@$scope.action_errors = null

		showErrors: (errors) ->
			@$scope.show_errors = true

			if errors.criteria?.length
				@$scope.criteria_errors = []
				for t in errors.criteria
					@$scope.criteria_errors.push(@findCriteriaTypeTitle(t))

			if errors.actions?.length
				@$scope.actions_errors = []
				for t in errors.actions
					@$scope.actions_errors.push(@findActionTypeTitle(t))