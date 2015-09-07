import React, {PropTypes} from "react";
import TaskCard from "./TaskCard";
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
export default class TaskCardGroup extends React.Component {
  render() {
    const _this = this;

    const columnClass = this.props.isOver ? 'list-group-hover' : '';

    return this.props.connectDropTarget(<div className={columnClass}>
      { this.props.divider && this.props.tasks && this.props.tasks.length > 0 ?
        <div className="divider"><hr/><h1><span>{this.props.divider}</span></h1></div> : '' }
      { this.props.tasks ? this.props.tasks.map((object) => {
        return <span key={object.id}>
          <TaskCard task={object} projects={this.props.projects} linked_items={this.props.linked_items}
                    departments={this.props.departments} teams={this.props.teams} agents={this.props.agents}
                    toggleDone={this.props.toggleDone.bind(this)} source={this.props.source}
                    dispatch={_this.props.dispatch.bind(_this)} editTask={_this.props.editTask.bind(_this)}
                    updateMassActions={_this.props.updateMassActions.bind(_this)}
                    selected={_this.props.actionable.indexOf(object.id) !== -1} tickets={this.props.tickets} />
        </span>
      }) : '' }
    </div>)
  }
}
