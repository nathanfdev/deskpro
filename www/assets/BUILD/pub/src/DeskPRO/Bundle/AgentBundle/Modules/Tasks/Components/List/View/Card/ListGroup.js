import React, { PropTypes } from 'react';
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
    const { connectDropTarget } = this.props;
    const { isOver, elements, title, updateData } = this.state;

    return connectDropTarget(
      <div className={classNames({ 'list-group-hover': isOver })}>
        <CardGroupDivider title={title} />

        {elements.map((task, key) =>
          <TaskCardEditContainer key={key} task={task} updateData={updateData}
            onUpdate={(value) => this.onUpdate(key, value)}
            >
            <TaskDragCard />
          </TaskCardEditContainer>
        )}
      </div>
    );
  }
}
