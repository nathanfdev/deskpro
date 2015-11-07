import React, { PropTypes } from 'react';
import { Controls } from './ControlBar/Controls';
import { TaskCard } from './TaskCard/TaskCard';

export class CalendarView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        <Controls />
        {this.props.tasks.map((task, index) => <TaskCard task={task} key={index} />)}
      </div>
    );
  }
}
