import React, { PropTypes } from 'react';
import {
  Card,
  CardLine,
  CardLineLeft,
  CardLineRight
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments,
  ShowDetailsButton,
  AssignButton,
  TicketLinkContainer,
  ProjectContainer,
  CardProject
} from '../../../TaskCard/index';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  renderDetails() {
    const { task } = this.props;

    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={task.get('date_due')}
                   onChange={this.onChangeDate} />

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
    const { task } = this.props;

    return (
      <Card minimized={this.isMinimized()}
            statusBars={false}
            type="task"
            additionalClasses="calendar-task-card">

        <CardLine>
          <CardLineLeft>
            <Title value={task.get('title')}
                   isDone={task.get('is_done')}
                   onChange={this.onTitleChange} />
          </CardLineLeft>
          <CardLineRight>
            {task.get('is_done')
              ? <ShowDetailsButton expanded={this.state.expanded}
                                   onToggleExpand={this.onToggleExpand}/>
              : <AssignButton task={task} />
            }
          </CardLineRight>
        </CardLine>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
