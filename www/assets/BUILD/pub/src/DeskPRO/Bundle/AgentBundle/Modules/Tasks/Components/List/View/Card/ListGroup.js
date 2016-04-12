import React from 'react';
import { CardGroupDivider } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import classNames from 'classnames';
import { BaseListGroup } from '../BaseListGroup';
import { TaskCardEditContainer } from '../../TaskCard/TaskCardEditContainer';
import { TaskDragCard } from './TaskCard/TaskDragCard';
import { DropTarget } from 'react-dnd';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { groupTargetSpec, targetCollect } from '../../TaskCard/TaskCardEditContainer';

@DropTarget(constants.TYPE_TASK, groupTargetSpec, targetCollect)
export class ListGroup extends BaseListGroup {

  render() {
    const { connectDropTarget, isOver, group } = this.props;
    if (!this.hasElements()) {
      return null;
    }

    return connectDropTarget(
      <div className={classNames({ 'list-group-hover': isOver })}>
        <CardGroupDivider title={group.get('title')} />

        {group.get('elements').map((task, key) =>
          <TaskCardEditContainer key={key} task={task}>
            <TaskDragCard />
          </TaskCardEditContainer>
        )}
      </div>
    );
  }
}
