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
  TicketLinkContainer,
  CardProjectContainer
} from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Components/List/TaskCard';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    moving: PropTypes.bool
  };

  renderDetails() {
    const { task, onChange } = this.props;
    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={task.get('date_due')}
                   onChange={onChange.bind(null, 'date_due')} />
          <CardProjectContainer value={task.get('project')}
                                onChange={onChange.bind(null, 'project')} />
          {this.state.ticketLink && <TicketLinkContainer ticket={this.state.ticketLink} />}
        </CardLineLeft>
        <CardLineRight>
          <Comments count={this.state.comments} />
          {task.get('subtasks_total') > 0 &&
            <SubTasks current={task.get('subtasks_done')}
                      total={task.get('subtasks_total')} />
          }
        </CardLineRight>
      </CardLine>
    );
  }

  render() {
    const { task, moving, selected } = this.props;
    const { onToggleSelected, onChange } = this.props;

    return (
      <Card moving={moving} minimized={this.isMinimized()} type="task">
        <MarkDoneButton isDone={task.get('is_done')} onToggle={onChange.bind(null, 'is_done', !task.get('is_done'))} />
        <CardCheckbox selected={selected} onClick={onToggleSelected} />
        <CardLine>
          <CardLineLeft>
            <Title value={task.get('title')}
                   isDone={task.get('is_done')}
                   onSubmit={onChange.bind(null, 'title')} />
          </CardLineLeft>
          <CardLineRight>
            {task.get('is_done')
              ? <ShowDetailsButton expanded={this.state.expanded} onToggleExpand={this.onToggleExpand}/>
              : <AssignButton value={task} onChange={onChange.bind(null, 'assignee')} />
            }
          </CardLineRight>
        </CardLine>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
