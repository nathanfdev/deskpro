import React, { PropTypes } from 'react';

export class TaskCardPreview extends React.Component {

  static propTypes = {
    task: PropTypes.object
  };

  render() {
    const { task } = this.props;

    return (
      <div style={{backgroundColor: 'green', width: 100, height: 100}}>
        Dragging {task.get('title')}...
      </div>
    );
  }
}
