import React, { PropTypes } from 'react';
import { TaskCard } from './TaskCard';

export class TaskCardPreview extends React.Component {

  static propTypes = {
    width: PropTypes.number
  };

  render() {
    const { width } = this.props;

    return (
      <div style={{ width }}>
        <TaskCard moving {...this.props} />
      </div>
    );
  }
}
