import React, { PropTypes } from 'react';
import { DragSource, DropTarget } from 'react-dnd';
import { getEmptyImage } from 'react-dnd-html5-backend';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { cardSourceSpec, cardSourceCollect, cardTargetSpec, targetCollect } from '../../../TaskCard/TaskCardEditContainer';
import { Td, TdId, TdTitle } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { BaseTaskCard, ProjectContainer, AssigneeContainer, AssigneeName } from '../../../TaskCard/index';
import { Project } from './Project';
import { TableCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import classNames from 'classnames';
import moment from 'moment';

@DragSource(constants.TYPE_TASK, cardSourceSpec, cardSourceCollect)
@DropTarget(constants.TYPE_TASK, cardTargetSpec, targetCollect)
export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object,
    tableVisibleFields: PropTypes.object,
    currentSort: PropTypes.string,
    connectDragSource: PropTypes.func.isRequired,
    connectDropTarget: PropTypes.func.isRequired,
    isOver: PropTypes.bool,
    isDragging: PropTypes.bool
  };

  componentDidMount() {
    this.props.connectDragPreview(getEmptyImage(), {
      captureDraggingState: true
    });
  }

  render() {
    const { task, selected, currentSort, onToggleSelected, tableVisibleFields, isOver, isDragging } = this.props;
    const { connectDragSource, connectDropTarget } = this.props;

    const isVisible = type => tableVisibleFields.includes(type);

    let result = connectDragSource(
      <tr className={classNames({
        'is-over': isOver,
        'done': task.get('is_done'),
        'dragging-item': isDragging
      })}>

        <Td>
          <TableCheckbox selected={selected} onClick={onToggleSelected} />
        </Td>
        <TdId visible={isVisible('id')}>{task.get('id')}</TdId>
        <TdTitle>{task.get('title')}</TdTitle>
        <Td visible={isVisible('project')}>
          <ProjectContainer project={task.get('project')}>
            <Project />
          </ProjectContainer>
        </Td>
        <Td visible={isVisible('date_due')}>
          {task.get('date_due') ? moment(task.get('date_due')).format('DD/MM/YY') : 'N/A'}
        </Td>
        <Td visible={isVisible('assignee')}>
          <AssigneeContainer>
            <AssigneeName task={task} />
          </AssigneeContainer>
        </Td>
      </tr>
    );

    if (currentSort === 'list') {
      result = connectDropTarget(result);
    }

    return result;
  }
}
