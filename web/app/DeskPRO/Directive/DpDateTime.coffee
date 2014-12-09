define ['angular', 'moment'], (angular, moment) ->

	($timeout) ->

		getUTCTime = (val) ->
			tmp = new Date() if !val
			tmp = moment(val).toDate() if val
			tmp.getTime() - (tmp.getTimezoneOffset() * 60000)
			tmp

		startOfDecade = (unixDate) ->
			startYear = parseInt(moment.utc(unixDate).year() / 10, 10) * 10
			moment.utc(unixDate).year(startYear).startOf 'year'

		dateObject = (params) ->
			date =
				val: new Date().getTime()
				selectable: true
			valid = ['val', 'display', 'active', 'selectable', 'past', 'future']
			for prop, val of params when valid.indexOf(prop) > -1
				date[prop] = val
			date

		defaults =
			startView: 'day'
			minView: 'minute'
			minuteStep: 10

		restrict: 'E'
		replace: true
		template: """
		<div class="datetimepicker table-responsive" style="position: absolute; z-index: 10000; background: #fff; box-shadow: 0 0 15px rgba(0,0,0,0.7);">
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="left"
								ng-click="changeView(data.currentView, data.leftDate, $event)"
								ng-show="data.leftDate.selectable">
							<i class="fa fa-arrow-left"/>
						</th>
						<th class="switch" colspan="5"
								ng-show="data.previousViewDate.selectable"
								ng-click="changeView(data.previousView, data.previousViewDate, $event)">
							{{ data.previousViewDate.display }}
						</th>
						<th class="right"
								ng-click="changeView(data.currentView, data.rightDate, $event)"
								ng-show="data.rightDate.selectable">
							<i class="fa fa-arrow-right"/>
						</th>
					</tr>
					<tr>
						<th class="dow" ng-repeat="day in data.dayNames">
							{{ day }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-if="data.currentView !== 'day'">
						<td colspan="7">
							<span
									ng-repeat="dateObject in data.dates"
									class="{{ data.currentView }}"
									ng-class="{active: dateObject.active, past: dateObject.past, future: dateObject.future, disabled: !dateObject.selectable}"
									ng-click="changeView(data.nextView, dateObject, $event)">
								{{ dateObject.display }}
							</span>
						</td>
					</tr>
					<tr ng-if="data.currentView === 'day'" ng-repeat="week in data.weeks">
						<td ng-repeat="dateObject in week.dates"
							ng-click="changeView(data.nextView, dateObject, $event)"
							class="day"
							ng-class="{active: dateObject.active, past: dateObject.past, future: dateObject.future, disabled: !dateObject.selectable}">
								{{ dateObject.display }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>"""
		scope: {}

		controller: ($scope) ->

			getVal = ->
				val = null
				if $scope.ngModel && $scope.ngModel.$modelValue
					val = $scope.ngModel.$modelValue
					val = new Date(val) if !(val instanceof Date)
				val

			dataFactory =
				year: (unixDate) ->
					selectedDate = moment.utc(unixDate).startOf 'year'
					# View starts one year before the decade starts and ends one year after the decade ends
					#	i.e. passing in a date of 1/1/2013 will give a range of 2009 to 2020
					#	Truncate the last digit from the current year and subtract 1 to get the start of the decade
					startDecade = parseInt(selectedDate.year() / 10, 10) * 10
					startDate = moment.utc(startOfDecade(unixDate)).subtract(1, 'year').startOf 'year'
					activeYear = if getVal() then moment(getVal()).year() else 0
					console.info startDate

					result =
						currentView: 'year'
						nextView: if $scope.options.minView == 'year' then 'setTime' else 'month'
						previousViewDate: dateObject {val: null, display: startDecade + '-' + (startDecade + 9)}
						leftDate: dateObject {val: moment.utc(startDate).subtract(9, 'year').valueOf()}
						rightDate: dateObject {val: moment.utc(startDate).add(11, 'year').valueOf()}
						dates: []

					for i in [0..11]
						yearMoment = moment.utc(startDate).add i, 'years'
						result.dates.push dateObject(
							val: yearMoment.valueOf()
							display: yearMoment.format('YYYY')
							past: yearMoment.year() < startDecade
							future: yearMoment.year() > startDecade + 9
							active: yearMoment.year() == activeYear
						)
					result

				month: (unixDate) ->
					startDate = moment.utc(unixDate).startOf 'year'
					previousViewDate = startOfDecade unixDate
					activeDate = if getVal() then moment(getVal()).format('YYYY-MMM') else 0

					result =
						previousView: 'year'
						currentView: 'month'
						nextView: if $scope.options.minView == 'month' then 'setTime' else 'day'
						previousViewDate: dateObject {val: previousViewDate.valueOf(), display: startDate.format('YYYY')}
						leftDate: dateObject {val: moment.utc(startDate).subtract(1, 'year').valueOf()}
						rightDate: dateObject {val: moment.utc(startDate).add(1, 'year').valueOf()}
						dates: []

					for i in [0..11]
						monthMoment = moment.utc(startDate).add i, 'months'
						result.dates.push dateObject(
							val: monthMoment.valueOf()
							display: monthMoment.format 'MMM'
							active: monthMoment.format 'YYYY-MMM' == activeDate
						)
					result

				day: (unixDate) ->
					selectedDate = moment.utc unixDate
					startOfMonth = moment.utc(selectedDate).startOf 'month'
					previousViewDate = moment.utc(selectedDate).startOf 'year'
					endOfMonth = moment.utc(selectedDate).endOf 'month'

					startDate = moment.utc(startOfMonth).subtract Math.abs(startOfMonth.weekday()), 'days'
					activeDate = if getVal() then moment(getVal()).format('YYYY-MMM-DD') else ''

					result =
						previousView: 'month',
						currentView: 'day',
						nextView: if $scope.options.minView == 'day' then 'setTime' else 'hour'
						previousViewDate: dateObject(
							val: previousViewDate.valueOf()
							display: startOfMonth.format('YYYY-MMM')
						)
						leftDate: dateObject({val: moment.utc(startOfMonth).subtract(1, 'months').valueOf()})
						rightDate: dateObject({val: moment.utc(startOfMonth).add(1, 'months').valueOf()})
						dayNames: [],
						weeks: []

					for dayNumber in [0..6]
						result.dayNames.push moment.utc().weekday(dayNumber).format('dd')

					for i in [0..5]
						week = {dates: []}
						for j in [0..6]
							monthMoment = moment.utc(startDate).add((i * 7) + j, 'days')
							obj = dateObject(
								val: monthMoment.valueOf()
								display: monthMoment.format('D')
								active: monthMoment.format('YYYY-MMM-DD') == activeDate
								past: monthMoment.isBefore(startOfMonth)
								future: monthMoment.isAfter(endOfMonth)
							)
							week.dates.push obj
						result.weeks.push week
					result

				hour: (unixDate) ->
					selectedDate = moment.utc(unixDate).startOf 'day'
					previousViewDate = moment.utc(selectedDate).startOf 'month'
					activeFormat = if getVal() then moment(getVal()).format('YYYY-MM-DD H') else ''

					result =
						previousView: 'day'
						currentView: 'hour'
						nextView: if $scope.options.minView == 'hour' then 'setTime' else 'minute'
						previousViewDate: dateObject(
							val: previousViewDate.valueOf()
							display: selectedDate.format('ll')
						)
						leftDate: dateObject {val: moment.utc(selectedDate).subtract(1, 'days').valueOf()}
						rightDate: dateObject {val: moment.utc(selectedDate).add(1, 'days').valueOf()}
						dates: []

					for i in [0..23]
						hourMoment = moment.utc(selectedDate).add i, 'hours'
						result.dates.push dateObject(
							val: hourMoment.valueOf()
							display: hourMoment.format('LT')
							active: hourMoment.format('YYYY-MM-DD H') == activeFormat
						)
					result

				minute: (unixDate) ->
					selectedDate = moment.utc(unixDate).startOf 'hour'
					previousViewDate = moment.utc(selectedDate).startOf 'day'
					activeFormat = if getVal() then moment(getVal()).format('YYYY-MM-DD H:mm') else ''

					result =
						previousView: 'hour'
						currentView: 'minute'
						nextView: 'setTime'
						previousViewDate: dateObject(
							val: previousViewDate.valueOf()
							display: selectedDate.format('lll')
						)
						leftDate: dateObject {val: moment.utc(selectedDate).subtract(1, 'hours').valueOf()}
						rightDate: dateObject {val: moment.utc(selectedDate).add(1, 'hours').valueOf()}
						dates: []

					limit = 60 / $scope.options.minuteStep

					for i in [0..23]
						hourMoment = moment.utc(selectedDate).add(i * $scope.options.minuteStep, 'minute')
						result.dates.push dateObject(
							val: hourMoment.valueOf()
							display: hourMoment.format('LT')
							active: hourMoment.format('YYYY-MM-DD H:mm') == activeFormat
						)
					result

				setTime: (unixDate) ->
					tmp = new Date(unixDate)
					newDate = new Date(tmp.getTime() + tmp.getTimezoneOffset() * 60000)
					if $scope.ngModel
						format = 'll'
						format = 'lll' if 'hour' == $scope.options.minView || 'minute' == $scope.options.minView
						$scope.ngModel.$setViewValue moment(newDate).format(format)
						$scope.ngModel.$render()
					$scope.visible = false
					dataFactory[$scope.options.startView](unixDate)


			$scope.changeView = (viewName, dateObject, event) ->
				if event
					event.stopPropagation()
					event.preventDefault()
				return if !(viewName && dateObject.val > -Infinity && dateObject.selectable && dataFactory[viewName])
				$scope.data = dataFactory[viewName](dateObject.val)


		link: ($scope, $el, $attr) ->
			$mask = $('.select2-drop-mask')

			$scope.$watch 'visible', (val) ->
				if val
					$el.show()
					$mask.show().css('z-index', 9999).on 'click.dp.datetime', -> $scope.visible = false
				else
					$el.hide()
					$mask.hide().off 'click.dp.datetime'
					$scope.ngModel = null
					$scope.options = {}

			$scope.$root.$on 'dp.datetime.show', ($event, ngModel, $callee, options) ->
				$scope.ngModel = ngModel
				$scope.options = angular.extend {}, defaults, options
				$scope.changeView $scope.options.startView, dateObject({val: getUTCTime(null)})
				$scope.visible = true

				# todo fit in screen bounds
				$el.css(
					left: $callee.offset().left
					top: $callee.offset().top
				)


