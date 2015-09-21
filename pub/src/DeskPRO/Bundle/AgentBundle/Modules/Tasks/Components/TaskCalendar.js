import React from 'react';
import Moment from 'moment';
import TaskCalendarCell from '../Components/TaskCalendarCell';
import TaskCalendarList from '../Components/TaskCalendarList';
import TaskCardGeneric from '../Components/TaskCardGeneric';
import Calendar from '../../../Services/Calendar';
import ComponentRootWrapper from 'DeskPRO/Component/ComponentRootWrapper';
import $ from 'jquery';

export default class TaskCalendar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      tasks: [],
      task: {},
      dayDate: null,
      showHover: false,
      showWindow: false,
      position: {
        x: 0,
        y: 0
      },
      hoverPosition: {
        x: 0,
        y: 0
      }
    };
  }

  displayTask(due) {
    const moment = new Moment(this.props.moment);
    const nextMonth = new Moment(this.props.moment);
    const lastMonth = new Moment(this.props.moment);

    nextMonth.add(1, 'months');
    lastMonth.subtract(1, 'months');

    const validMonths = [moment.month(), nextMonth.month(), lastMonth.month()];
    const validYears = [moment.year(), nextMonth.year(), lastMonth.year()];

    return (validMonths.indexOf(due.month()) !== -1 &&
            validYears.indexOf(due.year()) !== -1);
  }

  openCalendarList(tasks, date, element) {
    const target = $(element);

    this.setState({
      tasks: tasks,
      dayDate: date,
      showWindow: true,
      position: {
        x: target[0].getBoundingClientRect().left,
        y: target[0].getBoundingClientRect().bottom + 10
      }
    });
  }

  openHover(task, position) {

    this.setState({
      task: task,
      showHover: true,
      hoverPosition: {
        x: position.x,
        y: position.y
      }
    });
  }

  closeWindow() {
    this.setState({
      showWindow: false
    });
  }

  closeHover() {
    this.setState({
      showHover: false
    });
  }

  render() {
    const calendar = new Calendar();
    const moment = this.props.moment;
    const layout = calendar.getCalendar(moment.year(), moment.month());

    let i = 0;
    const j = layout.length;
    const rows = [];
    const chunk = 7;
    let processedThisMonth = false;
    const calendarDays = [];

    const lastMonth = new Moment(moment);
    lastMonth.subtract(1, 'month');
    const nextMonth = new Moment(moment);
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

    for (i = 0; i < j; i += chunk) {
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

    const tasks = {};
    const counts = {};

    if (this.props.tasks) {
      this.props.tasks.forEach((task) => {
        if (task.date_due) {
          const due = new Moment(task.date_due);

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

    return (<div>
      <div className="dpwd-calendar-controls">
        <div className="dpwd-calendar-controls-year">
          <span className="dpwd-calendar-controls-year-text">{this.props.moment.format('YYYY')}</span>
          <span className="dpwd-calendar-controls-year-dropdown"><i className="fa fa-caret-down" /></span>
        </div>

        <div className="dpwd-calendar-controls-month">
          <span className="dpwd-calendar-controls-month-last" onClick={this.props.prevMonth.bind(this)}><i className="fa fa-caret-left" /></span>
          <span className="dpwd-calendar-controls-month-text">{this.props.moment.format('MMMM')}</span>
          <span className="dpwd-calendar-controls-month-next" onClick={this.props.nextMonth.bind(this)}><i className="fa fa-caret-right" /></span>
        </div>
      </div>

      <table className="calendar-content" cellSpacing="0">
        <thead>
          {weekdays.map((weekday) => {
            const today = new Moment();
            const weekdayClass = today.format('dddd') === weekday &&
                                 today.format('MMMM YYYY') === this.props.moment.format('MMMM YYYY') ?
                                 'dpwd-calendar-header-today' : '';
            return <td key={weekday} className={weekdayClass}>{weekday}</td>;
          })}
        </thead>
        <tbody>
          {rows ? rows.map((row, key) => {
            let weekday = 0;
            return (<tr key={key}>
              {row.map((day, cellKey) => {
                const dateString = day.day + '-' + day.month;
                weekday++;
                return (<TaskCalendarCell tasks={tasks[dateString]}
                          counts={counts[dateString]} day={day} key={cellKey} weekday={weekday}
                          dispatch={_this.props.dispatch.bind(_this)}
                          moment={this.props.moment}
                          openCalendarList={this.openCalendarList.bind(this)}
                          openHover={this.openHover.bind(this)}
                          closeHover={this.closeHover.bind(this)} />);
              })}
            </tr>);
          }) : ''}
        </tbody>
      </table>

      <div>
        <ComponentRootWrapper open={this.state.showWindow}>
          <TaskCalendarList tasks={this.state.tasks}
                            dayDate={this.state.dayDate}
                            position={this.state.position}
                            closeWindow={this.closeWindow.bind(this)} />
        </ComponentRootWrapper>
      </div>

      <div>
        <ComponentRootWrapper open={this.state.showHover}>
          <div style={{position: 'absolute', top: this.state.hoverPosition.y, left: this.state.hoverPosition.x}}>
            <TaskCardGeneric task={this.state.task}
                             massActionable={false}
                             projects={this.props.projects}
                             position={this.state.hoverPosition}
                             tickets={this.props.tickets} />
          </div>
        </ComponentRootWrapper>
      </div>
    </div>);
  }
}
