import React, { PropTypes } from 'react';
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

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    cardVisibleFields: PropTypes.object,
    moving:            PropTypes.bool
  };

  renderDetails() {
    const { task, cardVisibleFields } = this.props;
    const onChange = this.onChange;
    const isVisible = type => cardVisibleFields.includes(type);

    return (
      <div className="details-line">
        <div>
          {this.renderField('date_due')}

          {this.renderField('project')}

          {this.renderField('linked')}
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
    const { task, cardVisibleFields } = this.props;
    const onChange = this.onChange;
    const isVisible = type => cardVisibleFields.includes(type);

    if (!isVisible(field)) {
      return null;
    }

    switch (field) {
      case 'title':
        return (
          <Title value={task.get('title')} isDone={task.get('is_done')} onSubmit={value => onChange('title', value)} />
        );

      case 'date_due':
        return [
          <div className="dpwd--card-line-item-container">
            <DateDue value={task.get('date_due')} onChange={value => onChange('date_due', value)} />
          </div>,
          <span className="dpw--card-disc" />
        ];

      case 'project':
        return [
          <div className="dpwd--card-line-item-container">
            <CardProjectContainer value={task.get('project')} onChange={value => onChange('project', value)} />
          </div>,
          <span className="dpw--card-disc" />
        ];

      case 'linked':
        return (
          <div className="dpwd--card-line-item-container">
            <LinkedItemContainer value={task} onChange={value => onChange('linked_items', value)} />
          </div>
        );

      case 'assignee':
        return <AssignButton value={task} onChange={value => onChange('assignee', value)} />;

      default:
        return null;
    }
  }

  render() {
    const { task, moving, selected, cardVisibleFields, onToggleSelected } = this.props;
    const onChange = this.onChange;

    return (
      <Card moving={moving} minimized={this.isMinimized()} type="task">
        <MarkDoneButton isDone={task.get('is_done')} onToggle={value => onChange('is_done', value)}/>
        <CardCheckbox selected={selected} onClick={onToggleSelected}/>
        <div className="title-line">
          {this.renderField('title')}

          <div className="icon-block">
            {task.get('is_done')
              ? <ShowDetailsButton expanded={this.state.expanded} onToggleExpand={this.onToggleExpand}/>
              : this.renderField('assignee')
            }
          </div>
        </div>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
