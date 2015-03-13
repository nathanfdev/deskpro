define ['angular', 'moment'], (angular, moment) ->
  angular.module('dp.datetimepicker', ['template/dp/datetime.html', 'ui.bootstrap'])

  .constant('dpDatetimeConfig',
    minView: 'minute'
    format: false
    dayViewHeaderFormat: 'MMMM YYYY'
    minDate: false
    maxDate: false
    locale: moment.locale()
    daysOfWeekDisabled: []
  )

  .directive('dpDatetimePopup', ['$compile', '$document', '$position', ($compile, $document, $position) ->
      require: ['^ngModel']
      restrict: 'A'
      link: ($scope, $el, $attr, ngModelCtrl) ->
        appendToBody = false
        $popupEl = angular.element """
					<div ng-style="{display: (isOpen && 'block') || 'none', top: position.top+'px', left: position.left+'px'}">
						<dp-datetime></dp-datetime>
					</div>
				"""
        $popupEl.addClass 'bootstrap-datetimepicker-widget dropdown-menu'
        $popupEl.css
          width: '280px'
          userSelect: 'none'
        $popupScope = $scope.$new(true)
        $popup = $compile($popupEl)($popupScope)
        $popupEl.remove()
        $el.after($popup)

        $el.on 'click', -> $popupScope.isOpen = true

        $popupScope.$watch 'isOpen', (val) ->
          return if !val?
          if val
            $popupScope.position = if appendToBody then $position.offset($el) else $position.position($el)
            $popupScope.position.top = $popupScope.position.top + $el.prop('offsetHeight')

        $popupScope.$watch 'date', (val) ->
          return if !val?
          ngModelCtrl.$setModelValue val.toDate()
    ])

  .directive('dpDatetime', ['dpDatetimeConfig', (defaults) ->
    restrict: 'E'
    replace: true
    templateUrl: 'template/dp/datetime.html'
    scope: {}
    controller: ->

    link: ($scope, $el, $attrs) ->
      date = if $scope.date? then moment($scope.date) else moment()
      today = moment()
      options = defaults #todo

      format = options.format || 'L LT'
      actualFormat = format.replace(/(\[[^\[]*\])|(\\)?(LTS|LT|LL?L?L?|l{1,4})/g, (input) ->
        date.localeData().longDateFormat(input) || input
      )
      parseFormats = if options.extraFormats then options.extraFormats.slice() else []
      if parseFormats.indexOf(format) < 0 && parseFormats.indexOf(actualFormat) < 0
        parseFormats.push actualFormat
      use24Hours = actualFormat.toLowerCase().indexOf('a') < 1 && actualFormat.indexOf('h') < 1

      $scope.modes = modes =
        minute: 0
        hour: 1
        time: 2
        day: 3
        month: 4
        year: 5
      $scope.days = []
      $scope.months = []
      $scope.years = []
      $scope.hours = []
      $scope.minutes = []
      $scope.mode = modes.day
      $scope.active = date.clone()
      $scope.use24 = use24Hours

      granularities =
        y: -> actualFormat.indexOf('Y') != -1
        M: -> actualFormat.indexOf('M') != -1
        d: -> actualFormat.toLowerCase().indexOf('d') != -1
        h: -> actualFormat.toLowerCase().indexOf('h') != -1
        H: -> actualFormat.toLowerCase().indexOf('h') != -1
        m: -> actualFormat.indexOf('m') != -1
        s: -> actualFormat.indexOf('s') != -1

      isEnabled = (granularity) ->
        func = granularities[granularity]
        return false if !func
        func(granularity)

      hasTime = -> isEnabled('h') || isEnabled('m') || isEnabled('s')

      hasDate = -> isEnabled('y') || isEnabled('M') || isEnabled('d')

      isValid = (targetMoment, granularity) ->
        return false if !targetMoment.isValid()
        return false if options.disabledDates && isInDisabledDates(targetMoment)
        return true if options.enabledDates && isInEnabledDates(targetMoment)
        return false if options.minDate && targetMoment.isBefore(options.minDate, granularity)
        return false if options.maxDate && targetMoment.isAfter(options.maxDate, granularity)
        return false if granularity == 'd' && options.daysOfWeekDisabled.indexOf(targetMoment.day()) != -1
        return true

      renderers = {}
      render = (mode) ->
        renderers[mode]? && renderers[mode]()

      renderers[modes.year] = ->
        startY = date.clone().subtract(5, 'y')
        endY = date.clone().add(6, 'y')
        $scope.years.length = 0
        $scope.prev = true
        $scope.next = true
        $scope.headerDisabled = true
        $scope.header = startY.year() + '-' + endY.year()
        while !startY.isAfter(endY, 'y')
          $scope.years.push startY.year()
          startY.add(1, 'y')
      renderers[modes.month] = ->
        $scope.months.length = 0
        $scope.prev = true
        $scope.next = true
        $scope.header = date.year()
        $scope.headerDisabled = false
        monthsShort = date.clone().startOf('y').hour(12)
        while monthsShort.isSame(date, 'y')
          $scope.months.push
            date: monthsShort.format('MMM')
            active: monthsShort.isSame(date, 'M')
          monthsShort.add(1, 'M')
      renderers[modes.day] = ->
        return if !(isEnabled('y') || isEnabled('M') || isEnabled('d'))
        row = []
        $scope.days.length = 0
        $scope.header = date.format options.dayViewHeaderFormat
        $scope.headerDisabled = false
        $scope.prev = isValid(date.clone().subtract(1, 'M'), 'M')
        $scope.next = isValid(date.clone().add(1, 'M'), 'M')
        currentDate = date.clone().startOf('M').startOf('week')
        while !date.clone().endOf('M').endOf('w').isBefore(currentDate, 'd')
          if 0 == currentDate.weekday()
            row = []
            $scope.days.push row
          day =
            date: currentDate.date()
            old: currentDate.isBefore(date, 'M')
            new: currentDate.isAfter(date, 'M')
            today: currentDate.isSame(today, 'd')
            disabled: !isValid(currentDate, 'd')
            active: $scope.active.isSame(currentDate, 'd')
          row.push day
          currentDate.add(1, 'd')
      renderers[modes.time] = ->
        # do nothing
      renderers[modes.hour] = ->
        $scope.hours.length = 0
        for n in [0..if use24Hours then 23 else 11]
          $scope.hours.push n
        $scope.hours[0] = 12 if !use24Hours
      renderers[modes.minute] = ->
        $scope.minutes.length = 0
        for n in [0..55] by 5
          if n < 10
            n = '0' + n
          $scope.minutes.push n

      setDatetime = (dt) ->
        dt.locale(options.locale)
        return if !isValid(dt)
        $scope.date = dt.toDate()
        $scope.active = dt.clone()
        date = dt
        render($scope.mode)

      $scope.selectDay = (day) ->
        return if day.disabled
        dt = date.clone()
        if day.old then dt.subtract(1, 'M')
        if day.new then dt.add(1, 'M')
        dt.date(day.date)
        setDatetime(dt)

      $scope.selectMonth = (month) ->
        date.month(month.date)
        $scope.mode = modes.day

      $scope.selectYear = (year) ->
        date.year(year)
        $scope.mode = modes.month

      $scope.selectHour = (hour) ->
        dt = date.clone()
        dt.hour(hour)
        setDatetime(dt)
        $scope.mode = modes.time

      $scope.selectMinute = (minute) ->
        dt = date.clone()
        dt.minutes(minute)
        setDatetime(dt)
        $scope.mode = modes.time

      $scope.togglePeriod = ->
        hours = if date.hours() >= 12 then -12 else 12
        setDatetime(date.clone().add(hours, 'h'))

      $scope.isModeAvailable = (mode) ->
        return modes[mode] && mode >= options.minView

      $scope.switchMode = (mode) ->
        return if $scope.headerDisabled && mode >= modes.day
        return if !$scope.isModeAvailable
        $scope.mode = mode

      $scope.$watch 'mode', (val) ->
        render(val)
  ])


  angular.module('template/dp/datetime.html', []).run(["$templateCache", ($templateCache) ->
    $templateCache.put(
      'template/dp/datetime.html'
                      """
				<ul class="list-unstyled">
					<li ng-if="mode >= modes.day" style="overflow: hidden;">
						<div class="datepicker">
							<table class="table-condensed">
								<thead>
									<tr>
										<th class="prev" ng-class="!prev && 'disabled'" ng-click="switch(-1)">
											<span class="fa fa-chevron-left"></span>
										</th>
										<th class="picker-switch" colspan="5" ng-click="switchMode(mode+1)" ng-class="headerDisabled && 'disabled'">
											{{ header }}
										</th>
										<th class="next" ng-class="!next && 'disabled'" ng-click="switch(1)">
											<span class="fa fa-chevron-right"></span>
										</th>
									</tr>
									<tr ng-if="modes.day === mode">
										<th class="dow">Su</th>
										<th class="dow">Mo</th>
										<th class="dow">Tu</th>
										<th class="dow">We</th>
										<th class="dow">Th</th>
										<th class="dow">Fr</th>
										<th class="dow">Sa</th>
									</tr>
								</thead>
								<tbody ng-if="modes.day === mode">
									<tr ng-repeat="row in days">
										<td class="day" ng-class="{active: day.active, today: day.today, disabled: day.disabled}" ng-repeat="day in row" ng-click="selectDay(day)">
											{{ day.date }}
										</td>
									</tr>
								</tbody>
								<tbody ng-if="modes.month === mode">
									<tr>
										<td colspan="7">
											<span class="month" ng-repeat="month in months" ng-class="month.active && 'active'" ng-click="selectMonth(month)">
												{{ month.date }}
											</span>
										</td>
									</tr>
								</tbody>
								<tbody ng-if="modes.year === mode">
									<tr>
										<td colspan="7">
											<span class="year" ng-repeat="year in years" ng-class="date.year() == year && 'active'" ng-click="selectYear(year)">
												{{ year }}
											</span>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</li>
					<li class="picker-switch">
						<table class="table-condensed">
							<tbody>
								<tr>
									<td ng-click="mode = mode < modes.day ? modes.day : modes.time">
										<a>
											<span class="fa fa-clock-o" ng-if="mode >= modes.day"></span>
											<span class="fa fa-calendar-o" ng-if="mode < modes.day"></span>
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</li>
					<li ng-if="mode < modes.day" style="overflow: hidden;">
						<table class="table-condensed">
							<tbody>
								<tr ng-if="modes.time === mode">
									<td>
										<a href="#" class="btn">
											<span class="fa fa-chevron-up"></span>
										</a>
									</td>
									<td class="separator"></td>
									<td>
										<a href="#" class="btn">
											<span class="fa fa-chevron-up"></span>
										</a>
									</td>
									<td class="separator"></td>
								</tr>
								<tr ng-if="modes.time === mode">
									<td>
										<span class="timepicker-hour" ng-click="switchMode(modes.hour)">
											{{ active.hour() }}
										</span>
									</td>
									<td class="separator">:</td>
									<td>
										<span class="timepicker-minute" ng-click="switchMode(modes.minute)">
											{{ active.format('mm') }}
										</span>
									</td>
									<td ng-if="!use24">
										<button class="btn btn-primary" type="button" ng-click="togglePeriod()">
											{{ active.format('A') }}
										</button>
									</td>
								</tr>
								<tr ng-if="modes.time === mode">
									<td>
										<a href="#" class="btn">
											<span class="fa fa-chevron-down"></span>
										</a>
									</td>
									<td class="separator"></td>
									<td>
										<a href="#" class="btn">
											<span class="fa fa-chevron-down"></span>
										</a>
									</td>
									<td class="separator"></td>
								</tr>
								<tr ng-if="modes.hour === mode">
									<td>
										<span class="hour" ng-repeat="hour in hours" ng-click="selectHour(hour)">
											{{ hour }}
										</span>
									</td>
								</tr>
								<tr>
									<td ng-if="modes.minute === mode">
										<span class="minute" ng-repeat="minute in minutes" ng-click="selectMinute(minute)">
											{{ minute }}
										</span>
									</td>
								</tr>
							</tbody>
						</table>
					</li>
				</ul>
			"""
    )
  ])

