import React from 'react';
import { TaskCard } from '../../Card/TaskCard/TaskCard';

export class TaskCardPreview extends React.Component {

  render() {
    return (
      <div style={{width: 500}}>
        <TaskCard {...this.props} />
      </div>
    );
  }
}
