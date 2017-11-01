import PropTypes from 'prop-types';
import React from 'react';
import {
  Card,
  CardLine,
  CardLineLeft,
  CardLineRight
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments,
  AssignButton,
  LinkedItemContainer,
  CardProjectContainer
} from '../../../TaskCard/index';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    moving:         PropTypes.bool,
    calendarFields: PropTypes.object.isRequired
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
          <div className="icon-block">
            {!task.get('is_done') && <AssignButton value={task} onChange={val => onChange('assignee', val)} />}
          </div>
        );

      default:
        return null;
    }
  }

  renderDetails() {
    const { task } = this.props;

    return (
      <div className="details-line">
        <div>
          {this._fields.map(field => this.renderField(field))}
        </div>
        <div className="icon-block">
          <Comments count={this.state.comments} />
          {task.get('subtasks_total') > 0 &&
          <SubTasks current={task.get('subtasks_done')} total={task.get('subtasks_total')} />
          }
        </div>
      </div>
    );
  }

  render() {
    const { task, moving, calendarFields } = this.props;
    const onChange = this.onChange;

    this._fields = [];
    let assignee;
    calendarFields.map(field => {
      if (field.get('id') === 'assignee') {
        assignee = field;
      } else {
        this._fields.push(field);
      }
    });

    return (
      <Card statusBars={false} type="task" moving={moving} additionalClasses="calendar-task-card">

        <div className="title-line">
          <Title value={task.get('title')} isDone={task.get('is_done')} onSubmit={val => onChange('title', val)} />
          {this.renderField(assignee)}
        </div>

        {this.renderDetails()}
      </Card>
    );
  }
}
