import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { TaskDragCard } from './TaskCard/TaskDragCard';
import { TaskCardEditContainer } from '../../TaskCard/TaskCardEditContainer';
import { BaseListGroup } from '../BaseListGroup';
import classNames from 'classnames';

import { DropTarget } from 'react-dnd';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { groupTargetSpec, targetCollect } from '../../TaskCard/TaskCardEditContainer';
@DropTarget(constants.TYPE_TASK, groupTargetSpec, targetCollect)

export class ListGroup extends BaseListGroup {

  render() {
    const { connectDropTarget } = this.props;
    const { isOver, elements, title, updateData } = this.state;

    return connectDropTarget(
      <div className={classNames('list', {'drag-hover': isOver})}>
        <h1 className="kanban-list-header">{title}</h1>

        {elements.valueSeq().map((task, key) =>
          <TaskCardEditContainer task={task}
                                 updateData={updateData}
                                 onUpdate={this.onUpdate.bind(this, key)}>
            <TaskDragCard />
          </TaskCardEditContainer>
        )}
      </div>
    );
  }
}
