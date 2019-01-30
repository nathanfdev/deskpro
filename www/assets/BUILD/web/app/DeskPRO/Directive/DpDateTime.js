// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['angular', 'moment'], (angular, moment) =>

  function($timeout) {

    const getUTCTime = function(val) {
      let tmp;
      if (!val) { tmp = new Date(); }
      if (val) { tmp = moment(val).toDate(); }
      tmp.getTime() - (tmp.getTimezoneOffset() * 60000);
      return tmp;
    };

    const startOfDecade = function(unixDate) {
      const startYear = parseInt(moment.utc(unixDate).year() / 10, 10) * 10;
      return moment.utc(unixDate).year(startYear).startOf('year');
    };

    const dateObject = function(params) {
      const date = {
        val: new Date().getTime(),
        selectable: true
      };
      const valid = ['val', 'display', 'active', 'selectable', 'past', 'future'];
      for (let prop in params) {
        const val = params[prop];
        if (valid.indexOf(prop) > -1) {
          date[prop] = val;
        }
      }
      return date;
    };

    const defaults = {
      startView: 'day',
      minView: 'minute',
      minuteStep: 10
    };

    return {
      restrict: 'E',
      replace: true,
      template: `\
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
</div>`,
      scope: {},

      controller($scope) {

        const getVal = function() {
          let val = null;
          if ($scope.ngModel && $scope.ngModel.$modelValue) {
            val = $scope.ngModel.$modelValue;
            if (!(val instanceof Date)) { val = new Date(val); }
          }
          return val;
        };

        var dataFactory = {
          year(unixDate) {
            const selectedDate = moment.utc(unixDate).startOf('year');
            // View starts one year before the decade starts and ends one year after the decade ends
            // i.e. passing in a date of 1/1/2013 will give a range of 2009 to 2020
            // Truncate the last digit from the current year and subtract 1 to get the start of the decade
            const startDecade = parseInt(selectedDate.year() / 10, 10) * 10;
            const startDate = moment.utc(startOfDecade(unixDate)).subtract(1, 'year').startOf('year');
            const activeYear = getVal() ? moment(getVal()).year() : 0;
            console.info(startDate);

            const result = {
              currentView: 'year',
              nextView: $scope.options.minView === 'year' ? 'setTime' : 'month',
              previousViewDate: dateObject({val: null, display: startDecade + '-' + (startDecade + 9)}),
              leftDate: dateObject({val: moment.utc(startDate).subtract(9, 'year').valueOf()}),
              rightDate: dateObject({val: moment.utc(startDate).add(11, 'year').valueOf()}),
              dates: []
            };

            for (let i = 0; i <= 11; i++) {
              const yearMoment = moment.utc(startDate).add(i, 'years');
              result.dates.push(dateObject({
                val: yearMoment.valueOf(),
                display: yearMoment.format('YYYY'),
                past: yearMoment.year() < startDecade,
                future: yearMoment.year() > (startDecade + 9),
                active: yearMoment.year() === activeYear
              })
              );
            }
            return result;
          },

          month(unixDate) {
            const startDate = moment.utc(unixDate).startOf('year');
            const previousViewDate = startOfDecade(unixDate);
            const activeDate = getVal() ? moment(getVal()).format('YYYY-MMM') : 0;

            const result = {
              previousView: 'year',
              currentView: 'month',
              nextView: $scope.options.minView === 'month' ? 'setTime' : 'day',
              previousViewDate: dateObject({val: previousViewDate.valueOf(), display: startDate.format('YYYY')}),
              leftDate: dateObject({val: moment.utc(startDate).subtract(1, 'year').valueOf()}),
              rightDate: dateObject({val: moment.utc(startDate).add(1, 'year').valueOf()}),
              dates: []
            };

            for (let i = 0; i <= 11; i++) {
              const monthMoment = moment.utc(startDate).add(i, 'months');
              result.dates.push(dateObject({
                val: monthMoment.valueOf(),
                display: monthMoment.format('MMM'),
                active: monthMoment.format('YYYY-MMM' === activeDate)
              })
              );
            }
            return result;
          },

          day(unixDate) {
            const selectedDate = moment.utc(unixDate);
            const startOfMonth = moment.utc(selectedDate).startOf('month');
            const previousViewDate = moment.utc(selectedDate).startOf('year');
            const endOfMonth = moment.utc(selectedDate).endOf('month');

            const startDate = moment.utc(startOfMonth).subtract(Math.abs(startOfMonth.weekday()), 'days');
            const activeDate = getVal() ? moment(getVal()).format('YYYY-MMM-DD') : '';

            const result = {
              previousView: 'month',
              currentView: 'day',
              nextView: $scope.options.minView === 'day' ? 'setTime' : 'hour',
              previousViewDate: dateObject({
                val: previousViewDate.valueOf(),
                display: startOfMonth.format('YYYY-MMM')
              }),
              leftDate: dateObject({val: moment.utc(startOfMonth).subtract(1, 'months').valueOf()}),
              rightDate: dateObject({val: moment.utc(startOfMonth).add(1, 'months').valueOf()}),
              dayNames: [],
              weeks: []
            };

            for (let dayNumber = 0; dayNumber <= 6; dayNumber++) {
              result.dayNames.push(moment.utc().weekday(dayNumber).format('dd'));
            }

            for (let i = 0; i <= 5; i++) {
              const week = {dates: []};
              for (let j = 0; j <= 6; j++) {
                const monthMoment = moment.utc(startDate).add((i * 7) + j, 'days');
                const obj = dateObject({
                  val: monthMoment.valueOf(),
                  display: monthMoment.format('D'),
                  active: monthMoment.format('YYYY-MMM-DD') === activeDate,
                  past: monthMoment.isBefore(startOfMonth),
                  future: monthMoment.isAfter(endOfMonth)
                });
                week.dates.push(obj);
              }
              result.weeks.push(week);
            }
            return result;
          },

          hour(unixDate) {
            const selectedDate = moment.utc(unixDate).startOf('day');
            const previousViewDate = moment.utc(selectedDate).startOf('month');
            const activeFormat = getVal() ? moment(getVal()).format('YYYY-MM-DD H') : '';

            const result = {
              previousView: 'day',
              currentView: 'hour',
              nextView: $scope.options.minView === 'hour' ? 'setTime' : 'minute',
              previousViewDate: dateObject({
                val: previousViewDate.valueOf(),
                display: selectedDate.format('ll')
              }),
              leftDate: dateObject({val: moment.utc(selectedDate).subtract(1, 'days').valueOf()}),
              rightDate: dateObject({val: moment.utc(selectedDate).add(1, 'days').valueOf()}),
              dates: []
            };

            for (let i = 0; i <= 23; i++) {
              const hourMoment = moment.utc(selectedDate).add(i, 'hours');
              result.dates.push(dateObject({
                val: hourMoment.valueOf(),
                display: hourMoment.format('LT'),
                active: hourMoment.format('YYYY-MM-DD H') === activeFormat
              })
              );
            }
            return result;
          },

          minute(unixDate) {
            const selectedDate = moment.utc(unixDate).startOf('hour');
            const previousViewDate = moment.utc(selectedDate).startOf('day');
            const activeFormat = getVal() ? moment(getVal()).format('YYYY-MM-DD H:mm') : '';

            const result = {
              previousView: 'hour',
              currentView: 'minute',
              nextView: 'setTime',
              previousViewDate: dateObject({
                val: previousViewDate.valueOf(),
                display: selectedDate.format('lll')
              }),
              leftDate: dateObject({val: moment.utc(selectedDate).subtract(1, 'hours').valueOf()}),
              rightDate: dateObject({val: moment.utc(selectedDate).add(1, 'hours').valueOf()}),
              dates: []
            };

            const limit = 60 / $scope.options.minuteStep;

            for (let i = 0; i <= 23; i++) {
              const hourMoment = moment.utc(selectedDate).add(i * $scope.options.minuteStep, 'minute');
              result.dates.push(dateObject({
                val: hourMoment.valueOf(),
                display: hourMoment.format('LT'),
                active: hourMoment.format('YYYY-MM-DD H:mm') === activeFormat
              })
              );
            }
            return result;
          },

          setTime(unixDate) {
            const tmp = new Date(unixDate);
            const newDate = new Date(tmp.getTime() + (tmp.getTimezoneOffset() * 60000));
            if ($scope.ngModel) {
              let format = 'll';
              if (('hour' === $scope.options.minView) || ('minute' === $scope.options.minView)) { format = 'lll'; }
              $scope.ngModel.$setViewValue(moment(newDate).format(format));
              $scope.ngModel.$render();
            }
            $scope.visible = false;
            return dataFactory[$scope.options.startView](unixDate);
          }
        };


        return $scope.changeView = function(viewName, dateObject, event) {
          if (event) {
            event.stopPropagation();
            event.preventDefault();
          }
          if (!(viewName && (dateObject.val > -Infinity) && dateObject.selectable && dataFactory[viewName])) { return; }
          return $scope.data = dataFactory[viewName](dateObject.val);
        };
      },


      link($scope, $el, $attr) {
        const $mask = $('.select2-drop-mask');

        $scope.$watch('visible', function(val) {
          if (val) {
            $el.show();
            return $mask.show().css('z-index', 9999).on('click.dp.datetime', () => $scope.visible = false);
          } else {
            $el.hide();
            $mask.hide().off('click.dp.datetime');
            $scope.ngModel = null;
            return $scope.options = {};
          }
      });

        return $scope.$root.$on('dp.datetime.show', function($event, ngModel, $callee, options) {
          $scope.ngModel = ngModel;
          $scope.options = angular.extend({}, defaults, options);
          $scope.changeView($scope.options.startView, dateObject({val: getUTCTime(null)}));
          $scope.visible = true;

          // todo fit in screen bounds
          return $el.css({
            left: $callee.offset().left,
            top: $callee.offset().top
          });
        });
      }
    };
  }
);


