define [
  'moment',
], (
  moment,
) ->
  class AgentActivity
    constructor: (Api, $sce) ->
      @Api = Api
      @$sce = $sce
      @html = ''
      @date = new Date()
      @all_agents = []
      @agent_teams = []
      @filter = {}
      @filter.date = moment(@date).format("YYYY-MM-DD")
      @filter.agent_or_team = 'all'


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
#      @startSpinner('loading_results')

      promise = @Api.sendGet("/reports/agent-activity/" + @filter.agent_or_team + "/" + @filter.date).then((res) =>
        @html = @$sce.trustAsHtml(res.data.html)
        @all_agents = res.data.all_agents
        @agent_teams = res.data.agent_teams

#        @stopSpinner('loading_results', true)
      )

      return promise