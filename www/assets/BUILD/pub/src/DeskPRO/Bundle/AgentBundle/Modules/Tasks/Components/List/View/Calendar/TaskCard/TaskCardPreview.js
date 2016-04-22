import React from 'react';
import { TaskCard } from './TaskCard';

export class TaskCardPreview extends React.Component {

  render() {
    return (
      <div style={{ width: 400 }}>
        <TaskCard moving {...this.props} />
      </div>
    );
  }
}
