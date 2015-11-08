import React, { PropTypes } from 'react';
import moment from 'moment';
import classNames from 'classnames';

export class CalendarCell extends React.Component {

  static propTypes = {
    dayDate: PropTypes.object.isRequired,
    date: PropTypes.object.isRequired,
    tasks: PropTypes.object.isRequired
  };

  render() {
    const { dayDate, date } = this.props;

    const today = moment();
    const firstDayOfMonth = moment(date).startOf('month');
    const lastDayOfMonth = moment(date).endOf('month');

    return (
      <td className={classNames(
        {'dpwd-calendar-past-month': dayDate.isBefore(firstDayOfMonth) || dayDate.isAfter(lastDayOfMonth)},
        {'dpwd-calendar-past-day': dayDate.isBefore(today)},
        {'weekend': [6, 7].indexOf(dayDate.isoWeekday()) !== -1}
      )}>
        <div className={classNames('dpwd-calendar-day', {'dpwd-calendar-day-today': today.isSame(dayDate, 'day')})}>
          <span className="dpwd-calendar-day-mark">{dayDate.date()}</span>
          <div className="dpwd-calendar-tasks">
          </div>
        </div>
      </td>
    );
  }
}
