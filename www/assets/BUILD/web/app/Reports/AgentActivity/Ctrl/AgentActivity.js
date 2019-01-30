define [
  'Reports/Main/Ctrl/Base',
  'moment',
], (
  ReportsBaseCtrl,
  moment,
) ->
  class Reports_AgentActivity_Ctrl_AgentActivity extends ReportsBaseCtrl
    @CTRL_ID   = 'Reports_AgentActivity_Ctrl_AgentActivity'
    @CTRL_AS   = 'AgentActivity'
    @DEPS      = ['Api', '$sce']


    ###
    # Initializing..
    ###
    init: ->
      @html = ''
      @all_agents = []
      @agent_teams = []
      @filter = {}
      @filter.date = new Date()
      @filter.agent_or_team = '0'

      @$scope.$watch('AgentActivity.filter.agent_or_team', =>
        @html = ''
      )
      @$scope.$watch('AgentActivity.filter.date', =>
        @html = ''
      )


    ###
    # Just doing all the necessary AJAX calls here
    ###
    initialLoad: ->
      date = moment(@filter.date).format("YYYY-MM-DD")
      @Api.sendGet("/reports/agent-activity/0/${date}").then (res) =>
        @html = @$sce.trustAsHtml(res.data.html)
        @all_agents = res.data.all_agents
        @agent_teams = res.data.agent_teams


    ###
    # This method updates current parameters that are used for sending request to API
    ###
    updateFilter: ->
      @filter.date = new Date() if !@filter.date
      @loadResults()


    ###
    # Loading the results of sending request to API
    ###
    loadResults: ->
      @startSpinner 'loading_results'
      date = moment(@filter.date).format("YYYY-MM-DD")
      @Api.sendGet("/reports/agent-activity/#{@filter.agent_or_team}/#{date}").then (res) =>
        @html = @$sce.trustAsHtml(res.data.html)
        @stopSpinner 'loading_results', true



  Reports_AgentActivity_Ctrl_AgentActivity.EXPORT_CTRL()