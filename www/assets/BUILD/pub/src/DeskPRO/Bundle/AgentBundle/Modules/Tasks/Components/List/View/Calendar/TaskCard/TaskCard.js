import React, { PropTypes } from 'react';
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
  TicketLinkContainer,
  CardProjectContainer
} from '../../../TaskCard';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object,
    moving: PropTypes.bool,
    onChangeTitle: PropTypes.func,
    onChangeDate: PropTypes.func,
    onSetEditing: PropTypes.func,
    dispatch: PropTypes.func.isRequired
  };

  onChange(prop, value) {
    const { dispatch, task } = this.props;
    return dispatch(editTask(task.get('id'), {[prop]: value}));
  }

  renderDetails() {
    const { task, onChangeDate, onSetEditing } = this.props;

    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={task.get('date_due')}
                   onChange={onChangeDate}
                   onSetEditing={onSetEditing} />
          <CardProjectContainer value={task.get('project')}
                                onSetEditing={onSetEditing}
                                onChange={this.onChange.bind(this, 'project')} />
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
    const { task, moving, onChangeTitle, onSetEditing } = this.props;

    return (
      <Card statusBars={false}
            type="task"
            moving={moving}
            additionalClasses="calendar-task-card">

        <CardLine>
          <CardLineLeft>
            <Title value={task.get('title')}
                   isDone={task.get('is_done')}
                   onSubmit={onChangeTitle}
                   onSetEditing={onSetEditing} />
          </CardLineLeft>
          <CardLineRight>
            {!task.get('is_done') && <AssignButton task={task} onAssign={this.onAssign} />}
          </CardLineRight>
        </CardLine>

        {this.renderDetails()}
      </Card>
    );
  }
}
