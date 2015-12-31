import $ from 'jquery';
import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';
import 'jquery-datetimepicker';
import moment from 'moment';

export default class DpxDateWidget extends PageWidget {
  renderWidget() {
    // this widget can work with a DATE form type or a DATETIME
    // it works by following "id" naming conventions from symfony's form component ("choice" widgets for the date)
    // it hides the original widgets and connects them with events to a new text input that uses jquery-datetimepicker
    const idDiv = this.$element.find('.fallback-input').find('div:first');
    const id = idDiv.attr('id');

    let isTimeIncluded = false;
    let $sYear = $(`#${id}_year`);
    let $sMonth = $(`#${id}_month`);
    let $sDay = $(`#${id}_day`);
    let $sHour = null;
    let $sMinute = null;

    const minDate = (this.$element.data('min-date').length === 0) ? null : moment(this.$element.data('min-date'), 'YYYY MM DD');
    const maxDate = (this.$element.data('max-date').length === 0) ? null : moment(this.$element.data('max-date'), 'YYYY MM DD');

    let weekdays = this.$element.data('weekdays');
    if (weekdays) {
      if (typeof weekdays === 'string') {
        weekdays = weekdays.split(',');
      } else {
        weekdays = [weekdays];
      }
    } else {
      weekdays = [0, 1, 2, 3, 4, 5, 6];
    }

    if (this.$element.hasClass('dpx-date-time')) {
      $sYear = $(`#${id}_date_year`);
      $sMonth = $(`#${id}_date_month`);
      $sDay = $(`#${id}_date_day`);
      $sHour = $(`#${id}_time_hour`);
      $sMinute = $(`#${id}_time_minute`);
      isTimeIncluded = true;
    }

    const $textBox = $('<input type="text">');
    const day = $sDay.val();
    const month = $sMonth.val();
    const year = $sYear.val();

    // find the initial value and set it on the text box
    let initialValue = false;
    if (day || month || year) {
      if (isTimeIncluded) {
        const minute = $sMinute.val();
        const hour = $sHour.val();

        if (minute || hour) {
          initialValue = new Date(year, month - 1, day, hour, minute);
          $textBox.val(moment(initialValue).format('MM/DD/YYYY hh:mma'));
        }
      } else {
        initialValue = new Date(year, month - 1, day);
        $textBox.val(moment(initialValue).format('MM/DD/YYYY'));
      }
    }

    const options = {
      parentID: $(this.$element).parent(),
      timepicker: isTimeIncluded,
      format: isTimeIncluded ? 'm/d/Y h:ia' : 'm/d/Y',
      startDate: initialValue,
      closeOnDateSelect: true,
      onChangeDateTime: (dp, $input) => {
        const m = moment($input.val(), isTimeIncluded ? 'M/D/YYYY hh:mma' : 'M/D/YYYY');

        $sMonth.val(m.month() + 1);
        $sDay.val(m.date());
        $sYear.val(m.year());
        if (isTimeIncluded) {
          $sHour.val(m.hour());
          $sMinute.val(m.minute());
        }
      }
    };

    // days of week
    if (weekdays.length > 0) {
      // disable all days of week
      options.onGenerate = function() {
        const that = this;
        const allowedWeekdays = _.map(weekdays, (day) => {
          // php stores 1 as monday and sunday as 7, but our cal uses 0 for sunday, 1 for monday, and so on.
          let d = _.parseInt(day);
          if (d === 6) {
            return 0;
          }
          return d + 1;
        });
        _.forEach([0, 1, 2, 3, 4, 5, 6], function(day) {
          if (!_.includes(allowedWeekdays, day)) {
            $(that).find('.xdsoft_day_of_week' + day).addClass('xdsoft_disabled');
          }
        });
      };
    }

    // min date
    if (minDate && maxDate) {
      options.minDate = minDate.format('YYYY/MM/DD');
      options.maxDate = maxDate.format('YYYY/MM/DD');
    }

    $textBox.datetimepicker(options);

    $textBox.addClass('dpx-date-input');
    $textBox.on('keyup', () => {
      $textBox.datetimepicker('hide');
    }).on('blur', () => {
      $textBox.datetimepicker('validate');
    });

    this.$element.hide();
    $textBox.insertAfter(this.$element);
  }
}
