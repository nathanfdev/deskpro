import React, { PropTypes } from 'react';
import { Checkbox } from './Checkbox';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments
} from '../../../TaskCard/index';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        <div className="card task-card">
          <div className="card-status-bar status-bar-left" />
          <div className="card-status-bar status-bar-right" />

          <Checkbox selected={this.state.selected}
                    onToggle={this.onToggleSelect} />

          <div className="content">
            <Title value={this.state.title}
                   isDone={this.state.isDone}
                   onChange={this.onTitleChange} />

            <div className="card-line task-details">
              <div className="top-right-box">
                  <span className="assignment">
                    assigneeName
                  </span>
              </div>
              <div>
                <DateDue value={this.state.dateDue}
                         onChange={this.onChangeDate} />
              </div>
            </div>
            <hr/>
            <div className="card-line task-properties">
              <Comments count={this.state.comments} />
              {this.state.subTasks.total > 0 &&
                <SubTasks current={this.state.subTasks.current}
                          total={this.state.subTasks.total} />
              }
            </div>
          </div>
        </div>
      </div>
    );
  }
}
