import React, { PropTypes } from 'react';

export class CalendarCellDropdownCard extends React.Component {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  render() {
    const { task } = this.props;

    return (
      <li key={task.get('id')}>
        {task.get('title')}
      </li>
    );
  }
}
