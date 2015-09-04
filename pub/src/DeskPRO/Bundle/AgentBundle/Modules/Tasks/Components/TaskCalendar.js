import React from "react";
import Moment from "moment";
import Calendar from "../../../Services/Calendar";

export default class TaskCalendar extends React.Component {

  render() {
    let calendar = new Calendar();
    let month = calendar.getCalendar(this.props.moment.year(), this.props.moment.month());

    let i = 0,
        j = month.length,
        rows = [],
        chunk = 7;

    for (i=0; i < j; i += chunk) {
      rows.push(month.slice(i, i + chunk));
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

          if (due.month() === this.props.moment.month()
            && due.year() === this.props.moment.year()) {
            const dateString = due.format('D');

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
                let additional = 0;
                let i = 0;
                let cellClass = (weekday === 0 || weekday === 6) ? 'weekend' : '';
                weekday++;
                return <td key={key} className={cellClass}>
                  { day ? <span>
                    <span className="date">{day.day}</span>
                    <ul>
                    {tasks[day.day] ? tasks[day.day].map((task) => {
                      i++;

                      if (i < 4) {
                        return <li key={task.id}><a href="#">{task.title}</a></li>
                      } else {
                        additional++;
                      }
                    }) : '' }
                    </ul>
                    { additional > 0 ? <span className="additional day-info">+ {additional} more</span> : '' }
                    { counts[day.day] && counts[day.day] > 0 ? <span className="completed day-info">{counts[day.day]} already complete</span> : '' }
                  </span>
                  : '' }
                </td>
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
