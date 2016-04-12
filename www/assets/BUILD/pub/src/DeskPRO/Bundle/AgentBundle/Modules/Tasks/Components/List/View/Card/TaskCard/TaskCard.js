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
    moving: PropTypes.bool
  };

  renderDetails() {
    const { task, onChange } = this.props;
    return (
      <div style={{ position: 'relative', paddingRight: 70, marginBottom: 2, whiteSpace: 'nowrap' }}>
        <div>
          <DateDue value={task.get('date_due')} onChange={value => onChange('date_due', value)} />
          <span className="dpw--card-disc" />
          <CardProjectContainer value={task.get('project')} onChange={value => onChange('project', value)} />
          <span className="dpw--card-disc" />
          <LinkedItemContainer value={task} onChange={value => onChange('linked_items', value)} />
        </div>
        <div style={{ position: 'absolute', right: 0, top: 0 }}>
          <Comments count={this.state.comments} />
          {task.get('subtasks_total') > 0 &&
            <SubTasks current={task.get('subtasks_done')} total={task.get('subtasks_total')} />
          }
        </div>
      </div>
    );
  }

  // todo: new card lines, move styles to css
  render() {
    const { task, moving, selected } = this.props;
    const { onToggleSelected, onChange } = this.props;

    return (
      <Card moving={moving} minimized={this.isMinimized()} type="task">
        <MarkDoneButton isDone={task.get('is_done')} onToggle={() => onChange('is_done', !task.get('is_done'))} />
        <CardCheckbox selected={selected} onClick={onToggleSelected} />
        <div style={{ position: 'relative', paddingRight: 30, marginBottom: 6 }}>
          <div>
            <Title
              value={task.get('title')}
              isDone={task.get('is_done')}
              onSubmit={value => onChange('title', value)}
              />
          </div>
          <div style={{ position: 'absolute', right: 0, top: 0 }}>
            {task.get('is_done')
              ? <ShowDetailsButton expanded={this.state.expanded} onToggleExpand={this.onToggleExpand} />
              : <AssignButton value={task} onChange={value => onChange('assignee', value)} />
            }
          </div>
        </div>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
