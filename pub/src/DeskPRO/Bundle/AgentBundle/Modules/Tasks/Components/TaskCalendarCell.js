import React, {PropTypes} from "react";
import Moment from "moment";
import TaskCalendarCard from "../Components/TaskCalendarCard";
import Calendar from "../../../Services/Calendar";
import { connect } from 'redux/react';
import * as TaskActions from "../Actions/TaskListActions";
import { DropTarget } from 'react-dnd';
import DragTypes from "../../../Services/DragTypes.js";

function collect(connect, monitor) {
  return {
    connectDropTarget: connect.dropTarget(),
    isOver: monitor.isOver()
  };
}

const listTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    const moment = new Moment();
    moment.year(props.day.year).month(props.day.month).date(props.day.day).endOf('day').utc();
    const dateDue = moment.format();

    let update = {
      taskId : item.id,
      date_due : dateDue
    };

    props.dispatch(TaskActions.editTask(update, 'tasks'));
  }
};

@DropTarget(DragTypes.TASK, listTarget, collect)
export default class TaskCalendarCell extends React.Component {
  render() {
    const {tasks, day, counts, weekday, moment} = this.props;

    let classes = [];
    if (moment.month() !== day.month) {
      classes.push('non-month');
    }
    if (weekday === 1 || weekday === 7) {
      classes.push('weekend');
    }

    let cellClass = classes.join(' ');
    cellClass = this.props.isOver ? 'list-group-hover' : cellClass;

    const _this = this;

    let additional = 0,
        i = 0;

    return this.props.connectDropTarget(<td className={cellClass}>
      { day ? <span>
        <span className="date">{day.day}</span>
        <ul>
        {tasks ? tasks.map((task) => {
          i++;

          if (i < 4) {
            return <TaskCalendarCard key={task.id} task={task}
                    dispatch={_this.props.dispatch.bind(_this)}/>
          } else {
            additional++;
          }
        }) : '' }
        </ul>
        { additional > 0 ? <span className="additional day-info">+ {additional} more</span> : '' }
        { counts && counts > 0 ? <span className="completed day-info">{counts} already complete</span> : '' }
      </span>
      : '' }
    </td>)
  }
}
