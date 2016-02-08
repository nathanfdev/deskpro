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
  ProjectContainer,
  CardProject
} from '../../../TaskCard';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object,
    moving: PropTypes.bool,
    onChangeTitle: PropTypes.func,
    onChangeDate: PropTypes.func,
    onSetEditing: PropTypes.func
  };

  renderDetails() {
    const { task, onChangeDate, onSetEditing } = this.props;

    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={task.get('date_due')}
                   onChange={onChangeDate}
                   onSetEditing={onSetEditing}onSetEditing={onSetEditing} />

          {task.get('project') &&
            <ProjectContainer project={task.get('project')}>
              <CardProject />
            </ProjectContainer>
          }
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
                   onChange={onChangeTitle}
                   onSetEditing={onSetEditing} />
          </CardLineLeft>
          <CardLineRight>
            {!task.get('is_done') && <AssignButton task={task} onSetEditing={onSetEditing} />}
          </CardLineRight>
        </CardLine>

        {this.renderDetails()}
      </Card>
    );
  }
}
