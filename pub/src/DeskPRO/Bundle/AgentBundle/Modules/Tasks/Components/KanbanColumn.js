import React, {PropTypes} from "react";
import TaskKanbanCard from "./TaskKanbanCard";
import { connect } from 'react-redux';
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
    let update = {
      taskId : item.id
    };
    update[props.updateField] = props.updateValue;

    item.dispatch(TaskActions.editTask(update, 'tasks'));
  }
};

@DropTarget(DragTypes.TASK, listTarget, collect)
export default class KanbanColumn extends React.Component {

  render() {
    const _this = this;

    const columnClass = this.props.isOver ? 'list drag-hover' : 'list';

    return this.props.connectDropTarget(<div className={columnClass}>
      <h1 className="kanban-list-header">{this.props.taskList.title}</h1>
      {
        this.props.tasks ? this.props.tasks.map((task) => {
          return <TaskKanbanCard task={task} key={task.id} departments={this.props.departments}
                               agents={this.props.agents} teams={this.props.teams}
                               dispatch={_this.props.dispatch.bind(_this)} />
        }) : ''
      }
    </div>);
  }
}
