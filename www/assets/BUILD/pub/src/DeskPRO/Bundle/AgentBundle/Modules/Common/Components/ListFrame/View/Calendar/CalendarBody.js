import PropTypes from 'prop-types';
import React from 'react';
import { CalendarCell } from './Cell/CalendarCell';
import { CalendarCellContent } from './Cell/CalendarCellContent';
import moment from 'moment';
import DateRange from 'moment-range';

export class CalendarBody extends React.Component {

  static propTypes = {
    date: PropTypes.object.isRequired
  };

  getCalendarMap() {
    const { date } = this.props;
    const firstDayOfMonth = moment(date).startOf('month');
    const lastDayOfMonth = moment(date).endOf('month');

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
    const { date } = this.props;

    return (
      <tbody>
        {this.getCalendarMap().map((week, index) =>
          <tr key={index}>
            {week.map(day =>
              <CalendarCell key={day.day()}
                dayDate={day}
                date={date} {...this.props}
              >

                <CalendarCellContent dayDate={day} {...this.props} />
              </CalendarCell>
            )}
          </tr>
        )}
      </tbody>
    );
  }
}
