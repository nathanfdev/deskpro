import React from 'react';
import TaskKanbanCard from './TaskKanbanCard';
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

    if (item[props.updateField] !== props.updateValue) {
      const update = {
        taskId: item.details.get('id')
      };
      update[props.updateField] = props.updateValue;

      item.dispatch(TaskActions.editTask(update, 'tasks'));
    }
  }
};

@DropTarget(DragTypes.TASK, listTarget, collect)
export default class KanbanColumn extends React.Component {
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

    const columnClass = this.props.isOver ? 'list drag-hover' : 'list';

    const tasks = this.state.tasks ? this.state.tasks : this.props.tasks;

    return this.props.connectDropTarget(<div className={columnClass}>
      <h1 className="kanban-list-header">{this.props.taskList.title}</h1>
      {
        tasks ? tasks.map((task) => {
          return (<TaskKanbanCard task={task} key={task.get('id')} departments={this.props.departments}
                                  agents={this.props.agents} teams={this.props.teams}
                                  dispatch={_this.props.dispatch.bind(_this)}
                                  moveCard={this.moveCard.bind(this)}
                                  order={this.props.order}
                                  updateMassActions={_this.props.updateMassActions.bind(_this)}
                                  selected={_this.props.actionable.indexOf(task.get('id')) !== -1}
                                />);
        }) : ''
      }
    </div>);
  }
}
