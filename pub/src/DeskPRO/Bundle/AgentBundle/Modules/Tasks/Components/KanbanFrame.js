import React, {PropTypes} from "react";
import TaskListCard from "../Components/TaskListCard";
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
    console.log('DROP!');
  }
};

@DropTarget(DragTypes.TASK, listTarget, collect)
export default class KanbanFrame extends React.Component {

  render() {

    let tasks = {};

    if (this.props.tasks && this.props.tasks.taskFrameList && this.props.tasks.taskFrameList.length > 0) {
      this.props.tasks.taskFrameList.forEach((object) => {
        if (object.list) {
          if (typeof tasks['list_' + object.list] === 'undefined') {
            tasks['list_' + object.list] = [];
          }
          let task = object;
          task.project = this.props.projects[task.project];

          tasks['list_' + object.list].push(object);
        }
      });
    }

    const columnClass = this.props.isOver ? 'list drag-hover' : 'list';

    return this.props.connectDropTarget(<div className={columnClass}>
      <h1 className="kanban-list-header">{this.props.taskList.title}</h1>
      {
        tasks['list_' + this.props.taskList.id] ? tasks['list_' + this.props.taskList.id].map((task) => {
          return <TaskListCard task={task} key={task.id} departments={this.props.departments}
                               agents={this.props.agents} teams={this.props.teams} />
        }) : ''
      }
    </div>);
  }
}
