import PropTypes from 'prop-types';
import React from 'react';
import { MarkDoneButton } from './MarkDoneButton';
import { Card, CardCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments,
  ShowDetailsButton,
  AssignButton,
  CardProjectContainer,
  LinkedItemContainer
} from '../../../TaskCard/index';
import Immutable from 'immutable';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    cardFields: PropTypes.object,
    moving:     PropTypes.bool
  };

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

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const { task } = this.props;
    const onChange = this.onChange;
    const fieldId = field.get('id');

    switch (fieldId) {

      case 'date_due':
        return [
          <div className="dpwd--card-line-item-container" key={`${fieldId}-field`}>
            <DateDue value={task.get('date_due')} onChange={value => onChange('date_due', value)} />
          </div>,
          <span className="dpw--card-disc" key={`${fieldId}-disc`} />
        ];

      case 'project':
        return [
          <div className="dpwd--card-line-item-container" key={`${fieldId}-field`}>
            <CardProjectContainer value={task.get('project')} onChange={value => onChange('project', value)} />
          </div>,
          <span className="dpw--card-disc" key={`${fieldId}-disc`} />
        ];

      case 'linked':
        return (
          <div className="dpwd--card-line-item-container" key={fieldId}>
            <LinkedItemContainer value={task} onChange={value => onChange('linked_items', value)} />
          </div>
        );

      case 'assignee':
        return <AssignButton value={task} onChange={value => onChange('assignee', value)} key={fieldId} />;

      default:
        return null;
    }
  }

  render() {
    const { task, moving, selected, onToggleSelected, cardFields } = this.props;
    const onChange = this.onChange;

    this._fields = [];
    let assignee;

    cardFields.map(field => {
      if (field.get('id') === 'assignee') {
        assignee = field;
      } else {
        this._fields.push(field);
      }
    });

    return (
      <Card moving={moving} minimized={this.isMinimized()} type="task">
        <MarkDoneButton isDone={task.get('is_done')} onToggle={value => onChange('is_done', value)} />
        <CardCheckbox selected={selected} onClick={onToggleSelected} />
        <div className="title-line">

          <Title value={task.get('title')} isDone={task.get('is_done')} onSubmit={value => onChange('title', value)} />

          <div className="icon-block">
            {task.get('is_done')
              ? <ShowDetailsButton expanded={this.state.expanded} onToggleExpand={this.onToggleExpand} />
              : this.renderField(assignee)
            }
          </div>
        </div>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
