import React from 'react';
import { Card } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import { CardLine } from './CardLine';
import { Title } from './Title';
import { MarkDoneButton } from './MarkDoneButton';
import { ShowDetailsButton } from './ShowDetailsButton';
import { AssignButton } from './AssignButton';
import { Comments } from './Comments';

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
            <span className="overdue dpwd--card-line-item">
              <i className="fa fa-calendar-o"/> Due: N/A
              <input type="text" name="due-date" className="due-date-field" disabled="disabled"/>
            </span>

            <span>
              <span className="dpw--card-disc"/>
              <span className="dpwd--card-line-item">
                <i className="fa fa-book"/> Some project
              </span>
            </span>

            <span>
              <span className="dpw--card-disc"/>
              <span className="dpwd--card-line-item">
                <i className="fa fa-link"/> <a href="#">Ticket title</a>
              </span>
            </span>
        </div>

        <div>
            <Comments count={0} />

            <span className="dpwd--card-line-item">
              <div>
                <span className="dpw--card-disc"/> 1/3 <i className="fa fa-folder-open"/>
              </div>
            </span>
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
