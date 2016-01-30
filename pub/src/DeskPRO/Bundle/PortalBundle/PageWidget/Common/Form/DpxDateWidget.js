import _ from 'lodash';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import 'jquery-datetimepicker';
import moment from 'moment';

export class DpxDateWidget extends PageWidget {

  renderWidget() {
    // this widget can work with a DATE form type or a DATETIME
    // it works by following "id" naming conventions from symfony's form component ("choice" widgets for the date)
    // it hides the original widgets and connects them with events to a new text input that uses jquery-datetimepicker
    const $el = this.$element;
    const idDiv = $el.find('.fallback-input').find('div:first');
    const id = idDiv.attr('id');

    let isTimeIncluded = false;
    let $sYear = $('#' + `${id}_year`);
    let $sMonth = $('#' + `${id}_month`);
    let $sDay = $('#' + `${id}_day`);
    let $sHour = null;
    let $sMinute = null;

    const minDate = ($el.data('min-date').length === 0) ? null : moment($el.data('min-date'), 'YYYY MM DD');
    const maxDate = ($el.data('max-date').length === 0) ? null : moment($el.data('max-date'), 'YYYY MM DD');

    let weekdays = $el.data('weekdays');
    if (weekdays) {
      if (typeof weekdays === 'string') {
        weekdays = weekdays.split(',');
      } else {
        weekdays = [weekdays];
      }
    } else {
      weekdays = [0, 1, 2, 3, 4, 5, 6];
    }

    if ($el.hasClass('dpx-date-time')) {
      $sYear = $('#' + `${id}_date_year`);
      $sMonth = $('#' + `${id}_date_month`);
      $sDay = $('#' + `${id}_date_day`);
      $sHour = $('#' + `${id}_time_hour`);
      $sMinute = $('#' + `${id}_time_minute`);
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
      parentID: $el.parent(),
      timepicker: isTimeIncluded,
      format: isTimeIncluded ? 'm/d/Y h:ia' : 'm/d/Y',
      startDate: initialValue,
      closeOnDateSelect: true,
      scrollInput: false,
      onChangeDateTime: (dp, $input) => {
        const m = moment($input.val(), isTimeIncluded ? 'M/D/YYYY hh:mma' : 'M/D/YYYY');

        $sMonth.val(m.month() + 1).trigger('change');
        $sDay.val(m.date()).trigger('change');
        $sYear.val(m.year()).trigger('change');

        if (isTimeIncluded) {
          $sHour.val(m.hour()).trigger('change');
          $sMinute.val(m.minute()).trigger('change');
        }
      }
    };

    // days of week
    if (weekdays.length > 0) {
      // disable all days of week
      options.onGenerate = function() {
        const that = this;
        const allowedWeekdays = _.map(weekdays, weekDay => {
          // php stores 1 as monday and sunday as 7, but our cal uses 0 for sunday, 1 for monday, and so on.
          const d = _.parseInt(weekDay);
          return d === 6 ? 0 : d + 1;
        });

        _.forEach([0, 1, 2, 3, 4, 5, 6], weekDay => {
          if (!_.includes(allowedWeekdays, weekDay)) {
            $(that).find('.xdsoft_day_of_week' + weekDay).addClass('xdsoft_disabled');
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

    $el.hide();
    $textBox.insertAfter($el);
  }
}
