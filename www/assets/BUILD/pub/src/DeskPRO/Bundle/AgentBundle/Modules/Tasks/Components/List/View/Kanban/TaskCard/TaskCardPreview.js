import PropTypes from 'prop-types';
import React from 'react';
import { TaskCard } from './TaskCard';

export class TaskCardPreview extends React.Component {

  static propTypes = {
    width: PropTypes.number
  };

  render() {
    return (
      <div style={{ width: this.props.width }}>
        <TaskCard moving {...this.props} />
      </div>
    );
  }
}
