import React from 'react';
import { TaskCard } from './TaskCard';
import Immutable from 'immutable';

export class TaskCardPreview extends React.Component {

  render() {

    const fields = Immutable.List();

    return (
      <div style={{ width: 400 }}>
        <TaskCard moving {...this.props} calendarFields={fields} />
      </div>
    );
  }
}
