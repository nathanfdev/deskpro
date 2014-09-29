(function() {
  define(['DeskPRO/Data/TzData'], function(TzData) {
    var Admin_Main_Directive_DpWorkingHours;
    Admin_Main_Directive_DpWorkingHours = [
      function() {
        return {
          restrict: 'E',
          require: 'ngModel',
          replace: true,
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/Common/work-hours-directive.html',
          scope: {},
          link: function(scope, element, attrs, ngModel) {
            var drawHoliday, els, holExists, i, row, updateViewValue, updateYearList, year, year_end, _i;
            scope.timezone = 'UTC';
            scope.start_hour = 9;
            scope.start_min = 0;
            scope.end_hour = 18;
            scope.end_min = 0;
            scope.work_days = [false, true, true, true, true, true, false];
            scope.hol_year = (new Date()).getFullYear();
            scope.hol_year = (new Date()).getFullYear();
            scope.hol_new_month = 1;
            scope.hol_new_day = 1;
            scope.hol_new_name = '';
            scope.holidays = [];
            els = {
              hol_wrap: element.find('.holiday-rows')
            };
            year = (new Date()).getFullYear();
            year_end = year + 4;
            for (i = _i = year; year <= year_end ? _i <= year_end : _i >= year_end; i = year <= year_end ? ++_i : --_i) {
              row = $('<div class="holiday-year-rows year-row year-' + i + '"></div>');
              row.appendTo(els.hol_wrap);
            }
            row = $('<div class="holiday-year-rows year-repeat"></div>');
            row.appendTo(els.hol_wrap);
            holExists = function(hol1, hol2) {
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
            scope.addHoliday = function($event) {
              var checkHol, day, exists, hol, month, repeat, title, _j, _len, _ref;
              $event.preventDefault();
              $event.stopPropagation();
              year = parseInt(scope.hol_year);
              month = parseInt(scope.hol_new_month);
              day = parseInt(scope.hol_new_day);
              title = $.trim(scope.hol_new_name);
              repeat = scope.hol_new_repeat;
              if (repeat) {
                year = 0;
              }
              if (!month || !day) {
                return;
              }
              exists = false;
              hol = {
                year: year,
                month: month,
                day: day,
                name: title
              };
              _ref = scope.holidays;
              for (_j = 0, _len = _ref.length; _j < _len; _j++) {
                checkHol = _ref[_j];
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
                year: year,
                month: month,
                day: day,
                repeat: repeat,
                name: title
              });
              scope.holidays.push(hol);
              scope.hol_new_name = '';
              scope.show_newhold = false;
              return updateYearList();
            };
            drawHoliday = function(hol) {
              var d_str, day, m_str, month, repeat, rowContainer, title, y_str;
              row = $('<div class="hol-row"><div class="remove-btn"><i class="fa fa-times-circle"></i></div> <span class="date-txt"></span> <span class="title-txt"></span></div></div>');
              year = hol.year;
              month = hol.month;
              day = hol.day;
              title = hol.name;
              repeat = year === 0;
              if (repeat) {
                y_str = 'Every Year';
                rowContainer = els.hol_wrap.find('.year-repeat');
              } else {
                y_str = year;
                rowContainer = els.hol_wrap.find('.year-' + year);
              }
              m_str = month < 10 ? "0" + month : month;
              d_str = day < 10 ? "0" + day : day;
              row.find('.date-txt').text("" + y_str + "-" + m_str + "-" + d_str);
              if (title.length) {
                row.find('.title-txt').text(title);
              }
              row.data('hol-rec', hol);
              return rowContainer.append(row);
            };
            element.find('.add-btn').on('click', function(ev) {
              return scope.$apply(function() {
                return scope.addHoliday(ev);
              });
            });
            element.on('click', '.remove-btn', function(ev) {
              var hol, holRec, idx, _j, _len, _ref, _results;
              ev.preventDefault();
              row = $(this).closest('.hol-row');
              holRec = row.data('hol-rec');
              row.remove();
              if (!scope.holidays.length) {
                return;
              }
              _ref = scope.holidays;
              _results = [];
              for (idx = _j = 0, _len = _ref.length; _j < _len; idx = ++_j) {
                hol = _ref[idx];
                if (hol === holRec) {
                  scope.holidays.splice(idx, 1);
                  break;
                } else {
                  _results.push(void 0);
                }
              }
              return _results;
            });
            updateYearList = function() {
              var any;
              any = false;
              if (els.hol_wrap.find('.year-repeat').find('.hol-row')[0]) {
                any = true;
              }
              els.hol_wrap.find('.year-row').hide();
              if (els.hol_wrap.find('.year-' + scope.hol_year).show().find('.hol-row')[0]) {
                any = true;
              }
              if (any) {
                els.hol_wrap.show();
              } else {
                els.hol_wrap.hide();
              }
              return null;
            };
            element.find('.holiday-years').on('change', function() {
              return updateYearList();
            });
            updateViewValue = function() {
              return ngModel.$setViewValue({
                timezone: scope.timezone || 'UTC',
                start_hour: scope.start_hour || 9,
                start_min: scope.start_min || 0,
                end_hour: scope.end_hour || 18,
                end_min: scope.end_min || 0,
                holidays: scope.holidays || [],
                work_days: scope.work_days || [false, true, true, true, true, true, false]
              });
            };
            scope.$watch('timezone', function() {
              return updateViewValue();
            });
            scope.$watch('start_hour', function() {
              return updateViewValue();
            });
            scope.$watch('start_min', function() {
              return updateViewValue();
            });
            scope.$watch('end_hour', function() {
              return updateViewValue();
            });
            scope.$watch('end_min', function() {
              return updateViewValue();
            });
            scope.$watch('holidays', function() {
              return updateViewValue();
            });
            ngModel.$render = function() {
              var day, enabled, hol, viewValue, _j, _k, _len, _len1, _ref, _ref1;
              element.find('.holiday-year-rows').empty();
              viewValue = ngModel.$viewValue;
              if (viewValue) {
                scope.timezone = viewValue.timezone || 'UTC';
                scope.start_hour = viewValue.start_hour || 9;
                scope.start_min = viewValue.start_min || 0;
                scope.end_hour = viewValue.end_hour || 18;
                scope.end_min = viewValue.end_min || 0;
                scope.holidays = viewValue.holidays || [];
                if (viewValue.work_days) {
                  _ref = viewValue.work_days;
                  for (day = _j = 0, _len = _ref.length; _j < _len; day = ++_j) {
                    enabled = _ref[day];
                    scope.work_days[day] = !!enabled;
                  }
                }
              }
              if (scope.holidays.length) {
                _ref1 = scope.holidays;
                for (_k = 0, _len1 = _ref1.length; _k < _len1; _k++) {
                  hol = _ref1[_k];
                  drawHoliday(hol);
                }
              }
              return updateYearList();
            };
            return ngModel.$render();
          }
        };
      }
    ];
    return Admin_Main_Directive_DpWorkingHours;
  });

}).call(this);

//# sourceMappingURL=DpWorkingHours.js.map
