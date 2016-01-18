import React, { PropTypes } from 'react';
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
  CardProject
} from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Components/List/TaskCard/index';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    onToggleDone: PropTypes.func,
    onChangeTitle: PropTypes.func,
    onChangeDate: PropTypes.func,
    onSetEditing: PropTypes.func,
    task: PropTypes.object,
    moving: PropTypes.bool,
    dispatch: PropTypes.func.isRequired
  };

  onAssign = (assignee) => {
    const { dispatch, task } = this.props;
    return dispatch(editTask(task.get('id'), assignee));
  };

  renderDetails() {
    const { task, onChangeDate, onSetEditing } = this.props;

    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={task.get('date_due')}
                   onChange={onChangeDate}
                   onSetEditing={onSetEditing} />

          <CardProject projectId={task.get('project')} onSetEditing={onSetEditing} />
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
    const { onToggleSelected, onToggleDone, onChangeTitle, onSetEditing } = this.props;

    return (
      <Card moving={moving} minimized={this.isMinimized()} type="task">
        <MarkDoneButton isDone={task.get('is_done')} onToggle={onToggleDone} />
        <CardCheckbox selected={selected} onClick={onToggleSelected} />
        <CardLine>
          <CardLineLeft>
            <Title value={task.get('title')}
                   isDone={task.get('is_done')}
                   onChange={onChangeTitle}
                   onSetEditing={onSetEditing} />
          </CardLineLeft>
          <CardLineRight>
            {task.get('is_done')
              ? <ShowDetailsButton expanded={this.state.expanded} onToggleExpand={this.onToggleExpand}/>
              : <AssignButton task={task} onSetEditing={onSetEditing} onAssign={this.onAssign} />
            }
          </CardLineRight>
        </CardLine>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
