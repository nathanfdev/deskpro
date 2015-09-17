import React from 'react';
import { DropTarget } from 'react-dnd';
import DragTypes from '../../../Services/DragTypes.js';

import * as TaskActions from '../Actions/TaskListActions';

const personTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    item.dispatch(TaskActions.editTask({
      taskId: item.id,
      agents: [props.agent.id],
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
  constructor(props) {
    super(props);
  }

  render() {
    const { agent, connectDropTarget, isOver, filterTasks } = this.props;

    return connectDropTarget(<li className={isOver ? 'droppable project-list-item' : 'project-list-item'}>
      <div className="list-counter-bucket">
        <a className="list-counter" href="#">{agent.assigned_tasks.length}</a>
      </div>
      <a href="#" className="item" onClick={filterTasks.bind(this, {agents: [agent.id]})}>
        {agent.picture_blob ?
          <span className="list-icon">
            <span
              style={{backgroundImage: 'url(' + agent.picture_blob.download_url + ')'}} className="avatar"/>
          </span> : '' }
        {agent.name}
      </a>
    </li>);
  }
}
