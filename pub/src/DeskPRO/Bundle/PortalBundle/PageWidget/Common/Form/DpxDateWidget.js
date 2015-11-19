import $ from "jquery";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import datetimepicker from "jquery-datetimepicker";
import moment from "moment";

export default class DpxDateWidget extends PageWidget {
  renderWidget() {
    // this widget can work with a DATE form type or a DATETIME
    // it works by following "id" naming conventions from symfony's form component ("choice" widgets for the date)
    // it hides the original widgets and connects them with events to a new text input that uses jquery-datetimepicker
    const id_div = this.$element.find('.fallback-input').find('div:first');
    const id = id_div.attr('id');

    let is_time_included = false;
    let $s_year = $(`#${id}_year`);
    let $s_month = $(`#${id}_month`);
    let $s_day = $(`#${id}_day`);
    let $s_hour = null;
    let $s_minute = null;
    let min_date = (this.$element.data('min-date').length === 0) ? null: moment(this.$element.data('min-date'), "YYYY MM DD");
    let max_date = (this.$element.data('max-date').length === 0) ? null : moment(this.$element.data('max-date'), "YYYY MM DD");
    let now = moment();
    let weekdays = this.$element.data('weekdays');
    if (weekdays) {
      if (typeof weekdays == 'string') {
        weekdays = weekdays.split(',');
      } else {
        weekdays = [weekdays];
      }
    } else {
      weekdays = [0,1,2,3,4,5,6];
    }
    if (this.$element.hasClass('dpx-date-time')) {
      $s_year = $(`#${id}_date_year`);
      $s_month = $(`#${id}_date_month`);
      $s_day = $(`#${id}_date_day`);
      $s_hour = $(`#${id}_time_hour`);
      $s_minute = $(`#${id}_time_minute`);
      is_time_included = true;
    }

    const $textBox = $('<input type="text">');
    const day = $s_day.val();
    const month = $s_month.val();
    const year = $s_year.val();

    // find the initial value and set it on the text box
    let initial_value = false;
    if (day || month || year) {
      if (is_time_included) {
        const minute = $s_minute.val();
        const hour = $s_hour.val();

        if (minute || hour) {
          initial_value = new Date(year, month - 1, day, hour, minute);
          $textBox.val(moment(initial_value).format('MM/DD/YYYY hh:mma'));
        }
      } else {
        initial_value = new Date(year, month - 1, day);
        $textBox.val(moment(initial_value).format('MM/DD/YYYY'));
      }
    }

    let options = {
      timepicker: is_time_included,
      format: is_time_included ? 'm/d/Y h:ia' : 'm/d/Y',
      startDate: initial_value,
      onChangeDateTime: function (dp, $input) {
        let m = moment($input.val(), is_time_included ? 'M/D/YYYY hh:mma' : 'M/D/YYYY');
        $s_month.val(m.month()+1);
        $s_day.val(m.date());
        $s_year.val(m.year());
        if (is_time_included) {
          $s_hour.val(m.hour());
          $s_minute.val(m.minute());
        }
      }
    };

    // days of week
    if (weekdays.length > 0) {
      // disable all days of week
      options['onGenerate']  = function() {
        let that = this;
        let allowed_weekdays = _.map(weekdays, (day) => {
          // php stores 1 as monday and sunday as 7, but our cal uses 0 for sunday, 1 for monday, and so on.
          let d = _.parseInt(day);
          if (d === 6) {
            return 0;
          }
          return d + 1;
        });
        _.forEach([0,1,2,3,4,5,6], function(day) {
          if (!_.includes(allowed_weekdays, day)) {
            $(that).find('.xdsoft_day_of_week'+day).addClass('xdsoft_disabled');
          }
        });
      }
    }

    // min date

    if (min_date && max_date) {
      options['minDate'] = min_date.format('YYYY/MM/DD');
      options['maxDate'] = max_date.format('YYYY/MM/DD');
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
