define ->
  ###
    # Description
    # -----------
    #
    # This directive adds a new form element for a time period described as a number and a unit. For example,
    # "2 days" or "5 hours". In the model, the number is saved as the time in seconds.
    #
    # Add a "model-type" attribute to the element to change how the time is represented in the model:
    # - seconds (default): Convert time into seconds. E.g., 1 hour is saved as 3600
    # - array: Save as an array: [time, unit]. E.g., 1 hour is [1, 'hours']
    # - object: Save in an object: { time: time, unit: unit}. E.g., 1 hour is {time: 1, unit: 'hours'}
    # - "X:Y": Save in an object using X and Y as keys: {X: time, Y: unit}
    #
    # Example Controller
    # ------------------
    # $scope.my_model = 7200
    # $scope.my_model_alt = {num: 4, time_unit: "hours"}
    #
    # Example View
    # ------------
    # <dp-time-with-unit ng-model="my_model" />
    # (7200 will render as "2 hours")
    #
    # <dp-time-with-unit model-type="num:time_unit" ng-model="my_model" />
    # (Renders as "4 hours")
    ###
  DeskPRO_Directive_DpTimeWithUnit = [ ->
    return {
      restrict: 'E',
      template: """
        <div class="dp-time-unit">
          <input type="text" ng-model="time_num" class="form-control time_num" style="vertical-align: middle;" />
          <select
            ng-model="time_unit"
            ui-select2
            style="min-width: 100px;"
          >
            <option ng-if="has_minutes" value="minutes">{{phrases.minutes}}</option>
            <option ng-if="has_hours" value="hours">{{phrases.hours}}</option>
            <option ng-if="has_days" value="days">{{phrases.days}}</option>
            <option ng-if="has_weeks" value="weeks">{{phrases.weeks}}</option>
            <option ng-if="has_months" value="months">{{phrases.months}}</option>
            <option ng-if="has_years" value="years">{{phrases.years}}</option>
          </select>
        </div>
      """,
      scope: {},
      require: 'ngModel',
      replace: true,
      link: (scope, iElement, iAttrs, ngModel) ->

        scope.time_num = ''
        scope.time_unit = 'minutes'

        availableUnits = null
        if iAttrs.availableUnits
          availableUnits = scope.$eval(iAttrs.availableUnits)
        if not availableUnits
          availableUnits = ['minutes', 'hours', 'days', 'weeks', 'months', 'years'];

        unitPhrases = null
        if iAttrs.unitPhrases
          unitPhrases = scope.$eval(iAttrs.unitPhrases)
        if not unitPhrases
          unitPhrases = {
            minutes: 'minutes',
            hours: 'hours',
            days: 'days',
            weeks: 'weeks',
            months: 'months',
            years: 'years'
          };

        scope.phrases = unitPhrases

        for v in availableUnits
          scope['has_' + v] = true

        modelType = 'seconds';
        objModelKeys = null

        if iAttrs.modelType
          if iAttrs.modelType == 'object' || iAttrs.modelType.indexOf(':') != -1
            modelType = 'object'

            if iAttrs.modelType.indexOf(':') != -1
              objModelKeys = iAttrs.modelType.split(':')
            else
              objModelKeys = ['time', 'unit']
          else if iAttrs.modelType == 'array'
            modelType = 'array'
          else
            modelType = 'seconds'

        multiplierMap = {
          seconds:   1,
          minutes:   60,
          hours:  3600,
          days:   86400,
          weeks:  604800,
          months: 2419200,
          years:  31536000
        }

        multiplierTypes = [
          'seconds',
          'minutes',
          'hours',
          'days',
          'weeks',
          'months',
          'years'
        ]

        multiplierTypes.reverse()

        ngModel.$parsers.push( (viewValue) ->
          unit = viewValue.unit || 'minutes'
          num  = viewValue.num || 1

          switch modelType
            when "object"
              obj = {}
              obj[objModelKeys[0]] = num
              obj[objModelKeys[1]] = unit
              return obj
            when "array"
              arr = [num, unit]
              return arr
            else
              secs = multiplierMap[unit] * num
              return secs
        )

        ngModel.$formatters.push( (modelValue) ->
          unit = 'minutes'
          num  = ''

          switch modelType
            when "object"
              if modelValue?[objModelKeys[0]]?
                unit = modelValue[objModelKeys[0]]
              if modelValue?[objModelKeys[1]]?
                num = modelValue[objModelKeys[1]]
            when "array"
              if modelValue?[1]?
                unit = modelValue[1]
              if modelValue?[0]?
                num = modelValue[0]
            else
              modelValue = parseInt(modelValue || 0)

              for unitName in multiplierTypes
                if modelValue % multiplierMap[unitName] == 0
                  unit = unitName
                  break

              if not unit
                unit = 'minutes'

              if modelValue
                num = modelValue / multiplierMap[unit]

          # not an allowed unit, keep going 'down' until we get one
          if not scope['has_' + unit]
            secs = multiplierMap[unit] * num
            unitIdx = multiplierTypes.indexOf(unit)
            while true
              unitIdx += 1
              newUnit = if multiplierTypes[unitIdx]? then multiplierTypes[unitIdx] else null
              break if not newUnit or scope['has_' + newUnit]

            if newUnit
              unit = newUnit
              num = secs / multiplierMap[unit]

          return {
            unit: unit,
            num:  num
          }
        )

        scope.$watch('time_unit + time_num', ->
          if scope.time_unit and scope.time_num
            ngModel.$setViewValue({
              unit: scope.time_unit
              num:  parseInt(scope.time_num)
            })
        )

        ngModel.$render = ->
          viewValue = ngModel.$viewValue
          if viewValue
            scope.time_num  = viewValue.num
            scope.time_unit = viewValue.unit
            iElement.find('select').first().select2('val', viewValue.unit)

        ngModel.$render()
    }
  ]

  return DeskPRO_Directive_DpTimeWithUnit