import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import moment from 'moment';
import DateRange from 'moment-range';

export class CalendarHeader extends React.Component {

  static propTypes = {
    date: PropTypes.object.isRequired
  };

  render() {
    const today = moment();
    const { date } = this.props;
    const range = moment.range(moment(date).startOf('isoweek'), moment(date).endOf('isoweek'));

    const items = [];
    range.by('days', weekday => items.push(weekday));

    return (
      <thead>
        <tr>
          {items.map(weekday =>
            <td key={weekday.isoWeekday()}
              className={classNames({
                'dpwd-calendar-header-today': weekday.isSame(today, 'day')
              })}
            >

              {weekday.format('dddd')}
            </td>
          )}
        </tr>
      </thead>
    );
  }
}
