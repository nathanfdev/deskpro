import React, { PropTypes } from 'react';
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
    const { connectDropTarget } = this.props;
    const { isOver, elements, title, updateData } = this.state;

    return connectDropTarget(
      <tbody className={classNames({'list-group-hover': isOver})}>
        {title && <TableGroupDivider title={title} />}
        {elements.valueSeq().map((task, key) =>
          <TaskCardEditContainer key={key}
                                 task={task}
                                 updateData={updateData}
                                 onUpdate={this.onUpdate.bind(this, key)}>
            <TaskCard />
          </TaskCardEditContainer>
        )}
      </tbody>
    );
  }
}
