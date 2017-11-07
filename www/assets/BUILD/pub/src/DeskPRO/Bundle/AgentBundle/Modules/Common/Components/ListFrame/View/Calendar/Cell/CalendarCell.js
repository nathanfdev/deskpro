import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import classNames from 'classnames';

export class CalendarCell extends React.Component {

  static propTypes = {
    dayDate:       PropTypes.object.isRequired,
    date:          PropTypes.object.isRequired,
    dateField:     PropTypes.string.isRequired,
    children:      PropTypes.node,
    onDoubleClick: PropTypes.func,
    draggable:     PropTypes.shape({
      target: PropTypes.node.isRequired
    })
  };

  onDoubleClick = (event) => {
    if (this.props.onDoubleClick) {
      this.props.onDoubleClick(this.props.dayDate, event);
    }
  };

  render() {
    const { dayDate, date, dateField, children, draggable } = this.props;
    const targetProps = draggable.target.props;

    const today = moment();
    const firstDayOfMonth = moment(date).startOf('month');
    const lastDayOfMonth = moment(date).endOf('month');

    const draggableChildren = (
      <div className={classNames('dpwd-calendar-day', { 'dpwd-calendar-day-today': today.isSame(dayDate, 'day') })}>
        <span className="dpwd-calendar-day-mark">{dayDate.date()}</span>
        <div className="dpwd-calendar-tasks">
          {children}
        </div>
      </div>
    );

    return (
      <td className={classNames(
        { 'dpwd-calendar-past-month': dayDate.isBefore(firstDayOfMonth) || dayDate.isAfter(lastDayOfMonth) },
        { 'dpwd-calendar-past-day': dayDate.isBefore(today, 'day') },
        { weekend: [6, 7].indexOf(dayDate.isoWeekday()) !== -1 }
      )} onDoubleClick={this.onDoubleClick}
      >
        {React.cloneElement(draggable.target, {
          ...targetProps,

          updateData: { [dateField]: dayDate.utc().format().replace(/^(.+):(\d{2})$/, '$1$2') },
          children:   draggableChildren
        })}
      </td>
    );
  }
}
