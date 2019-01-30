// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS202: Simplify dynamic range loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['angular', 'moment'], function(angular, moment) {
  angular.module('dp.datetimepicker', ['template/dp/datetime.html', 'ui.bootstrap'])

  .constant('dpDatetimeConfig', {
    format: 'DD.MM.YYYY HH:mm', // moment formats
    dayViewHeaderFormat: 'MMMM YYYY',
    minDate: false,
    maxDate: false,
    locale: moment.locale()
  }
  )

  .directive('dpDatetimePopup', ['$compile', '$document', '$position', 'dpDatetimeConfig', ($compile, $document, $position, defaults) =>
      ({
        require: 'ngModel',
        restrict: 'A',
        scope: {
          format: '@dpDatetimePopup',
          date: '=ngModel',
          minView: '@',
          minDate: '@',
          maxDate: '@',
          appendTo: '@'
        },
        link($scope, $el, $attr, ngModelCtrl) {

          ngModelCtrl.$formatters.push(function(val) {
            if (!val) { return ''; }
            return moment(val).format($scope.format || defaults.format);
          });

          // warning: this parser is not called when user select date in DatePicker
          //          because we change the value programmatically (through the DatePicker)
          //          and $parsers are not called when the bound ngModel expression changes programmatically
          //          this parser called only if user manually type date in input
          ngModelCtrl.$parsers.push(function(val) {
            if (!val) { return null; }
            return moment(val).toDate();
          });

          // todo should be always in body, only one instance of dpDatetime for all dpDatetimePopup
          // todo as we have multiple instances of datetime directive for now
          const appendToBody = 'body' === $scope.appendTo;

          const $popupEl = angular.element(`\
<div ng-style="{display: (isOpen && 'block') || 'none', top: position.top+'px', left: position.left+'px'}">
	  <dp-datetime ng-model="date" format="${$scope.format}" min-view="${$scope.minView}" min-date="${$scope.minDate}" max-date="${$scope.maxDate}"></dp-datetime>
</div>\
`
          );
          $popupEl.addClass('bootstrap-datetimepicker-widget dropdown-menu');
          $popupEl.css({
            width: '280px',
            userSelect: 'none',
            zIndex: 10000
          });
          const $popup = $compile($popupEl)($scope);
          $popupEl.remove();
          if (appendToBody) { $document.find('body').append($popup); } else { $el.after($popup); }

          $popup.on('click', function(e) {
            e.preventDefault();
            return e.stopPropagation();
          });

          const documentHandler = event =>
            $scope.$apply(function() {
              if ($scope.isOpen && (event.target !== $el[0])) {
                return $scope.isOpen = false;
              }
            })
          ;

          $el.on('click', () => $scope.$apply(() => $scope.isOpen = true));
          $document.on('click', documentHandler);

          $scope.$watch('isOpen', function(val) {
            if (!val) { return; }
            $scope.position = appendToBody ? $position.offset($el) : $position.position($el);
            return $scope.position.top = $scope.position.top + $el.prop('offsetHeight');
          });

          return $scope.$on('$destroy', function() {
            $popup.remove();
            return $document.off('click', documentHandler);
          });
        }
      })
    
    ])

  .directive('dpDatetime', ['dpDatetimeConfig', defaults =>
    ({
      restrict: 'E',
      replace: true,
      templateUrl: 'template/dp/datetime.html',
      scope: {
        date: '=ngModel',
        format: '@',
        minView: '@',
        minDate: '@',
        maxDate: '@'
      },
      controller() {},

      link($scope, $el) {
        let format, modes;
        let date = ($scope.date != null) && ($scope.date !== 'undefined') ? moment($scope.date) : moment();
        const today = moment();

        $scope.modes = (modes = {
          minute: 0,
          hour: 1,
          time: 2,
          day: 3,
          month: 4,
          year: 5
        });
        $scope.days = [];
        $scope.months = [];
        $scope.years = [];
        $scope.hours = [];
        $scope.minutes = [];
        $scope.mode = modes.day;
        $scope.active = date.clone();

        $scope.format = (format = $scope.format || defaults.format);
        $scope.use24 = ($scope.format.toLowerCase().indexOf('a') < 1) && ($scope.format.indexOf('h') < 1);
        $scope.minDate = $scope.minDate || defaults.minDate;
        $scope.maxDate = $scope.maxDate || defaults.maxDate;
        if (modes[$scope.minView]) { $scope.minMode = modes[$scope.minView]; }

        const granularities = {
          // todo check $scope.format initialized as undefined. changing to explicit value for now
          y() { return format.indexOf('Y') !== -1; },
          M() { return format.indexOf('M') !== -1; },
          d() { return format.toLowerCase().indexOf('d') !== -1; },
          h() { return format.toLowerCase().indexOf('h') !== -1; },
          H() { return format.toLowerCase().indexOf('h') !== -1; },
          m() { return format.indexOf('m') !== -1; },
          s() { return format.indexOf('s') !== -1; }
        };
        const isEnabled = function(granularity) {
          const func = granularities[granularity];
          if (!func) { return false; }
          return func(granularity);
        };
        const hasTime = () => isEnabled('h') || isEnabled('m') || isEnabled('s');
        const hasDate = () => isEnabled('y') || isEnabled('M') || isEnabled('d');

        if (($scope.minMode == null)) {
          $scope.minMode = !hasTime() ? modes.day : ($scope.minMode = modes.minute);
        }
        if (!isEnabled('h')) { date.hours(0); }
        if (!isEnabled('m')) { date.minutes(0); }

        const isValid = function(targetMoment, granularity) {
          if (!targetMoment.isValid()) { return false; }
          if (($scope.minDate != null) && ($scope.minDate !== 'undefined') && targetMoment.isBefore($scope.minDate, granularity)) { return false; }
          if (($scope.maxDate != null) && ($scope.maxDate !== 'undefined') && targetMoment.isAfter($scope.maxDate, granularity)) { return false; }
          return true;
        };

        const renderers = {};
        const render = function(mode) {
          if ((renderers[mode] == null)) { return; }
          const i = mode === modes.year ? 12 : 1;
          const g = mode > modes.day ? 'Y' : 'M';
          $scope.prev = isValid(date.clone().subtract(i, g), g);
          $scope.next = isValid(date.clone().add(i, g), g);
          $scope.headerDisabled = false;
          return renderers[mode]();
        };

        renderers[modes.year] = function() {
          const startY = date.clone().subtract(5, 'y');
          const endY = date.clone().add(6, 'y');
          $scope.years.length = 0;
          $scope.headerDisabled = true;
          $scope.header = startY.year() + '-' + endY.year();
          return (() => {
            const result = [];
            while (date.isValid() && !startY.isAfter(endY, 'y')) {
              $scope.years.push(startY.year());
              result.push(startY.add(1, 'y'));
            }
            return result;
          })();
        };
        renderers[modes.month] = function() {
          $scope.months.length = 0;
          $scope.header = date.year();
          const monthsShort = date.clone().startOf('y').hour(12);
          return (() => {
            const result = [];
            while (monthsShort.isSame(date, 'y')) {
              $scope.months.push({
                date: monthsShort.format('MMM'),
                active: monthsShort.isSame(date, 'M')
              });
              result.push(monthsShort.add(1, 'M'));
            }
            return result;
          })();
        };
        renderers[modes.day] = function() {
          if (!(isEnabled('y') || isEnabled('M') || isEnabled('d'))) { return; }
          let row = [];
          $scope.days.length = 0;
          $scope.header = date.format(defaults.dayViewHeaderFormat);
          const currentDate = date.clone().startOf('M').startOf('week');
          return (() => {
            const result = [];
            while (date.isValid() && !date.clone().endOf('M').endOf('w').isBefore(currentDate, 'd')) {
              if (0 === currentDate.weekday()) {
                row = [];
                $scope.days.push(row);
              }
              const day = {
                date: currentDate.date(),
                old: currentDate.isBefore(date, 'M'),
                new: currentDate.isAfter(date, 'M'),
                today: currentDate.isSame(today, 'd'),
                disabled: !isValid(currentDate, 'd'),
                active: $scope.active.isSame(currentDate, 'd')
              };
              row.push(day);
              result.push(currentDate.add(1, 'd'));
            }
            return result;
          })();
        };
        renderers[modes.time] = function() {};
          // do nothing
        renderers[modes.hour] = function() {
          $scope.hours.length = 0;
          for (let n = 0, end = $scope.use24 ? 23 : 11, asc = 0 <= end; asc ? n <= end : n >= end; asc ? n++ : n--) {
            $scope.hours.push(n);
          }
          if (!$scope.use24) { return $scope.hours[0] = 12; }
        };
        renderers[modes.minute] = function() {
          $scope.minutes.length = 0;
          return (() => {
            const result = [];
            for (let i = 0, n = i; i <= 55; i += 5, n = i) {
              if (n < 10) {
                n = `0${n}`;
              }
              result.push($scope.minutes.push(n));
            }
            return result;
          })();
        };

        const setDatetime = function(dt) {
          dt.locale(defaults.locale);
          if (!isValid(dt)) { return; }
          $scope.date = dt;
          $scope.active = dt.clone();
          date = dt;
          if (modes.day === $scope.minMode) {
            $scope.$parent.isOpen = false;
          }
          return render($scope.mode);
        };

        $scope.selectDay = function(day) {
          if (day.disabled) { return; }
          const dt = date.clone();
          if (day.old) { dt.subtract(1, 'M'); }
          if (day.new) { dt.add(1, 'M'); }
          dt.date(day.date);
          return setDatetime(dt);
        };

        $scope.selectMonth = function(month) {
          date.month(month.date);
          return $scope.mode = modes.day;
        };

        $scope.selectYear = function(year) {
          date.year(year);
          return $scope.mode = modes.month;
        };

        $scope.selectHour = function(hour) {
          const dt = date.clone();
          dt.hour(hour);
          setDatetime(dt);
          return $scope.mode = modes.time;
        };

        $scope.selectMinute = function(minute) {
          const dt = date.clone();
          dt.minutes(minute);
          setDatetime(dt);
          return $scope.mode = modes.time;
        };

        $scope.togglePeriod = function() {
          const hours = date.hours() >= 12 ? -12 : 12;
          return setDatetime(date.clone().add(hours, 'h'));
        };

        $scope.isModeAvailable = mode => (mode >= $scope.minMode) && (mode <= modes.year);

        $scope.switchMode = function(mode) {
          if ($scope.headerDisabled) { return; }
          if (!$scope.isModeAvailable(mode)) { return; }
          return $scope.mode = mode;
        };

        $scope.increment = g => setDatetime(date.clone().add(1, g));

        $scope.decrement = g => setDatetime(date.clone().subtract(1, g));

        $scope.go = function(dir) {
          if (!$scope[dir]) { return; }
          let i = 1;
          let g = null;
          if (modes.day === $scope.mode) {
            g = 'month';
          } else if (modes.month === $scope.mode) {
            g = 'year';
          } else if (modes.year === $scope.mode) {
            g = 'years';
            i = 12;
          }
          if (!g) { return; }
          const method = 'next' === dir ? 'add' : 'subtract';
          date[method](i, g);
          return render($scope.mode);
        };

        return $scope.$watch('mode', val => render(val));
      }
    })
  
  ]);


  return angular.module('template/dp/datetime.html', []).run(["$templateCache", $templateCache =>
    $templateCache.put(
      'template/dp/datetime.html',
        `\
<ul class="list-unstyled">
	<li ng-if="mode >= modes.day" style="overflow: hidden;">
		<div class="datepicker">
			<table class="table-condensed">
				<thead>
					<tr>
						<th class="prev" ng-class="!prev && 'disabled'" ng-click="go('prev')">
							<span class="fa fa-chevron-left"></span>
						</th>
						<th class="picker-switch" colspan="5" ng-click="switchMode(mode+1)" ng-class="headerDisabled && 'disabled'">
							{{ header }}
						</th>
						<th class="next" ng-class="!next && 'disabled'" ng-click="go('next')">
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
	<li class="picker-switch" ng-if="isModeAvailable(modes.time)">
		<table class="table-condensed">
			<tbody>
				<tr>
					<td ng-click="switchMode(mode < modes.day ? modes.day : modes.time)">
						<a>
							<span class="far fa-clock" ng-if="mode >= modes.day"></span>
							<span class="far fa-calendar" ng-if="mode < modes.day"></span>
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
						<a class="btn" ng-click="increment('h')">
							<span class="fa fa-chevron-up"></span>
						</a>
					</td>
					<td class="separator"></td>
					<td>
						<a class="btn" ng-click="increment('m')">
							<span class="fa fa-chevron-up"></span>
						</a>
					</td>
					<td class="separator" ng-if="!use24"></td>
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
						<a class="btn" ng-click="decrement('h')">
							<span class="fa fa-chevron-down"></span>
						</a>
					</td>
					<td class="separator"></td>
					<td>
						<a class="btn" ng-click="decrement('m')">
							<span class="fa fa-chevron-down"></span>
						</a>
					</td>
					<td class="separator" ng-if="!use24"></td>
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
</ul>\
`
    )
  
  ]);
});

