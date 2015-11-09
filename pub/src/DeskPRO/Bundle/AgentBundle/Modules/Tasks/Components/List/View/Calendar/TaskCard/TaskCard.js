import React, { PropTypes } from 'react';
import { BaseTaskCard } from '../../BaseTaskCard';
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
          <div className="dpw--card-line-left card-title">
            <div className="dpwd--card-title strikethrough">
              <h1>{task.get('title')}</h1>
            </div>
          </div>
        </div>
      </Card>
    );
  }
}
