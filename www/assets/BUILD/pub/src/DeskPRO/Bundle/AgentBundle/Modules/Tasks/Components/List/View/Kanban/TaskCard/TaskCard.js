import PropTypes from 'prop-types';
import React from 'react';
import { KanbanCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Kanban';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments,
  AssignButton,
  CardProjectContainer,
  LinkedItemContainer,
  AssigneeName
} from '../../../TaskCard';
import classNames from 'classnames';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    className:    PropTypes.string,
    moving:       PropTypes.bool,
    dragging:     PropTypes.bool,
    kanbanFields: PropTypes.object.isRequired
  };

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const { task } = this.props;
    const onChange = this.onChange;

    switch (field.get('id')) {

      case 'date_due':
        return (
          <div key={field.get('id')}>
            <DateDue value={task.get('date_due')} onChange={val => onChange('date_due', val)} />
          </div>
        );

      case 'project':
        return (
          <div key={field.get('id')}>
            <CardProjectContainer value={task.get('project')} onChange={val => onChange('project', val)} />
          </div>
        );

      case 'linked':
        return (
          <div key={field.get('id')}>
            <LinkedItemContainer value={task} onChange={val => onChange('linked_items', val)} />
          </div>
        );

      case 'assignee':
        return (
          <div key={field.get('id')}>
            <AssignButton value={task} onChange={val => onChange('assignee', val)} />
          </div>
        );

      default:
        return null;
    }
  }

  render() {
    const { task, selected, moving, dragging, kanbanFields } = this.props;
    const { onToggleSelected } = this.props;
    const onChange = this.onChange;

    return (
      <div className={classNames('card', 'task-card', { moving, 'dragging-item': dragging })}>

        <div className="card-status-bar status-bar-left" />
        <div className="card-status-bar status-bar-right" />

        <KanbanCheckbox selected={selected} onClick={onToggleSelected} />

        <div className="content">
          <Title value={task.get('title')} isDone={task.get('is_done')} onSubmit={val => onChange('title', val)} />

          <div className="card-line task-details">

            {kanbanFields.map(field => this.renderField(field))}

          </div>
          <hr />
          <div className="card-line task-properties">
            <Comments count={this.state.comments} />
            {task.get('subtasks_total') > 0 &&
            <SubTasks current={task.get('subtasks_done')} total={task.get('subtasks_total')} />
            || null}
          </div>
        </div>
      </div>
    );
  }
}
