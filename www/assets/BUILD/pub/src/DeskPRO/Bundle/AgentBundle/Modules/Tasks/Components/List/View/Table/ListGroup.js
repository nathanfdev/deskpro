import React from 'react';
import { TableGroupDivider } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import classNames from 'classnames';
import { TaskCardEditContainer } from '../../TaskCard/TaskCardEditContainer';
import { TaskCard } from './TaskCard/TaskCard';
import { BaseListGroup } from '../BaseListGroup';
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
      <tbody className={classNames({ 'list-group-hover': isOver })}>
        {group.get('title') && <TableGroupDivider title={group.get('title')} />}
        {group.get('elements').map((task, key) =>
          <TaskCardEditContainer
            key={key}
            task={task}
            updateData={group.get('updateData')}
            >

            <TaskCard />
          </TaskCardEditContainer>
        )}
      </tbody>
    );
  }
}
