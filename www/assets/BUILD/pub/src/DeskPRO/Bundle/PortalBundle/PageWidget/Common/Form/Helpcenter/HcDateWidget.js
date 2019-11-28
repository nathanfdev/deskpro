import React from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import 'DeskPRO/Bundle/AppBundle/moment-locales';
import ReactDatePicker from '@deskpro/react-datepicker-hijri';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import moment from 'moment';
import $ from '../../../../../../../../../web/bower_components/oclazyload/examples/requireJSExample/js/jquery';

class HcDateInput extends React.Component {
  static propTypes = {
    dateFormat: PropTypes.string,
    startDate:  PropTypes.object,
    onChange:   PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      date: props.startDate
    };
  }

  setDate = (date) => {
    this.props.onChange(date);
    this.setState({
      date
    });
  };

  handleChangeRaw = (value) => {
    const date = moment(value, this.props.dateFormat);
    this.props.onChange(date);
    this.setState({
      date
    });
  };

  render() {
    const { startDate, onChange, ...props } = this.props;
    const { date } = this.state;

    return (
      <ReactDatePicker
        onChange={newDate => this.setDate(newDate)}
        onChangeRaw={event => this.handleChangeRaw(event.target.value)}
        selected={date}
        {...props}
      />
    );
  }
}

export class HcDateWidget extends PageWidget {
  renderWidget() {
    const $el = this.$element;
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget dp-pc_field form-control"></div>').insertAfter(this.$element);

    const calendar = $el.data('calendar');

    if (window.DESKPRO_LOCALE) {
      moment.locale(window.DESKPRO_LOCALE.toLowerCase().replace('_', '-'));
    } else {
      moment.locale('en');
    }

    let $sYear;
    let $sMonth;
    let $sDay;
    let $sHour;
    let $sMinute;
    let showTimeSelect;

    const idDiv = $el.find('.fallback-input').find('div:first');
    const id = idDiv.attr('id');

    if ($el.hasClass('dpx-date-time')) {
      $sYear = $(`#${id}_date_year`);
      $sMonth = $(`#${id}_date_month`);
      $sDay = $(`#${id}_date_day`);
      $sHour = $(`#${id}_time_hour`);
      $sMinute = $(`#${id}_time_minute`);

      showTimeSelect = true;
    } else {
      $sYear = $(`#${id}_year`);
      $sMonth = $(`#${id}_month`);
      $sDay = $(`#${id}_day`);

      showTimeSelect = false;
    }

    const format  = showTimeSelect ? 'L LT' : 'L';

    let filterDate;
    let weekdays = $el.data('weekdays');
    if (weekdays) {
      if (typeof weekdays === 'string') {
        weekdays = weekdays.split(',');
      } else {
        weekdays = [weekdays];
      }
      filterDate = (date) => {
        const day = date.day();
        return weekdays.indexOf((day - 1).toString()) !== -1;
      };
    }

    const minDate = ($el.data('min-date').length === 0) ? null : moment($el.data('min-date'));
    const maxDate = ($el.data('max-date').length === 0) ? null : moment($el.data('max-date'));

    const getDate = () => {
      const day   = $sDay.val();
      const month = $sMonth.val();
      const year  = $sYear.val();

      if (day && month && year) {
        if (showTimeSelect) {
          const minute = $sMinute.val();
          const hour   = $sHour.val();

          if (minute.length && hour.length) {
            return moment().year(year).month(month - 1).date(day)
              .hours(hour)
              .minutes(minute);
          }
        } else {
          return moment().year(year).month(month - 1).date(day);
        }
      }
      return null;
    };

    const onUpdateValue = (m) => {
      // reset items to proper set new value
      [$sMonth, $sDay, $sYear, $sHour, $sMinute].forEach($item => $item && $item.val(''));

      $sMonth.val(m.month() + 1).trigger('change');
      $sDay.val(m.date()).trigger('change');
      $sYear.val(m.year()).trigger('change');

      if (showTimeSelect) {
        $sHour.val(m.hour()).trigger('change');
        $sMinute.val(m.minute()).trigger('change');
      }
    };

    const startDate = getDate();

    const component = React.createElement(
      HcDateInput,
      {
        calendar,
        minDate,
        maxDate,
        filterDate,
        showTimeSelect,
        startDate,
        dateFormat:               format,
        timeFormat:               'HH:mm',
        nextMonthButtonLabel:     portalPhrases.get('helpcenter.forms.date-picker-next-month'),
        previousMonthButtonLabel: portalPhrases.get('helpcenter.forms.date-picker-previous-month'),
        timeCaption:              portalPhrases.get('helpcenter.forms.date-picker-time'),
        onChange:                 date => onUpdateValue(date),
      }
    );

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
