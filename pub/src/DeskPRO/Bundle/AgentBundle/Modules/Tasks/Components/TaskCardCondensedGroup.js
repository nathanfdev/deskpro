import React from 'react';
import TaskCondensedCard from './TaskCondensedCard';
import * as TaskActions from '../Actions/TaskListActions';
import { DropTarget } from 'react-dnd';
import DragTypes from '../../../Services/DragTypes.js';

function collect(connect, monitor) {
  return {
    connectDropTarget: connect.dropTarget(),
    isOver: monitor.isOver()
  };
}

const listTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    const update = {
      taskId: item.id
    };
    update[props.updateField] = props.updateValue;

    item.dispatch(TaskActions.editTask(update, 'tasks'));
  }
};

@DropTarget(DragTypes.TASK, listTarget, collect)
export default class TaskCardCondensedGroup extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      tasks: props.tasks
    };
  }

  moveCard(item, targetItem) {
    this.props.moveCard(item, targetItem, this.state.tasks, (cards) => {
      this.setState({
        tasks: cards
      });
    });
  }

  render() {
    const _this = this;

    const className = this.props.isOver ? 'list-group-hover' : '';
    const tasks = this.state.tasks ? this.state.tasks : this.props.tasks;

    return this.props.connectDropTarget(this.props.divider && this.props.tasks && this.props.tasks.length > 0 ? <tbody className={className}>
    <tr className="divider">
      <td colSpan="4">
        <hr/>
        <span>{this.props.divider}</span>
      </td>
    </tr>
    { tasks ? tasks.map((object) => {
      return (<TaskCondensedCard task={object} projects={this.props.projects} linked_items={this.props.linked_items}
                                 departments={this.props.departments} teams={this.props.teams} agents={this.props.agents}
                                 toggleDone={this.props.toggleDone.bind(this)} source={this.props.source}
                                 dispatch={_this.props.dispatch.bind(_this)} editTask={_this.props.editTask.bind(_this)}
                                 updateMassActions={_this.props.updateMassActions.bind(_this)} key={object.get('id')}
                                 selected={_this.props.actionable.indexOf(object.get('id')) !== -1}
                                 tickets={this.props.tickets} moveCard={this.moveCard.bind(this)}
                                 order={this.props.order}/>);
    }) : '' }
    </tbody> : null);
  }
}
