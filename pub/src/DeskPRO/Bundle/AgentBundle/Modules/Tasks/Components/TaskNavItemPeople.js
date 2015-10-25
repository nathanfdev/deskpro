import React from 'react';
import { DropTarget } from 'react-dnd';
import DragTypes from '../../../Services/DragTypes.js';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';

import * as TaskActions from '../Actions/TaskListActions';

const personTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    item.dispatch(TaskActions.editTask({
      taskId: item.id,
      agents: [props.agent.get('id')],
      teams: [],
      departments: []
    }, item.source));
  }
};

function collect(connector, monitor) {
  return {
    connectDropTarget: connector.dropTarget(),
    isOver: monitor.isOver()
  };
}

@DropTarget(DragTypes.TASK, personTarget, collect)
export default class TaskNavItemPeople extends React.Component {
  render() {
    const { agent, connectDropTarget, isOver, filterTasks } = this.props;

    return connectDropTarget(<li className={isOver ? 'droppable project-list-item' : 'project-list-item'}>
      <div className="list-counter-bucket">
        <a className="list-counter" href="#">{agent.get('assigned_tasks', 0).size}</a>
      </div>
      <a href="#" className="item" onClick={filterTasks.bind(this, {agents: [agent.get('id')]})}>
        <span className="list-icon">
          <PersonAvatar person={agent} size="16" />
        </span>
        {agent.get('name')}
      </a>
    </li>);
  }
}
