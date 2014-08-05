define [
	'Admin/Main/Ctrl/Base',
	'DeskPRO/Util/Util',
	'Admin/TicketSlas/SlaFormMapper'
], (
	Admin_Ctrl_Base,
	Util,
	SlaFormMapper
) ->
	class Admin_TicketSlas_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketSlas_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['dpObTypesDefTicketActions', 'dpObTypesDefTicketCriteria']

		init: ->
			@formMapper = new SlaFormMapper()
			@slaData    = @DataService.get('TicketSlas')
			@sla        = null
			@form       = @getFormFromModel({})

			@actionsTypeDef = @dpObTypesDefTicketActions
			@criteraTypeDef = @dpObTypesDefTicketCriteria

			@$scope.criteriaOptionTypes = []
			@$scope.actionOptionTypes   = []

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
			proms = []

			if @$stateParams.id
				proms.push @slaData.loadEditSlaData(@$stateParams.id).then( (data) =>
					@sla = data.sla
					@form = @getFormFromModel(@sla)
					@origForm = Util.clone(@form, true)
				)
			else
				@macro = {}
				@sla = {}
				@form = @getFormFromModel({})
				@origForm = Util.clone(@form, true)
				return null

			proms.push @actionsTypeDef.loadDataOptions()
			proms.push @criteraTypeDef.loadDataOptions()

			return @$q.all(proms).then(=>
				@updateCriteriaOptionTypes()
			)

		getFormFromModel: (slaModel) ->
			return @formMapper.getFormFromModel(slaModel)

		saveForm: ->
			postData = @formMapper.getPostDataFromFormModel(@form)

			@startSpinner('saving')
			if @sla.id
				is_new = false
				promise = @Api.sendPostJson("/ticket_slas/#{@sla.id}", postData)
			else
				is_new = true
				promise = @Api.sendPutJson('/ticket_slas', postData)

			promise.success( (result) =>
				@sla.id = result.sla_id

				if is_new
					@sla.is_enabled = true

				@sla.title = postData.title

				@stopSpinner('saving', true).then(=>
					@Growl.success("Saved")
				)

				@slaData.mergeDataModel({
					id: @sla.id,
					title: @sla.title
				})

				@skipDirtyState()
				if is_new
					@$state.go('tickets.slas.gocreate')
			)
			promise.error( (info, code) =>
				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

			return promise

	Admin_TicketSlas_Ctrl_Edit.EXPORT_CTRL()