import React from 'react';
import { DropTarget } from 'react-dnd';
import DragTypes from '../../../Services/DragTypes.js';

import * as TaskActions from '../Actions/TaskListActions';

const labelTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    const currentLabels = item.details.labels;

    if (currentLabels.indexOf(props.label.label) === -1) {
      const mergedLabels = currentLabels;
      mergedLabels.push(props.label.label);

      item.dispatch(TaskActions.editTask({
        taskId: item.id,
        labels: mergedLabels
      }, item.source));
    }
  }
};

function collect(connector, monitor) {
  return {
    connectDropTarget: connector.dropTarget(),
    isOver: monitor.isOver()
  };
}

@DropTarget(DragTypes.TASK, labelTarget, collect)
export default class TaskNavItemLabel extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { label, connectDropTarget, isOver, filterTasks } = this.props;

    return connectDropTarget(<a href="#" className={isOver ? 'item-label active' : 'item-label'}
            key={label.get('label')} onClick={filterTasks.bind(this, {labels: [label.get('label')]})}>
            {label.get('label')}
          </a>);
  }
}
