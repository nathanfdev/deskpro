import React from 'react';
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
    const { connectDropTarget, isOver, group } = this.props;

    return connectDropTarget(
      <div className={classNames('list', { 'drag-hover': isOver })}>
        <h1 className="kanban-list-header">{group.get('title')}</h1>

        {group.get('elements').map((task, key) =>
          <TaskCardEditContainer
            key={key}
            task={task}
            updateData={group.get('updateData')}
            >

            <TaskDragCard />
          </TaskCardEditContainer>
        )}
      </div>
    );
  }
}
