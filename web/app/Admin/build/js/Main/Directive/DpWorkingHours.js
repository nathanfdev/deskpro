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
            var els, holExists, i, row, updateYearList, year, year_end, _i;
            scope.timezone = 'UTC';
            scope.start_hour = 9;
            scope.start_min = 0;
            scope.end_hour = 18;
            scope.end_min = 0;
            scope.hol_year = (new Date()).getFullYear();
            scope.hol_year = (new Date()).getFullYear();
            scope.hol_new_month = 1;
            scope.hol_new_day = 1;
            scope.hol_new_name = '';
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
              var checkHol, d_str, day, exists, hol, m_str, month, repeat, rowContainer, title, y_str, _j, _len, _ref;
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
              if (!ngModel.$modelValue) {
                ngModel.$modelValue = {};
              }
              if (ngModel.$modelValue.holidays == null) {
                ngModel.$modelValue.holidays = [];
              }
              exists = false;
              hol = {
                year: year,
                month: month,
                day: day,
                title: title
              };
              _ref = ngModel.$modelValue.holidays;
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
              ngModel.$modelValue.holidays.push(hol);
              if (repeat) {
                y_str = 'Every Year';
                rowContainer = els.hol_wrap.find('.year-repeat');
              } else {
                y_str = year;
                rowContainer = els.hol_wrap.find('.year-' + year);
              }
              row = $('<div class="hol-row"><div class="remove-btn"><i class="fa fa-remove-sign"></i></div> <span class="date-txt"></span> <span class="title-txt"></span></div></div>');
              m_str = month < 10 ? "0" + month : month;
              d_str = day < 10 ? "0" + day : day;
              row.find('.date-txt').text("" + y_str + "-" + m_str + "-" + d_str);
              if (title.length) {
                row.find('.title-txt').text(title);
              }
              row.data('holRec', hol);
              scope.hol_new_name = '';
              scope.show_newhold = false;
              rowContainer.append(row);
              return updateYearList();
            };
            element.find('.add-btn').on('click', function(ev) {
              return scope.$apply(function() {
                return scope.addHoliday(ev);
              });
            });
            element.on('click', '.remove-btn', function(ev) {
              var hol, holRec, idx, _j, _len, _ref, _ref1, _results;
              ev.preventDefault();
              holRec = $(this).data('holRec');
              $(this).closest('.hol-row').remove();
              if (((_ref = ngModel.$modelValue) != null ? _ref.holidays : void 0) == null) {
                return;
              }
              _ref1 = ngModel.$modelValue.holidays;
              _results = [];
              for (idx = _j = 0, _len = _ref1.length; _j < _len; idx = ++_j) {
                hol = _ref1[idx];
                if (hol === holRec) {
                  ngModel.$modelValue.holidays.slice(idx, 1);
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
            return ngModel.$render = function() {
              return updateYearList();
            };
          }
        };
      }
    ];
    return Admin_Main_Directive_DpWorkingHours;
  });

}).call(this);

/*
//@ sourceMappingURL=DpWorkingHours.js.map
*/