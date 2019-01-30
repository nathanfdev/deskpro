define ['DeskPRO/Data/TzData'], (TzData) ->
  Admin_Main_Directive_DpWorkingHours = [ ->
    return {
      restrict: 'E',
      require: 'ngModel',
      replace: true,
      templateUrl: DP_BASE_ADMIN_URL+'/load-view/Common/work-hours-directive.html',
      scope: {},
      link: (scope, element, attrs, ngModel) ->

        scope.timezone       = 'UTC'
        scope.start_hour     = 9
        scope.start_min      = 0
        scope.end_hour       = 18
        scope.end_min        = 0
        scope.work_days      = [null, true, true, true, true, true, false, false] #0=null because valid idx is 1-7 for ISO-8601 days
        scope.hol_year       = (new Date()).getFullYear()
        scope.hol_new_month  = 1
        scope.hol_new_day    = 1
        scope.hol_new_name   = ''
        scope.holidays       = []

        scope.minutes = []
        for i in [0..59]
          scope.minutes.push {id: i, label: ("0" + i).slice(-2)}
        scope.hours = []
        for i in [0..23]
          scope.hours.push {id: i, label: ("0" + i).slice(-2)}

        scope.days = []
        for i in [1..31]
          scope.days.push {id: i, label: ("0" + i).slice(-2)}
        scope.months = []
        for i in [1..12]
          scope.months.push {id: i, label: ("0" + i).slice(-2)}

        currentYear = new Date().getFullYear();
        scope.years = []
        for i in [currentYear..currentYear+9]
          scope.years.push {id: i, label: i + ""}



        #------------------------------
        # Element references
        #------------------------------

        els = {
          hol_wrap: element.find('.holiday-rows'),
        }

        #------------------------------
        # Init holiday years
        #------------------------------

        year = (new Date()).getFullYear()
        year_end = year + 4

        for i in [year..year_end]
          row = $('<div class="holiday-year-rows year-row year-'+i+'"></div>')
          row.appendTo(els.hol_wrap)

        row = $('<div class="holiday-year-rows year-repeat"></div>')
        row.appendTo(els.hol_wrap)

        #------------------------------
        # Adding/removing holidays
        #------------------------------

        holExists = (hol1, hol2) ->
          if hol1.year != hol2.year
            return false

          if hol1.month != hol2.month
            return false

          if hol1.day != hol2.day
            return false

          return true

        scope.addHoliday = ($event) ->
          $event.preventDefault()
          $event.stopPropagation()

          year   = parseInt(scope.hol_year)
          month  = parseInt(scope.hol_new_month)
          day    = parseInt(scope.hol_new_day)
          title  = $.trim(scope.hol_new_name)
          repeat = scope.hol_new_repeat

          if repeat
            year = 0

          if not month or not day
            return

          exists = false
          hol = { year: year, month: month, day: day, name: title }

          for checkHol in scope.holidays
            if holExists(hol, checkHol)
              exists = true
              break

          if exists
            scope.hol_new_name = ''
            scope.show_newhold = false
            return

          drawHoliday({
            year: year,
            month: month,
            day: day,
            repeat: repeat,
            name: title
          })

          scope.holidays.push(hol)
          scope.hol_new_name = ''
          scope.show_newhold = false
          updateYearList()

        drawHoliday = (hol) ->
          row = $('<div class="hol-row"><div class="remove-btn"><i class="fa fa-times-circle"></i></div> <span class="date-txt"></span> <span class="title-txt"></span></div></div>')

          year   = hol.year
          month  = hol.month
          day    = hol.day
          title  = hol.name
          repeat = year == 0

          if repeat
            y_str = 'Every Year'
            rowContainer = els.hol_wrap.find('.year-repeat')
          else
            y_str = year
            rowContainer = els.hol_wrap.find('.year-' + year)

          m_str = if month < 10 then "0#{month}" else month
          d_str = if day < 10 then "0#{day}" else day
          row.find('.date-txt').text("#{y_str}-#{m_str}-#{d_str}")
          if title.length
            row.find('.title-txt').text(title)

          row.data('hol-rec', hol)
          rowContainer.append(row)

        element.find('.add-btn').on('click', (ev) ->
          scope.$apply( ->
            scope.addHoliday(ev)
          )
        )

        element.on('click', '.remove-btn', (ev) ->
          ev.preventDefault()
          row = $(this).closest('.hol-row')
          holRec = row.data('hol-rec')
          row.remove()

          if not scope.holidays.length
            return

          for hol, idx in scope.holidays
            if hol == holRec
              scope.holidays.splice(idx, 1)
              break
        )

        updateYearList = ->
          any = false
          if els.hol_wrap.find('.year-repeat').find('.hol-row')[0]
            any = true

          els.hol_wrap.find('.year-row').hide()
          if els.hol_wrap.find('.year-' + scope.hol_year).show().find('.hol-row')[0]
            any = true

          if any
            els.hol_wrap.show()
          else
            els.hol_wrap.hide()

          return null

        element.find('.holiday-years').on('change', ->
          updateYearList()
        )

        updateViewValue = ->
          startHour = 9
          endHour = 18
          if(scope.start_hour || scope.start_hour == 0)
            startHour = scope.start_hour
          if(scope.end_hour || scope.end_hour == 0)
            endHour = scope.end_hour
            
          ngModel.$setViewValue({
            timezone       : scope.timezone || 'UTC',
            start_hour     : startHour,
            start_min      : scope.start_min || 0,
            end_hour       : endHour,
            end_min        : scope.end_min || 0,
            holidays       : scope.holidays || [],
            work_days      : scope.work_days || [null, false, true, true, true, true, true, false]
          })

        scope.$watch('timezone',   -> updateViewValue())
        scope.$watch('start_hour', -> updateViewValue())
        scope.$watch('start_min',  -> updateViewValue())
        scope.$watch('end_hour',   -> updateViewValue())
        scope.$watch('end_min',    -> updateViewValue())
        scope.$watch('holidays',   -> updateViewValue())

        ngModel.$render = ->
          element.find('.holiday-year-rows').empty()
          if ngModel.$viewValue
            viewValue = ngModel.$viewValue
          else
            viewValue =
              start_hour: null
              end_hour: null

          startHour = 9
          endHour = 18
          if(viewValue.start_hour || viewValue.start_hour == 0)
            startHour = viewValue.start_hour
          if(viewValue.end_hour || viewValue.end_hour == 0)
            endHour = viewValue.end_hour

          if viewValue
            scope.timezone       = viewValue.timezone || 'UTC'
            scope.start_hour     = startHour
            scope.start_min      = viewValue.start_min || 0
            scope.end_hour       = endHour
            scope.end_min        = viewValue.end_min || 0
            scope.holidays       = viewValue.holidays || []

            if viewValue.work_days
              for enabled, day in viewValue.work_days
                scope.work_days[day] = !!enabled

          if scope.holidays.length
            for hol in scope.holidays
              drawHoliday(hol)

          updateYearList()

        ngModel.$render()
    }
  ]

  return Admin_Main_Directive_DpWorkingHours