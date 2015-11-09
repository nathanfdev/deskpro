import React, { PropTypes } from 'react';
import { BaseTaskCard } from '../../../TaskCard/BaseTaskCard';
import { Title } from '../../../TaskCard/Title';
import { DateDue } from '../../../TaskCard/DateDue';
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
            <span className="dpwd--card-line-item">
              1 <i className="fa fa-comment" />
            </span>
            <span className="dpwd--card-line-item">
              <div><span className="dpw--card-disc" /> 1/3 <i className="fa fa-folder-open"/></div>
            </span>
          </div>
        </div>
      </Card>
    );
  }
}
