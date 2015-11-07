import React, { PropTypes } from 'react';
import { Controls } from './ControlBar/Controls';

export class CalendarView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        <Controls />
      </div>
    );
  }
}
