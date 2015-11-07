import React, { PropTypes } from 'react';
import { BaseTaskCard } from '../../BaseTaskCard';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        {this.props.task.get('title')}
      </div>
    );
  }
}
