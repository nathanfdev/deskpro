import React, { PropTypes } from 'react';
import { DropTarget } from 'react-dnd';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { groupTargetSpec, targetCollect } from '../../../TaskCard/TaskCardEditContainer';

@DropTarget(constants.TYPE_TASK, groupTargetSpec, targetCollect)
export class TaskCardDragTarget extends React.Component {

  static propTypes = {
    children: PropTypes.any,
    connectDropTarget: PropTypes.func.isRequired
  };

  render() {
    const { connectDropTarget, children } = this.props;

    return connectDropTarget(
      <div>
        {children}
      </div>
    );
  }
}
