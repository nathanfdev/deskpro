import React, { PropTypes } from 'react';
import { DragSource, DropTarget } from 'react-dnd';
import { cardSourceSpec, cardSourceCollect, cardTargetSpec, targetCollect } from '../../TaskCardContainer';
import { Td, TdId, TdTitle } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { BaseTaskCard, ProjectContainer } from '../../../TaskCard/index';
import { Project } from './Project';
import { AssigneeContainer } from './AssigneeContainer';
import { TableCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import classNames from 'classnames';
import Moment from 'moment';

@DragSource('TASK', cardSourceSpec, cardSourceCollect)
@DropTarget('TASK', cardTargetSpec, targetCollect)
export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object,
    tableVisibleFields: PropTypes.object,
    currentSort: PropTypes.string,
    connectDragSource: PropTypes.func.isRequired,
    connectDropTarget: PropTypes.func.isRequired,
    isOver: PropTypes.bool
  };

  render() {
    const { task, selected, currentSort, onToggleSelected, tableVisibleFields, isOver } = this.props;
    const { connectDragSource, connectDropTarget } = this.props;

    const isVisible = type => tableVisibleFields.includes(type);

    let result = connectDragSource(
      <tr className={classNames({
        'is-over': isOver,
        'done': task.get('is_done')
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
          {task.get('date_due') ? Moment(task.get('date_due')).format('DD/MM/YY') : 'N/A'}
        </Td>
        <Td visible={isVisible('assignee')}>
          <AssigneeContainer task={task} />
        </Td>
      </tr>
    );

    if (currentSort === 'list') {
      result = connectDropTarget(result);
    }

    return result;
  }
}
