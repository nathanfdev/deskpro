import React, { PropTypes } from 'react';
import { DropTarget } from 'react-dnd';
import { groupTargetSpec, targetCollect } from '../TaskCardContainer';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { TableGroupDivider } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import classNames from 'classnames';

@DropTarget(constants.TYPE_TASK, groupTargetSpec, targetCollect)
export class ListGroup extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    children: PropTypes.any,
    isOver: PropTypes.bool,
    connectDropTarget: PropTypes.func.isRequired
  };

  render() {
    const { title, children, isOver, connectDropTarget } = this.props;

    return connectDropTarget(
      <tbody className={classNames({'list-group-hover': isOver})}>
        {title && <TableGroupDivider title={title} />}
        {children}
      </tbody>
    );
  }
}
