import _ from 'lodash';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import moment from 'moment';
import 'DeskPRO/Bundle/AppBundle/moment-locales';
// import 'jquery-datetimepicker/jquery.datetimepicker';
import 'jquery-datetimepicker/build/jquery.datetimepicker.full';

export class DpxDateWidget extends PageWidget {

  renderWidget() {
    if (window.DESKPRO_LANG) {
      // datetime picker has locale for month/day names
      $.datetimepicker.setLocale(window.DESKPRO_LANG.toLowerCase().split('_')[0]);

      // we have to use moment for formatting tho
      moment.locale(window.DESKPRO_LANG.toLowerCase().replace('_', '-'));
    }

    // Change 'jquery.datetimepicker.full' to 'jquery.datetimepicker' when this will be fixed
    // https://github.com/xdan/datetimepicker/issues/392
    // $.datetimepicker.setDateFormatter({
    //   parseDate: (date, format) => {
    //     const d = moment(date, format);
    //     return d.isValid() ? d.toDate() : false;
    //   },
    //   formatDate: (date, format) => moment(date).format(format)
    // });

    // this widget can work with a DATE form type or a DATETIME
    // it works by following "id" naming conventions from symfony's form component ("choice" widgets for the date)
    // it hides the original widgets and connects them with events to a new text input that uses jquery-datetimepicker
    const $el = this.$element;
    const idDiv = $el.find('.fallback-input').find('div:first');
    const id = idDiv.attr('id');

    let $sYear;
    let $sMonth;
    let $sDay;
    let $sHour;
    let $sMinute;
    let isTimeIncluded;

    if ($el.hasClass('dpx-date-time')) {
      $sYear = $('#' + `${id}_date_year`);
      $sMonth = $('#' + `${id}_date_month`);
      $sDay = $('#' + `${id}_date_day`);
      $sHour = $('#' + `${id}_time_hour`);
      $sMinute = $('#' + `${id}_time_minute`);

      isTimeIncluded = true;
    } else {
      $sYear = $('#' + `${id}_year`);
      $sMonth = $('#' + `${id}_month`);
      $sDay = $('#' + `${id}_day`);

      isTimeIncluded = false;
    }

    const minDate = ($el.data('min-date').length === 0) ? null : moment($el.data('min-date'), 'YYYY MM DD');
    const maxDate = ($el.data('max-date').length === 0) ? null : moment($el.data('max-date'), 'YYYY MM DD');

    const $textBox = $('<input type="text">');
    const options = {
      parentID:   $el.parent(),
      timepicker: isTimeIncluded,

      // change format when https://github.com/xdan/datetimepicker/issues/392 will be fixed
      // format:            isTimeIncluded ? 'L LT' : 'L',
      format: isTimeIncluded ? 'm/d/Y h:ia' : 'm/d/Y',

      closeOnDateSelect: true,
      scrollInput:       false,
      onChangeDateTime:  () => {
        const m = moment($textBox.datetimepicker('getValue'));

        // reset items to proper set new value
        [$sMonth, $sDay, $sYear, $sHour, $sMinute].forEach($item => $item && $item.val(''));

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

    if (weekdays.length > 0) {
      // disable all days of week
      options.onGenerate = function () {
        const that = this;
        const allowedWeekdays = _.map(weekdays, weekDay => {
          // php stores 1 as monday and sunday as 7, but our cal uses 0 for sunday, 1 for monday, and so on.
          const d = _.parseInt(weekDay);
          return d === 6 ? 0 : d + 1;
        });

        _.forEach([0, 1, 2, 3, 4, 5, 6], weekDay => {
          if (!_.includes(allowedWeekdays, weekDay)) {
            $(that).find(`.xdsoft_day_of_week${weekDay}`).addClass('xdsoft_disabled');
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

    const onSetDate = () => {
      const day   = $sDay.val();
      const month = $sMonth.val();
      const year  = $sYear.val();

      const onUpdateValue = (newDate, format) => {
        const oldValue = moment($textBox.datetimepicker('getValue'));
        const newValue = moment(newDate);

        if (oldValue.format() !== newValue.format()) {
          $textBox.val(newValue.format(format));
        }
      };

      if (day && month && year) {
        if (isTimeIncluded) {
          const minute = $sMinute.val();
          const hour   = $sHour.val();

          if (minute.length && hour.length) {
            onUpdateValue(new Date(year, month - 1, day, hour, minute), 'MM/DD/YYYY hh:mma');
          }
        } else {
          onUpdateValue(new Date(year, month - 1, day), 'MM/DD/YYYY');
        }
      }
    };

    // find the initial value and set it on the text box
    onSetDate();

    $sDay.on('change', onSetDate);
    $sMonth.on('change', onSetDate);
    $sYear.on('change', onSetDate);

    if (isTimeIncluded) {
      $sHour.on('change', onSetDate);
      $sMinute.on('change', onSetDate);
    }

    $textBox
      .addClass('dpx-date-input')
      .on('keyup', () => $textBox.datetimepicker('hide'))
      .on('blur', () => $textBox.datetimepicker('validate'));

    $el.hide();
    $textBox.insertAfter($el);
  }
}
