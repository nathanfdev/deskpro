import React, { PropTypes } from 'react';
import { BaseTaskCard } from '../../BaseTaskCard';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  render() {
    const { task } = this.props;

    return (
      <div>
        <div className="card task-card">
          <div className="card-status-bar status-bar-left" />
          <div className="card-status-bar status-bar-right" />

          <div className="card-checkbox">
              <span className="checkbox">
                <i className="fa fa-check" />
              </span>
          </div>

          <div className="content">
            <h1 className="complete">{task.get('title')}</h1>
            <div className="card-line task-details">
              <div className="top-right-box">
                  <span className="assignment">
                    assigneeName
                  </span>
              </div>
              <div>
                <i className="fa fa-calendar-o" /> Due: N/A
              </div>
            </div>
            <hr/>
            <div className="card-line task-properties">
              <span>0 <i className="fa fa-comment"/></span>

                <span>
                  <span className="disc"/>
                  <div className="subtask-count">1/2 <i className="fa fa-folder-open"/></div>
                </span>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
