define [
	'Admin/Main/Ctrl/Base',
	'DeskPRO/Util/Util'
], (
	Admin_Ctrl_Base,
	Util
) ->
	class Admin_TicketFilters_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketFilters_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['dpObTypesDefTicketFilter', '$stateParams']

		init: ->
			@filterId = parseInt(@$stateParams.id || 0)
			@filterData = @DataService.get('TicketFilters')
			@filter = null

			@filter_criteria = {}
			@criteriaTypeDef = @dpObTypesDefTicketFilter
			@criteriaOptionTypes = @criteriaTypeDef.getOptionsForTypes()

		initialLoad: ->
			return @filterData.loadEditFilterData(@filterId).then( (data) =>
				@agents = data.agents
				@teams = data.teams
				if not @teams[0]
					@teams = null

				if data.filter
					@filter = data.filter
					@form = @getFormFromModel(@filter)

					@filter_criteria = {}
					for term in @filter.terms.terms
						rowId = Util.uid('term')
						@filter_criteria[rowId] = term

				else
					@filter = {
						is_global: true
					}
					@form = @getFormFromModel(@filter)
			)

		getFormFromModel: (filterModel) ->
			form = {}
			form.title = filterModel.title || ''

			if filterModel.is_gloabl
				form.perm_type = 'global'
			else if filterModel.agent_team and @teams[0]
				form.perm_type = 'team'
			else
				form.perm_type = 'agent'

			if @filter.person
				form.agent_id = @filter.person.id + ""
			else
				form.agent_id = @agents[0].id + ""

			form.team_id = null
			if @teams
				if @filter.agent_team
					form.team_id = @filter.agent_team.id + ""
				else
					form.team_id = @teams[0].id + ""

			return form

		saveForm: ->
			if not @$scope.form_props.$valid then return

			if @filterId
				method = 'POST'
				url = "/ticket_filters/#{@filterId}"
			else
				method = 'PUT'
				url = "/ticket_filters"

			postData = {
				filter: {
					title: @form.title,
					is_global:     @form.perm_type == 'global',
					person_id:     if @form.perm_type == 'agent' then parseInt(@form.agent_id) || null else null,
					agent_team_id: if @form.perm_type == 'team' then parseInt(@form.team_id) || null else null
				}
			}
			postData.filter.terms = @filter_criteria

			@sendFormSaveApiCall(method, url, postData).then( (res) =>
				@Growl.success(@getRegisteredMessage('saved_filter'))

				@filter.title = @form.title
				if res.data.filter_id
					@filter.id = res.data.filter_id

				@filter.is_global = @form.perm_type == 'global'
				@filter.person = null
				@filter.agent_team = null

				if @form.perm_type == 'agent'
					@filter.person = @agents.filter((x) => x.id == parseInt(@form.agent_id))[0]
				if @form.perm_type == 'team'
					@filter.agent_team = @teams.filter((x) => x.id == parseInt(@form.team_id))[0]

				@filterData.mergeDataModel(@filter)

				if !@filterId
					@$state.go('tickets.ticket_filters.gocreate')
			)

	Admin_TicketFilters_Ctrl_Edit.EXPORT_CTRL()