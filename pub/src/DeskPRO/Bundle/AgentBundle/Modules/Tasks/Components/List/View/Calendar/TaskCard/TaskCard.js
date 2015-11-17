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
    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={this.state.dateDue}
                   onChange={this.onChangeDate} />

          {this.state.project &&
            <ProjectContainer project={this.state.project}>
              <CardProject />
            </ProjectContainer>
          }
          {this.state.ticketLink && <TicketLinkContainer ticket={this.state.ticketLink} />}
        </CardLineLeft>
        <CardLineRight>
          <Comments count={this.state.comments} />
          {this.state.subTasks.total > 0 &&
            <SubTasks current={this.state.subTasks.current}
                      total={this.state.subTasks.total} />
          }
        </CardLineRight>
      </CardLine>
    );
  }

  render() {
    return (
      <Card minimized={this.isMinimized()}
            statusBars={false}
            type="task"
            additionalClasses="calendar-task-card">

        <CardLine>
          <CardLineLeft>
            <Title value={this.state.title}
                   isDone={this.state.isDone}
                   onChange={this.onTitleChange} />
          </CardLineLeft>
          <CardLineRight>
            {this.state.isDone
              ? <ShowDetailsButton expanded={this.state.expanded}
                                   onToggleExpand={this.onToggleExpand}/>
              : <AssignButton task={this.props.task} />
            }
          </CardLineRight>
        </CardLine>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
