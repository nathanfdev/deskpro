import PropTypes from 'prop-types';
import React from 'react';
import { DropTarget } from 'react-dnd';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { groupTargetSpec, targetCollect } from '../../../TaskCard/TaskCardEditContainer';

@DropTarget(constants.TYPE_TASK, groupTargetSpec, targetCollect)
export class TaskCardDragTarget extends React.Component {

  static propTypes = {
    children:          PropTypes.any,
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
