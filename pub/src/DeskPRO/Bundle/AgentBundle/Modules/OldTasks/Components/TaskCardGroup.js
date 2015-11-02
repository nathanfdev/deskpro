import React, {PropTypes} from 'react';
import TaskCard from './TaskCard';
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
      taskId: item.details.get('id')
    };
    update[props.updateField] = props.updateValue;

    item.dispatch(TaskActions.editTask(update, 'tasks'));
  }
};

@DropTarget(DragTypes.TASK, listTarget, collect)
export default class TaskCardGroup extends React.Component {
  static propTypes = {
    divider: PropTypes.any,
    departments: PropTypes.object,
    teams: PropTypes.object,
    agents: PropTypes.object,
    dispatch: PropTypes.func,
    editTask: PropTypes.func,
    updateMassActions: PropTypes.func,
    actionable: PropTypes.array,
    tickets: PropTypes.object,
    projects: PropTypes.object,
  }

  constructor(props) {
    super(props);
    this.state = {
      tasks: props.tasks
    };
  }

  moveCard(item, targetItem) {
    const tasks = this.state.tasks ? this.state.tasks : this.props.tasks;

    this.props.moveCard(item, targetItem, tasks, (cards) => {
      this.setState({
        tasks: cards
      });
    });
  }

  render() {
    const _this = this;

    const columnClass = this.props.isOver ? 'list-group-hover' : '';

    const tasks = this.state.tasks ? this.state.tasks : this.props.tasks;

    return this.props.connectDropTarget(<div className={columnClass}>
      { this.props.divider && tasks && tasks.length > 0 ?
        <div className="divider"><hr/><h1><span>{this.props.divider}</span></h1></div> : '' }
      { tasks ? tasks.map((object) => {
        return (<span key={object.get('id')}>
                  <TaskCard task={object} projects={this.props.projects}
                            departments={this.props.departments} teams={this.props.teams} agents={this.props.agents}
                            toggleDone={this.props.toggleDone.bind(this)} source={this.props.source}
                            dispatch={_this.props.dispatch.bind(_this)} editTask={_this.props.editTask.bind(_this)}
                            updateMassActions={_this.props.updateMassActions.bind(_this)}
                            selected={_this.props.actionable && _this.props.actionable.indexOf(object.get('id')) !== -1} tickets={this.props.tickets}
                            moveCard={this.moveCard.bind(this)} order={this.props.order}
                            toggleAssignWindow={_this.props.toggleAssignWindow.bind(_this)} />
                </span>);
      }) : '' }
    </div>);
  }
}
