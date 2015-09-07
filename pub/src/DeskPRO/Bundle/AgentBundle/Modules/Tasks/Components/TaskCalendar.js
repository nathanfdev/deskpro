import React from "react";
import Moment from "moment";
import TaskCalendarCell from "../Components/TaskCalendarCell";
import Calendar from "../../../Services/Calendar";

export default class TaskCalendar extends React.Component {

  displayTask(due) {
    let moment = new Moment(this.props.moment),
        nextMonth = new Moment(this.props.moment),
        lastMonth = new Moment(this.props.moment);

    nextMonth.add(1, 'months');
    lastMonth.subtract(1, 'months');

    let validMonths = [moment.month(), nextMonth.month(), lastMonth.month()];
    let validYears = [moment.year(), nextMonth.year(), lastMonth.year()];

    return (validMonths.indexOf(due.month()) !== -1 &&
            validYears.indexOf(due.year()) !== -1);
  }

  render() {
    let calendar = new Calendar(),
        moment = this.props.moment,
        layout = calendar.getCalendar(moment.year(), moment.month());

    let i = 0,
        j = layout.length,
        rows = [],
        chunk = 7,
        processedThisMonth = false,
        calendarDays = [];

    let lastMonth = new Moment(moment);
    lastMonth.subtract(1, 'month');
    let nextMonth = new Moment(moment);
    nextMonth.add(1, 'month').startOf('month');

    // Figure out what day of the week the first of the month is
    const firstWeekday = this.props.moment.startOf('month').day();

    // Do some crazy maths to get the moment object for the first in our block
    lastMonth.endOf('month').subtract(firstWeekday, 'days').add(1, 'day');

    layout.forEach((day) => {
      let newDay = day;
      if (processedThisMonth === false && day === false) {
        newDay = {
          day: lastMonth.date(),
          weekDay: lastMonth.day(),
          month: lastMonth.month(),
          year: lastMonth.year()
        };

        lastMonth.add(1, 'day');
      } else if (day === false) {
        newDay = {
          day: nextMonth.date(),
          weekDay: nextMonth.day(),
          month: nextMonth.month(),
          year: nextMonth.year()
        };

        nextMonth.add(1, 'day');
      } else {
        processedThisMonth = true;
      }

      calendarDays.push(newDay);
    });

    for (i=0; i < j; i += chunk) {
      rows.push(calendarDays.slice(i, i + chunk));
    }

    const weekdays = [
      'Sunday',
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday'
    ];

    let tasks = {};
    let counts = {};

    if (this.props.tasks) {
      this.props.tasks.forEach((task) => {
        if (task.date_due) {
          let due = new Moment(task.date_due);

          if (this.displayTask(due)) {
            const dateString = due.date() + '-' + due.month();

            if (typeof tasks[dateString] === 'undefined') {
              tasks[dateString] = [];
            }

            if (typeof counts[dateString] === 'undefined') {
              counts[dateString] = 0;
            }

            if (task.is_done) {
              counts[dateString]++;
            } else {
              tasks[dateString].push(task);
            }
          }
        }
      });
    }

    const _this = this;

    return <div>
      <h1>{this.props.moment.format("MMMM YYYY")}</h1>
      <table className="task-calendar" cellSpacing="0">
        <thead>
          {weekdays.map((weekday) => {
            return <th key={weekday}>{weekday}</th>
          })}
        </thead>
        <tbody>
          {rows ? rows.map((row, key) => {
            let weekday = 0;
            return <tr key={key}>
              {row.map((day, key) => {
                let additional = 0,
                    dateString = day.day + '-' + day.month,
                    i = 0;
                weekday++;
                return <TaskCalendarCell tasks={tasks[dateString]}
                  counts={counts[dateString]} day={day} key={key} weekday={weekday}
                  dispatch={_this.props.dispatch.bind(_this)}
                  moment={this.props.moment} />
              })}
            </tr>
          }) : ''}
        </tbody>
      </table>
      <ul className="task-calendar-navigation">
        <li><a href="#" onClick={this.props.prevMonth.bind(this)}><i className="fa fa-angle-double-left" /> Previous Month</a></li>
        <li><a href="#" onClick={this.props.nextMonth.bind(this)}>Next Month <i className="fa fa-angle-double-right" /></a></li>
      </ul>
    </div>
  }
}
