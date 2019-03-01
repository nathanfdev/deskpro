define(['DeskPRO/Data/TzData'], function(TzData) {
  const Admin_Main_Directive_DpWorkingHours = [() =>
    ({
      restrict:    'E',
      require:     'ngModel',
      replace:     true,
      templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Common/work-hours-directive.html`,
      scope:       {},
      link(scope, element, attrs, ngModel) {
        let i,
          row;
        let asc,
          end;
        let asc1,
          end1;
        scope.timezone       = 'UTC';
        scope.start_hour     = 9;
        scope.start_min      = 0;
        scope.end_hour       = 18;
        scope.end_min        = 0;
        scope.work_days      = [null, true, true, true, true, true, false, false]; // 0=null because valid idx is 1-7 for ISO-8601 days
        scope.hol_year       = (new Date()).getFullYear();
        scope.hol_new_month  = 1;
        scope.hol_new_day    = 1;
        scope.hol_new_name   = '';
        scope.holidays       = [];

        scope.minutes = [];
        for (i = 0; i <= 59; i++) {
          scope.minutes.push({ id: i, label: (`0${i}`).slice(-2) });
        }
        scope.hours = [];
        for (i = 0; i <= 23; i++) {
          scope.hours.push({ id: i, label: (`0${i}`).slice(-2) });
        }

        scope.days = [];
        for (i = 1; i <= 31; i++) {
          scope.days.push({ id: i, label: (`0${i}`).slice(-2) });
        }
        scope.months = [];
        for (i = 1; i <= 12; i++) {
          scope.months.push({ id: i, label: (`0${i}`).slice(-2) });
        }

        const currentYear = new Date().getFullYear();
        scope.years = [];
        for (i = currentYear, end = currentYear + 9, asc = currentYear <= end; asc ? i <= end : i >= end; asc ? i++ : i--) {
          scope.years.push({ id: i, label: `${i}` });
        }


        //------------------------------
        // Element references
        //------------------------------

        const els = {
          hol_wrap: element.find('.holiday-rows'),
        };

        //------------------------------
        // Init holiday years
        //------------------------------

        let year = (new Date()).getFullYear();
        const year_end = year + 4;

        for (i = year, end1 = year_end, asc1 = year <= end1; asc1 ? i <= end1 : i >= end1; asc1 ? i++ : i--) {
          row = $(`<div class="holiday-year-rows year-row year-${i}"></div>`);
          row.appendTo(els.hol_wrap);
        }

        row = $('<div class="holiday-year-rows year-repeat"></div>');
        row.appendTo(els.hol_wrap);

        //------------------------------
        // Adding/removing holidays
        //------------------------------

        const holExists = function (hol1, hol2) {
          if (hol1.year !== hol2.year) {
            return false;
          }

          if (hol1.month !== hol2.month) {
            return false;
          }

          if (hol1.day !== hol2.day) {
            return false;
          }

          return true;
        };

        scope.addHoliday = function ($event) {
          $event.preventDefault();
          $event.stopPropagation();

          year   = parseInt(scope.hol_year);
          const month  = parseInt(scope.hol_new_month);
          const day    = parseInt(scope.hol_new_day);
          const title  = $.trim(scope.hol_new_name);
          const repeat = scope.hol_new_repeat;

          if (repeat) {
            year = 0;
          }

          if (!month || !day) {
            return;
          }

          let exists = false;
          const hol = { year, month, day, name: title };

          for (const checkHol of Array.from(scope.holidays)) {
            if (holExists(hol, checkHol)) {
              exists = true;
              break;
            }
          }

          if (exists) {
            scope.hol_new_name = '';
            scope.show_newhold = false;
            return;
          }

          drawHoliday({
            year,
            month,
            day,
            repeat,
            name: title
          });

          scope.holidays.push(hol);
          scope.hol_new_name = '';
          scope.show_newhold = false;
          return updateYearList();
        };

        var drawHoliday = function (hol) {
          let rowContainer,
            y_str;
          row = $('<div class="hol-row"><div class="remove-btn"><i class="fa fa-times-circle"></i></div> <span class="date-txt"></span> <span class="title-txt"></span></div></div>');

          ({ year }   = hol);
          const { month }  = hol;
          const { day }    = hol;
          const title  = hol.name;
          const repeat = year === 0;

          if (repeat) {
            y_str = 'Every Year';
            rowContainer = els.hol_wrap.find('.year-repeat');
          } else {
            y_str = year;
            rowContainer = els.hol_wrap.find(`.year-${year}`);
          }

          const m_str = month < 10 ? `0${month}` : month;
          const d_str = day < 10 ? `0${day}` : day;
          row.find('.date-txt').text(`${y_str}-${m_str}-${d_str}`);
          if (title.length) {
            row.find('.title-txt').text(title);
          }

          row.data('hol-rec', hol);
          return rowContainer.append(row);
        };

        element.find('.add-btn').on('click', ev =>
          scope.$apply(() => scope.addHoliday(ev))
        );

        element.on('click', '.remove-btn', function (ev) {
          ev.preventDefault();
          row = $(this).closest('.hol-row');
          const holRec = row.data('hol-rec');
          row.remove();

          if (!scope.holidays.length) {
            return;
          }

          return (() => {
            const result = [];
            for (let idx = 0; idx < scope.holidays.length; idx++) {
              const hol = scope.holidays[idx];
              if (hol === holRec) {
                scope.holidays.splice(idx, 1);
                break;
              } else {
                result.push(undefined);
              }
            }
            return result;
          })();
        });

        var updateYearList = function () {
          let any = false;
          if (els.hol_wrap.find('.year-repeat').find('.hol-row')[0]) {
            any = true;
          }

          els.hol_wrap.find('.year-row').hide();
          if (els.hol_wrap.find(`.year-${scope.hol_year}`).show().find('.hol-row')[0]) {
            any = true;
          }

          if (any) {
            els.hol_wrap.show();
          } else {
            els.hol_wrap.hide();
          }

          return null;
        };

        element.find('.holiday-years').on('change', () => updateYearList());

        const updateViewValue = function () {
          let startHour = 9;
          let endHour = 18;
          if (scope.start_hour || (scope.start_hour === 0)) {
            startHour = scope.start_hour;
          }
          if (scope.end_hour || (scope.end_hour === 0)) {
            endHour = scope.end_hour;
          }

          return ngModel.$setViewValue({
            timezone:   scope.timezone || 'UTC',
            start_hour: startHour,
            start_min:  scope.start_min || 0,
            end_hour:   endHour,
            end_min:    scope.end_min || 0,
            holidays:   scope.holidays || [],
            work_days:  scope.work_days || [null, false, true, true, true, true, true, false]
          });
        };

        scope.$watch('timezone',   () => updateViewValue());
        scope.$watch('start_hour', () => updateViewValue());
        scope.$watch('start_min',  () => updateViewValue());
        scope.$watch('end_hour',   () => updateViewValue());
        scope.$watch('end_min',    () => updateViewValue());
        scope.$watch('holidays',   () => updateViewValue());

        ngModel.$render = function () {
          let viewValue;
          element.find('.holiday-year-rows').empty();
          if (ngModel.$viewValue) {
            viewValue = ngModel.$viewValue;
          } else {
            viewValue = {
              start_hour: null,
              end_hour:   null
            };
          }

          let startHour = 9;
          let endHour = 18;
          if (viewValue.start_hour || (viewValue.start_hour === 0)) {
            startHour = viewValue.start_hour;
          }
          if (viewValue.end_hour || (viewValue.end_hour === 0)) {
            endHour = viewValue.end_hour;
          }

          if (viewValue) {
            scope.timezone       = viewValue.timezone || 'UTC';
            scope.start_hour     = startHour;
            scope.start_min      = viewValue.start_min || 0;
            scope.end_hour       = endHour;
            scope.end_min        = viewValue.end_min || 0;
            scope.holidays       = viewValue.holidays || [];

            if (viewValue.work_days) {
              for (let day = 0; day < viewValue.work_days.length; day++) {
                const enabled = viewValue.work_days[day];
                scope.work_days[day] = !!enabled;
              }
            }
          }

          if (scope.holidays.length) {
            for (const hol of Array.from(scope.holidays)) {
              drawHoliday(hol);
            }
          }

          return updateYearList();
        };

        return ngModel.$render();
      }
    })

  ];

  return Admin_Main_Directive_DpWorkingHours;
});
