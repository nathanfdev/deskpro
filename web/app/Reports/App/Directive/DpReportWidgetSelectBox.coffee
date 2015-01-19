define ->
  ###
   # Description
   # -----------
    #
    # Example View
    # ------------
    # <dp-report-builder-select-box
    #         value-to-decorate="scope.title"
    #         possible-values="scope.some_object">
    # </span>
    #
    # Parameters
    # ------------
    # 1) 'value-to-decorate' (required parameter) - ...
    # 2) 'possible-values' (required parameter) - ...
   #
  ###
  Reports_App_Directive_DpReportWidgetSelectBox = ['$compile', '$state', ($compile, $state) ->
    return {
    restrict: 'AE',
    replace: true,
    scope:
      changeParams: "&changeWidgetParams"
      reportId: "=reportId"
      valueToDecorate: "=valueToDecorate"
      possibleValues: "=possibleValues"

    link: (scope, element, attrs) ->
      template = """
        <a ng-href="{{report_link}}">
          <span ng-repeat="text in texts" style="margin-left: 5px">
            <span style="vertical-align:middle;" ng-bind-html="text"></span>
            <select ng-if="options[$index]" style="min-width:70px;" ng-model="selected[$index].value" ng-change="changeOption()">
              <option ng-repeat="option in options[$index]" ng-value="option.value"
                ng-selected="selected[$parent.$index].value == option.value">
                {{ option.label }}
              </option>
            </select>
          </span>
        </a>
      """


      ###
      # Below variables will look like following
      #
      # scope.texts = ['Number of tickets created','grouped by',' & ']
      # scope.options = [[{value: 'yesterday', label: 'Yesterday'}, {value: 'today', label: 'Today'}, {value: '123', label: '123'}, {value: '456', label: '456'}]
      #                                   [{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
      #                                   [{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
      # ]
      # scope.selected = ['today', 'agent', 'department']
      ###
      scope.texts = []
      scope.options = []
      scope.selected = []
      scope.defaultLinkParams = ''
      scope.type = attrs.type || 'builtIn'

      ###
      # This function builds directive by constructing it on 'the fly' using DOM operations
      # The reason for doing so - problems with inner directives that were compiled with $compile() functionality
      ###
      buildDirectiveVariables = (value) ->
        lastPiece = value
        # This regexp matches every string like <1:date group:, default: this month> or
        # <2:field group: tickets, default: sla> note that it only indicator that we have some options here
        regex = /(.*?)(<(\d+:.+?)>)/g

        while match = regex.exec(value)
          scope.texts.push(match[1])
          collected = collectSelectOptions(match[3])
          scope.options.push(collected.options)
          scope.selected.push(collected.selected)

          # case when text that continues after last select box
          lastPiece = lastPiece.replace(match[1], '').replace(match[2], '')

        # finding icon for case we have it
        lastPiece = lastPiece.replace('[', '').replace(']', '')
        lastPiece = lastPiece.replace(/<chart:([a-z0-9_-]+)>/gi, '<span class="report-chart-icon report-chart-icon-$1"></span>')

        scope.texts.push(lastPiece)

      ###
      # Returning select box options that was rendered according to 'input' parameter
      ###
      collectSelectOptions = (input) ->
        possibleValues = scope.possibleValues
        choices = {}
        extras = {}
        options = []

        # some regular expressions parsing...

        # date group have no parameteres, only default value
        if input.match(/^\d+:(date group)(.*)$/)
          choices = possibleValues.dates
          placeholder = RegExp.$1
          extrasMatch = RegExp.$2
        # field group, status and order has columns to apply filter, so we have to parse it
        # so we storing it in type variable, and as standalone variant we have type none for date group and
        # extraMatch for date = first match (it will be default: smth section)
        else if input.match(/^\d+:(field group):([a-zA-Z0-9_]+)(.*)$/)
          placeholder = RegExp.$1
          type = RegExp.$2
          if typeof possibleValues.fields[type] != 'undefined'
            choices = possibleValues.fields[type]
            extrasMatch = RegExp.$3
        else if input.match(/^\d+:(status group):([a-zA-Z0-9_]+)(.*)$/)
          placeholder = RegExp.$1
          type = RegExp.$2
          if typeof possibleValues.statuses[type] != 'undefined'
            choices = possibleValues.statuses[type]
            extrasMatch = RegExp.$3
        else if input.match(/^\d+:(order group):([a-zA-Z0-9_]+)(.*)$/)
          placeholder = RegExp.$1
          type = RegExp.$2
          if typeof possibleValues.orders[type] != 'undefined'
            choices = possibleValues.orders[type]
            extrasMatch = RegExp.$3

        # information about default group...

        if extrasMatch
          regex = /,([a-zA-Z0-9_ ]+):([^,]+)/g
          while match = regex.exec(extrasMatch)
            extras[$.trim(match[1])] = $.trim(match[2])

        # constructing selects...

        for own key, value of choices
          options.push({value: key, label: value[0], placeholder: placeholder})

        if !options.length
          options.push {value: 0, label: 'invalid value'}

        return {
        options: options
        selected: {placeholder: placeholder, value: (if extras.default then extras.default else options[0]?.value)}
        }

      ###
      # Going to correponding route after changing selected options inside select box
      ###
      scope.changeOption = () ->
        scope.changeParams {params: scope.selected}

      buildDirectiveVariables(scope.valueToDecorate)

      scope.$watch(attrs.possibleValues, (newVal) =>
        if typeof newVal == 'undefined' then return
        buildDirectiveVariables(scope.valueToDecorate)
        scope.defaultLinkParams = scope.selected.join(',')
      )
      linkFn = $compile(template)
      content = linkFn(scope)
      element.replaceWith(content)
    }
  ]

  return Reports_App_Directive_DpReportWidgetSelectBox