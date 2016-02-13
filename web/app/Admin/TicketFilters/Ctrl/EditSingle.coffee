define [
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Util'
], (
  Admin_Ctrl_Base,
  Util
) ->
  class Admin_TicketFilters_Ctrl_EditSingle extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketFilters_Ctrl_EditSingle'
    @CTRL_AS   = 'EditSingleCtrl'
    @DEPS      = ['dpObTypesDefTicketFilter', '$stateParams', '$timeout']

    init: ->
      @filterSetId = parseInt(@$stateParams.id || 0)
      @filterId = parseInt(@$stateParams.filter_id || 0)
      @filterData = @DataService.get('TicketFilters')
      @filter_criteria = {}
      @criteriaTypeDef = @dpObTypesDefTicketFilter
      @criteriaOptionTypes = @criteriaTypeDef.getOptionsForTypes()
      @$scope.addTheFilter = @saveFilter
      @$scope.newFilter = {}

    initialLoad: ->
      p = @filterData.loadEditFilterData(@filterId).then( (data) =>
        @filter = data
        @$scope.newFilter = data
      )

    saveFilter: =>
      filter = @$scope.newFilter
      # Let's fake the bloody filter for now.
      filter.term = {
        "type": "agent",
        "options": {
          "agent_ids": [1,2]
        }
      }
      filter.filter_set = @filterSetId
      
      @filterData.saveFilterData(filter).then( (response) =>
        @filter = response.data
        @$scope.$parent.EditCtrl.filterset.filters.push(@filter)
        @$state.go('tickets.ticket_filters.edit', { id: @filterSetId })
      )

    # getFormFromModel: (filterModel) ->
    #   form = {}
    #   form.title = filterModel.title || ''
    #
    #   if filterModel.is_global
    #     form.perm_type = 'global'
    #   else if filterModel.agent_team and @teams[0]
    #     form.perm_type = 'team'
    #   else
    #     form.perm_type = 'agent'
    #
    #   if @filter.person
    #     form.agent_id = @filter.person.id + ""
    #   else
    #     form.agent_id = @agents[0].id + ""
    #
    #   form.team_id = null
    #   if @teams
    #     if @filter.agent_team
    #       form.team_id = @filter.agent_team.id + ""
    #     else
    #       form.team_id = @teams[0].id + ""
    #
    #   return form
    #
    # saveForm: ->
    #   if not @$scope.form_props.$valid then return
    #
    #   if @filterId
    #     method = 'POST'
    #     url = "/ticket_filters/#{@filterId}"
    #   else
    #     method = 'PUT'
    #     url = "/ticket_filters"
    #
    #   postData = {
    #     filter: {
    #       title: @form.title,
    #       is_global:     @form.perm_type == 'global',
    #       person_id:     if @form.perm_type == 'agent' then parseInt(@form.agent_id) || null else null,
    #       agent_team_id: if @form.perm_type == 'team' then parseInt(@form.team_id) || null else null
    #     }
    #   }
    #   postData.filter.terms = @filter_criteria
    #
    #   @sendFormSaveApiCall(method, url, postData).then( (res) =>
    #     @Growl.success(@getRegisteredMessage('saved_filter'))
    #
    #     @filter.title = @form.title
    #     if res.data.filter_id
    #       @filter.id = res.data.filter_id
    #
    #     @filter.is_global = @form.perm_type == 'global'
    #     @filter.person = null
    #     @filter.agent_team = null
    #
    #     if @form.perm_type == 'agent'
    #       @filter.person = @agents.filter((x) => x.id == parseInt(@form.agent_id))[0]
    #     if @form.perm_type == 'team'
    #       @filter.agent_team = @teams.filter((x) => x.id == parseInt(@form.team_id))[0]
    #
    #     @filterSetData.mergeDataModel(@filter)
    #
    #     if !@filterId
    #       @$state.go('tickets.ticket_filters.gocreate')
    #   )

  Admin_TicketFilters_Ctrl_EditSingle.EXPORT_CTRL()
