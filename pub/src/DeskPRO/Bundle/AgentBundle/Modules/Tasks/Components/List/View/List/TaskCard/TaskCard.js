import React from 'react';
import { Card } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import { CardLine } from './CardLine';
import { Title } from './Title';
import { MarkDoneButton } from './MarkDoneButton';
import { ShowDetailsButton } from './ShowDetailsButton';
import { AssignButton } from './AssignButton';
import { Comments } from './Comments';
import { SubTasks } from './SubTasks';
import { Project } from './Project';
import { Due } from './Due';
import { TicketLink } from './TicketLink';

export class TaskCard extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      expanded: false,
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

  renderDetails() {
    return (
      <CardLine>
        <div>
            <Due due="N/A" />
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
      <Card minimized={!this.state.isDone} type="task">
        <MarkDoneButton isDone={this.state.isDone}
                        onToggle={this.onToggleDone} />

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

        {(this.state.expanded || !this.state.isDone) && this.renderDetails()}
      </Card>
    );
  }
}
