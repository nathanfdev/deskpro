import React, { PropTypes } from 'react';
import { DropTarget } from 'react-dnd';
import { groupTargetSpec, targetCollect } from '../TaskCardContainer';
import classNames from 'classnames';

@DropTarget('TASK', groupTargetSpec, targetCollect)
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
      <div className={classNames('list', {'drag-hover': isOver})}>
        <h1 className="kanban-list-header">{title}</h1>

        {children}
      </div>
    );
  }
}
