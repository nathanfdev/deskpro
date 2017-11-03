define ['DeskPRO/Util/Util'], (Util) ->
  class ReportsOverview
    constructor: (Api, $q) ->
      @overviewUrl = '/reports/overview/data/';
      @statsUrl = '/reports/overview/get-stats/';
      @Api = Api
      @$q = $q
      #just a stub, have to be refactored
      @$scope = {}

    getData: (dataKey) ->
      dataKey = dataKey.replace(/\-/g, '_')

      promise = @Api.sendGet("#{@overviewUrl}#{dataKey}")
      promise.then (data) =>
        @$scope[dataKey] = data.data
        if dataKey == 'tickets_user_waiting_time' or dataKey == 'tickets_response_time'
          @setDataForTableWithBarGraphs(dataKey)
        else if dataKey == 'tickets_opened_hour'
          @setDataForTicketsOpenedHours()
        else
          @setDataForBarGraphs(dataKey)
        @$scope[dataKey]





    ###
    # This method is used in select boxes for defining grouping field and / or other search parameters
    # @param {String} dataKey - using this key data is looked in @$scope
    ###
    getStats: (dataKey) ->
#      @toggleLoadingState(dataKey)

      dataKey = dataKey.replace(/\-/g, '_')

      promise = @Api.sendGet('/reports/overview/get-stats/' + dataKey, {
        grouping_field: @$scope[dataKey].grouping_field
        date_choice: @$scope[dataKey].date_choice
        sla_id: @$scope[dataKey].sla_id
      })

      promise.success (data) =>
#        @toggleLoadingState(dataKey)
        @$scope[dataKey] = data

        if dataKey == 'tickets_user_waiting_time' or dataKey == 'tickets_response_time'
          @setDataForTableWithBarGraphs(dataKey)
        else if dataKey == 'tickets_opened_hour'
          @setDataForTicketsOpenedHours()
        else
          @setDataForBarGraphs(dataKey)
          
        @$scope[dataKey]

    ###
    # We need to display bar graphs - so let's pre-calculate some variables
    # @param {String} dataKey - using this key data is looked in @$scope
    ###
    setDataForBarGraphs: (dataKey) ->
      dataKey = dataKey.replace(/\-/g, '_')

      @$scope[dataKey].empty = true if Util.isEmpty(@$scope[dataKey].values)
      @$scope[dataKey].stats = []
      denominator = @$scope[dataKey].max || 1

      for key of @$scope[dataKey].titles when @$scope[dataKey].values[key]

        percentage = @$scope[dataKey].values[key] / denominator * 100
        percentage = 1 if percentage < 1

        @$scope[dataKey].stats.push({
          title: @$scope[dataKey].titles[key]
          value: @$scope[dataKey].values[key] || 0
          left_percentage: percentage
          right_percentage: 100 - percentage
        })


    ###
    # We need to display bar graphs - so let's pre-calculate some variables
    # This method is special case of @setDataForBarGraphs()
    ###
    setDataForTicketsOpenedHours: ->
      @$scope.tickets_opened_hour.empty = true if Util.isEmpty(@$scope.tickets_opened_hour.values)
      @$scope.tickets_opened_hour.stats = []
      @$scope.tickets_opened_hour.column_width = 100 / Object.keys(@$scope.tickets_opened_hour.titles).length
      denominator = @$scope.tickets_opened_hour.max || 1

      for key of @$scope.tickets_opened_hour.titles

        if @$scope.tickets_opened_hour.values[key]

          percentage = @$scope.tickets_opened_hour.values[key] / denominator * 100
          percentage = 1 if percentage < 1

          @$scope.tickets_opened_hour.stats.push({
            title: @$scope.tickets_opened_hour.titles[key]
            value: @$scope.tickets_opened_hour.values[key] || 0
            percentage: percentage
          })
        else
          @$scope.tickets_opened_hour.stats.push({
            title: @$scope.tickets_opened_hour.titles[key]
          })


    ###
    # We need to display bar graphs - so let's pre-calculate some variables
    # What is special here - we display every piece of data
    # Just for cases with no data we display only labels without graphical bars
    # Ie. if we have 0 tickets created < 5 minutes ago, we still display '< 5 minutes' label, but without bar
    # This leads to the situation that we have to iterate over all the '@$scope.tickets_user_waiting_time.titles' array
    # @param {String} dataKey - using this key data is looked in @$scope
    ###
    setDataForTableWithBarGraphs: (dataKey) ->
      dataKey = dataKey.replace(/\-/g, '_')
      
      @$scope[dataKey].empty = true if Util.isEmpty(@$scope[dataKey].values)
      @$scope[dataKey].stats = []
      denominator = @$scope[dataKey].max || 1

      for key of @$scope[dataKey].titles

        percentage = @$scope[dataKey].values[key] / denominator * 100
        percentage = 1 if percentage < 1

        # case of simple data without sub-data

        if not @$scope[dataKey].sub_titles

          if @$scope[dataKey].values[key]
            @$scope[dataKey].stats.push({
              title: @$scope[dataKey].titles[key]
              value: @$scope[dataKey].values[key] || 0
              percentage: percentage
            })
          else
            @$scope[dataKey].stats.push({
              title: @$scope[dataKey].titles[key]
            })

        else

# case of more sophisticated case with sub-data

          percentage = @$scope[dataKey].group_total[key] / denominator * 100
          percentage = 1 if percentage < 1

          if @$scope[dataKey].group_total[key]
            sub_stats = []

            for subid, subtitle of @$scope[dataKey].sub_titles when @$scope[dataKey].values[key][subid]
              sub_percentage = @$scope[dataKey].values[key][subid] / @$scope[dataKey].group_total[key] * 100
              sub_percentage = 1 if sub_percentage < 1
              sub_stats.push({
                title: subtitle + ' (' + @$scope[dataKey].values[key][subid] + ')'
                percentage: sub_percentage
                background: @$scope[dataKey].group_keys[subid]
              })

            @$scope[dataKey].stats.push({
              title: @$scope[dataKey].titles[key]
              value: @$scope[dataKey].group_total[key] || 0
              percentage: percentage
              sub_stats: sub_stats
            })
          else
            @$scope[dataKey].stats.push({
              title: @$scope[dataKey].titles[key]
            })