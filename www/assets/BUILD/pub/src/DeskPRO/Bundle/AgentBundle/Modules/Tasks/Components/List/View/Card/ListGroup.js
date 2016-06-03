import React from 'react';
import { CardGroupDivider } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import classNames from 'classnames';
import { BaseListGroup } from '../BaseListGroup';
import { TaskCardEditContainer } from '../../TaskCard/TaskCardEditContainer';
import { TaskDragCard } from './TaskCard/TaskDragCard';
import { DropTarget } from 'react-dnd';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { groupTargetSpec, targetCollect } from '../../TaskCard/TaskCardEditContainer';
import Immutable from 'immutable';

@DropTarget(constants.TYPE_TASK, groupTargetSpec, targetCollect)

export class ListGroup extends BaseListGroup {

  render() {
    const { connectDropTarget, group, isOver } = this.props;
    const updateData = group.get('updateData', {});
    const elements = group.get('elements', Immutable.List());
    const title = group.get('title');

    return connectDropTarget(
      <div className={classNames({ 'list-group-hover': isOver })}>
        <CardGroupDivider title={title} />

        {elements.entrySeq().map(([key, task]) =>
          <TaskCardEditContainer key={key} task={task} updateData={updateData}>
            <TaskDragCard />
          </TaskCardEditContainer>
        )}
      </div>
    );
  }
}
