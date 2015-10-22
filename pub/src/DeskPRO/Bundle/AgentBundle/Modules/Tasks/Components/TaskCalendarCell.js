import React from 'react';
import Moment from 'moment';
import TaskCalendarCard from '../Components/TaskCalendarCard';
import * as TaskActions from '../Actions/TaskListActions';
import { DropTarget } from 'react-dnd';
import DragTypes from '../../../Services/DragTypes.js';

function collect(connector, monitor) {
  return {
    connectDropTarget: connector.dropTarget(),
    isOver: monitor.isOver()
  };
}

const listTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    const moment = new Moment();
    moment.year(props.day.year).month(props.day.month).date(props.day.day).endOf('day').utc();
    const dateDue = moment.format();

    const update = {
      taskId: item.details.get('id'),
      date_due: dateDue
    };

    props.dispatch(TaskActions.editTask(update, 'tasks'));
  }
};

@DropTarget(DragTypes.TASK, listTarget, collect)
export default class TaskCalendarCell extends React.Component {
  openCalendarList(tasks, date, event) {
    const boundingBox = event.target.getBoundingClientRect();

    this.props.openCalendarList(tasks, date, boundingBox);
  }

  render() {
    const {tasks, day, weekday, moment} = this.props;

    const today = new Moment();
    const dayMoment = new Moment(day.year + '-' + (day.month + 1) + '-' + day.day, 'YYYY-M-D');

    const classes = [];
    if (moment.month() !== day.month) {
      classes.push('dpwd-calendar-past-month');
    } else if (dayMoment.format('YYYY-MM-DD') < today.format('YYYY-MM-DD')) {
      classes.push('dpwd-calendar-past-day');
    }
    if (weekday === 1 || weekday === 7) {
      classes.push('weekend');
    }

    const dayClass = dayMoment.format('YYYY-MM-DD') === today.format('YYYY-MM-DD') ? 'dpwd-calendar-day dpwd-calendar-day-today' : 'dpwd-calendar-day';

    let cellClass = classes.join(' ');
    cellClass = this.props.isOver ? 'list-group-hover' : cellClass;

    const _this = this;

    const additional = [];
    let counter = 0;

    return this.props.connectDropTarget(<td className={cellClass}>
      { day ? <div className={dayClass}>
        <span className="dpwd-calendar-day-mark">{day.day}</span>
        <div className="dpwd-calendar-tasks">
          <ul>
            {tasks ? tasks.map((task) => {
              counter++;

              if (counter < 3) {
                return (<TaskCalendarCard key={task.get('id')} task={task}
                            dispatch={_this.props.dispatch.bind(_this)}
                            openHover={_this.props.openHover.bind(_this)}
                            closeHover={_this.props.closeHover.bind(_this)} />);
              }

              additional.push(task);
            }) : '' }
            { additional.length > 0 ? <li>
              <a href="#" className="dpwd-calendar-tasks-show-more" onClick={this.openCalendarList.bind(this, additional, dayMoment)}>
                + {additional.length} tasks <i className="fa fa-sort" />
              </a>
            </li> : '' }
          </ul>
        </div>
      </div>
      : '' }
    </td>);
  }
}
