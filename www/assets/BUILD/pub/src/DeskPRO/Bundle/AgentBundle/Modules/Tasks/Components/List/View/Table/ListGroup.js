import React from 'react';
import { TableGroupDivider } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import classNames from 'classnames';
import { TaskCardEditContainer } from '../../TaskCard/TaskCardEditContainer';
import { TaskCard } from './TaskCard/TaskCard';
import { BaseListGroup } from '../BaseListGroup';
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
      <tbody className={classNames({ 'list-group-hover': isOver })}>
        {title && <TableGroupDivider title={title} />}
        {elements.map((task, key) =>
          <TaskCardEditContainer key={key} task={task} updateData={updateData}>
            <TaskCard />
          </TaskCardEditContainer>
        )}
      </tbody>
    );
  }
}
