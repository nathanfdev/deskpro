import React from 'react';
import { TaskCard } from './TaskCard';

export class TaskCardPreview extends React.Component {

  render() {
    const moving = true;

    return (
      <div style={{width: 400}}>
        <TaskCard moving={moving} {...this.props} />
      </div>
    );
  }
}
