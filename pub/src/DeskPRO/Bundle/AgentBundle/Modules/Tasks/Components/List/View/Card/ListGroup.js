import React, { PropTypes } from 'react';
import { DropTarget } from 'react-dnd';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { groupTargetSpec, targetCollect } from '../../TaskCard/TaskCardEditContainer';
import { CardGroupDivider } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import { NewTaskButton } from './NewTaskButton';
import classNames from 'classnames';

@DropTarget(constants.TYPE_TASK, groupTargetSpec, targetCollect)
export class ListGroup extends React.Component {

  static propTypes = {
    title: PropTypes.any,
    children: PropTypes.node,
    isOver: PropTypes.bool,
    connectDropTarget: PropTypes.func.isRequired
  };

  render() {
    const { title, children, isOver, connectDropTarget } = this.props;

    return connectDropTarget(
      <div className={classNames({'list-group-hover': isOver})}>
        <CardGroupDivider title={title} />

        {children}
      </div>
    );
  }
}
