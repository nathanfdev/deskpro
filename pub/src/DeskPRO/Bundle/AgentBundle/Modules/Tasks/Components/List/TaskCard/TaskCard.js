import React, { PropTypes } from 'react';
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
import { DueDate } from './DueDate';
import { TicketLinkContainer } from './TicketLinkContainer';

export class TaskCard extends React.Component {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      expanded: false,
      selected: false,
      title: 'Task title',
      dateDue: props.task.get('date_due'),
      project: 'Some Project',
      ticketLink: 'Some Ticket',
      comments: 1,
      subTasks: {
        current: 1,
        total: 3
      },
      isDone: true
    };
  }

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

  onToggleSelect = () => {
    this.setState({
      selected: !this.state.selected
    });
  };

  onChangeDate = value => {
    this.setState({
      dateDue: value
    });
  };

  isMinimized() {
    return !this.state.expanded && this.state.isDone;
  }

  renderDetails() {
    return (
      <CardLine>
        <div>
            <DueDate value={this.state.dateDue}
                     onChange={this.onChangeDate} />
            {this.state.project && <Project project={this.state.project} />}
            {this.state.ticketLink && <TicketLinkContainer ticket={this.state.ticketLink} />}
        </div>

        <div>
            <Comments count={this.state.comments} />
            <SubTasks current={this.state.subTasks.current}
                      total={this.state.subTasks.total} />
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
