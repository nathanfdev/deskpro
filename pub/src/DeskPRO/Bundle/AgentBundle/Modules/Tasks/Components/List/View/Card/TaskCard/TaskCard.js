import React, { PropTypes } from 'react';
import { Card, CardCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import { MarkDoneButton } from './MarkDoneButton';
import {
  BaseTaskCard,
  CardLine,
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
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object
  };

  onToggleDone = () => {
    this.setState({
      isDone: !this.state.isDone
    });
  };

  renderDetails() {
    return (
      <CardLine>
        <div>
            <DateDue value={this.state.dateDue}
                     onChange={this.onChangeDate} />

            {this.state.project &&
              <ProjectContainer project={this.state.project}>
                <CardProject />
              </ProjectContainer>
            }
            {this.state.ticketLink && <TicketLinkContainer ticket={this.state.ticketLink} />}
        </div>

        <div>
            <Comments count={this.state.comments} />
            {this.state.subTasks.total > 0 &&
              <SubTasks current={this.state.subTasks.current}
                        total={this.state.subTasks.total} />
            }
        </div>
      </CardLine>
    );
  }

  render() {
    const { selected, onToggleSelected } = this.props;

    return (
      <Card minimized={this.isMinimized()} type="task">
        <MarkDoneButton isDone={this.state.isDone}
                        onToggle={this.onToggleDone} />

        <CardCheckbox selected={selected} onClick={onToggleSelected} />
        <CardLine>
          <Title value={this.state.title}
                 isDone={this.state.isDone}
                 onChange={this.onTitleChange} />

          {this.state.isDone
            ? <ShowDetailsButton expanded={this.state.expanded}
                                 onToggleExpand={this.onToggleExpand}/>
            : <AssignButton task={this.props.task} />
          }
        </CardLine>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
