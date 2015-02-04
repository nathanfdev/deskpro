define [
  'DeskPRO/Main/Ctrl/Base',
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
      @date = new Date()
      @all_agents = []
      @agent_teams = []
      @filter = {}
      @filter.date = moment(@date).format("YYYY-MM-DD")
      @filter.agent_or_team = 'all'


    ###
    # Just doing all the necessary AJAX calls here
    ###
    initialLoad: ->
      return @loadResults()


    ###
    # This method updates current parameters that are used for sending request to API
    ###
    updateFilter: ->
      @filter.date = moment(@date).format("YYYY-MM-DD")
      @loadResults()


    ###
    # Loading the results of sending request to API
    ###
    loadResults: ->
      @startSpinner('loading_results')

      promise = @Api.sendGet("/reports/agent-activity/" + @filter.agent_or_team + "/" + @filter.date).then((res) =>
        @html = @$sce.trustAsHtml(res.data.html)
        @all_agents = res.data.all_agents
        @agent_teams = res.data.agent_teams

        @stopSpinner('loading_results', true)
      )

      return promise


  Reports_AgentActivity_Ctrl_AgentActivity.EXPORT_CTRL()