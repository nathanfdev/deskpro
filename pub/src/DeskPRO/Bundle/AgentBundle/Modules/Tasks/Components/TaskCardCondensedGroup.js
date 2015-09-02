import React, {PropTypes} from "react";
import TaskCondensedCard from "./TaskCondensedCard";
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
    let update = {
      taskId : item.id
    };
    update[props.updateField] = props.updateValue;

    item.dispatch(TaskActions.editTask(update, 'tasks'));
  }
};

export default class TaskCardCondensedGroup extends React.Component {
  render() {
    const _this = this;

    return (this.props.divider && this.props.tasks && this.props.tasks.length > 0 ? <tbody>
    <tr className="divider">
      <td colSpan="4"><hr/><span>{this.props.divider}</span></td>
    </tr>
    { this.props.tasks ? this.props.tasks.map((object) => {
      return <TaskCondensedCard task={object} projects={this.props.projects} linked_items={this.props.linked_items}
                                departments={this.props.departments} teams={this.props.teams} agents={this.props.agents}
                                toggleDone={this.props.toggleDone.bind(this)} source={this.props.source}
                                dispatch={_this.props.dispatch.bind(_this)} editTask={_this.props.editTask.bind(_this)}
                                updateMassActions={_this.props.updateMassActions.bind(_this)} key={object.id}
                                selected={_this.props.actionable.indexOf(object.id) !== -1}
                                tickets={this.props.tickets}/>
    }) : '' }
    </tbody> : null)
  }
}
