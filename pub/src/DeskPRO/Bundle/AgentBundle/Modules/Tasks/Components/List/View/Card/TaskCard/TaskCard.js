import React, { PropTypes } from 'react';
import { BaseTaskCard } from '../../../TaskCard/BaseTaskCard';
import { DateDue } from '../../../TaskCard/DateDue';
import { Card } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import { CardLine } from './CardLine';
import { Title } from './Title';
import { Checkbox } from './Checkbox';
import { MarkDoneButton } from './MarkDoneButton';
import { ShowDetailsButton } from './ShowDetailsButton';
import { AssignButton } from './AssignButton';
import { Comments } from './Comments';
import { SubTasks } from './SubTasks';
import { Project } from './Project';
import { TicketLinkContainer } from './TicketLinkContainer';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  onTitleChange = value => {
    this.setState({
      title: value
    });
  };

  onToggleDone = () => {
    this.setState({
      isDone: !this.state.isDone
    });
  };

  onToggleExpand = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  isMinimized() {
    return !this.state.expanded && this.state.isDone;
  }

  renderDetails() {
    return (
      <CardLine>
        <div>
            <DateDue value={this.state.dateDue}
                     onChange={this.onChangeDate} />
            {this.state.project && <Project project={this.state.project} />}
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
    return (
      <Card minimized={this.isMinimized()} type="task">
        <MarkDoneButton isDone={this.state.isDone}
                        onToggle={this.onToggleDone} />


        <Checkbox selected={this.state.selected}
                  onToggle={this.onToggleSelect} />
        <CardLine>
          <Title value={this.state.title}
                 isDone={this.state.isDone}
                 onChange={this.onTitleChange} />

          <div>
            {this.state.isDone
              ? <ShowDetailsButton expanded={this.state.expanded}
                                   onToggleExpand={this.onToggleExpand}/>
              : <AssignButton />
            }
          </div>
        </CardLine>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
