import React, { PropTypes } from 'react';
import { BaseTaskCard, Title, DateDue, SubTasks, Comments } from '../../../TaskCard/index';
import { Card } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  render() {
    const { task } = this.props;

    return (
      <Card statusBars={false}
            type="floating"
            additionalClasses={`calendar-task-card-${task.get('id')}`}>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left">
            <Title value={this.state.title}
                   isDone={this.state.isDone}
                   onChange={this.onTitleChange} />
          </div>

          <div className="dpw--card-line-right">
            <div className="dpwd--card-assigned">
              <div className="dpw--avatar-face">assigneeAvatar</div>
            </div>
          </div>
        </div>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left">
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

          <div className="dpw--card-line-right">
            <Comments count={this.state.comments} />
            {this.state.subTasks.total > 0 &&
            <SubTasks current={this.state.subTasks.current}
                      total={this.state.subTasks.total} />
            }
          </div>
        </div>
      </Card>
    );
  }
}
