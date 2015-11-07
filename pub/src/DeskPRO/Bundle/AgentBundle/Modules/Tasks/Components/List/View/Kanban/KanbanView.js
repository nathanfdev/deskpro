import React, { PropTypes } from 'react';

export class KanbanView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>KanbanView</div>
    );
  }
}
