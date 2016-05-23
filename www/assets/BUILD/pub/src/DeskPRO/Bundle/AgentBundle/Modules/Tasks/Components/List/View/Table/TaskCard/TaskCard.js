import React, { PropTypes } from 'react';
import { DragSource, DropTarget } from 'react-dnd';
import { getEmptyImage } from 'react-dnd-html5-backend';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { cardSourceSpec, cardSourceCollect, cardTargetSpec, targetCollect } from '../../../TaskCard/TaskCardEditContainer';
import { Td, TdId, TdTitle } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { BaseTaskCard, CardProjectContainer, AssigneeName, DateDue, AssignButton } from '../../../TaskCard';
import { Project } from './Project';
import { TableCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import classNames from 'classnames';
import moment from 'moment';

@DragSource(constants.TYPE_TASK, cardSourceSpec, cardSourceCollect)
@DropTarget(constants.TYPE_TASK, cardTargetSpec, targetCollect)
export class TaskCard extends BaseTaskCard {

  static propTypes = {
    tableVisibleFields: PropTypes.object,
    currentSort:        PropTypes.string,
    connectDragSource:  PropTypes.func.isRequired,
    connectDropTarget:  PropTypes.func.isRequired,
    isOver:             PropTypes.bool,
    isDragging:         PropTypes.bool
  };

  componentDidMount() {
    this.props.connectDragPreview(getEmptyImage(), {
      captureDraggingState: true
    });
  }

  render() {
    const { task, selected, currentSort, onToggleSelected, tableVisibleFields, isOver, isDragging } = this.props;
    const { connectDragSource, connectDropTarget } = this.props;
    const onChange = this.onChange;

    const isVisible = type => tableVisibleFields.includes(type);

    const classes = classNames('ticket-tabular', {
      'is-over':       isOver,
      done:            task.get('is_done'),
      'dragging-item': isDragging
    });

    let result = connectDragSource(
      <tr className={classes}>
        <Td className="bulk-edit-col">
          <TableCheckbox selected={selected} onClick={onToggleSelected} />
        </Td>
        <TdId visible={isVisible('id')}>
          <span className="dpw--ticket-id">
            {task.get('id')}
          </span>
        </TdId>
        <TdTitle className="nowrap subject-col">
          <div className="ticket-title overflow-ellipsis" title={task.get('title')}>
            {task.get('title')}
          </div>
        </TdTitle>
        <Td visible={isVisible('project')}>
          <CardProjectContainer value={task.get('project')} onChange={val => onChange('project', val)}
            className="overflow-ellipsis"
            withoutIcon
          />
        </Td>
        <Td visible={isVisible('date_due')}>
          <DateDue value={task.get('date_due')} onChange={val => onChange('date_due', val)}
            className="overflow-ellipsis"
            withoutIcon
          />
        </Td>
        <Td visible={isVisible('assignee')} className="agent-col">
          <AssignButton value={task} onChange={val => onChange('assignee', val)} />
        </Td>
      </tr>
    );

    if (currentSort === 'list') {
      result = connectDropTarget(result);
    }

    return result;
  }
}
