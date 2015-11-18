import React, { PropTypes } from 'react';

export class TaskCardPreview extends React.Component {

  static propTypes = {
    item: PropTypes.object
  };

  render() {
    const { item } = this.props;

    return (
      <div style={{backgroundColor: 'green', width: 100, height: 100}}>Dragging {item.id}...</div>
    );
  }
}
