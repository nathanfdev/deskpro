import React, { PropTypes } from 'react';

export class CalendarView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>CalendarView</div>
    );
  }
}
