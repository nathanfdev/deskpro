import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { MarkDoneButton } from './MarkDoneButton';
import { editTask } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Actions/listActions';
import {
  Card,
  CardCheckbox,
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
  ShowDetailsButton,
  AssignButton,
  CardProjectContainer,
  LinkedItemContainer
} from '../../../TaskCard';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    moving: PropTypes.bool
  };

  renderDetails() {

    const { task, onChange } = this.props;
    return (
      <div style={{position: 'relative',paddingRight: 70, marginBottom: 2, whiteSpace: 'nowrap'}}>
        <div>
          <DateDue value={task.get('date_due')}
                   onChange={onChange.bind(null, 'date_due')} />
          <span className="dpw--card-disc"/>
          <CardProjectContainer value={task.get('project')}
                                onChange={onChange.bind(null, 'project')} />
          <span className="dpw--card-disc"/>
          <LinkedItemContainer value={task} onChange={onChange.bind(null, 'linked_items')} />
        </div>
        <div style={{position: 'absolute', right: 0, top: 0}}>
          <Comments count={this.state.comments} />
          {task.get('subtasks_total') > 0 &&
            <SubTasks current={task.get('subtasks_done')}
                      total={task.get('subtasks_total')} />
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
        <MarkDoneButton isDone={task.get('is_done')} onToggle={onChange.bind(null, 'is_done', !task.get('is_done'))} />
        <CardCheckbox selected={selected} onClick={onToggleSelected} />
        <div style={{position: 'relative',paddingRight: 30, marginBottom: 6}}>
          <div>
            <Title value={task.get('title')}
                   isDone={task.get('is_done')}
                   onSubmit={onChange.bind(null, 'title')} />
          </div>
          <div style={{position: 'absolute', right: 0, top: 0}}>
            {task.get('is_done')
              ? <ShowDetailsButton expanded={this.state.expanded} onToggleExpand={this.onToggleExpand}/>
              : <AssignButton value={task} onChange={onChange.bind(null, 'assignee')} />
            }
          </div>
        </div>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
