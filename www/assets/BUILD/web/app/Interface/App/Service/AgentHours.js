define [
  'Reports/App/Service/BaseService'
  'moment',
], (
  BaseService
  moment,
) ->
  class AgentHours extends BaseService

    constructor: (Api, $sce, $q) ->
      @Api = Api
      @$sce = $sce
      @$q = $q
      @html = ''
      @date1 = new Date()
      @date2 = new Date()
      @filter = {}
      @filter.date1 = moment(@date).format("YYYY-MM-DD")
      @filter.date2 = moment(@date).format("YYYY-MM-DD")

    ###
    # This method updates current parameters that are used for sending request to API
    ###
    updateFilter: ->
      @filter.date1 = moment(@date1).format("YYYY-MM-DD")
      @filter.date2 = moment(@date2).format("YYYY-MM-DD")
      @loadResults()

    ###
    # Loading the results of sending request to API
    ###
    loadResults: ->
#      @startSpinner('loading_results')

      promise = @Api.sendGet("/reports/agent-hours/" + @filter.date1 + "/" + @filter.date2).then((res) =>
        @html = @$sce.trustAsHtml(res.data.html)

#        @stopSpinner('loading_results', true)
      )

      return promise