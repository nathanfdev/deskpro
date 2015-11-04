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
import { TicketLink } from './TicketLink';

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

  isMinimized() {
    return !this.state.expanded && this.state.isDone;
  }

  renderDetails() {
    const { task } = this.props;

    return (
      <CardLine>
        <div>
            <DueDate date={task.get('date_due')} />
            <Project project="Some Project" />
            <TicketLink ticket="Some ticket" />
        </div>

        <div>
            <Comments count={0} />
            <SubTasks current={1} total={3} />
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
