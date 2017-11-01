import PropTypes from 'prop-types';
import React from 'react';
import { Field } from 'react-forms';
import { Input } from 'DeskPRO/Component/Semantic/ReactForm';
import 'jquery-datetimepicker-iframe/jquery.datetimepicker';
import $ from 'jquery';
import moment from 'moment';
import 'DeskPRO/Bundle/AppBundle/moment-locales';
import { AbstractCustomField } from './AbstractCustomField';

export class CustomFieldDateTime extends AbstractCustomField {

  static propTypes = {
    timePicker: PropTypes.bool
  };

  render() {
    const { name, timePicker = false, config, widgetOptions } = this.props;

    return (
      <Field select={name}>
        <DateTimeWidget
          key={config.get('id')}
          timePicker={timePicker}
          options={config.get('options')}
          widgetOptions={widgetOptions}
        />
      </Field>
    );
  }
}

class DateTimeWidget extends React.Component {

  static propTypes = {
    options:       PropTypes.object,
    timePicker:    PropTypes.bool,
    value:         PropTypes.string,
    onChange:      PropTypes.func,
    widgetOptions: PropTypes.object
  };

  componentDidMount() {
    const { timePicker, value, onChange, widgetOptions } = this.props;
    const $wrapper = $(this.wrapper);
    const $input = $wrapper.find('input');

    $.datetimepicker.setDateFormatter({
      parseDate: (date, format) => {
        const d = moment(date, format);
        return d.isValid() ? d.toDate() : false;
      },
      formatDate: (date, format) => moment(date).format(format)
    });

    const format  = timePicker ? 'L LT' : 'L';
    const options = {
      ...this.getRangeOptions(),
      ...this.getWeekdaysOptions(),

      ownerDocument: widgetOptions.ownerDocument || window,
      contentWindow: widgetOptions.contentWindow || document,
      parentID:      $wrapper,
      timepicker:    timePicker,

      format,
      value:      value ? moment(value).format(format) : null,
      formatTime: 'LT',
      formatDate: 'L',

      closeOnDateSelect: true,
      scrollInput:       false,

      onChangeDateTime: () => {
        const newValue = moment($input.datetimepicker('getValue')).format(format);
        onChange(newValue);
      }
    };

    $input.datetimepicker(options);
  }

  getRangeOptions() {
    const { options, timePicker } = this.props;
    const format  = timePicker ? 'L LT' : 'L';
    const rangeType = options.get('date_valid_type');

    let minRangeFormat;
    let maxRangeFormat;
    if (rangeType === 'range') {
      const minRange = options.get('date_valid_range1');
      const maxRange = options.get('date_valid_range2');

      minRangeFormat = minRange ? moment().subtract(minRange, 'days') : null;
      maxRangeFormat = maxRange ? moment().add(maxRange, 'days') : null;
    } else if (rangeType === 'date') {
      minRangeFormat = options.get('date_valid_date1');
      maxRangeFormat = options.get('date_valid_date2');
    }

    const rangeOptions = {};
    if (minRangeFormat) {
      rangeOptions.minDate = moment(minRangeFormat).format(format);
    }
    if (maxRangeFormat) {
      rangeOptions.maxDate = moment(maxRangeFormat).format(format);
    }

    return rangeOptions;
  }

  getWeekdaysOptions() {
    const { options } = this.props;
    const weekdaysOptions = {};
    const $wrapper = $(this.wrapper);

    let allowedWeekdays = [0, 1, 2, 3, 4, 5, 6];
    if (options.get('date_valid_dow')) {
      allowedWeekdays = options.get('date_valid_dow').toArray();
    }

    allowedWeekdays = allowedWeekdays.map((weekDay) => {
      // php stores 1 as monday and sunday as 7, but our cal uses 0 for sunday, 1 for monday, and so on.
      const d = parseInt(weekDay, 10);
      return d === 6 ? 0 : d + 1;
    });

    weekdaysOptions.onGenerate = () => {
      [0, 1, 2, 3, 4, 5, 6].forEach((weekDay) => {
        if (allowedWeekdays.indexOf(weekDay) === -1) {
          $wrapper.find(`.xdsoft_day_of_week${weekDay}`).addClass('xdsoft_disabled');
        }
      });
    };

    return weekdaysOptions;
  }

  render() {
    return (
      <div ref={(c) => { this.wrapper = c; }} style={{ position: 'relative' }}>
        <Input type="text" />
      </div>
    );
  }
}
