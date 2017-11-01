import PropTypes from 'prop-types';
import React from 'react';
import { DragSource, DropTarget } from 'react-dnd';
import { getEmptyImage } from 'react-dnd-html5-backend';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { cardSourceSpec, cardSourceCollect, cardTargetSpec, targetCollect } from '../../../TaskCard/TaskCardEditContainer';
import { Td, TdId, TdTitle } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { BaseTaskCard, CardProjectContainer, DateDue, AssignButton } from '../../../TaskCard';
import { TableCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import classNames from 'classnames';

@DragSource(constants.TYPE_TASK, cardSourceSpec, cardSourceCollect)
@DropTarget(constants.TYPE_TASK, cardTargetSpec, targetCollect)
export class TaskCard extends BaseTaskCard {

  static propTypes = {
    tableFields:       PropTypes.object,
    currentSort:       PropTypes.string,
    connectDragSource: PropTypes.func.isRequired,
    connectDropTarget: PropTypes.func.isRequired,
    isOver:            PropTypes.bool,
    isDragging:        PropTypes.bool
  };

  componentDidMount() {
    this.props.connectDragPreview(getEmptyImage(), {
      captureDraggingState: true
    });
  }

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const { task } = this.props;
    const onChange = this.onChange;
    const fieldId = field.get('id');

    switch (fieldId) {

      case 'id':
        return (
          <TdId key={fieldId}>
            <span className="dpw--ticket-id">
              {task.get('id')}
            </span>
          </TdId>
        );

      case 'title':
        return (
          <TdTitle className="nowrap subject-col" key={fieldId}>
            <div className="ticket-title overflow-ellipsis" title={task.get('title')}>
              {task.get('title')}
            </div>
          </TdTitle>
        );

      case 'project':
        return (
          <Td key={fieldId}>
            <CardProjectContainer value={task.get('project')} onChange={val => onChange('project', val)}
              className="overflow-ellipsis"
              withoutIcon
            />
          </Td>
        );

      case 'date_due':
        return (
          <Td key={fieldId}>
            <DateDue value={task.get('date_due')} onChange={val => onChange('date_due', val)}
              className="overflow-ellipsis"
              withoutIcon
            />
          </Td>
        );

      case 'assignee':
        return (
          <Td className="agent-col" key={fieldId}>
            <AssignButton value={task} onChange={val => onChange('assignee', val)} />
          </Td>
        );

      default:
        return <Td key={fieldId}>{task.get(fieldId)}</Td>;
    }
  }

  render() {
    const { task, selected, currentSort, onToggleSelected, isOver, isDragging, tableFields } = this.props;
    const { connectDragSource, connectDropTarget } = this.props;


    const classes = classNames('ticket-tabular', {
      'is-over':       isOver,
      done:            task.get('is_done'),
      'dragging-item': isDragging
    });

    let result = connectDragSource(
      <tr className={classes}>
        <Td className="bulk-edit-col" key="bulk-edit-col">
          <TableCheckbox selected={selected} onClick={onToggleSelected} />
        </Td>

        {tableFields.map(field => this.renderField(field))}

      </tr>
    );

    if (currentSort === 'list') {
      result = connectDropTarget(result);
    }

    return result;
  }
}
