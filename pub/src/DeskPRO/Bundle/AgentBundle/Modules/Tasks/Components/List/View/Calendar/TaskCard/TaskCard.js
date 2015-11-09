import React, { PropTypes } from 'react';
import { Card } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import {
  BaseTaskCard,
  CardLine,
  Title,
  DateDue,
  SubTasks,
  Comments,
  ShowDetailsButton,
  AssignButton
} from '../../../TaskCard/index';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  renderDetails() {
    return (
      <CardLine>
        <div>
          <DateDue value={this.state.dateDue}
                   onChange={this.onChangeDate} />

            <span>
              <span className="dpw--card-disc" />
              <span className="dpwd--card-line-item">
                <i className="fa fa-book" /> Project title
              </span>
            </span>

            <span>
              <span className="dpw--card-disc" />
              <span className="dpwd--card-line-item">
                <i className="fa fa-link" /> <a href="#">Ticket title</a>
              </span>
            </span>
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
      <Card minimized={this.isMinimized()}
            statusBars={false}
            type="task"
            additionalClasses="calendar-task-card">

        <CardLine>
          <Title value={this.state.title}
                 isDone={this.state.isDone}
                 onChange={this.onTitleChange} />

          {this.state.isDone
            ? <ShowDetailsButton expanded={this.state.expanded}
                                 onToggleExpand={this.onToggleExpand}/>
            : <AssignButton />
          }
        </CardLine>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
