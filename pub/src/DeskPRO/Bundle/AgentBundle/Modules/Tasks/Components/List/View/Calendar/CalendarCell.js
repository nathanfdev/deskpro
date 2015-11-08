import React, { PropTypes } from 'react';

export class CalendarCell extends React.Component {

  static propTypes = {
    dayDate: PropTypes.object.isRequired,
    date: PropTypes.object.isRequired
  };

  render() {
    const { dayDate } = this.props;

    return (
      <td className="dpwd-calendar-past-month">
        <div className="dpwd-calendar-day">
          <span className="dpwd-calendar-day-mark">{dayDate.date()}</span>
          <div className="dpwd-calendar-tasks">
          </div>
        </div>
      </td>
    );
  }
}
