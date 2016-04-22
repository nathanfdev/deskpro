import React from 'react';
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
    const { connectDropTarget, group, isOver } = this.props;
    const updateData = group.get('updateData', {});
    const elements = group.get('elements', Immutable.List());
    const title = group.get('title');

    return connectDropTarget(
      <div className={classNames('list', { 'drag-hover': isOver })}>
        <h1 className="kanban-list-header">{title}</h1>

        {elements.map((task, key) =>
          <TaskCardEditContainer key={key} task={task} updateData={updateData}>
            <TaskDragCard />
          </TaskCardEditContainer>
        )}
      </div>
    );
  }
}
