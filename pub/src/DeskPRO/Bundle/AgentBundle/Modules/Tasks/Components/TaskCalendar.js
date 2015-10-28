import React from 'react';
import Moment from 'moment';
import TaskCalendarCell from '../Components/TaskCalendarCell';
import TaskCalendarList from '../Components/TaskCalendarList';
import TaskCardGeneric from '../Components/TaskCardGeneric';
import TaskCalendarYearsDropdown from '../Components/TaskCalendarYearsDropdown';
import Calendar from '../../../Services/Calendar';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import jQuery from 'jquery';

export default class TaskCalendar extends React.Component {
  static propTypes = {
    agents: React.PropTypes.object,
    departments: React.PropTypes.object,
    dispatch: React.PropTypes.func,
    linkedItems: React.PropTypes.object,
    moment: React.PropTypes.object,
    nextMonth: React.PropTypes.func,
    prevMonth: React.PropTypes.func,
    projects: React.PropTypes.object,
    setYear: React.PropTypes.func,
    tasks: React.PropTypes.object,
    teams: React.PropTypes.object,
    tickets: React.PropTypes.object
  }

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
      },
      yearDropdownPosition: {
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

  openCalendarList(tasks, date, boundingBox) {
    this.setState({
      tasks: tasks,
      dayDate: date,
      showWindow: true,
      position: {
        x: boundingBox.left,
        y: boundingBox.bottom + 10
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

  openYearDropdown(element) {
    const target = jQuery(element);

    this.setState({
      showYearDropdown: true,
      yearDropdownPosition: {
        x: target[0].target.getBoundingClientRect().left,
        y: target[0].target.getBoundingClientRect().bottom + 10
      }
    });
  }

  closeYearDropdown() {
    this.setState({
      showYearDropdown: false
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

    let incrementer = 0;
    const calendarLength = layout.length;
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

    for (incrementer = 0; incrementer < calendarLength; incrementer += chunk) {
      rows.push(calendarDays.slice(incrementer, incrementer + chunk));
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
      this.props.tasks.map((task) => {
        if (task.has('date_due')) {
          const due = new Moment(task.get('date_due'));
          const dateString = due.date() + '-' + due.month();

          if (this.displayTask(due)) {
            if (typeof tasks[dateString] === 'undefined') {
              tasks[dateString] = [];
            }

            if (typeof counts[dateString] === 'undefined') {
              counts[dateString] = 0;
            }

            if (task.get('is_done')) {
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
          <span className="dpwd-calendar-controls-year-dropdown" onClick={this.openYearDropdown.bind(this)}>
            <i className="fa fa-caret-down" />
          </span>
        </div>

        <div className="dpwd-calendar-controls-month">
          <span className="dpwd-calendar-controls-month-last" onClick={this.props.prevMonth.bind(this)}><i className="fa fa-caret-left" /></span>
          <span className="dpwd-calendar-controls-month-text">{this.props.moment.format('MMMM')}</span>
          <span className="dpwd-calendar-controls-month-next" onClick={this.props.nextMonth.bind(this)}><i className="fa fa-caret-right" /></span>
        </div>
      </div>

      <table className="calendar-content" cellSpacing="0">
        <thead>
          <tr>
          {weekdays.map((weekday) => {
            const today = new Moment();
            const weekdayClass = today.format('dddd') === weekday &&
                                 today.format('MMMM YYYY') === this.props.moment.format('MMMM YYYY') ?
                                 'dpwd-calendar-header-today' : '';
            return <td key={weekday} className={weekdayClass}>{weekday}</td>;
          })}
          </tr>
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
        <Positioned isOpen={this.state.showWindow}>

          <TaskCalendarList tasks={this.state.tasks}
                            dayDate={this.state.dayDate}
                            position={this.state.position}
                            closeWindow={this.closeWindow.bind(this)}
                            openHover={this.openHover.bind(this)}
                            closeHover={this.closeHover.bind(this)}
                            dispatch={this.props.dispatch.bind(this)} />

        </Positioned>
      </div>

      <div>
        <Positioned isOpen={this.state.showYearDropdown}>
          <TaskCalendarYearsDropdown setYear={this.props.setYear.bind(this)}
                                     position={this.state.yearDropdownPosition}
                                     openYearDropdown={this.openYearDropdown.bind(this)}
                                     closeYearDropdown={this.closeYearDropdown.bind(this)} />
        </Positioned>
      </div>

      <div>
        <Positioned isOpen={this.state.showHover}>
          <div style={{position: 'absolute', top: this.state.hoverPosition.y, left: this.state.hoverPosition.x}}>
            {typeof this.state.task !== 'undefined' && this.state.task && this.state.task.size > 0 ?
            <TaskCardGeneric task={this.state.task}
                             projects={this.props.projects}
                             linkedItems={this.props.linkedItems}
                             departments={this.props.departments}
                             teams={this.props.teams}
                             agents={this.props.agents}
                             position={this.state.hoverPosition}
                             tickets={this.props.tickets} />
            : <div />}
          </div>
        </Positioned>
      </div>
    </div>);
  }
}
