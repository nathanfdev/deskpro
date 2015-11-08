import React, { PropTypes } from 'react';

export class CalendarCellContent extends React.Component {

  static propTypes = {
    dayDate: PropTypes.object.isRequired,
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div />
    );
  }
}
