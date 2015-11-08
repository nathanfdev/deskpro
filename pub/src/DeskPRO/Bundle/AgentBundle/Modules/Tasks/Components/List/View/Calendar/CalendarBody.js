import React, { PropTypes } from 'react';
import { CalendarCell } from './CalendarCell';
import moment from 'moment';
import DateRange from 'moment-range';

export class CalendarBody extends React.Component {

  static propTypes = {
    date: PropTypes.object.isRequired
  };

  getCalendarMap() {
    const today = moment(this.props.date);

    const firstDayOfMonth = moment(today).startOf('month');
    const lastDayOfMonth = moment(today).endOf('month');

    const start = moment(firstDayOfMonth).subtract(firstDayOfMonth.isoWeekday() - 1, 'day');
    const end = moment(lastDayOfMonth).add(7 - lastDayOfMonth.isoWeekday(), 'day');

    let lastWeekDate;
    const map = [];
    moment.range(start, moment(end).add(1)).by('weeks', weekDate => {
      if (lastWeekDate) {
        const weekDays = [];
        moment.range(lastWeekDate.add(1), weekDate).by('days', dayDate => weekDays.push(dayDate));
        map.push(weekDays);
      }

      lastWeekDate = weekDate;
    });

    return map;
  }

  render() {
    return (
      <tbody>
        {this.getCalendarMap().map((week, index) =>
          <tr key={index}>
            {week.map(day => <CalendarCell dayDate={day} />)}
          </tr>
        )}
      </tbody>
    );
  }
}
