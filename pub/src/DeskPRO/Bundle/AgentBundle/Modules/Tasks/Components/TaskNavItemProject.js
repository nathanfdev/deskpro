import React from "react";
import ProjectCreateHover from "../Components/ProjectCreateHover";
import ComponentRootWrapper from "DeskPRO/Component/ComponentRootWrapper";
import { connect } from 'redux/react';
import { DropTarget } from "react-dnd";
import DragTypes from "../../../Services/DragTypes.js";

import * as TaskActions from "../Actions/TaskListActions";

const projectTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    item.dispatch(TaskActions.editTask({
      taskId : item.id,
      project : props.project.id
    }, item.source));
  }
};

function collect(connect, monitor) {
  return {
    connectDropTarget: connect.dropTarget(),
    isOver: monitor.isOver()
  };
}

@connect(state => ({
  failedProject: state.failedProject
}))
@DropTarget(DragTypes.TASK, projectTarget, collect)
export default class TaskNavItemProject extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      showEditIcon: false
    };
  }

  toggleEditIcon(state) {
    this.setState({
      showEditIcon: state
    });
  }

  render() {
    const { project, connectDropTarget, isOver, filterTasks } = this.props;

    return connectDropTarget(<li className={isOver ? "droppable project-list-item" : "project-list-item"}>
      <div className="list-counter-bucket" onMouseEnter={this.toggleEditIcon.bind(this, true)} onMouseLeave={this.toggleEditIcon.bind(this, false)}>
        {this.state.showEditIcon ?
        <a href="#" className="edit-icon" onClick={this.props.toggleWindow.bind(this, project, event)}><i className="fa fa-cog" /></a> :
        <a className="list-counter" href="#">
          {project.remaining}
        </a>}
      </div>
      <a href="#" className="item" onClick={filterTasks.bind(this, {projects: [project.id]})}><i
        className="fa fa-book"/> {project.title} </a>
    </li>);
  }
}
