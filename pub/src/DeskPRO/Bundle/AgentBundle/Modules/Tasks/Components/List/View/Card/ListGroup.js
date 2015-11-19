import React, { PropTypes } from 'react';
import { DropTarget } from 'react-dnd';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { groupTargetSpec, targetCollect } from '../TaskCardContainer';
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
        <div className="divider">
          <hr/>
          <h1>
            <span>{title}</span>
          </h1>
        </div>

        {children}
      </div>
    );
  }
}
